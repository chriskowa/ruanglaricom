<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class EventPaymentRecoveryController extends Controller
{
    public function show(string $slug)
    {
        $event = Event::query()->where('slug', $slug)->firstOrFail();

        return view('events.continue-payment', [
            'event' => $event,
        ]);
    }

    public function pending(Request $request, string $slug)
    {
        $event = Event::query()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'phone' => 'required|string|min:6|max:32',
            'transaction_id' => 'nullable|string|max:32',
        ]);

        $normalizedPhone = $this->normalizePhone($validated['phone']);

        if ($normalizedPhone === '') {
            return response()->json([
                'success' => false,
                'message' => 'Nomor HP tidak valid.',
            ], 422);
        }

        if (! empty($validated['transaction_id'])) {
            $tx = Transaction::query()
                ->where('event_id', $event->id)
                ->where('payment_gateway', 'midtrans')
                ->where('payment_status', 'pending')
                ->where('id', $validated['transaction_id'])
                ->first();

            if (! $tx) {
                return response()->json([
                    'success' => true,
                    'transactions' => [],
                ]);
            }

            if (! $this->phoneMatchesTransaction($normalizedPhone, $tx)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak cocok.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'transactions' => [$this->serializeTransaction($tx)],
            ]);
        }

        $candidates = Transaction::query()
            ->where('event_id', $event->id)
            ->where('payment_gateway', 'midtrans')
            ->where('payment_status', 'pending')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $matches = [];
        foreach ($candidates as $tx) {
            if ($this->phoneMatchesTransaction($normalizedPhone, $tx)) {
                $matches[] = $this->serializeTransaction($tx);
            }
        }

        return response()->json([
            'success' => true,
            'transactions' => array_slice($matches, 0, 10),
        ]);
    }

    public function status(Request $request, string $slug, Transaction $transaction, MidtransService $midtransService)
    {
        $event = Event::query()->where('slug', $slug)->firstOrFail();
        if ($transaction->event_id !== $event->id) {
            abort(404);
        }

        $validated = $request->validate([
            'phone' => 'required|string|min:6|max:32',
        ]);

        $normalizedPhone = $this->normalizePhone($validated['phone']);
        if (! $this->phoneMatchesTransaction($normalizedPhone, $transaction)) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak cocok.',
            ], 403);
        }

        if (($transaction->payment_gateway ?? null) !== 'midtrans') {
            return response()->json([
                'success' => false,
                'message' => 'Gateway tidak valid.',
            ], 409);
        }

        if (! $transaction->midtrans_order_id) {
            return response()->json([
                'success' => true,
                'transaction' => $this->serializeTransaction($transaction),
            ]);
        }

        $mode = $transaction->midtrans_mode ? (string) $transaction->midtrans_mode : null;
        $result = $midtransService->checkTransactionStatus($transaction->midtrans_order_id, $mode);
        if (! ($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengecek status.',
            ], 400);
        }

        $midtransStatus = (string) ($result['status']->transaction_status ?? '');
        $internal = $this->mapMidtransStatusToInternal($midtransStatus);

        if ($internal !== null) {
            $transaction->update([
                'payment_status' => $internal,
                'midtrans_transaction_status' => $midtransStatus,
                'paid_at' => $internal === 'paid' ? now() : $transaction->paid_at,
            ]);
        } else {
            $transaction->update([
                'midtrans_transaction_status' => $midtransStatus,
            ]);
        }

        return response()->json([
            'success' => true,
            'transaction' => $this->serializeTransaction($transaction->fresh()),
        ]);
    }

    public function resume(Request $request, string $slug, Transaction $transaction)
    {
        $event = Event::query()->where('slug', $slug)->firstOrFail();
        if ($transaction->event_id !== $event->id) {
            abort(404);
        }

        $validated = $request->validate([
            'phone' => 'required|string|min:6|max:32',
        ]);

        $normalizedPhone = $this->normalizePhone($validated['phone']);
        if (! $this->phoneMatchesTransaction($normalizedPhone, $transaction)) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak cocok.',
            ], 403);
        }

        if (($transaction->payment_gateway ?? null) !== 'midtrans') {
            return response()->json([
                'success' => false,
                'message' => 'Gateway tidak valid.',
            ], 409);
        }

        if (($transaction->payment_status ?? null) !== 'pending') {
            return response()->json([
                'success' => true,
                'transaction' => $this->serializeTransaction($transaction),
                'snap_token' => null,
                'message' => 'Transaksi tidak dalam status pending.',
            ]);
        }

        if (! $transaction->snap_token) {
            return response()->json([
                'success' => false,
                'message' => 'Token pembayaran tidak tersedia. Silakan cek status atau lakukan registrasi ulang jika transaksi sudah expire.',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'transaction' => $this->serializeTransaction($transaction),
            'snap_token' => $transaction->snap_token,
        ]);
    }

    private function mapMidtransStatusToInternal(string $status): ?string
    {
        $s = strtolower(trim($status));

        if (in_array($s, ['settlement', 'capture'], true)) {
            return 'paid';
        }

        if ($s === 'pending') {
            return 'pending';
        }

        if (in_array($s, ['deny', 'expire', 'cancel'], true)) {
            return 'failed';
        }

        return null;
    }

    private function serializeTransaction(Transaction $tx): array
    {
        $pic = is_array($tx->pic_data) ? $tx->pic_data : [];
        $name = (string) ($pic['name'] ?? '');
        $phone = (string) ($pic['phone'] ?? '');

        return [
            'id' => $tx->id,
            'public_ref' => $tx->public_ref ?? null,
            'pic_name' => $name !== '' ? $name : null,
            'pic_phone_masked' => $this->maskPhone($phone),
            'final_amount' => (float) ($tx->final_amount ?? 0),
            'payment_status' => $tx->payment_status,
            'midtrans_transaction_status' => $tx->midtrans_transaction_status,
            'created_at' => optional($tx->created_at)->toISOString(),
        ];
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') {
            return '';
        }

        $len = strlen($digits);
        if ($len <= 6) {
            return str_repeat('*', max(0, $len - 2)).substr($digits, -2);
        }

        return substr($digits, 0, 2).str_repeat('*', $len - 6).substr($digits, -4);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (! is_string($digits) || $digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }

    private function phoneMatchesTransaction(string $normalizedPhone, Transaction $tx): bool
    {
        $pic = is_array($tx->pic_data) ? $tx->pic_data : [];
        $stored = (string) ($pic['phone'] ?? '');
        $storedNormalized = $this->normalizePhone($stored);
        if ($storedNormalized === '') {
            return false;
        }

        return $storedNormalized === $normalizedPhone;
    }
}
