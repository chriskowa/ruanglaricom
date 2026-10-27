<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\RunnerWaSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;

class WaSubscriptionController extends Controller
{
    private const PRICE = 20000;
    private const DURATION_DAYS = 30;

    /**
     * Initiate Midtrans Snap checkout for WA Program Reminder subscription.
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Anda harus login terlebih dahulu.',
            ], 401);
        }

        if (empty($user->phone)) {
            return response()->json([
                'ok' => false,
                'message' => 'Silakan lengkapi nomor WhatsApp/HP di profil Anda terlebih dahulu.',
                'need_phone' => true,
            ], 422);
        }

        $orderId = 'WA-SUB-' . $user->id . '-' . time();

        $sub = RunnerWaSubscription::create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'amount' => self::PRICE,
            'status' => 'pending',
            'meta' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);

        // If in testing mode, auto approve
        if (config('midtrans.testing_mode')) {
            $from = now();
            $baseDate = ($user->wa_subscription_expires_at && $user->wa_subscription_expires_at->isFuture())
                ? $user->wa_subscription_expires_at
                : now();
            $until = $baseDate->copy()->addDays(self::DURATION_DAYS);

            $sub->update([
                'status' => 'paid',
                'paid_at' => now(),
                'subscribed_from' => $from,
                'subscribed_until' => $until,
                'payment_method' => 'testing',
                'midtrans_transaction_status' => 'settlement',
            ]);

            $user->update([
                'wa_subscription_expires_at' => $until,
                'is_receive_wa' => true,
            ]);

            return response()->json([
                'ok' => true,
                'status' => 'success',
                'message' => 'Langganan WhatsApp 30 hari berhasil diaktifkan (Mode Testing).',
                'testing' => true,
                'order_id' => $orderId,
                'expires_at' => $until->translatedFormat('d F Y'),
            ]);
        }

        // Midtrans Snap configuration
        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('midtrans.is_production');
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => self::PRICE,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'item_details' => [
                [
                    'id' => 'WA-REMINDER-30D',
                    'price' => self::PRICE,
                    'quantity' => 1,
                    'name' => 'Langganan WA Program Harian (30 Hari)',
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $sub->update([
                'snap_token' => $snapToken,
            ]);

            return response()->json([
                'ok' => true,
                'status' => 'pending',
                'snap_token' => $snapToken,
                'order_id' => $orderId,
                'amount' => self::PRICE,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to generate Midtrans Snap token for WA subscription: " . $e->getMessage());
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menghubungkan ke gateway pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm payment status after Midtrans popup closes.
     */
    public function confirm(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        $sub = RunnerWaSubscription::where('order_id', $data['order_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$sub) {
            return response()->json([
                'ok' => false,
                'message' => 'Transaksi langganan tidak ditemukan.',
            ], 404);
        }

        if ($sub->status === 'paid') {
            return response()->json([
                'ok' => true,
                'status' => 'paid',
                'message' => 'Langganan WhatsApp aktif.',
                'expires_at' => $sub->subscribed_until ? $sub->subscribed_until->translatedFormat('d F Y') : null,
            ]);
        }

        if (config('midtrans.testing_mode')) {
            $from = now();
            $user = $sub->user;
            $baseDate = ($user->wa_subscription_expires_at && $user->wa_subscription_expires_at->isFuture())
                ? $user->wa_subscription_expires_at
                : now();
            $until = $baseDate->copy()->addDays(self::DURATION_DAYS);

            $sub->update([
                'status' => 'paid',
                'paid_at' => now(),
                'subscribed_from' => $from,
                'subscribed_until' => $until,
                'midtrans_transaction_status' => 'settlement',
            ]);

            $user->update([
                'wa_subscription_expires_at' => $until,
                'is_receive_wa' => true,
            ]);

            return response()->json([
                'ok' => true,
                'status' => 'paid',
                'message' => 'Langganan WhatsApp aktif.',
                'expires_at' => $until->translatedFormat('d F Y'),
            ]);
        }

        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('midtrans.is_production');

        try {
            $status = MidtransTransaction::status($data['order_id']);
            $transactionStatus = (string) ($status->transaction_status ?? '');
            $fraudStatus = (string) ($status->fraud_status ?? '');
            $paymentType = (string) ($status->payment_type ?? '');

            $isPaid = in_array($transactionStatus, ['capture', 'settlement'], true)
                && ($fraudStatus === '' || $fraudStatus === 'accept');

            if ($isPaid) {
                $from = now();
                $user = $sub->user;
                $baseDate = ($user->wa_subscription_expires_at && $user->wa_subscription_expires_at->isFuture())
                    ? $user->wa_subscription_expires_at
                    : now();
                $until = $baseDate->copy()->addDays(self::DURATION_DAYS);

                $sub->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'subscribed_from' => $from,
                    'subscribed_until' => $until,
                    'payment_method' => $paymentType,
                    'midtrans_transaction_status' => $transactionStatus,
                ]);

                $user->update([
                    'wa_subscription_expires_at' => $until,
                    'is_receive_wa' => true,
                ]);

                return response()->json([
                    'ok' => true,
                    'status' => 'paid',
                    'message' => 'Pembayaran berhasil! Notifikasi WhatsApp aktif hingga ' . $until->translatedFormat('d F Y') . '.',
                    'expires_at' => $until->translatedFormat('d F Y'),
                ]);
            }

            $sub->update([
                'midtrans_transaction_status' => $transactionStatus,
                'payment_method' => $paymentType ?: $sub->payment_method,
            ]);

            return response()->json([
                'ok' => true,
                'status' => $transactionStatus ?: 'pending',
                'message' => 'Status pembayaran: ' . ($transactionStatus ?: 'pending'),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed checking Midtrans status for {$data['order_id']}: " . $e->getMessage());
            return response()->json([
                'ok' => false,
                'message' => 'Gagal memverifikasi status pembayaran.',
            ], 500);
        }
    }

    /**
     * Get transaction history for current runner.
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $subscriptions = RunnerWaSubscription::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'order_id' => $sub->order_id,
                    'amount' => (int) $sub->amount,
                    'amount_formatted' => 'Rp ' . number_format($sub->amount, 0, ',', '.'),
                    'status' => $sub->status,
                    'payment_method' => $sub->payment_method ?? 'Midtrans',
                    'paid_at' => $sub->paid_at ? $sub->paid_at->translatedFormat('d M Y H:i') : null,
                    'subscribed_from' => $sub->subscribed_from ? $sub->subscribed_from->translatedFormat('d M Y') : null,
                    'subscribed_until' => $sub->subscribed_until ? $sub->subscribed_until->translatedFormat('d M Y') : null,
                    'created_at' => $sub->created_at->translatedFormat('d M Y H:i'),
                ];
            });

        return response()->json([
            'ok' => true,
            'is_whitelisted' => (bool) $user->is_wa_whitelisted,
            'is_receive_wa' => (bool) $user->is_receive_wa,
            'expires_at' => $user->wa_subscription_expires_at ? $user->wa_subscription_expires_at->translatedFormat('d F Y') : null,
            'is_active' => $user->hasActiveWaSubscription(),
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Handle incoming Midtrans Webhook for WA-SUB-* transactions.
     */
    public function webhook(Request $request)
    {
        $orderId = (string) $request->input('order_id', '');
        $transactionStatus = (string) $request->input('transaction_status', '');
        $fraudStatus = (string) $request->input('fraud_status', '');
        $paymentType = (string) $request->input('payment_type', '');

        Log::info("WA Subscription Midtrans Webhook received: {$orderId} status: {$transactionStatus}");

        $sub = RunnerWaSubscription::where('order_id', $orderId)->first();
        if (!$sub) {
            return response()->json(['message' => 'Subscription record not found'], 404);
        }

        $isPaid = in_array($transactionStatus, ['capture', 'settlement'], true)
            && ($fraudStatus === '' || $fraudStatus === 'accept');

        if ($isPaid) {
            $from = now();
            $user = $sub->user;
            $baseDate = ($user && $user->wa_subscription_expires_at && $user->wa_subscription_expires_at->isFuture())
                ? $user->wa_subscription_expires_at
                : now();
            $until = $baseDate->copy()->addDays(self::DURATION_DAYS);

            $sub->update([
                'status' => 'paid',
                'paid_at' => now(),
                'subscribed_from' => $from,
                'subscribed_until' => $until,
                'payment_method' => $paymentType ?: $sub->payment_method,
                'midtrans_transaction_status' => $transactionStatus,
            ]);

            if ($user) {
                $user->update([
                    'wa_subscription_expires_at' => $until,
                    'is_receive_wa' => true,
                ]);
            }

            return response()->json(['message' => 'Subscription activated successfully']);
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'expire'], true)) {
            $sub->update([
                'status' => 'failed',
                'midtrans_transaction_status' => $transactionStatus,
            ]);
            return response()->json(['message' => 'Subscription marked failed/cancelled']);
        }

        $sub->update([
            'midtrans_transaction_status' => $transactionStatus,
            'payment_method' => $paymentType ?: $sub->payment_method,
        ]);

        return response()->json(['message' => 'Subscription status updated']);
    }
}
