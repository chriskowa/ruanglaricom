<?php

namespace App\Jobs;

use App\Helpers\WhatsApp;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
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
            if (! empty($picData['email'])) {
                $this->sendEmailNotification($picData['email'], $event, $participants);
            }

            // Send WhatsApp notification to PIC (if phone number available)
            if (! empty($picData['phone'])) {
                $this->sendWhatsAppNotification($picData['phone'], $event, $participants);
            }

            // Send email to each participant
            foreach ($participants as $participant) {
                if (! empty($participant->email) && $participant->email !== $picData['email']) {
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
            $notifiableName = $participants->first()->name ?? 'Peserta';
            
            // If sending to PIC, use PIC name
            if ($email === ($this->transaction->pic_data['email'] ?? '')) {
                $notifiableName = $this->transaction->pic_data['name'] ?? $notifiableName;
            }

            Mail::to($email)->send(new \App\Mail\EventRegistrationSuccess(
                $event, 
                $this->transaction, 
                $participants, 
                $notifiableName
            ));

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
            // Normalisasi nomor WhatsApp ke format 62XXXXXXXX (hilangkan non-digit, ubah leading 0 -> 62, pastikan prefix 62)
            $normalized = preg_replace('/\D+/', '', $phone);
            if (str_starts_with($normalized, '0')) {
                $normalized = '62'.substr($normalized, 1);
            } elseif (! str_starts_with($normalized, '62')) {
                $normalized = '62'.$normalized;
            }
            $phone = $normalized;

            $message = $this->buildWhatsAppMessage($event, $participants);

            Log::info('WhatsApp notification prepared', [
                'phone' => $phone,
                'transaction_id' => $this->transaction->id,
                'message_preview' => substr($message, 0, 100),
            ]);

            WhatsApp::send($phone, $message);

        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification', [
                'phone' => $phone,
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build WhatsApp message content
     */
    protected function buildWhatsAppMessage($event, $participants): string
    {
        $message = "✅ *Registrasi Berhasil!*\n\n";
        $message .= "Event: *{$event->name}*\n";
        $message .= 'Tanggal: '.($event->start_at ? $event->start_at->format('d F Y H:i') : 'TBA')."\n";
        $message .= "Lokasi: {$event->location_name}\n\n";

        $message .= 'Total: Rp '.number_format($this->transaction->final_amount, 0, ',', '.')."\n";
        $message .= 'Status: '.strtoupper($this->transaction->payment_status)."\n\n";

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

        if ($this->transaction->payment_status === 'paid') {
            $message .= "\nTerima kasih sudah menyelesaikan registrasi. Sampai jumpa di lokasi pada tanggal dan waktu tertera.\n";
        } elseif ($this->transaction->payment_status === 'cod') {
            $message .= "\nMetode Pembayaran: *COD*\nSilakan melakukan pembayaran di lokasi saat pengambilan race pack. Nomor tiket akan dikonfirmasi setelah pembayaran.\n";
        }

        $createdUsers = collect($this->transaction->pic_data['created_users'] ?? []);
        if ($createdUsers->count() > 0) {
            $loginUrl = route('login');
            $message .= "\nAkses akun baru:\n";
            foreach ($createdUsers as $email) {
                $pwd = Cache::get('new_user_password:'.$email);
                if ($pwd) {
                    $message .= "• Login: {$loginUrl}\n  Email: {$email}\n  Password: {$pwd}\n";
                }
            }
        }

        $message .= "\nTerima kasih! 🏃‍♂️";

        return $message;
    }
}
