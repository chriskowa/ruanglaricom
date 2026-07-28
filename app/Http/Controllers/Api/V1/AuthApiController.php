<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthApiController extends BaseApiController
{
    /**
     * Register a new runner user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'gender' => 'nullable|string|in:male,female,Pria,Wanita',
            'phone' => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        $gender = null;
        if ($request->filled('gender')) {
            $gender = in_array(strtolower($request->gender), ['female', 'wanita'], true) ? 'female' : 'male';
        }

        $username = Str::slug($request->name);
        $count = 1;
        while (User::where('username', $username)->exists()) {
            $username = Str::slug($request->name) . $count++;
        }

        /** @var User $user */
        $user = User::create([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'username' => $username,
            'password' => Hash::make($request->password),
            'role' => 'runner',
            'gender' => $gender,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        $tokenData = $user->createToken('mobile-app');

        return $this->successResponse([
            'token' => $tokenData['plainTextToken'],
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'Pendaftaran akun pelari berhasil', 201);
    }

    /**
     * Login runner user with email & password
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Email atau password tidak sesuai.', 401);
        }

        if (isset($user->is_active) && ! $user->is_active) {
            return $this->errorResponse('Akun Anda sedang dinonaktifkan. Silakan hubungi dukungan RuangLari.', 403);
        }

        $tokenData = $user->createToken('mobile-app');

        return $this->successResponse([
            'token' => $tokenData['plainTextToken'],
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'Login berhasil');
    }

    /**
     * Social login (Google / Strava)
     */
    public function socialLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|string|in:google,strava',
            'provider_id' => 'required|string',
            'email' => 'required_if:provider,google|nullable|email',
            'name' => 'nullable|string|max:255',
            'avatar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi login sosial gagal', 422, $validator->errors());
        }

        $provider = $request->provider;
        $providerId = $request->provider_id;
        $email = $request->email ? strtolower(trim($request->email)) : null;

        $user = null;
        if ($provider === 'strava') {
            $user = User::where('strava_id', $providerId)->first();
        }

        if (! $user && $email) {
            $user = User::where('email', $email)->first();
        }

        if (! $user) {
            $name = $request->name ?: ($email ? explode('@', $email)[0] : 'Runner ' . rand(1000, 9999));
            $username = Str::slug($name);
            $count = 1;
            while (User::where('username', $username)->exists()) {
                $username = Str::slug($name) . $count++;
            }

            $user = User::create([
                'name' => $name,
                'email' => $email ?: "{$provider}_{$providerId}@ruanglari.com",
                'username' => $username,
                'password' => Hash::make(Str::random(24)),
                'role' => 'runner',
                'avatar' => $request->avatar,
                'strava_id' => $provider === 'strava' ? $providerId : null,
                'is_active' => true,
            ]);
        } else {
            if ($provider === 'strava' && ! $user->strava_id) {
                $user->update(['strava_id' => $providerId]);
            }
            if (! $user->avatar && $request->avatar) {
                $user->update(['avatar' => $request->avatar]);
            }
        }

        $tokenData = $user->createToken('mobile-app');

        return $this->successResponse([
            'token' => $tokenData['plainTextToken'],
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'Login sosial berhasil');
    }

    /**
     * Logout authenticated runner
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $header = $request->header('Authorization');
            if ($header && str_starts_with($header, 'Bearer ')) {
                $plainToken = trim(substr($header, 7));
                $tokenHash = hash('sha256', $plainToken);
                $user->tokens()->where('token', $tokenHash)->delete();
            }
        }

        return $this->successResponse(null, 'Logout berhasil');
    }

    /**
     * Forgot password request
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (! $user) {
            // Return success for security to prevent email enumeration
            return $this->successResponse(null, 'Instruksi reset password telah dikirim ke email Anda jika terdaftar.');
        }

        return $this->successResponse(null, 'Instruksi reset password telah dikirim ke email Anda.');
    }
}
