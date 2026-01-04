<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->load('wallet');

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
        ]);
    }
}
