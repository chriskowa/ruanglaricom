<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Order;
use App\Models\ProgramEnrollment;
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
        $transactionStatus = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? null;

        $marketplaceOrder = MarketplaceOrder::where('invoice_number', $orderId)->first();

        if ($marketplaceOrder) {
            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                if ($fraudStatus !== 'challenge') {
                    $marketplaceOrder->update(['status' => 'paid']);
                }
            } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $marketplaceOrder->update(['status' => 'cancelled']);
                foreach ($marketplaceOrder->items as $item) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            return response()->json(['status' => 'ok']);
        }

        $programOrder = Order::where('order_number', $orderId)->with('items.program', 'user')->first();

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
                            ->where('status', '!=', 'cancelled')
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
                        }
                    }
                });
            }
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $programOrder->update([
                'payment_status' => 'failed',
                'status' => 'cancelled',
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
