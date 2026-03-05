<?php

namespace App\Http\Controllers;

use App\Models\PaidFeature;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;

class PaidFeatureController extends Controller
{
    private const CATALOG = [
        'motion-capture-expert' => [
            'name' => 'Expert Form Analyzer',
            'price' => 25000,
        ],
    ];

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'feature_slug' => ['required', 'string'],
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        if (! array_key_exists($data['feature_slug'], self::CATALOG)) {
            return response()->json([
                'ok' => false,
                'message' => 'Fitur tidak tersedia.',
            ], 422);
        }

        $user = $request->user();
        if (! $user) {
            $user = User::query()->where('email', $data['email'])->first();
        }

        if (! $user) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make(Str::random(20)),
                'role' => 'runner',
                'is_active' => true,
            ]);
        }

        if (! Auth::check()) {
            Auth::login($user);
        }

        $dirty = false;
        if ($data['name'] && $user->name !== $data['name']) {
            $user->name = $data['name'];
            $dirty = true;
        }
        if ($data['phone'] && $user->phone !== $data['phone']) {
            $user->phone = $data['phone'];
            $dirty = true;
        }
        if ($dirty) {
            $user->save();
        }

        $featureSlug = $data['feature_slug'];
        $catalog = self::CATALOG[$featureSlug];

        $feature = PaidFeature::query()->firstOrNew([
            'user_id' => $user->id,
            'feature_slug' => $featureSlug,
        ]);

        if ($feature->status === 'paid' && (! $feature->expires_at || $feature->expires_at->isFuture())) {
            return response()->json([
                'ok' => true,
                'status' => 'already_paid',
                'message' => 'Fitur sudah aktif.',
            ]);
        }

        $feature->price = $catalog['price'];
        $feature->status = 'pending';
        $feature->meta = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ];
        $feature->save();

        if (config('midtrans.testing_mode')) {
            $feature->update([
                'status' => 'paid',
                'purchased_at' => now(),
                'midtrans_transaction_status' => 'testing',
            ]);

            return response()->json([
                'ok' => true,
                'status' => 'success',
                'message' => 'Fitur Expert aktif.',
                'testing' => true,
            ]);
        }

        $orderId = 'FEATURE-'.$feature->id.'-'.time();

        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('midtrans.is_production');
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $catalog['price'],
            ],
            'customer_details' => [
                'first_name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ],
            'item_details' => [
                [
                    'id' => $featureSlug,
                    'price' => (int) $catalog['price'],
                    'quantity' => 1,
                    'name' => $catalog['name'],
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $feature->update([
                'snap_token' => $snapToken,
                'midtrans_order_id' => $orderId,
            ]);

            return response()->json([
                'ok' => true,
                'status' => 'pending',
                'snap_token' => $snapToken,
                'order_id' => $orderId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function confirm(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        $feature = PaidFeature::query()->where('midtrans_order_id', $data['order_id'])->first();
        if (! $feature) {
            return response()->json([
                'ok' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        if (config('midtrans.testing_mode')) {
            $feature->update([
                'status' => 'paid',
                'purchased_at' => now(),
                'midtrans_transaction_status' => 'testing',
            ]);

            return response()->json([
                'ok' => true,
                'status' => 'paid',
            ]);
        }

        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('midtrans.is_production');

        try {
            $status = MidtransTransaction::status($data['order_id']);
            $transactionStatus = (string) ($status->transaction_status ?? '');
            $fraudStatus = (string) ($status->fraud_status ?? '');

            $paid = in_array($transactionStatus, ['capture', 'settlement'], true)
                && ($fraudStatus === '' || $fraudStatus === 'accept');

            if ($paid) {
                $feature->update([
                    'status' => 'paid',
                    'purchased_at' => now(),
                    'midtrans_transaction_status' => $transactionStatus,
                ]);

                return response()->json([
                    'ok' => true,
                    'status' => 'paid',
                ]);
            }

            $feature->update([
                'midtrans_transaction_status' => $transactionStatus,
            ]);

            return response()->json([
                'ok' => true,
                'status' => $transactionStatus ?: 'pending',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
