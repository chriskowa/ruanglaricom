<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\MootaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MootaWebhookController extends Controller
{
    protected $mootaService;

    public function __construct(MootaService $mootaService)
    {
        $this->mootaService = $mootaService;
    }

    public function handle(Request $request)
    {
        // 1. Verify Signature if secret is set
        $signature = $request->header('Signature'); 
        
        // $this->mootaService->verifySignature($signature, $request->getContent());

        $data = $request->all();
        Log::info('Moota Webhook Received:', $data);

        // Moota payload structure usually contains array of mutations or a single push
        $mutations = isset($data['data']) ? $data['data'] : $data;
        
        // Normalize if it's a single object
        if (isset($mutations['id'])) {
            $mutations = [$mutations];
        }

        foreach ($mutations as $mutation) {
            // We only care about Credit (CR) / incoming money
            if (isset($mutation['type']) && $mutation['type'] !== 'CR') {
                continue;
            }

            $amount = $mutation['amount'];
            
            // Find transaction with this exact amount (including unique code)
            // and status pending
            $transaction = Transaction::where('final_amount', $amount)
                ->where('payment_status', 'pending')
                ->where('payment_gateway', 'moota') 
                ->first();

            if ($transaction) {
                // Mark as paid
                $transaction->update([
                    'payment_status' => 'paid',
                    'moota_transaction_id' => $mutation['id'] ?? null,
                    'payment_gateway_reference' => $mutation['id'] ?? null,
                    'paid_at' => now(),
                ]);

                // Dispatch job to process payment
                try {
                    \App\Jobs\ProcessPaidEventTransaction::dispatch($transaction);
                    Log::info('Transaction processed via Moota', ['id' => $transaction->id]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch ProcessPaidEventTransaction: ' . $e->getMessage());
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
