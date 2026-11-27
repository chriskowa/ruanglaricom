<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Transaction;

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

        // Event IDs organized by this EO
        $eventIds = Event::where('user_id', $user->id)->pluck('id');
        $totalEvents = $eventIds->count();

        // Calculate actual net earnings from paid event transactions (matches participants page Net Revenue)
        $totalEarnings = (float) Transaction::whereIn('event_id', $eventIds)
            ->where('payment_status', 'paid')
            ->selectRaw('COALESCE(SUM(GREATEST(final_amount - COALESCE(admin_fee, 0), 0)), 0) as net')
            ->value('net');

        // Count confirmed/paid runners across this EO's events (matches Paid & Confirmed on participants page)
        $totalParticipants = Participant::whereHas('transaction', function ($q) use ($eventIds) {
            $q->whereIn('event_id', $eventIds)->where('payment_status', 'paid');
        })->count();

        // Upcoming and recent events for overview
        $upcomingEvents = Event::where('user_id', $user->id)
            ->withCount(['categories as total_categories'])
            ->orderByDesc('start_at')
            ->limit(5)
            ->get()
            ->map(function ($ev) {
                $ev->participants_count = Participant::whereHas('transaction', function ($q) use ($ev) {
                    $q->where('event_id', $ev->id)->where('payment_status', 'paid');
                })->count();
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

