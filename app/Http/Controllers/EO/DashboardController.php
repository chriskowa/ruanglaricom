<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;

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
            'pro' => 'PRO',
            'lite' => 'LITE',
            'basic' => 'BASIC',
        ];

        $tierClassMap = [
            'elite' => 'bg-purple-500 shadow-[0_0_10px_rgba(168,85,247,0.5)]',
            'pro' => 'bg-neon-yellow shadow-[0_0_10px_rgba(234,179,8,0.5)]',
            'lite' => 'bg-cyan-500 shadow-[0_0_10px_rgba(6,182,212,0.5)]',
            'basic' => 'bg-slate-500 shadow-[0_0_10px_rgba(100,116,139,0.35)]',
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

        return view('eo.dashboard', [
            'walletBalance' => $user->wallet ? $user->wallet->balance : 0,
            'totalEarnings' => $totalEarnings,
            'tierBadgeText' => $tierBadgeText,
            'tierBadgeClass' => $tierBadgeClass,
        ]);
    }
}
