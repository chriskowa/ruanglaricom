<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class JoinNowController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $event = Event::query()
                ->where('is_featured', true)
                ->where('is_active', true)
                ->where('status', 'published')
                ->where('start_at', '>=', now())
                ->orderBy('start_at', 'asc')
                ->first();

            if (! $event || ! $event->slug) {
                return redirect()->route('events.index')->with('error', 'Event belum tersedia.');
            }

            return redirect()->route('running-event.detail', $event->slug);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('events.index')->with('error', 'Terjadi kesalahan saat membuka event. Silakan coba lagi.');
        }
    }
}
