<?php

namespace App\Jobs;

use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class SendEventRegistrationNotification implements ShouldQueue
{
    use Queueable;

    protected $transaction;

    /**
     * Create a new job instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Reload transaction with relationships
            $this->transaction->refresh();
            $this->transaction->load(['participants.category', 'event.user', 'coupon']);

            $event = $this->transaction->event;
            $picData = $this->transaction->pic_data;
            $participants = $this->transaction->participants;

            // Send email to PIC
            if (!empty($picData['email'])) {
                $this->sendEmailNotification($picData['email'], $event, $participants);
            }

            // Send WhatsApp notification to PIC (if phone number available)
            if (!empty($picData['phone'])) {
                $this->sendWhatsAppNotification($picData['phone'], $event, $participants);
            }

            // Send email to each participant
            foreach ($participants as $participant) {
                if (!empty($participant->email) && $participant->email !== $picData['email']) {
                    $this->sendEmailNotification($participant->email, $event, collect([$participant]));
                }
            }

            Log::info('SendEventRegistrationNotification completed', [
                'transaction_id' => $this->transaction->id,
            ]);

        } catch (\Exception $e) {
            Log::error('SendEventRegistrationNotification failed', [
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't throw exception to prevent job retry loops
            // Just log the error
        }
    }

    /**
     * Send email notification
     */
    protected function sendEmailNotification(string $email, $event, $participants): void
    {
        try {
            // For now, use simple mail. You can create a Mailable class later
            $subject = "Konfirmasi Registrasi: {$event->name}";
            $message = $this->buildEmailMessage($event, $participants);

            Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)
                     ->subject($subject);
            });

        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'email' => $email,
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send WhatsApp notification
     * Note: You'll need to integrate with WhatsApp API (e.g., Twilio, WhatsApp Business API)
     */
    protected function sendWhatsAppNotification(string $phone, $event, $participants): void
    {
        try {
            // Format phone number (remove +, spaces, etc.)
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            // For now, just log. You can integrate with WhatsApp API later
            // Example: Twilio, WhatsApp Business API, etc.
            $message = $this->buildWhatsAppMessage($event, $participants);
            
            Log::info('WhatsApp notification prepared', [
                'phone' => $phone,
                'transaction_id' => $this->transaction->id,
                'message_preview' => substr($message, 0, 100),
            ]);

            // TODO: Integrate with WhatsApp API
            // Example:
            // $whatsappService->send($phone, $message);

        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification', [
                'phone' => $phone,
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build email message content
     */
    protected function buildEmailMessage($event, $participants): string
    {
        $message = "Terima kasih telah mendaftar di event {$event->name}!\n\n";
        $message .= "Detail Registrasi:\n";
        $message .= "Event: {$event->name}\n";
        $message .= "Tanggal: " . ($event->start_at ? $event->start_at->format('d F Y H:i') : 'TBA') . "\n";
        $message .= "Lokasi: {$event->location_name}\n";
        $message .= "Total Pembayaran: Rp " . number_format($this->transaction->final_amount, 0, ',', '.') . "\n\n";
        
        $message .= "Peserta yang Terdaftar:\n";
        foreach ($participants as $participant) {
            $message .= "- {$participant->name}";
            if ($participant->category) {
                $message .= " ({$participant->category->name})";
            }
            if ($participant->bib_number) {
                $message .= " - BIB: {$participant->bib_number}";
            }
            $message .= "\n";
        }
        
        $message .= "\n";
        $message .= "Status Pembayaran: " . strtoupper($this->transaction->payment_status) . "\n";
        
        if ($this->transaction->payment_status === 'paid') {
            $message .= "\nPembayaran Anda telah dikonfirmasi. Silakan cek email untuk informasi lebih lanjut.\n";
        } else {
            $message .= "\nSilakan selesaikan pembayaran Anda untuk menyelesaikan registrasi.\n";
        }
        
        $message .= "\nTerima kasih!";

        return $message;
    }

    /**
     * Build WhatsApp message content
     */
    protected function buildWhatsAppMessage($event, $participants): string
    {
        $message = "✅ *Registrasi Berhasil!*\n\n";
        $message .= "Event: *{$event->name}*\n";
        $message .= "Tanggal: " . ($event->start_at ? $event->start_at->format('d F Y H:i') : 'TBA') . "\n";
        $message .= "Lokasi: {$event->location_name}\n\n";
        
        $message .= "Total: Rp " . number_format($this->transaction->final_amount, 0, ',', '.') . "\n";
        $message .= "Status: " . strtoupper($this->transaction->payment_status) . "\n\n";
        
        if ($participants->count() > 0) {
            $message .= "Peserta:\n";
            foreach ($participants as $participant) {
                $message .= "• {$participant->name}";
                if ($participant->category) {
                    $message .= " ({$participant->category->name})";
                }
                if ($participant->bib_number) {
                    $message .= " - BIB: {$participant->bib_number}";
                }
                $message .= "\n";
            }
        }
        
        $message .= "\nTerima kasih! 🏃‍♂️";

        return $message;
    }
}
