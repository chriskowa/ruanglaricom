<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\OtpToken;
use App\Helpers\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => ['required', function ($attribute, $value, $fail) {
                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => env('RECAPTCHA_SECRET_KEY'),
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

                if (! $response->json('success')) {
                    $fail('Verifikasi reCAPTCHA gagal. Silakan coba lagi.');
                }
            }],
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akun belum terverifikasi. Silakan masukkan kode OTP.'
                    ], 403);
                }
                return redirect()->route('pacer.otp', ['user' => $user->id])
                    ->with('success', 'Akun belum terverifikasi. Silakan masukkan kode OTP yang dikirim.');
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $user
                ]);
            }

            // Redirect ke intended URL atau dashboard berdasarkan role
            $dashboard = match ($user->role) {
                'admin' => route('admin.dashboard'),
                'coach' => route('coach.dashboard'),
                'runner' => route('runner.dashboard'),
                'eo' => route('eo.dashboard'),
                default => route('runner.dashboard'),
            };

            return redirect()->intended($dashboard);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.'
            ], 422);
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
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:coach,runner,eo', // Admin tidak bisa didaftar via form
            'package_tier' => 'nullable|required_if:role,eo|in:lite,pro,elite',
            'g-recaptcha-response' => ['required', function ($attribute, $value, $fail) {
                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => env('RECAPTCHA_SECRET_KEY'),
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

                if (! $response->json('success')) {
                    $fail('Verifikasi reCAPTCHA gagal. Silakan coba lagi.');
                }
            }],
        ]);

        $phone = preg_replace('/\D+/', '', $validated['phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        } elseif (! str_starts_with($phone, '62')) {
            $phone = '62'.$phone;
        }

        if (User::where('phone', $phone)->exists()) {
            return back()
                ->withErrors(['phone' => 'Nomor WhatsApp sudah terdaftar.'])
                ->withInput();
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phone,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => false,
            'referral_code' => $this->generateReferralCode(),
            'package_tier' => $validated['role'] === 'eo' ? $validated['package_tier'] : 'basic',
        ]);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'locked_balance' => 0,
        ]);

        $user->update(['wallet_id' => $wallet->id]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        OtpToken::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        $otpChannel = env('OTP_CHANNEL', 'whatsapp');
        $successMsg = 'Kami telah mengirim OTP ke WhatsApp Anda.';

        if ($otpChannel === 'email') {
            try {
                Mail::raw('Kode OTP RuangLari Anda: '.$code.' (berlaku 10 menit)', function ($message) use ($user) {
                    $message->to($user->email)->subject('Kode OTP RuangLari');
                });
                $successMsg = 'Kami telah mengirim OTP ke Email Anda.';
            } catch (\Exception $e) {
                Log::error('Email OTP failed: '.$e->getMessage());
            }
        } else {
            WhatsApp::send($phone, 'Kode OTP RuangLari Anda: '.$code.' (berlaku 10 menit)');
        }

        return redirect()->route('pacer.otp', ['user' => $user->id])->with('success', $successMsg);
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

        if (! $user) {
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

        // Redirect ke intended URL atau dashboard berdasarkan role
        $dashboard = match ($user->role) {
            'admin' => route('admin.dashboard'),
            'coach' => route('coach.dashboard'),
            'runner' => route('runner.dashboard'),
            'eo' => route('eo.dashboard'),
            default => route('runner.dashboard'),
        };

        return redirect()->intended($dashboard);
    }

    private function generateReferralCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }
}
