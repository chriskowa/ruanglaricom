<?php

namespace App\Jobs;

use App\Models\EventEmailCampaign;
use App\Models\EventEmailDelivery;
use App\Models\Participant;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ProcessEventEmailCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public function __construct(
        public EventEmailCampaign $campaign
    ) {}

    public function handle(): void
    {
        if ($this->campaign->status === 'completed' || $this->campaign->status === 'paused') {
            return;
        }

        $this->campaign->update(['status' => 'processing']);
        $event = $this->campaign->event;

        try {
            // Build Query based on filters
            $query = Participant::whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id);
                
                // Filter by payment status if specified
                $filters = $this->campaign->filters ?? [];
                if (!empty($filters['payment_status'])) {
                    $q->whereIn('payment_status', $filters['payment_status']);
                } else {
                    // Default to paid/settlement
                    $q->whereIn('payment_status', ['paid', 'settlement', 'capture', 'cod']);
                }
            });

            $totalTargets = $query->count();
            $this->campaign->update(['target_count' => $totalTargets]);

            // Process in chunks
            $query->chunk(100, function ($participants) {
                foreach ($participants as $participant) {
                    $email = trim(strtolower($participant->email));
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        continue;
                    }

                    // Create delivery record (idempotent due to unique constraint)
                    try {
                        $delivery = EventEmailDelivery::firstOrCreate(
                            [
                                'event_email_campaign_id' => $this->campaign->id,
                                'to_email' => $email,
                            ],
                            [
                                'participant_id' => $participant->id,
                                'to_name' => $participant->name,
                                'status' => 'pending',
                                'scheduled_at' => now(),
                            ]
                        );

                        // If pending, dispatch email job
                        if ($delivery->status === 'pending') {
                            // Using a rate limiter logic (optional but recommended for SES/Mailgun limits)
                            Redis::throttle('event_email_campaigns')
                                ->allow(10) // 10 emails
                                ->every(1) // per second
                                ->then(function () use ($delivery, $participant) {
                                    $delivery->update(['status' => 'queued']);
                                    SendEventCampaignDelivery::dispatch($delivery, $this->campaign, $participant)
                                        ->onQueue('emails-blast');
                                }, function () use ($delivery, $participant) {
                                    // If rate limited, delay slightly
                                    $delivery->update(['status' => 'queued']);
                                    SendEventCampaignDelivery::dispatch($delivery, $this->campaign, $participant)
                                        ->delay(now()->addSeconds(rand(5, 30)))
                                        ->onQueue('emails-blast');
                                });
                        }
                    } catch (\Exception $e) {
                        Log::error("Error queuing delivery for campaign {$this->campaign->id} email {$email}: " . $e->getMessage());
                    }
                }
            });

            // Mark campaign as completed (it only queues the deliveries, the actual sending happens in SendEventCampaignDelivery)
            $this->campaign->update(['status' => 'completed']);

        } catch (Exception $e) {
            $this->campaign->update(['status' => 'failed']);
            Log::error("Campaign Process Failed: {$this->campaign->id} - " . $e->getMessage());
            throw $e;
        }
    }
}
