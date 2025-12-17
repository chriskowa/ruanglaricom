<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $coaches = User::where('role', 'coach')
            ->with('city.province', 'wallet', 'programs')
            ->paginate(12);

        return view('coach.users.index', [
            'users' => $coaches,
            'title' => 'Daftar Coach',
        ]);
    }
}
