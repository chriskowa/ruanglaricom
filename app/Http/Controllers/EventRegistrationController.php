<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Coupon;
use App\Services\EventCacheService;
use App\Actions\Events\StoreRegistrationAction;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'coupon_code' => 'required|string',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $validated['coupon_code'])
            ->where('event_id', $validated['event_id'])
            ->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Kode kupon tidak ditemukan',
            ], 404);
        }

        if (!$coupon->canBeUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'Kupon tidak valid atau sudah tidak dapat digunakan',
            ], 400);
        }

        $discountAmount = $coupon->applyDiscount($validated['total_amount']);
        $finalAmount = $validated['total_amount'] - $discountAmount;

        return response()->json([
            'success' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
            ],
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
        ]);
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
                    ->whereHas('transaction', function($query) {
                        $query->whereIn('payment_status', ['pending', 'paid']);
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
     * Store registration
     */
    public function store(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        
        try {
            $transaction = $this->storeAction->execute($request, $event);
            
            // If AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi berhasil!',
                    'snap_token' => $transaction->snap_token,
                    'testing_mode' => config('midtrans.testing_mode', false),
                ]);
            }
            
            // Redirect back with success message and snap token
            return redirect()->route('events.show', $slug)
                ->with('success', 'Registrasi berhasil!')
                ->with('snap_token', $transaction->snap_token);
        } catch (\Exception $e) {
            // If AJAX request, return JSON error
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'message' => $e->getMessage(),
                ], 400);
            }
            
            return redirect()->route('events.show', $slug)
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}
