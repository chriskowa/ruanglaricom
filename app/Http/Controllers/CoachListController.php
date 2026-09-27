<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\User;
use Illuminate\Http\Request;

class CoachListController extends Controller
{
    public function index(Request $request)
    {
        // Scope for real, human-curated programs (excluding AI VDOT generator plans)
        $nonAiProgramFilter = function ($q) {
            $q->where('is_published', true)
              ->where('is_active', true)
              ->where(function ($sq) {
                  $sq->whereNull('is_self_generated')->orWhere('is_self_generated', false);
              })
              ->where(function ($sq) {
                  $sq->whereNull('is_vdot_generated')->orWhere('is_vdot_generated', false);
              });
        };

        // Query only real coaches (exclude runners who generated AI plans, Coach AI chatbot, and test accounts)
        $query = User::where('role', 'coach')
            ->where('is_active', true)
            ->whereNotIn('email', ['ai-coach@ruanglari.com', 'testcoach@ruanglari.com'])
            ->where('username', '!=', 'coach-ai')
            ->where('username', '!=', 'test-coach')
            ->where('name', 'not like', '%Coach AI%')
            ->where('name', 'not like', '%AI Coach%')
            ->where('name', 'not like', '%Test Coach%')
            ->whereHas('programs', $nonAiProgramFilter)
            ->with([
                'city.province',
                'programs' => function ($q) use ($nonAiProgramFilter) {
                    $nonAiProgramFilter($q);
                    $q->select('id', 'coach_id', 'title', 'slug', 'distance_target', 'difficulty', 'price');
                }
            ])
            ->withAvg(['programs' => function ($q) use ($nonAiProgramFilter) {
                $nonAiProgramFilter($q);
            }], 'average_rating')
            ->withCount(['programs' => function ($q) use ($nonAiProgramFilter) {
                $nonAiProgramFilter($q);
            }]);

        // Smart Search: Name, Username, City Name, or Non-AI Program Topic
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search, $nonAiProgramFilter) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('username', 'like', '%'.$search.'%')
                  ->orWhereHas('city', function ($cq) use ($search) {
                      $cq->where('name', 'like', '%'.$search.'%');
                  })
                  ->orWhereHas('programs', function ($pq) use ($search, $nonAiProgramFilter) {
                      $nonAiProgramFilter($pq);
                      $pq->where(function ($sub) use ($search) {
                          $sub->where('title', 'like', '%'.$search.'%')
                              ->orWhere('description', 'like', '%'.$search.'%');
                      });
                  });
            });
        }

        // Filter by Location (City ID or City Name)
        if ($request->filled('city_id')) {
            $cityParam = trim($request->city_id);
            if (is_numeric($cityParam)) {
                $query->where('city_id', $cityParam);
            } else {
                $citySlug = strtolower($cityParam);
                if ($citySlug === 'bali') {
                    $query->where(function ($q) {
                        $q->whereHas('city', function ($cq) {
                            $cq->where('name', 'like', '%Denpasar%')
                              ->orWhere('name', 'like', '%Bali%')
                              ->orWhere('seourl', 'like', '%denpasar%');
                        })->orWhereHas('city.province', function ($pq) {
                            $pq->where('name', 'like', '%Bali%');
                        });
                    });
                } else {
                    $query->where(function ($q) use ($cityParam) {
                        $q->whereHas('city', function ($cq) use ($cityParam) {
                            $cq->where('name', 'like', '%'.$cityParam.'%')
                              ->orWhere('seourl', 'like', '%'.$cityParam.'%');
                        })->orWhereHas('city.province', function ($pq) use ($cityParam) {
                            $pq->where('name', 'like', '%'.$cityParam.'%');
                        });
                    });
                }
            }
        }

        // Filter by Training Goal / Program Distance Target
        if ($request->filled('distance')) {
            $dist = strtolower(trim($request->distance));
            if ($dist === 'mulai_lari' || $dist === 'mulai-lari') {
                $query->where(function ($q) use ($nonAiProgramFilter) {
                    $q->whereHas('programs', function ($sub) use ($nonAiProgramFilter) {
                        $nonAiProgramFilter($sub);
                        $sub->where(function ($s) {
                            $s->where('difficulty', 'beginner')
                              ->orWhere('distance_target', '5k')
                              ->orWhere('title', 'like', '%pemula%')
                              ->orWhere('title', 'like', '%mulai%');
                        });
                    });
                });
            } elseif ($dist === 'performance') {
                $query->whereHas('programs', function ($q) use ($nonAiProgramFilter) {
                    $nonAiProgramFilter($q);
                    $q->where(function ($sub) {
                        $sub->where('difficulty', 'advanced')
                            ->orWhere('title', 'like', '%speed%')
                            ->orWhere('title', 'like', '%performance%')
                            ->orWhere('title', 'like', '%pace%')
                            ->orWhere('title', 'like', '%pb%');
                    });
                });
            } elseif (in_array($dist, ['race', 'race_prep', 'race-prep'])) {
                $query->whereHas('programs', function ($q) use ($nonAiProgramFilter) {
                    $nonAiProgramFilter($q);
                    $q->where(function ($sub) {
                        $sub->whereIn('distance_target', ['10k', '21k', 'hm', 'half_marathon', '42k', 'fm', 'marathon'])
                            ->orWhere('title', 'like', '%race%')
                            ->orWhere('title', 'like', '%finisher%')
                            ->orWhere('title', 'like', '%marathon%');
                    });
                });
            } else {
                $query->whereHas('programs', function ($q) use ($dist, $nonAiProgramFilter) {
                    $nonAiProgramFilter($q);
                    if (in_array($dist, ['21k', 'hm', 'half_marathon', 'half-marathon'])) {
                        $q->whereIn('distance_target', ['21k', 'hm', 'half_marathon']);
                    } elseif (in_array($dist, ['42k', 'fm', 'marathon', 'full_marathon'])) {
                        $q->where(function ($sq) {
                            $sq->whereIn('distance_target', ['42k', 'fm', 'marathon'])
                               ->orWhere('title', 'like', '%marathon%');
                        });
                    } else {
                        $q->where('distance_target', $dist);
                    }
                });
            }
        }

        // Filter by Experience Level (Difficulty)
        if ($request->filled('difficulty')) {
            $diff = strtolower(trim($request->difficulty));
            $query->whereHas('programs', function ($q) use ($diff, $nonAiProgramFilter) {
                $nonAiProgramFilter($q);
                $q->where('difficulty', $diff);
            });
        }

        // Filter by Training Method (Offline / Online / Hybrid)
        if ($request->filled('method')) {
            $method = strtolower(trim($request->method));
            if ($method === 'offline') {
                $query->whereNotNull('city_id');
            } elseif ($method === 'online') {
                $query->where(function ($q) use ($nonAiProgramFilter) {
                    $q->whereNull('city_id')
                      ->orWhereHas('programs', function ($pq) use ($nonAiProgramFilter) {
                          $nonAiProgramFilter($pq);
                          $pq->whereNull('city_id');
                      });
                });
            }
            // Hybrid defaults to all coaches
        }

        // Filter by Program Pricing
        if ($request->filled('pricing')) {
            if ($request->pricing === 'free') {
                $query->whereHas('programs', function ($q) use ($nonAiProgramFilter) {
                    $nonAiProgramFilter($q);
                    $q->where('price', 0);
                });
            } elseif ($request->pricing === 'paid') {
                $query->whereHas('programs', function ($q) use ($nonAiProgramFilter) {
                    $nonAiProgramFilter($q);
                    $q->where('price', '>', 0);
                });
            }
        }

        // Sorting (Default: prioritize coaches with published programs, then latest)
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'popular':
                    $query->orderByDesc('programs_count');
                    break;
                case 'name':
                    $query->orderBy('name');
                    break;
                default:
                    $query->orderByDesc('programs_count')->latest();
            }
        } else {
            $query->orderByDesc('programs_count')->latest();
        }

        $coaches = $query->paginate(12)->withQueryString();

        if ($request->ajax()) {
            return view('coaches.partials.list', compact('coaches'))->render();
        }

        $cities = City::orderBy('name')->get();

        $featuredCities = [
            ['name' => 'Malang', 'slug' => 'malang', 'desc' => 'Pelatih lari berlisensi di Malang untuk pembentukan fundamental, 5K hingga Half Marathon.'],
            ['name' => 'Tasikmalaya', 'slug' => 'tasikmalaya', 'desc' => 'Program coaching lari terstruktur di Tasikmalaya & Jawa Barat untuk persiapan half marathon.'],
            ['name' => 'Jakarta', 'slug' => 'jakarta', 'desc' => 'Bimbingan online coaching intensif untuk pelari Jakarta dengan simulasi target pace.'],
            ['name' => 'Surabaya', 'slug' => 'surabaya', 'desc' => 'Mentoring lari online & pendampingan target race untuk pelari di area Surabaya & Jawa Timur.'],
            ['name' => 'Bandung', 'slug' => 'bandung', 'desc' => 'Program latihan elevasi, endurance, dan form lari efisien untuk pelari area Bandung.'],
            ['name' => 'Bali', 'slug' => 'bali', 'desc' => 'Program coaching lari online untuk road running, beach run, dan persiapan race di Bali.'],
        ];

        return view('coaches.index', compact('coaches', 'cities', 'featuredCities'));
    }
}

