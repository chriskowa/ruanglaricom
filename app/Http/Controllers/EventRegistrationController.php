<?php

namespace App\Http\Controllers;

use App\Actions\Events\StoreRegistrationAction;
use App\Models\Coupon;
use App\Models\Event;
use App\Services\EventCacheService;
use Illuminate\Http\Request;

class EventRegistrationController extends Controller
{
    protected $cacheService;

    protected $storeAction;

    public function __construct(EventCacheService $cacheService, StoreRegistrationAction $storeAction)
    {
        $this->cacheService = $cacheService;
        $this->storeAction = $storeAction;
    }

    /**
     * Show registration form - redirect to event show page
     * Form is now inline in show.blade.php
     */
    public function show($slug)
    {
        return redirect()->route('events.show', $slug)->with('show_form', true);
    }

    /**
     * Apply coupon code
     */
    public function applyCoupon(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Apply Coupon Request', [
                'event_id' => $request->event_id,
                'code' => $request->coupon_code,
                'total_amount' => $request->total_amount,
                'user_id' => auth()->id(),
            ]);

            $validated = $request->validate([
                'event_id' => 'required|exists:events,id',
                'coupon_code' => 'required|string',
                'total_amount' => 'required|numeric|min:0',
            ]);

            $coupon = Coupon::where('code', $validated['coupon_code'])
                ->where(function ($query) use ($validated) {
                    $query->where('event_id', $validated['event_id'])
                          ->orWhereNull('event_id');
                })
                ->first();

            if (! $coupon) {
                \Illuminate\Support\Facades\Log::warning('Coupon not found', ['code' => $validated['coupon_code']]);
                return response()->json([
                    'success' => false,
                    'message' => 'Kode kupon tidak ditemukan',
                ], 404);
            }

            if (! $coupon->canBeUsed($validated['event_id'], $validated['total_amount'], auth()->id())) {
                \Illuminate\Support\Facades\Log::warning('Coupon invalid condition', [
                    'code' => $coupon->code,
                    'reason' => 'Failed canBeUsed check',
                    'min_trx' => $coupon->min_transaction_amount ?? 0,
                    'request_amount' => $validated['total_amount']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Kupon tidak valid atau syarat minimum transaksi tidak terpenuhi',
                ], 400);
            }

            $discountAmount = $coupon->applyDiscount($validated['total_amount']);
            $finalAmount = $validated['total_amount'] - $discountAmount;

            \Illuminate\Support\Facades\Log::info('Coupon Applied Successfully', [
                'code' => $coupon->code,
                'discount' => $discountAmount,
                'final' => $finalAmount
            ]);

            return response()->json([
                'success' => true,
                'original_price' => $validated['total_amount'],
                'discount_amount' => $discountAmount,
                'final_price' => $finalAmount,
                'final_amount' => $finalAmount, // Keep backward compatibility
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                ],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Apply Coupon Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check quota for categories
     */
    public function checkQuota(Request $request, $slug)
    {
        $validated = $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:race_categories,id',
        ]);

        $quotas = [];
        foreach ($validated['category_ids'] as $categoryId) {
            $category = \App\Models\RaceCategory::find($categoryId);
            if ($category) {
                // Count registered participants
                $registeredCount = \App\Models\Participant::where('race_category_id', $categoryId)
                    ->whereHas('transaction', function ($query) {
                        $query->whereIn('payment_status', ['paid', 'cod']);
                    })
                    ->count();

                $remainingQuota = $category->quota ? ($category->quota - $registeredCount) : 999999;

                $quotas[$categoryId] = [
                    'remaining_quota' => max(0, $remainingQuota),
                    'is_sold_out' => $category->quota && $remainingQuota <= 0,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'quotas' => $quotas,
        ]);
    }

    /**
     * Show payment instruction page
     */
    public function payment($slug, \App\Models\Transaction $transaction)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        
        // Security check: ensure transaction belongs to this event
        if ($transaction->event_id !== $event->id) {
            abort(404);
        }
        
        // Ensure transaction is moota and pending
        if ($transaction->payment_gateway !== 'moota' || $transaction->payment_status !== 'pending') {
             return redirect()->route('events.show', $slug)->with('info', 'Transaksi tidak valid atau sudah dibayar.');
        }

        return view('events.payment', [
            'event' => $event,
            'transaction' => $transaction,
            'bankAccounts' => config('moota.bank_accounts'),
            'instructions' => AppSettings::get('moota_instructions'),
        ]);
    }

    /**
     * Store registration
     */
    public function store(Request $request, $slug)
    {
        \Illuminate\Support\Facades\Log::info('Registration Request Received', [
            'slug' => $slug,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_ajax' => $request->ajax(),
            'content_type' => $request->header('Content-Type'),
        ]);

        $event = Event::where('slug', $slug)->firstOrFail();

        try {
            $transaction = $this->storeAction->execute($request, $event);

            // Handle Moota Redirect
            if ($transaction->payment_gateway === 'moota' && $transaction->payment_status === 'pending') {
                 if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Registrasi berhasil! Silakan lakukan pembayaran.',
                        'payment_gateway' => 'moota',
                        'payment_status' => $transaction->payment_status,
                        'transaction_id' => $transaction->id,
                        'registration_id' => $transaction->public_ref,
                        'final_amount' => (float) ($transaction->final_amount ?? 0),
                        'unique_code' => (int) ($transaction->unique_code ?? 0),
                        'redirect_url' => route('events.payment', ['slug' => $slug, 'transaction' => $transaction->id]),
                    ]);
                }
                
                return redirect()->route('events.payment', ['slug' => $slug, 'transaction' => $transaction->id]);
            }

            // If AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi berhasil!',
                    'snap_token' => $transaction->snap_token,
                    'transaction_id' => $transaction->id,
                    'registration_id' => $transaction->public_ref,
                    'testing_mode' => config('midtrans.testing_mode', false),
                ]);
            }

            // Redirect back with success message and snap token
            return redirect()->route('events.show', $slug)
                ->with('success', 'Registrasi berhasil!')
                ->with('snap_token', $transaction->snap_token);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            // If AJAX request, return JSON error
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'message' => $e->getMessage(),
                ], 400);
            }

            return redirect()->route('events.show', [
                'slug' => $slug,
                'payment' => 'failed',
                'error_message' => $e->getMessage(),
            ])
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}
