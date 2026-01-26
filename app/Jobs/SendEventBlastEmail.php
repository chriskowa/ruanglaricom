<?php

namespace App\Jobs;

use App\Mail\EventBlastEmail;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEventBlastEmail implements ShouldQueue
{
    use Queueable;

    protected $event;
    protected $subject;
    protected $content;
    protected $filters;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event, string $subject, string $content, array $filters = [])
    {
        $this->event = $event;
        $this->subject = $subject;
        $this->content = $content;
        $this->filters = $filters;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting blast email for event: ' . $this->event->name);

        $query = Participant::whereHas('transaction', function ($q) {
            $q->where('event_id', $this->event->id)
              ->where('payment_status', 'paid');
        });

        // Apply filters
        if (!empty($this->filters['category_id'])) {
            $query->where('race_category_id', $this->filters['category_id']);
        }
        
        // Chunk results to handle large datasets
        $query->chunk(100, function ($participants) {
            foreach ($participants as $participant) {
                try {
                    Mail::to($participant->email)->send(new EventBlastEmail(
                        $this->event,
                        $this->subject,
                        $this->content,
                        $participant->name
                    ));
                } catch (\Exception $e) {
                    Log::error('Failed to send blast email to ' . $participant->email . ': ' . $e->getMessage());
                }
            }
        });

        Log::info('Blast email completed for event: ' . $this->event->name);
    }
}
