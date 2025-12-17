<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\MidtransService;
use App\Jobs\ProcessPaidEventTransaction;
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

        // Find transaction by order ID
        $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

        if (!$transaction) {
            Log::warning('Event transaction webhook: Transaction not found', [
                'order_id' => $orderId,
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
                'message' => 'Transaction ' . $transactionStatus,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unknown transaction status',
        ]);
    }
}









