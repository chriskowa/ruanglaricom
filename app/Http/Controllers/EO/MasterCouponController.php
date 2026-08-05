<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterCouponController extends Controller
{
    /**
     * Display a listing of coupons.
     */
    public function index(Request $request)
    {
        $query = Coupon::whereHas('event', function ($q) {
            $q->where('user_id', auth()->id());
        });

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('code', 'like', '%'.$request->search.'%');
        }

        // Filter by Event
        if ($request->has('event_id') && $request->event_id) {
            $query->where('event_id', $request->event_id);
        }

        $coupons = $query->with('event')->latest()->paginate(10);

        if ($request->ajax()) {
            return view('eo.coupons._list', compact('coupons'));
        }

        $events = Event::where('user_id', auth()->id())->latest()->get(['id', 'name']);

        return view('eo.coupons.index', compact('coupons', 'events'));
    }

    /**
     * Show the form for creating a new coupon.
     */
    public function create()
    {
        $events = Event::where('user_id', auth()->id())->latest()->get(['id', 'name', 'start_at']);

        return view('eo.coupons.create', compact('events'));
    }

    /**
     * Store a newly created coupon in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'nullable|exists:events,id',
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'min_transaction_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'start_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:start_at',
            'is_stackable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'applicable_categories' => 'nullable|array',
            'applicable_categories.*' => 'integer|exists:race_categories,id',
        ]);

        if (! empty($validated['event_id'])) {
            $event = Event::findOrFail($validated['event_id']);
            if ($event->user_id !== auth()->id()) {
                abort(403);
            }
        } else {
            $validated['event_id'] = null;
        }

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;
        $validated['is_stackable'] = $request->has('is_stackable') ? (bool) $request->input('is_stackable') : false;
        $validated['code'] = strtoupper($validated['code']);

        // Default values for nullable fields to avoid null violation if DB default is missing or strict
        $validated['min_transaction_amount'] = $validated['min_transaction_amount'] ?? 0;

        $coupon = Coupon::create($validated);

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kupon berhasil dibuat!',
                'coupon' => $coupon,
            ]);
        }

        return redirect()->route('eo.coupons.index')
            ->with('success', 'Kupon berhasil dibuat!');
    }

    /**
     * Show the form for editing the specified coupon.
     */
    public function edit(Coupon $coupon)
    {
        if ($coupon->event->user_id !== auth()->id()) {
            abort(403);
        }

        $events = Event::where('user_id', auth()->id())->latest()->get(['id', 'name', 'start_at']);

        // Load categories for the selected event to populate the checklist if needed
        $coupon->event->load('categories');

        return view('eo.coupons.edit', compact('coupon', 'events'));
    }

    /**
     * Update the specified coupon in storage.
     */
    public function update(Request $request, Coupon $coupon)
    {
        if ($coupon->event->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,'.$coupon->id,
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'min_transaction_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'start_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:start_at',
            'is_stackable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'applicable_categories' => 'nullable|array',
            'applicable_categories.*' => 'integer|exists:race_categories,id',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_stackable'] = $request->has('is_stackable');
        $validated['code'] = strtoupper($validated['code']);

        // Default values for nullable fields to avoid null violation
        $validated['min_transaction_amount'] = $validated['min_transaction_amount'] ?? 0;

        // Don't update event_id to prevent confusion or security issues, or allow it but check auth
        // Usually coupons are tied to an event. If we allow changing event, we must verify ownership.
        // For now let's assume event_id is fixed on creation or add it if needed.
        // If we want to allow changing event, we need to validate 'event_id' again.

        $coupon->update($validated);

        return redirect()->route('eo.coupons.index')
            ->with('success', 'Kupon berhasil diperbarui!');
    }

    /**
     * Remove the specified coupon from storage.
     */
    public function destroy(Coupon $coupon)
    {
        if ($coupon->event->user_id !== auth()->id()) {
            abort(403);
        }

        $coupon->delete();

        return redirect()->route('eo.coupons.index')
            ->with('success', 'Kupon berhasil dihapus!');
    }

    /**
     * Generate a random unique code.
     */
    public function generateCode(Request $request)
    {
        $prefix = $request->input('prefix', 'PROMO');
        $code = strtoupper($prefix.Str::random(5));

        // Ensure uniqueness
        while (Coupon::where('code', $code)->exists()) {
            $code = strtoupper($prefix.Str::random(5));
        }

        return response()->json(['code' => $code]);
    }

    /**
     * Search coupons for multi-select input.
     */
    public function search(Request $request)
    {
        $term = trim((string) $request->input('q', ''));

        $query = Coupon::where(function ($q) {
            $q->whereHas('event', function ($eq) {
                $eq->where('user_id', auth()->id());
            })->orWhereNull('event_id');
        });

        if ($term !== '') {
            $query->where('code', 'like', '%'.strtoupper($term).'%');
        }

        $coupons = $query->latest()->limit(15)->get();

        return response()->json([
            'success' => true,
            'coupons' => $coupons,
        ]);
    }
}
