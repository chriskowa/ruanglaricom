<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\ProgramEnrollment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->load('wallet');
        
        $activeEnrollments = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->with('program')
            ->get();

        // Calculate total earnings (income from commission, etc)
        $totalEarnings = 0;
        if ($user->wallet) {
            $totalEarnings = $user->wallet->transactions()
                ->where('type', 'commission')
                ->where('status', 'completed')
                ->sum('amount');
        }

        return view('runner.dashboard', [
            'activeEnrollments' => $activeEnrollments,
            'walletBalance' => $user->wallet ? $user->wallet->balance : 0,
            'totalEarnings' => $totalEarnings,
        ]);
    }
}
