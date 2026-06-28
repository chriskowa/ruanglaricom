<?php

namespace App\Jobs;

use App\Helpers\WhatsApp;
use App\Mail\ProgramOrderPaid;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ProcessPaidProgramOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $orderId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::with(['items.program', 'user'])->find($this->orderId);

        if (!$order) {
            Log::error("ProcessPaidProgramOrder: Order ID {$this->orderId} not found.");
            return;
        }

        // 1. Send Email Notification
        try {
            Mail::to($order->user->email)->send(new ProgramOrderPaid($order));
            Log::info("ProcessPaidProgramOrder: Email sent to {$order->user->email} for Order #{$order->order_number}");
        } catch (\Throwable $e) {
            Log::error("ProcessPaidProgramOrder: Failed to send email to {$order->user->email}. Error: " . $e->getMessage());
        }

        // 2. Send WhatsApp Notification
        try {
            $phone = $order->user->phone;
            if ($phone) {
                $messageLines = [
                    "🏃‍♂️ *PEMBELIAN PROGRAM SUKSES!*",
                    "Halo, Kak *{$order->user->name}*! Pembayaran untuk pembelian program latihan Anda telah berhasil kami terima.",
                    "",
                    "📝 *Detail Pesanan:*",
                    "- Invoice: {$order->order_number}",
                    "- Tanggal: " . $order->created_at->format('d M Y H:i'),
                    "- Total Bayar: Rp " . number_format($order->total, 0, ',', '.'),
                    "",
                    "📋 *Program yang Dibeli:*",
                ];

                foreach ($order->items as $item) {
                    $messageLines[] = "- {$item->program_title} (Rp " . number_format($item->price, 0, ',', '.') . ")";
                }

                $messageLines[] = "";
                $messageLines[] = "🔑 *Akses Latihan:*";
                $messageLines[] = "- Email Anda: {$order->user->email}";
                $messageLines[] = "- Link Login: " . route('login');
                $messageLines[] = "- Link Dashboard: " . route('runner.dashboard');
                $messageLines[] = "";
                $messageLines[] = "Silakan login untuk mengakses jadwal dan detail latihan harian Anda. Selamat berlatih, tetap konsisten, dan raih performa terbaik Anda!";

                $message = implode("\n", $messageLines);

                WhatsApp::send($phone, $message);
                Log::info("ProcessPaidProgramOrder: WhatsApp message sent to {$phone} for Order #{$order->order_number}");
            } else {
                Log::warning("ProcessPaidProgramOrder: User {$order->user->email} has no phone number, WhatsApp skipped.");
            }
        } catch (\Throwable $e) {
            Log::error("ProcessPaidProgramOrder: Failed to send WhatsApp to {$order->user->phone}. Error: " . $e->getMessage());
        }
    }
}
