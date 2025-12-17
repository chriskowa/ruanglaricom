<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\City;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Get role from route parameter or query string
        $role = $request->route('role') ?? $request->query('role');
        
        $query = User::query();

        // Filter by role
        if ($role) {
            $query->where('role', $role);
        } else {
            // Default: show runners and coaches
            $query->whereIn('role', ['runner', 'coach']);
        }

        // Filter by gender
        if ($request->has('gender') && $request->gender) {
            $query->where('gender', $request->gender);
        }

        // Filter by city
        if ($request->has('city_id') && $request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        $users = $query->with('city.province', 'wallet')
            ->when($role === 'coach', function($q) {
                $q->with('programs');
            })
            ->paginate(12);

        $cities = City::with('province')->get();
        $title = $role === 'coach' ? 'Daftar Coach' : ($role === 'runner' ? 'Daftar Runner' : 'Daftar User');

        return view('users.index', [
            'users' => $users,
            'cities' => $cities,
            'title' => $title,
            'role' => $role,
            'filters' => $request->only(['gender', 'city_id']),
        ]);
    }
}
