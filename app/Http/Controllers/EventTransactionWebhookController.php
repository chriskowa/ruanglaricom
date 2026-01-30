<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaidEventTransaction;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventTransactionWebhookController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Handle Midtrans webhook for event transactions
     */
    public function handle(Request $request)
    {
        $orderId = $request->input('order_id');
        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $signatureKey = $request->input('signature_key');

        // Find transaction by order ID
        $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

        if (! $transaction) {
            Log::warning('Event transaction webhook: Transaction not found', [
                'order_id' => $orderId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        if (($transaction->payment_gateway ?? null) !== 'midtrans') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment gateway',
            ], 409);
        }

        $incomingMode = null;
        if (is_string($orderId) && str_contains($orderId, 'EVENT-SBX-')) {
            $incomingMode = 'sandbox';
        } elseif (is_string($orderId) && str_contains($orderId, 'EVENT-PRD-')) {
            $incomingMode = 'production';
        }

        $storedMode = (string) ($transaction->midtrans_mode ?? 'production');
        if ($incomingMode !== null && $incomingMode !== $storedMode) {
            Log::warning('Event transaction webhook: Mode mismatch', [
                'order_id' => $orderId,
                'incoming_mode' => $incomingMode,
                'stored_mode' => $storedMode,
                'transaction_id' => $transaction->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Mode mismatch',
            ], 409);
        }

        if (! $statusCode || ! $grossAmount || ! $signatureKey) {
            Log::warning('Event transaction webhook: Missing signature fields', [
                'order_id' => $orderId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Missing signature fields',
            ], 400);
        }

        $serverKey = $storedMode === 'sandbox'
            ? (string) config('midtrans.server_key_sandbox')
            : (string) config('midtrans.server_key');

        if ($serverKey === '') {
            Log::error('Event transaction webhook: Server key not configured', [
                'order_id' => $orderId,
                'mode' => $storedMode,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server key not configured',
            ], 500);
        }

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
        if (! hash_equals($expectedSignature, (string) $signatureKey)) {
            Log::warning('Event transaction webhook: Invalid signature', [
                'order_id' => $orderId,
                'mode' => $storedMode,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 401);
        }

        // Check transaction status
        if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                // Transaction is successful
                $transaction->markAsPaid($orderId, $transactionStatus);

                // Dispatch job to process payment
                ProcessPaidEventTransaction::dispatch($transaction);

                return response()->json([
                    'success' => true,
                    'message' => 'Transaction processed successfully',
                ]);
            }
        } elseif ($transactionStatus == 'pending') {
            // Transaction is pending
            $transaction->update([
                'payment_status' => 'pending',
                'midtrans_transaction_status' => $transactionStatus,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction pending',
            ]);
        } elseif ($transactionStatus == 'deny' ||
                  $transactionStatus == 'expire' ||
                  $transactionStatus == 'cancel') {
            // Transaction failed
            $transaction->markAsFailed($orderId, $transactionStatus);

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
