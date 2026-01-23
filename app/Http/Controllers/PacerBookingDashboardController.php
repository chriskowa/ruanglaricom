<?php

namespace App\Http\Controllers;

use App\Models\PacerBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PacerBookingDashboardController extends Controller
{
    public function my()
    {
        $user = Auth::user();

        $bookings = PacerBooking::with('pacer.user')
            ->where('runner_id', $user->id)
            ->orderByDesc('id')
            ->paginate(20);

        return view('runner.bookings.pacer', compact('bookings'));
    }

    public function inbox()
    {
        $user = Auth::user();
        if (!$user->is_pacer) {
            abort(403);
        }

        $bookings = PacerBooking::with('runner', 'pacer.user')
            ->whereHas('pacer', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByDesc('id')
            ->paginate(20);

        return view('pacer.bookings.inbox', compact('bookings'));
    }
}

