<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPackage;
use App\Services\EventCacheService;
use Illuminate\Http\Request;

class EventPackageController extends Controller
{
    protected $cacheService;

    public function __construct(EventCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:1',
        ]);

        $validated['event_id'] = $event->id;

        $package = EventPackage::create($validated);
        
        // Invalidate cache
        $this->cacheService->invalidateEventCache($event);

        return redirect()->route('eo.events.show', $event)
            ->with('success', 'Paket berhasil ditambahkan!');
    }

    public function update(Request $request, EventPackage $package)
    {
        $this->authorizePackage($package);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:1',
        ]);

        $package->update($validated);
        
        // Invalidate cache
        if ($package->event) {
            $this->cacheService->invalidateEventCache($package->event);
        }

        return redirect()->route('eo.events.show', $package->event)
            ->with('success', 'Paket berhasil diperbarui!');
    }

    public function destroy(EventPackage $package)
    {
        $event = $package->event;
        $this->authorizePackage($package);
        
        $package->delete();
        
        // Invalidate cache
        if ($event) {
            $this->cacheService->invalidateEventCache($event);
        }

        return redirect()->route('eo.events.show', $event)
            ->with('success', 'Paket berhasil dihapus!');
    }

    protected function authorizeEvent(Event $event)
    {
        if ($event->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }

    protected function authorizePackage(EventPackage $package)
    {
        if (!$package->event || $package->event->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}


