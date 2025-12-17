<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $validated['event_id'] = $event->id;
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->is_active : true;

        Coupon::create($validated);

        return redirect()->route('eo.events.show', $event)
            ->with('success', 'Kupon berhasil ditambahkan!');
    }

    public function destroy(Coupon $coupon)
    {
        $event = $coupon->event;
        $this->authorizeEvent($event);
        
        $coupon->delete();

        return redirect()->route('eo.events.show', $event)
            ->with('success', 'Kupon berhasil dihapus!');
    }

    protected function authorizeEvent(Event $event)
    {
        if ($event->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}


