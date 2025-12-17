<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $runners = User::where('role', 'runner')
            ->with('city.province', 'wallet')
            ->paginate(12);

        return view('runner.users.index', [
            'users' => $runners,
            'title' => 'Daftar Runner',
        ]);
    }
}
