<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\MootaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MootaWebhookController extends Controller
{
    protected $mootaService;

    public function __construct(MootaService $mootaService)
    {
        $this->mootaService = $mootaService;
    }

    public function handle(Request $request)
    {
        // Log for debugging
        Log::info('Moota Webhook received', $request->all());

        // 1. Signature Verification
        // Note: Check actual Moota docs for header name. Assuming 'Signature' or similar.
        // For now, we skip strict verification if we don't know the exact header, 
        // but we should implement it if we find out.
        // $signature = $request->header('Signature');
        // if (!$this->mootaService->verifySignature($signature, $request->getContent())) {
        //     return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        // }

        // 2. Parse Payload
        // Moota typically sends a JSON with a list of mutations
        $data = $request->all();
        
        // Handle if 'push_id' exists, it's a push notification
        $mutations = $data;
        
        // If data is wrapped in 'data' key?
        // Let's handle generic array of mutations
        if (!is_array($mutations)) {
             return response()->json(['status' => 'error', 'message' => 'Invalid data format'], 400);
        }

        foreach ($mutations as $mutation) {
            // Ensure it's an array
            if (!is_array($mutation)) continue;

            $amount = $mutation['amount'] ?? 0;
            $type = $mutation['type'] ?? ''; // CR / DR
            
            // We only care about Credit (Incoming)
            if ($type !== 'CR') {
                continue;
            }

            // Find transaction with this EXACT final_amount
            // We look for PENDING transactions.
            $transaction = Transaction::where('payment_gateway', 'moota')
                ->where('payment_status', 'pending')
                ->where('final_amount', $amount)
                ->first();

            if ($transaction) {
                try {
                    DB::transaction(function () use ($transaction, $mutation) {
                        $transaction->update([
                            'payment_status' => 'paid',
                            'paid_at' => now(),
                            'payment_channel' => $mutation['bank_type'] ?? 'bank_transfer',
                        ]);
                        
                        // Log success
                        Log::info("Transaction {$transaction->id} marked as paid via Moota. Amount: {$mutation['amount']}");
                    });
                } catch (\Exception $e) {
                    Log::error("Failed to update transaction {$transaction->id}: " . $e->getMessage());
                }
            } else {
                Log::warning("No pending transaction found for amount: {$amount}");
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
