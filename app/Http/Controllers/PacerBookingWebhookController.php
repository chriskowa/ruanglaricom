<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaidPacerBooking;
use App\Models\PacerBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PacerBookingWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        Log::info('Pacer Booking Webhook', $payload);

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (! $orderId) {
            return response()->json(['message' => 'Missing order_id'], 422);
        }

        $booking = PacerBooking::where('invoice_number', $orderId)->first();
        if (! $booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
            if ($fraudStatus === 'challenge') {
                return response()->json(['status' => 'challenge']);
            }

            if ($booking->status === 'pending') {
                $booking->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                ProcessPaidPacerBooking::dispatch($booking->id);
            }
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'], true)) {
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
