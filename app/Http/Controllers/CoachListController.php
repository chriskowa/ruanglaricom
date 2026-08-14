<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\User;
use Illuminate\Http\Request;

class CoachListController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'coach')
            ->with([
                'city.province',
                'programs' => function ($q) {
                    $q->where('is_published', true)->select('id', 'coach_id', 'title', 'slug', 'distance_target', 'difficulty', 'price');
                }
            ])
            ->withAvg('programs', 'average_rating')
            ->withCount(['programs' => function ($q) {
                $q->where('is_published', true);
            }]);

        // Filter by Search (Name)
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter by Location (City)
        if ($request->has('city_id') && $request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        // Filter by Program Distance Target
        if ($request->has('distance') && $request->distance) {
            $dist = strtolower($request->distance);
            $query->whereHas('programs', function ($q) use ($dist) {
                $q->where('is_published', true);
                if (in_array($dist, ['21k', 'hm', 'half_marathon'])) {
                    $q->whereIn('distance_target', ['21k', 'hm', 'half_marathon']);
                } elseif (in_array($dist, ['42k', 'fm', 'marathon'])) {
                    $q->whereIn('distance_target', ['42k', 'fm', 'marathon']);
                } else {
                    $q->where('distance_target', $dist);
                }
            });
        }

        // Filter by Program Difficulty Level
        if ($request->has('difficulty') && $request->difficulty) {
            $query->whereHas('programs', function ($q) use ($request) {
                $q->where('is_published', true)
                  ->where('difficulty', $request->difficulty);
            });
        }

        // Filter by Program Pricing (Free vs Premium)
        if ($request->has('pricing') && $request->pricing) {
            if ($request->pricing === 'free') {
                $query->whereHas('programs', function ($q) {
                    $q->where('is_published', true)->where('price', 0);
                });
            } elseif ($request->pricing === 'paid') {
                $query->whereHas('programs', function ($q) {
                    $q->where('is_published', true)->where('price', '>', 0);
                });
            }
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
