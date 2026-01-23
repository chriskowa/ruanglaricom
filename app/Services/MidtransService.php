<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Transaction as EventTransaction;
use App\Models\User;
use App\Models\WalletTopup;
use App\Models\MembershipTransaction;
use Illuminate\Http\Request;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        // Set Midtrans configuration
        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
    }

    /**
     * Create top-up transaction
     */
    public function createTopupTransaction(User $user, float $amount): array
    {
        // Create wallet topup record
        $topup = WalletTopup::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_method' => config('midtrans.testing_mode') ? 'testing' : 'midtrans',
            'status' => 'pending',
        ]);

        // Testing mode: Auto approve payment
        if (config('midtrans.testing_mode')) {
            $orderId = 'TEST-'.$topup->id.'-'.time();

            // Update topup as paid immediately
            $topup->update([
                'midtrans_order_id' => $orderId,
                'midtrans_transaction_status' => 'settlement',
                'status' => 'success',
            ]);

            // Add balance to user wallet
            if ($user->wallet) {
                $balanceBefore = $user->wallet->balance;
                $user->wallet->increment('balance', $amount);
                $balanceAfter = $user->wallet->balance;

                // Create wallet transaction record
                $user->wallet->transactions()->create([
                    'type' => 'deposit',
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'status' => 'completed',
                    'description' => 'Top up via Testing Mode',
                    'reference_id' => $topup->id,
                    'reference_type' => WalletTopup::class,
                    'metadata' => [
                        'testing_mode' => true,
                        'order_id' => $orderId,
                    ],
                    'processed_at' => now(),
                ]);

                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'wallet_deposit',
                    'title' => 'Topup Berhasil',
                    'message' => 'Topup Rp ' . number_format($amount, 0, ',', '.') . ' berhasil masuk ke wallet.',
                    'reference_type' => WalletTopup::class,
                    'reference_id' => $topup->id,
                    'is_read' => false,
                ]);

                $adminIds = User::query()->where('role', 'admin')->pluck('id');
                foreach ($adminIds as $adminId) {
                    Notification::create([
                        'user_id' => $adminId,
                        'type' => 'wallet_deposit',
                        'title' => 'Deposit Masuk (Topup)',
                        'message' => ($user->name ?? 'User') . ' topup Rp ' . number_format($amount, 0, ',', '.') . ' (Testing Mode).',
                        'reference_type' => WalletTopup::class,
                        'reference_id' => $topup->id,
                        'is_read' => false,
                    ]);
                }
            }

            return [
                'success' => true,
                'snap_token' => null, // No snap token in testing mode
                'topup_id' => $topup->id,
                'order_id' => $orderId,
                'testing_mode' => true,
            ];
        }

        // Production/Sandbox mode: Use Midtrans
        // Prepare transaction parameters
        $params = [
            'transaction_details' => [
                'order_id' => 'TOPUP-'.$topup->id.'-'.time(),
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => 'TOPUP',
                    'price' => $amount,
                    'quantity' => 1,
                    'name' => 'Top Up Wallet',
                ],
            ],
        ];

        try {
            // Get Snap Token from Midtrans
            $snapToken = Snap::getSnapToken($params);

            // Update topup with order ID
            $topup->update([
                'midtrans_order_id' => $params['transaction_details']['order_id'],
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'topup_id' => $topup->id,
                'order_id' => $params['transaction_details']['order_id'],
            ];
        } catch (\Exception $e) {
            $topup->markAsFailed();

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook/callback from Midtrans
     */
    public function handleWebhook(Request $request): array
    {
        $orderId = $request->input('order_id');
        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status');

        // Find topup by order ID
        $topup = WalletTopup::where('midtrans_order_id', $orderId)->first();

        if (! $topup) {
            return [
                'success' => false,
                'message' => 'Topup not found',
            ];
        }

        // Check transaction status
        if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                // Transaction is successful, update topup status
                $topup->markAsPaid($orderId, $transactionStatus);

                // Add balance to user wallet
                $user = $topup->user;
                if ($user->wallet) {
                    $balanceBefore = $user->wallet->balance;
                    $user->wallet->increment('balance', $topup->amount);
                    $balanceAfter = $user->wallet->balance;

                    // Create wallet transaction record
                    $user->wallet->transactions()->create([
                        'type' => 'deposit',
                        'amount' => $topup->amount,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'status' => 'completed',
                        'description' => 'Top up via Midtrans',
                        'reference_id' => $topup->id,
                        'reference_type' => WalletTopup::class,
                        'metadata' => [
                            'midtrans_order_id' => $orderId,
                            'transaction_status' => $transactionStatus,
                        ],
                        'processed_at' => now(),
                    ]);

                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'wallet_deposit',
                        'title' => 'Topup Berhasil',
                        'message' => 'Topup Rp ' . number_format((float) $topup->amount, 0, ',', '.') . ' berhasil masuk ke wallet.',
                        'reference_type' => WalletTopup::class,
                        'reference_id' => $topup->id,
                        'is_read' => false,
                    ]);

                    $adminIds = User::query()->where('role', 'admin')->pluck('id');
                    foreach ($adminIds as $adminId) {
                        Notification::create([
                            'user_id' => $adminId,
                            'type' => 'wallet_deposit',
                            'title' => 'Deposit Masuk (Topup)',
                            'message' => ($user->name ?? 'User') . ' topup Rp ' . number_format((float) $topup->amount, 0, ',', '.') . '.',
                            'reference_type' => WalletTopup::class,
                            'reference_id' => $topup->id,
                            'is_read' => false,
                        ]);
                    }
                }

                return [
                    'success' => true,
                    'message' => 'Topup successful',
                ];
            }
        } elseif ($transactionStatus == 'pending') {
            // Transaction is pending
            $topup->update([
                'status' => 'pending',
                'midtrans_transaction_status' => $transactionStatus,
            ]);

            return [
                'success' => true,
                'message' => 'Transaction pending',
            ];
        } elseif ($transactionStatus == 'deny' ||
                  $transactionStatus == 'expire' ||
                  $transactionStatus == 'cancel') {
            // Transaction failed
            $topup->markAsFailed($orderId, $transactionStatus);

            return [
                'success' => false,
                'message' => 'Transaction '.$transactionStatus,
            ];
        }

        return [
            'success' => false,
            'message' => 'Unknown transaction status',
        ];
    }

    /**
     * Create event transaction (Snap Token)
     */
    public function createEventTransaction(EventTransaction $transaction): array
    {
        $event = $transaction->event;
        $picData = $transaction->pic_data;

        // Prepare item details from participants
        $itemDetails = [];
        $now = now();

        foreach ($transaction->participants as $participant) {
            // Load category if not loaded
            if (! $participant->relationLoaded('category')) {
                $participant->load('category');
            }

            if (! $participant->category) {
                continue; // Skip if no category
            }

            $category = $participant->category;

            // Calculate price based on registration period
            $price = $this->getCategoryPrice($category, $now);

            $itemDetails[] = [
                'id' => 'CATEGORY-'.$category->id,
                'price' => (float) $price,
                'quantity' => 1,
                'name' => $category->name.' - '.$participant->name,
            ];
        }

        // Add discount as item if exists
        if ($transaction->discount_amount > 0) {
            $itemDetails[] = [
                'id' => 'DISCOUNT',
                'price' => -(float) $transaction->discount_amount,
                'quantity' => 1,
                'name' => 'Diskon',
            ];
        }

        // Prepare transaction parameters
        $params = [
            'transaction_details' => [
                'order_id' => 'EVENT-'.$transaction->id.'-'.time(),
                'gross_amount' => (float) $transaction->final_amount,
            ],
            'customer_details' => [
                'first_name' => $picData['name'] ?? 'Customer',
                'email' => $picData['email'] ?? '',
                'phone' => $picData['phone'] ?? '',
            ],
            'item_details' => $itemDetails,
        ];

        try {
            // Get Snap Token from Midtrans
            $snapToken = Snap::getSnapToken($params);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $params['transaction_details']['order_id'],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check transaction status
     */
    public function checkTransactionStatus(string $orderId): array
    {
        try {
            $status = Transaction::status($orderId);

            return [
                'success' => true,
                'status' => $status,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get category price based on registration period
     *
     * @param  \App\Models\RaceCategory  $category
     * @param  \Carbon\Carbon  $now
     */
    protected function getCategoryPrice($category, $now): int
    {
        // If no registration period, use regular or early price
        if (! $category->reg_start_at || ! $category->reg_end_at) {
            return $category->price_regular ?? $category->price_early ?? 0;
        }

        $regStart = $category->reg_start_at;
        $regEnd = $category->reg_end_at;

        if ($now < $regStart) {
            // Registration not open yet
            return $category->price_regular ?? $category->price_early ?? 0;
        } elseif ($now >= $regStart && $now < $regEnd) {
            // Early bird period
            return $category->price_early ?? $category->price_regular ?? 0;
        } else {
            // Late period
            return $category->price_late ?? $category->price_regular ?? 0;
        }
    }

    /**
     * Create Snap Token for Membership Transaction
     */
    public function createMembershipTransaction(MembershipTransaction $transaction): array
    {
        // Production/Sandbox mode: Use Midtrans
        // Prepare transaction parameters
        $params = [
            'transaction_details' => [
                'order_id' => 'MEMBERSHIP-'.$transaction->id,
                'gross_amount' => (int) $transaction->total_amount,
            ],
            'customer_details' => [
                'first_name' => $transaction->user->name,
                'email' => $transaction->user->email,
                'phone' => $transaction->user->phone,
            ],
            'item_details' => [
                [
                    'id' => 'PKG-'.$transaction->package->id,
                    'price' => (int) $transaction->amount,
                    'quantity' => 1,
                    'name' => $transaction->package->name,
                ],
            ],
        ];

        // Add admin fee if exists
        if ($transaction->admin_fee > 0) {
            $params['item_details'][] = [
                'id' => 'ADMIN-FEE',
                'price' => (int) $transaction->admin_fee,
                'quantity' => 1,
                'name' => 'Biaya Admin',
            ];
        }

        try {
            // Get Snap Token from Midtrans
            $snapToken = Snap::getSnapToken($params);

            // Save token
            $transaction->update(['snap_token' => $snapToken]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $params['transaction_details']['order_id'],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
