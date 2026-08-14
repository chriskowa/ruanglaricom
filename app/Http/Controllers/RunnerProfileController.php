<?php

namespace App\Http\Controllers;

use App\Models\User;

class RunnerProfileController extends Controller
{
    public function show($username)
    {
        $user = null;

        // 1. Check if input is numeric (ID) -> 301 redirect to canonical slug
        if (is_numeric($username)) {
            $user = User::find($username);

            if ($user && $user->username) {
                return redirect()->route('runner.profile.show', $user->username, 301);
            }
        }

        // 2. Check by exact username
        if (! $user) {
            $user = User::where('username', $username)->first();
        }

        // 3. Fallback: Check case-insensitive username or slug from name
        if (! $user) {
            $user = User::whereRaw('LOWER(username) = ?', [strtolower($username)])
                ->orWhereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [strtolower($username)])
                ->orWhereRaw('LOWER(name) = ?', [strtolower($username)])
                ->first();

            // If found and user has no username set, generate and assign clean hyphenated slug
            if ($user && empty($user->username)) {
                $baseSlug = \Illuminate\Support\Str::slug($user->name);
                $slug = $baseSlug;
                $count = 2;
                while (User::where('username', $slug)->where('id', '!=', $user->id)->exists()) {
                    $slug = $baseSlug . '-' . $count++;
                }
                $user->username = $slug;
                $user->saveQuietly();
            }
        }

        if (! $user) {
            abort(404, 'Profil runner tidak ditemukan.');
        }

        // 4. Canonical 301 redirect if requested URL differs from lowercase canonical slug
        if ($user->username && $username !== $user->username) {
            return redirect()->route('runner.profile.show', $user->username, 301);
        }

        return view('runner.profile', compact('user'));
    }
}
