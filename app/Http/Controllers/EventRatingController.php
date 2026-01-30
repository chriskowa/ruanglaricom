<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRating;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class EventRatingController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'fingerprint' => ['required', 'string', 'min:8', 'max:500'],
        ]);

        $event = Event::where('slug', $slug)
            ->whereIn('status', ['published', 'archived'])
            ->firstOrFail();

        $cookieName = 'rl_rating_id';
        $cookieValue = $request->cookie($cookieName) ?: (string) Str::uuid();

        $salt = (string) config('app.key');
        $cookieHash = hash('sha256', $cookieValue . '|' . $salt);
        $ipHash = hash('sha256', (string) $request->ip() . '|' . $salt);
        $fingerprintHash = hash('sha256', (string) $validated['fingerprint'] . '|' . $salt);

        $isDuplicate = EventRating::where('event_id', $event->id)
            ->where(function ($q) use ($cookieHash, $ipHash, $fingerprintHash) {
                $q->where(function ($q2) use ($cookieHash, $ipHash) {
                    $q2->where('cookie_hash', $cookieHash)->where('ip_hash', $ipHash);
                })->orWhere(function ($q2) use ($cookieHash, $fingerprintHash) {
                    $q2->where('cookie_hash', $cookieHash)->where('fingerprint_hash', $fingerprintHash);
                })->orWhere(function ($q2) use ($ipHash, $fingerprintHash) {
                    $q2->where('ip_hash', $ipHash)->where('fingerprint_hash', $fingerprintHash);
                });
            })
            ->exists();

        $cookie = cookie(
            $cookieName,
            $cookieValue,
            60 * 24 * 365,
            '/',
            null,
            app()->environment('production'),
            true,
            false,
            'Lax'
        );

        if (! Schema::hasTable('event_ratings')) {
            return response()->json([
                'message' => 'Sistem rating sedang dalam proses aktivasi. Silakan coba lagi nanti.',
            ], 503)->withCookie($cookie);
        }

        if ($isDuplicate) {
            return response()->json([
                'message' => 'Anda sudah pernah memberikan rating untuk event ini.',
            ], 409)->withCookie($cookie);
        }

        EventRating::create([
            'event_id' => $event->id,
            'rating' => $validated['rating'],
            'cookie_hash' => $cookieHash,
            'ip_hash' => $ipHash,
            'fingerprint_hash' => $fingerprintHash,
        ]);

        $stats = null;
        try {
            $stats = EventRating::where('event_id', $event->id)
                ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as rating_count')
                ->first();
        } catch (\Throwable $e) {
            $stats = null;
        }

        return response()->json([
            'message' => 'Rating berhasil dikirim.',
            'average_rating' => round((float) ($stats->avg_rating ?? 0), 2),
            'rating_count' => (int) ($stats->rating_count ?? 0),
        ])->withCookie($cookie);
    }
}
