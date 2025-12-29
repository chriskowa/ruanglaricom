<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\City;
use Illuminate\Http\Request;

class CoachListController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'coach')
            ->with(['city.province'])
            ->withAvg('programs', 'average_rating')
            ->withCount('programs');

        // Filter by Search (Name)
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by Location (City)
        if ($request->has('city_id') && $request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        // Filter by Rating
        if ($request->has('rating') && $request->rating) {
            $query->having('programs_average_rating', '>=', $request->rating);
        }
        
        // Sorting
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'rating_high':
                    $query->orderByDesc('programs_average_rating');
                    break;
                case 'popular':
                    $query->orderByDesc('programs_count');
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        $coaches = $query->paginate(12)->withQueryString();
        
        if ($request->ajax()) {
            return view('coaches.partials.list', compact('coaches'))->render();
        }

        $cities = City::orderBy('name')->get();

        return view('coaches.index', compact('coaches', 'cities'));
    }
}
