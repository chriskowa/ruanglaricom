<?php

namespace App\Http\Controllers;

use App\Models\MembershipTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MembershipWebhookController extends Controller
{
    /**
     * Handle Midtrans webhook for membership transactions
     */
    public function handle(Request $request)
    {
        $orderId = $request->input('order_id');
        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status');

        // Extract ID from order_id (MEMBERSHIP-{uuid})
        // Midtrans Order ID: MEMBERSHIP-uuid
        // We can just search by partial match or store midtrans_order_id if we had that column,
        // but currently we constructed order_id as MEMBERSHIP-id.
        // Let's parse the ID.

        // However, looking at MidtransService:
        // 'order_id' => 'MEMBERSHIP-'.$transaction->id,

        $parts = explode('-', $orderId);
        // MEMBERSHIP-uuid... wait, UUID can contain dashes.
        // So we should remove 'MEMBERSHIP-' prefix.

        $transactionId = str_replace('MEMBERSHIP-', '', $orderId);

        // Find transaction
        $transaction = MembershipTransaction::find($transactionId);

        if (! $transaction) {
            Log::warning('Membership webhook: Transaction not found', [
                'order_id' => $orderId,
                'parsed_id' => $transactionId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        // Check transaction status
        if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                // Transaction is successful
                $transaction->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                // Activate User Membership
                $user = $transaction->user;
                $package = $transaction->package;

                $user->update([
                    'membership_status' => 'active',
                    'membership_package_id' => $package->id,
                    'membership_expires_at' => now()->addDays($package->duration_days),
                ]);

                Log::info('Membership activated via webhook', [
                    'user_id' => $user->id,
                    'package' => $package->slug,
                    'transaction_id' => $transaction->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Membership activated successfully',
                ]);
            }
        } elseif ($transactionStatus == 'pending') {
            // Transaction is pending
            $transaction->update([
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction pending',
            ]);
        } elseif ($transactionStatus == 'deny' ||
                  $transactionStatus == 'expire' ||
                  $transactionStatus == 'cancel') {
            // Transaction failed
            $transaction->update([
                'status' => 'failed',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Transaction '.$transactionStatus,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unknown transaction status',
        ]);
    }
}
