<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RunnerRegistrationController extends Controller
{
    public function create()
    {
        return view('runner.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'gender' => 'nullable|in:male,female,other',
            'birthdate' => 'nullable|date',
            'city_id' => 'nullable|exists:cities,id',
            'height_cm' => 'nullable|numeric|min:0|max:300',
            'weight_kg' => 'nullable|numeric|min:0|max:500',
            'pb_5k_time' => 'nullable|date_format:H:i:s',
            'pb_10k_time' => 'nullable|date_format:H:i:s',
            'pb_21k_time' => 'nullable|date_format:H:i:s',
            'pb_42k_time' => 'nullable|date_format:H:i:s',
            'cooper_distance' => 'nullable|integer|min:0',
            'resting_hr' => 'nullable|integer|min:20|max:240',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'runner',
            'gender' => $validated['gender'] ?? null,
            'birthdate' => $validated['birthdate'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
            // Optional extended profile fields (ensure columns exist or adjust to your schema)
            'height_cm' => $validated['height_cm'] ?? null,
            'weight_kg' => $validated['weight_kg'] ?? null,
            'pb_5k_time' => $validated['pb_5k_time'] ?? null,
            'pb_10k_time' => $validated['pb_10k_time'] ?? null,
            'pb_21k_time' => $validated['pb_21k_time'] ?? null,
            'pb_42k_time' => $validated['pb_42k_time'] ?? null,
            'cooper_distance' => $validated['cooper_distance'] ?? null,
            'resting_hr' => $validated['resting_hr'] ?? null,
        ]);

        Auth::login($user);

        // Redirect ke daftar program challenge
        return redirect()->route('runner.programs.challenges')
            ->with('success', 'Registrasi berhasil! Silakan pilih program tantangan.');
    }
}
