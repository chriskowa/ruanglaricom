<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Order;
use App\Models\Notification;
use App\Models\ProgramEnrollment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\PlatformWalletService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        Log::info('Marketplace Webhook', $payload);

        $orderId = $payload['order_id'];

        $baseOrderId = $orderId;
        if (str_contains($orderId, '-')) {
            $lastPart = substr(strrchr($orderId, "-"), 1);
            if (is_numeric($lastPart)) {
                $baseOrderId = substr($orderId, 0, strrpos($orderId, "-"));
            }
        }

        $transactionStatus = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? null;

        $marketplaceOrder = MarketplaceOrder::where('invoice_number', $baseOrderId)->first();

        if ($marketplaceOrder) {
            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                if ($fraudStatus !== 'challenge') {
                    $marketplaceOrder->update(['status' => 'paid']);

                    try {
                        $marketplaceOrder->load(['seller', 'buyer', 'items.product']);
                        if ($marketplaceOrder->seller && $marketplaceOrder->seller->email) {
                            \Illuminate\Support\Facades\Mail::to($marketplaceOrder->seller->email)->send(new \App\Mail\MarketplaceOrderPaidSellerMail($marketplaceOrder));
                        }

                        $adminEmail = config('mail.admin_address', 'admin@ruanglari.com');
                        \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\MarketplaceOrderPaidAdminMail($marketplaceOrder));

                        if ($marketplaceOrder->seller && $marketplaceOrder->seller->phone && $marketplaceOrder->seller->is_receive_wa) {
                            $waMsg = "Halo {$marketplaceOrder->seller->name}! Ada pesanan baru masuk di RuangLari Marketplace.\n\n"
                                . "Invoice: {$marketplaceOrder->invoice_number}\n"
                                . "Pembeli: " . ($marketplaceOrder->buyer->name ?? 'Buyer') . "\n"
                                . "Alamat: {$marketplaceOrder->shipping_address}, {$marketplaceOrder->shipping_city}\n"
                                . "Nominal Bersih: Rp " . number_format($marketplaceOrder->seller_amount, 0, ',', '.') . "\n\n"
                                . "Silakan cek dashboard seller Anda untuk memproses pengiriman.";
                            \App\Helpers\WhatsApp::send($marketplaceOrder->seller->phone, $waMsg, 'seller_order');
                        }

                        // Send in-app notification to Seller
                        if ($marketplaceOrder->seller) {
                            Notification::create([
                                'user_id' => $marketplaceOrder->seller_id,
                                'type' => 'marketplace_sale',
                                'title' => 'Pesanan Baru Masuk',
                                'message' => 'Pesanan #' . $marketplaceOrder->invoice_number . ' (' . ($marketplaceOrder->buyer->name ?? 'Pembeli') . ') telah dibayar. Silakan proses pengiriman.',
                                'reference_type' => MarketplaceOrder::class,
                                'reference_id' => $marketplaceOrder->id,
                            ]);
                        }

                        // Send in-app notification to Admin users
                        $adminUsers = \App\Models\User::where('role', 'admin')->get();
                        foreach ($adminUsers as $adminUser) {
                            Notification::create([
                                'user_id' => $adminUser->id,
                                'type' => 'marketplace_order_paid',
                                'title' => 'Penjualan Marketplace Baru',
                                'message' => 'Pesanan #' . $marketplaceOrder->invoice_number . ' senilai Rp ' . number_format($marketplaceOrder->total_amount, 0, ',', '.') . ' telah dibayar oleh ' . ($marketplaceOrder->buyer->name ?? 'Pembeli'),
                                'reference_type' => MarketplaceOrder::class,
                                'reference_id' => $marketplaceOrder->id,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to dispatch webhook marketplace notifications: ' . $e->getMessage());
                    }
                }
            } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $marketplaceOrder->update(['status' => 'cancelled']);
                foreach ($marketplaceOrder->items as $item) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            return response()->json(['status' => 'ok']);
        }

        $programOrder = Order::where('order_number', $baseOrderId)->with('items.program', 'user')->first();

        if (! $programOrder) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            if ($fraudStatus !== 'challenge') {
                DB::transaction(function () use ($programOrder, $payload) {
                    $programOrder->markAsPaid($payload['transaction_id'] ?? null);
                    $programOrder->markAsCompleted();

                    $user = $programOrder->user;

                    foreach ($programOrder->items as $item) {
                        $program = $item->program;

                        if (! $program) {
                            continue;
                        }

                        $existingEnrollment = ProgramEnrollment::where('program_id', $program->id)
                            ->where('runner_id', $user->id)
                            ->whereIn('status', ['purchased', 'active'])
                            ->first();

                        if (! $existingEnrollment) {
                            $endDate = Carbon::today()->addWeeks($program->duration_weeks ?? 12);

                            ProgramEnrollment::create([
                                'program_id' => $program->id,
                                'runner_id' => $user->id,
                                'start_date' => Carbon::today(),
                                'end_date' => $endDate,
                                'status' => 'active',
                                'payment_status' => 'paid',
                            ]);

                            $program->increment('enrolled_count');

                            // Distribute funds to Coach and Platform
                            $this->distributeProgramFunds($programOrder, $item, $program);
                        }
                    }
                });

                try {
                    \App\Jobs\ProcessPaidProgramOrder::dispatch($programOrder->id);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch ProcessPaidProgramOrder in Webhook: ' . $e->getMessage());
                }
            }
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $programOrder->update([
                'payment_status' => 'failed',
                'status' => 'cancelled',
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Distribute funds to Coach wallet and Platform wallet
     */
    protected function distributeProgramFunds($order, $item, $program)
    {
        $coach = $program->coach;
        if (!$coach) return;

        $platformWalletService = app(PlatformWalletService::class);
        $feePercent = $platformWalletService->getPlatformFeePercent();
        
        $totalPrice = (float) $item->price;
        $platformFee = $totalPrice * ($feePercent / 100);
        $coachAmount = $totalPrice - $platformFee;

        // 1. Credit Coach Wallet
        $coachWallet = Wallet::firstOrCreate(
            ['user_id' => $coach->id],
            ['balance' => 0, 'locked_balance' => 0]
        );

        $coachBefore = (float) $coachWallet->balance;
        $coachWallet->increment('balance', $coachAmount);
        $coachAfter = (float) $coachWallet->balance;

        $coachWallet->transactions()->create([
            'type' => 'deposit',
            'amount' => $coachAmount,
            'balance_before' => $coachBefore,
            'balance_after' => $coachAfter,
            'status' => 'completed',
            'description' => 'Pendapatan program: ' . $program->title . ' (Order #' . $order->order_number . ')',
            'reference_type' => ProgramEnrollment::class,
            'reference_id' => $program->id, // Enrollment ID is better if available, but program ID is fallback
            'processed_at' => now(),
        ]);

        // 2. Credit Platform Wallet (Fee)
        if ($platformFee > 0) {
            $platformWallet = $platformWalletService->getPlatformWallet();
            $platformBefore = (float) $platformWallet->balance;
            $platformWallet->increment('balance', $platformFee);
            $platformAfter = (float) $platformWallet->balance;

            $platformWallet->transactions()->create([
                'type' => 'fee',
                'amount' => $platformFee,
                'balance_before' => $platformBefore,
                'balance_after' => $platformAfter,
                'status' => 'completed',
                'description' => 'Platform fee program: ' . $program->title . ' (Order #' . $order->order_number . ')',
                'reference_type' => ProgramEnrollment::class,
                'reference_id' => $program->id,
                'processed_at' => now(),
            ]);
        }

        // Notify Coach
        try {
            Notification::create([
                'user_id' => $coach->id,
                'type' => 'program_sale',
                'title' => 'Pendapatan Baru Masuk',
                'message' => 'Anda menerima Rp ' . number_format($coachAmount, 0, ',', '.') . ' dari penjualan program: ' . $program->title,
                'reference_type' => ProgramEnrollment::class,
                'reference_id' => $program->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Webhook notification error: ' . $e->getMessage());
        }
    }
}
