<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpToken;
use App\Helpers\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
            'phone' => 'required|string|max:20',
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

        $phone = preg_replace('/\D+/', '', $validated['phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        } elseif (! str_starts_with($phone, '62')) {
            $phone = '62'.$phone;
        }
        if (User::where('phone', $phone)->exists()) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp sudah terdaftar.'])->withInput();
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phone,
            'password' => Hash::make($validated['password']),
            'role' => 'runner',
            'is_active' => false,
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
            'is_active' => !env('LOGIN_OTP_ENABLED', true),
        ]);

        if (!env('LOGIN_OTP_ENABLED', true)) {
            Auth::login($user);
            return redirect()->route('runner.dashboard');
        }

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
                Mail::raw('Kode OTP Runner RuangLari Anda: '.$code.' (berlaku 10 menit)', function ($message) use ($user) {
                    $message->to($user->email)->subject('Kode OTP RuangLari');
                });
                $successMsg = 'Kami telah mengirim OTP ke Email Anda.';
            } catch (\Exception $e) {
                Log::error('Email OTP failed: '.$e->getMessage());
            }
        } else {
            WhatsApp::send($phone, 'Kode OTP Runner RuangLari Anda: '.$code.' (berlaku 10 menit)');
        }

        return redirect()->route('pacer.otp', ['user' => $user->id])->with('success', $successMsg);
    }
}
