<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Wallet;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Redirect berdasarkan role
            return match($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'coach' => redirect()->route('coach.dashboard'),
                'runner' => redirect()->route('runner.dashboard'),
                'eo' => redirect()->route('eo.dashboard'),
                default => redirect()->route('runner.dashboard'),
            };
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showRegister(Request $request, $role = 'runner')
    {
        $role = $request->get('role', $role);
        return view('auth.register', ['role' => $role]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:coach,runner,eo', // Admin tidak bisa didaftar via form
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
            'referral_code' => $this->generateReferralCode(),
        ]);

        // Create wallet for user
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'locked_balance' => 0,
        ]);

        $user->update(['wallet_id' => $wallet->id]);

        Auth::login($user);

        return redirect()->route($user->role . '.dashboard');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // TODO: Implement email sending for password reset
        // Untuk sekarang, return success message
        return back()->with('status', 'Link reset password telah dikirim ke email Anda!');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Login via Google gagal. Silakan coba lagi.');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(\Illuminate\Support\Str::random(16)), // Random password
                'role' => 'runner', // Default role
                'is_active' => true,
                'referral_code' => $this->generateReferralCode(),
            ]);

            // Create wallet for user
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'locked_balance' => 0,
            ]);

            $user->update(['wallet_id' => $wallet->id]);
        }

        Auth::login($user);

        return redirect()->route('runner.dashboard');
    }

    private function generateReferralCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }
}
