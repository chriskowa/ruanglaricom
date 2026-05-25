<?php

namespace App\Jobs;

use App\Helpers\WhatsApp;
use App\Models\EventEmailDeliveryLog;
use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCustomWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $participantId,
        protected string $messageTemplate
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $participant = Participant::with(['transaction.event', 'category'])->find($this->participantId);
        if (!$participant) {
            return;
        }

        $transaction = $participant->transaction;
        if (!$transaction) {
            return;
        }

        $event = $transaction->event;
        if (!$event) {
            return;
        }

        $phone = $participant->phone ?: ($transaction->pic_data['phone'] ?? $transaction->user->phone ?? null);
        if (!$phone) {
            Log::info("Custom WA Reminder: No phone number for participant {$participant->id}");
            return;
        }

        // Normalisasi nomor WhatsApp ke format 62XXXXXXXX
        $normalized = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($normalized, '0')) {
            $normalized = '62'.substr($normalized, 1);
        } elseif (! str_starts_with($normalized, '62')) {
            $normalized = '62'.$normalized;
        }
        $phone = $normalized;

        // Link Pembayaran
        $link = route('events.payments.continue', $event->slug);

        // Replace placeholders
        $search = ['{name}', '{event}', '{status}', '{link}'];
        $replace = [
            $participant->name,
            $event->name,
            strtoupper($transaction->payment_status ?: $participant->status),
            $link
        ];
        $message = str_replace($search, $replace, $this->messageTemplate);

        try {
            WhatsApp::send($phone, $message);

            EventEmailDeliveryLog::create([
                'event_id' => $event->id,
                'transaction_id' => $transaction->id,
                'context' => 'custom_whatsapp_reminder',
                'channel' => 'whatsapp',
                'to' => $phone,
                'status' => 'sent',
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send custom WA reminder to participant {$participant->id}: " . $e->getMessage());

            EventEmailDeliveryLog::create([
                'event_id' => $event->id,
                'transaction_id' => $transaction->id,
                'context' => 'custom_whatsapp_reminder',
                'channel' => 'whatsapp',
                'to' => $phone,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
