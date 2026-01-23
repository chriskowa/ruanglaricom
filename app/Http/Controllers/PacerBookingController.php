<?php

namespace App\Http\Controllers;

use App\Models\AppSettings;
use App\Models\Notification;
use App\Models\Pacer;
use App\Models\PacerBooking;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\PlatformWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class PacerBookingController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function store(Request $request, string $slug, PlatformWalletService $platformWalletService)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $runner = Auth::user();
        $pacer = Pacer::with('user')->where('seo_slug', $slug)->firstOrFail();

        $data = $request->validate([
            'event_name' => ['nullable', 'string', 'max:120'],
            'race_date' => ['nullable', 'date'],
            'distance' => ['nullable', 'string', 'max:30'],
            'target_pace' => ['nullable', 'string', 'max:30'],
            'meeting_point' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $basePrice = (float) AppSettings::get('pacer_booking_base_price', 150000);
        $feePercent = (float) $platformWalletService->getPlatformFeePercent();
        $platformFeeAmount = round($basePrice * ($feePercent / 100), 2);
        $pacerAmount = round($basePrice - $platformFeeAmount, 2);

        $booking = DB::transaction(function () use ($runner, $pacer, $data, $basePrice, $platformFeeAmount, $pacerAmount) {
            return PacerBooking::create([
                'invoice_number' => 'INV-PACER-'.strtoupper(Str::random(10)),
                'runner_id' => $runner->id,
                'pacer_id' => $pacer->id,
                'event_name' => $data['event_name'] ?? null,
                'race_date' => $data['race_date'] ?? null,
                'distance' => $data['distance'] ?? null,
                'target_pace' => $data['target_pace'] ?? null,
                'meeting_point' => $data['meeting_point'] ?? null,
                'notes' => $data['notes'] ?? null,
                'total_amount' => $basePrice,
                'platform_fee_amount' => $platformFeeAmount,
                'pacer_amount' => $pacerAmount,
                'status' => 'pending',
            ]);
        });

        $params = [
            'transaction_details' => [
                'order_id' => $booking->invoice_number,
                'gross_amount' => (int) $booking->total_amount,
            ],
            'customer_details' => [
                'first_name' => $runner->name,
                'email' => $runner->email,
            ],
            'item_details' => [
                [
                    'id' => 'pacer_'.$pacer->id,
                    'price' => (int) $booking->total_amount,
                    'quantity' => 1,
                    'name' => substr('Pacer Booking - '.$pacer->user->name, 0, 50),
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $booking->update([
                'snap_token' => $snapToken,
                'midtrans_order_id' => $booking->invoice_number,
            ]);

            return redirect()->route('pacer.bookings.pay', $booking->id);
        } catch (\Exception $e) {
            return back()->with('error', 'Payment gateway error: '.$e->getMessage());
        }
    }

    public function pay(PacerBooking $booking)
    {
        if (!Auth::check() || $booking->runner_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return redirect()->route('pacer.show', $booking->pacer->seo_slug)->with('info', 'Booking already processed.');
        }

        if (!$booking->snap_token) {
            $runner = Auth::user();
            $pacer = $booking->pacer()->with('user')->first();

            $params = [
                'transaction_details' => [
                    'order_id' => $booking->invoice_number,
                    'gross_amount' => (int) $booking->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $runner->name,
                    'email' => $runner->email,
                ],
                'item_details' => [
                    [
                        'id' => 'pacer_'.$pacer->id,
                        'price' => (int) $booking->total_amount,
                        'quantity' => 1,
                        'name' => substr('Pacer Booking - '.$pacer->user->name, 0, 50),
                    ],
                ],
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
                $booking->update([
                    'snap_token' => $snapToken,
                    'midtrans_order_id' => $booking->invoice_number,
                ]);
            } catch (\Exception $e) {
                return back()->with('error', 'Payment gateway error: '.$e->getMessage());
            }
        }

        $booking->load('pacer.user');
        return view('pacer.bookings.pay', compact('booking'));
    }

    public function confirm(PacerBooking $booking)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $booking->load('pacer.user');

        if (!$user->is_pacer || $booking->pacer->user_id !== $user->id) {
            abort(403);
        }

        if (!in_array($booking->status, ['paid', 'confirmed'], true)) {
            return back()->with('error', 'Booking status invalid.');
        }

        if ($booking->status !== 'confirmed') {
            $booking->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            Notification::create([
                'user_id' => $booking->runner_id,
                'type' => 'pacer_booking',
                'title' => 'Booking Dikonfirmasi',
                'message' => 'Pacer mengonfirmasi booking. Invoice: '.$booking->invoice_number,
                'reference_type' => PacerBooking::class,
                'reference_id' => $booking->id,
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Booking confirmed.');
    }

    public function complete(PacerBooking $booking, PlatformWalletService $platformWalletService)
    {
        if (!Auth::check() || $booking->runner_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($booking->status, ['paid', 'confirmed'], true)) {
            return back()->with('error', 'Booking status invalid.');
        }

        DB::transaction(function () use ($booking, $platformWalletService) {
            $booking->refresh();
            $booking->load('pacer.user');

            $platformWallet = $platformWalletService->getPlatformWallet();
            $platformWallet->refresh();

            $amountTotal = (float) $booking->total_amount;
            $feeAmount = (float) $booking->platform_fee_amount;
            $pacerAmount = (float) $booking->pacer_amount;

            $lockedBefore = (float) $platformWallet->locked_balance;
            if ($lockedBefore < $amountTotal) {
                throw new \RuntimeException('Escrow balance insufficient.');
            }

            $platformWallet->locked_balance = $lockedBefore - $amountTotal;
            $platformBalanceBefore = (float) $platformWallet->balance;
            $platformWallet->balance = $platformBalanceBefore + $feeAmount;
            $platformWallet->save();

            WalletTransaction::create([
                'wallet_id' => $platformWallet->id,
                'type' => 'platform_fee_income',
                'amount' => $feeAmount,
                'balance_before' => $platformBalanceBefore,
                'balance_after' => (float) $platformWallet->balance,
                'status' => 'completed',
                'reference_type' => PacerBooking::class,
                'reference_id' => $booking->id,
                'description' => 'Platform fee for booking '.$booking->invoice_number,
                'metadata' => [
                    'invoice_number' => $booking->invoice_number,
                    'locked_before' => $lockedBefore,
                    'locked_after' => (float) $platformWallet->locked_balance,
                ],
                'processed_at' => now(),
            ]);

            $pacerUser = $booking->pacer->user;
            $pacerWallet = $pacerUser->wallet;
            if (!$pacerWallet) {
                $pacerWallet = Wallet::create([
                    'user_id' => $pacerUser->id,
                    'balance' => 0,
                    'locked_balance' => 0,
                ]);
            } else {
                $pacerWallet->refresh();
            }

            $pacerBalanceBefore = (float) $pacerWallet->balance;
            $pacerWallet->balance = $pacerBalanceBefore + $pacerAmount;
            $pacerWallet->save();

            WalletTransaction::create([
                'wallet_id' => $pacerWallet->id,
                'type' => 'pacer_payout',
                'amount' => $pacerAmount,
                'balance_before' => $pacerBalanceBefore,
                'balance_after' => (float) $pacerWallet->balance,
                'status' => 'completed',
                'reference_type' => PacerBooking::class,
                'reference_id' => $booking->id,
                'description' => 'Pacer payout for booking '.$booking->invoice_number,
                'metadata' => [
                    'invoice_number' => $booking->invoice_number,
                ],
                'processed_at' => now(),
            ]);

            $booking->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Notification::create([
                'user_id' => $booking->runner_id,
                'type' => 'pacer_booking',
                'title' => 'Booking Completed',
                'message' => 'Booking selesai. Terima kasih! Invoice: '.$booking->invoice_number,
                'reference_type' => PacerBooking::class,
                'reference_id' => $booking->id,
                'is_read' => false,
            ]);

            Notification::create([
                'user_id' => $pacerUser->id,
                'type' => 'pacer_booking',
                'title' => 'Payout Released',
                'message' => 'Booking selesai. Payout masuk ke wallet. Invoice: '.$booking->invoice_number,
                'reference_type' => PacerBooking::class,
                'reference_id' => $booking->id,
                'is_read' => false,
            ]);
        });

        return back()->with('success', 'Booking completed. Thank you!');
    }
}
