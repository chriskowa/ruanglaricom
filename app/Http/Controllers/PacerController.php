<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Pacer;
use App\Models\PacerBooking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PacerController extends Controller
{
    public function index(Request $request)
    {
        $query = Pacer::with(['user.city'])
            ->whereHas('user', function (Builder $q) {
                $q->where('is_pacer', true);
            });

        // Filter by City
        if ($request->filled('city_id')) {
            $query->whereHas('user', function (Builder $q) use ($request) {
                $q->where('city_id', $request->city_id);
            });
        }

        // Search by Name or Nickname
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                })
                    ->orWhere('nickname', 'like', "%{$search}%");
            });
        }

        // Filter by Pace
        if ($request->filled('pace')) {
            $query->where('pace', 'like', '%'.$request->pace.'%');
        }

        // Filter by PB (Faster than or equal to input)
        // Assuming input format is H:i:s or similar
        $pbFields = ['pb_5k', 'pb_10k', 'pb_hm', 'pb_fm'];
        foreach ($pbFields as $field) {
            if ($request->filled($field)) {
                $query->whereHas('user', function (Builder $q) use ($field, $request) {
                    $q->where($field, '<=', $request->input($field))
                        ->whereNotNull($field)
                        ->where($field, '!=', '');
                });
            }
        }

        // Existing filters (Category) - usually handled by frontend but can be backend too
        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        $pacers = $query->orderBy('verified', 'desc')->orderBy('total_races', 'desc')->get();

        // Get cities that actually have pacers
        $cities = City::whereHas('users.pacer')->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'pacers' => $pacers,
                'cities' => $cities,
                'count' => $pacers->count(),
            ]);
        }

        return view('pacer.index', compact('pacers', 'cities'));
    }

    public function show(string $slug)
    {
        $pacer = Pacer::with(['user.city'])->where('seo_slug', $slug)->firstOrFail();

        $contactUnlocked = false;
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->isAdmin() || ($user->is_pacer && $pacer->user_id === $user->id)) {
                $contactUnlocked = true;
            } elseif ($user->isRunner()) {
                $contactUnlocked = PacerBooking::where('runner_id', $user->id)
                    ->where('pacer_id', $pacer->id)
                    ->whereIn('status', ['paid', 'confirmed', 'completed'])
                    ->exists();
            }
        }

        return view('pacer.profile', compact('pacer', 'contactUnlocked'));
    }
}
