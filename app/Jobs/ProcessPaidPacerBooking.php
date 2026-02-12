<?php

namespace App\Jobs;

use App\Helpers\WhatsApp;
use App\Mail\PacerBookingPaid;
use App\Models\Notification;
use App\Models\PacerBooking;
use App\Models\WalletTransaction;
use App\Services\PlatformWalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcessPaidPacerBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $bookingId) {}

    public function handle(): void
    {
        $booking = PacerBooking::with('pacer.user', 'runner')->find($this->bookingId);
        if (! $booking) {
            return;
        }

        if ($booking->status !== 'paid') {
            return;
        }

        $metadata = is_array($booking->metadata) ? $booking->metadata : [];
        if (! empty($metadata['escrow_locked_at'])) {
            return;
        }

        DB::transaction(function () use ($booking, $metadata) {
            $platformWalletService = app(PlatformWalletService::class);
            $platformWallet = $platformWalletService->getPlatformWallet();
            $platformWallet->refresh();

            $lockedBefore = (float) $platformWallet->locked_balance;
            $amount = (float) $booking->total_amount;
            $platformWallet->locked_balance = $lockedBefore + $amount;
            $platformWallet->save();

            WalletTransaction::create([
                'wallet_id' => $platformWallet->id,
                'type' => 'pacer_booking_escrow_lock',
                'amount' => $amount,
                'balance_before' => (float) $platformWallet->balance,
                'balance_after' => (float) $platformWallet->balance,
                'status' => 'completed',
                'reference_type' => PacerBooking::class,
                'reference_id' => $booking->id,
                'description' => 'Escrow locked for booking '.$booking->invoice_number,
                'metadata' => [
                    'invoice_number' => $booking->invoice_number,
                    'locked_before' => $lockedBefore,
                    'locked_after' => (float) $platformWallet->locked_balance,
                ],
                'processed_at' => now(),
            ]);

            $metadata['escrow_locked_at'] = now()->toISOString();
            $booking->metadata = $metadata;
            $booking->save();
        });

        $booking->refresh();
        $booking->load('pacer.user', 'runner');

        Notification::create([
            'user_id' => $booking->pacer->user->id,
            'type' => 'pacer_booking',
            'title' => 'Booking Baru (Paid)',
            'message' => 'Ada booking baru yang sudah dibayar. Invoice: '.$booking->invoice_number,
            'reference_type' => PacerBooking::class,
            'reference_id' => $booking->id,
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $booking->runner->id,
            'type' => 'pacer_booking',
            'title' => 'Booking Berhasil Dibayar',
            'message' => 'Booking pacer sudah dibayar. Menunggu konfirmasi pacer. Invoice: '.$booking->invoice_number,
            'reference_type' => PacerBooking::class,
            'reference_id' => $booking->id,
            'is_read' => false,
        ]);

        try {
            Mail::to($booking->pacer->user->email)->send(new PacerBookingPaid($booking));
        } catch (\Throwable $e) {
        }

        try {
            $wa = $booking->pacer->whatsapp ?: ($booking->pacer->user->phone ?? null);
            if ($wa) {
                $messageLines = [
                    '*Booking Paid*',
                    'Invoice: '.$booking->invoice_number,
                    'Runner: '.($booking->runner->name ?? '-'),
                    'Race: '.($booking->event_name ?: '-'),
                    'Tanggal: '.($booking->race_date ? $booking->race_date->format('Y-m-d') : '-'),
                    'Jarak: '.($booking->distance ?: '-'),
                    'Target pace: '.($booking->target_pace ?: '-'),
                    'Meeting: '.($booking->meeting_point ?: '-'),
                ];
                WhatsApp::send($wa, implode("\n", $messageLines));
            }
        } catch (\Throwable $e) {
        }
    }
}
