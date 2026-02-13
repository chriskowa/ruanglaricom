<?php

namespace App\Jobs;

use App\Helpers\WhatsApp;
use App\Mail\PendingPaymentReminder;
use App\Models\EventEmailDeliveryLog;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPendingPaymentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Transaction $transaction
    ) {}

    public function handle(): void
    {
        $this->transaction->loadMissing('event');
        $event = $this->transaction->event;

        // Throttling check (double check inside job to be safe)
        if ($this->transaction->payment_status !== 'pending') {
            return;
        }

        if ($this->transaction->pending_reminder_last_sent_at && 
            $this->transaction->pending_reminder_last_sent_at->diffInHours(now()) < 24) {
            Log::info("Skipping reminder for transaction {$this->transaction->id}: already sent < 24h ago");
            return;
        }

        $picData = $this->transaction->pic_data;
        $email = $picData['email'] ?? null;
        $phone = $picData['phone'] ?? null;
        $name = $picData['name'] ?? 'Peserta';
        $link = route('events.payments.continue', $event->slug);

        // 1. Send Email
        if ($email) {
            try {
                Mail::to($email)->send(new PendingPaymentReminder($this->transaction));
                
                EventEmailDeliveryLog::create([
                    'event_id' => $event->id,
                    'transaction_id' => $this->transaction->id,
                    'context' => 'pending_payment_reminder',
                    'channel' => 'email',
                    'to' => $email,
                    'status' => 'sent',
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send pending payment reminder email: " . $e->getMessage());
                EventEmailDeliveryLog::create([
                    'event_id' => $event->id,
                    'transaction_id' => $this->transaction->id,
                    'context' => 'pending_payment_reminder',
                    'channel' => 'email',
                    'to' => $email,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        // 2. Send WhatsApp
        if ($phone) {
            try {
                $message = "Halo {$name},\n\n";
                $message .= "Kami menunggu pembayaran Anda untuk event *{$event->name}*.\n\n";
                $message .= "ID Transaksi: *{$this->transaction->public_ref}*\n";
                $message .= "Total: Rp " . number_format($this->transaction->final_amount, 0, ',', '.') . "\n\n";
                $message .= "Mohon segera selesaikan pembayaran melalui link berikut:\n";
                $message .= "{$link}\n\n";
                $message .= "Terima kasih.";

                WhatsApp::send($phone, $message);

                EventEmailDeliveryLog::create([
                    'event_id' => $event->id,
                    'transaction_id' => $this->transaction->id,
                    'context' => 'pending_payment_reminder',
                    'channel' => 'whatsapp',
                    'to' => $phone,
                    'status' => 'sent',
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send pending payment reminder WA: " . $e->getMessage());
                EventEmailDeliveryLog::create([
                    'event_id' => $event->id,
                    'transaction_id' => $this->transaction->id,
                    'context' => 'pending_payment_reminder',
                    'channel' => 'whatsapp',
                    'to' => $phone,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        // 3. Update Transaction
        $this->transaction->update([
            'pending_reminder_last_sent_at' => now(),
            'pending_reminder_count' => $this->transaction->pending_reminder_count + 1,
            'pending_reminder_last_channel' => $email && $phone ? 'email_wa' : ($email ? 'email' : 'wa'),
        ]);
    }
}
