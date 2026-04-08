<?php

namespace App\Jobs;

use App\Mail\EventCampaignEmail;
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
use Illuminate\Support\Facades\Mail;

class SendEventCampaignDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    public function __construct(
        public EventEmailDelivery $delivery,
        public EventEmailCampaign $campaign,
        public Participant $participant
    ) {}

    public function handle(): void
    {
        if ($this->delivery->status === 'sent') {
            return;
        }

        try {
            $this->delivery->increment('attempts');
            $this->delivery->update(['status' => 'processing']);

            // Parse placeholders in subject
            $subjectLine = str_replace(
                ['{{name}}', '{{bib}}'],
                [$this->participant->name, $this->participant->bib_number],
                $this->campaign->subject
            );

            // Send Mail
            Mail::to($this->delivery->to_email, $this->delivery->to_name)
                ->send(new EventCampaignEmail(
                    $this->campaign->event,
                    $this->participant,
                    $subjectLine,
                    $this->campaign->preset_template,
                    $this->campaign->content ?? []
                ));

            // Mark sent
            $this->delivery->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $this->campaign->increment('sent_count');

        } catch (Exception $e) {
            $this->delivery->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Campaign Delivery Failed: {$this->delivery->id} - " . $e->getMessage());
            throw $e;
        }
    }
}
