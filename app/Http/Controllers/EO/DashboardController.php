<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->load('wallet');

        $tierKey = strtolower(trim((string) ($user->package_tier ?? 'basic')));
        if ($tierKey === 'business') {
            $tierKey = 'elite';
        }
        if ($tierKey === '') {
            $tierKey = 'basic';
        }

        $tierTextMap = [
            'elite' => 'ELITE',
            'premium' => 'PREMIUM',
            'pro' => 'PRO',
            'lite' => 'LITE',
            'basic' => 'BASIC',
        ];

        $tierClassMap = [
            'elite' => 'bg-amber-400/10 text-amber-300 border border-amber-400/25',
            'premium' => 'bg-amber-400/10 text-amber-300 border border-amber-400/25',
            'pro' => 'bg-amber-400/10 text-amber-300 border border-amber-400/25',
            'lite' => 'bg-slate-800 text-slate-200 border border-slate-700',
            'basic' => 'bg-slate-800 text-slate-300 border border-slate-700',
        ];

        $tierBadgeText = $tierTextMap[$tierKey] ?? $tierTextMap['basic'];
        $tierBadgeClass = $tierClassMap[$tierKey] ?? $tierClassMap['basic'];

        // Calculate total earnings from event ticket sales
        $totalEarnings = 0;
        if ($user->wallet) {
            $totalEarnings = $user->wallet->transactions()
                ->whereIn('type', ['commission', 'deposit'])
                ->where('status', 'completed')
                ->sum('amount');
        }

        // Event IDs organized by this EO
        $eventIds = Event::where('user_id', $user->id)->pluck('id');
        $totalEvents = $eventIds->count();

        // Count all registered runners for this EO's events (excluding cancelled)
        $totalParticipants = Participant::where(function ($query) use ($eventIds) {
            $query->whereHas('transaction', function ($q) use ($eventIds) {
                $q->whereIn('event_id', $eventIds);
            })->orWhereHas('category', function ($q) use ($eventIds) {
                $q->whereIn('event_id', $eventIds);
            });
        })->where('status', '!=', 'cancelled')->count();

        // Upcoming and recent events for overview
        $upcomingEvents = Event::where('user_id', $user->id)
            ->withCount(['categories as total_categories'])
            ->orderByDesc('start_at')
            ->limit(5)
            ->get()
            ->map(function ($ev) {
                $ev->participants_count = Participant::where(function ($query) use ($ev) {
                    $query->whereHas('transaction', function ($q) use ($ev) {
                        $q->where('event_id', $ev->id);
                    })->orWhereHas('category', function ($q) use ($ev) {
                        $q->where('event_id', $ev->id);
                    });
                })->where('status', '!=', 'cancelled')->count();
                return $ev;
            });

        return view('eo.dashboard', [
            'walletBalance' => $user->wallet ? $user->wallet->balance : 0,
            'totalEarnings' => $totalEarnings,
            'totalEvents' => $totalEvents,
            'totalParticipants' => $totalParticipants,
            'managedEvents' => $upcomingEvents,
            'upcomingEvents' => $upcomingEvents,
            'tierBadgeText' => $tierBadgeText,
            'tierBadgeClass' => $tierBadgeClass,
        ]);
    }
}

