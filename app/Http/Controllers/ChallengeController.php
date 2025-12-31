<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ChallengeController extends Controller
{
    /**
     * Handle challenge registration and enrollment.
     * This method is now effectively part of the OTP verification flow.
     * We keep it for backward compatibility or direct calls if needed, 
     * but the main flow uses sendOtp -> verifyOtp.
     */
    public function join(Request $request)
    {
        // Deprecated in favor of verifyOtp flow for 40days challenge
        return response()->json(['message' => 'Please use OTP flow.']);
    }

    /**
     * Register User (Inactive) & Send OTP
     */
    public function sendOtp(Request $request)
    {
        // 1. Validate Registration Data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email', 
            'password' => 'required|min:8',
            'whatsapp' => 'required|string',
            'gender' => 'required|in:Pria,Wanita,Male,Female',
            'pb_5km' => 'nullable|string',
            'strava_url' => 'nullable|url',
            'avatar' => 'nullable|image|max:2048', // Max 2MB
            'valid_proof' => 'required|image|max:2048', // Bukti Valid 5K
            'terms_agreed' => 'required|accepted',
        ]);

        // 2. Format Phone
        $phone = preg_replace('/[^0-9]/', '', $data['whatsapp']);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        $data['whatsapp'] = $phone;

        // 3. Generate Username
        $baseSlug = Str::slug($data['name']);
        $username = $baseSlug;
        $i = 1;
        while(User::where('username', $username)->exists()){
            $username = $baseSlug . '.' . $i;
            $i++;
        }

        // 4. Handle Avatar
        $avatarPath = null;
        if($request->hasFile('avatar')){
            $path = $request->file('avatar')->store('avatars', 'public');
            $avatarPath = '/storage/' . $path;
        }

        // Handle Valid Proof (Banner)
        $bannerPath = null;
        if($request->hasFile('valid_proof')){
            $path = $request->file('valid_proof')->store('proofs', 'public');
            $bannerPath = '/storage/' . $path;
        }

        // 5. Check for existing user
        $user = User::where('email', $data['email'])->first();
        
        $gender = $data['gender'] === 'Pria' ? 'male' : ($data['gender'] === 'Wanita' ? 'female' : strtolower($data['gender']));

        $userData = [
            'name' => $data['name'],
            'password' => Hash::make($data['password']),
            'whatsapp' => $data['whatsapp'],
            'gender' => $gender,
            'pb_5k' => $data['pb_5km'] ?? null,
            'strava_url' => $data['strava_url'] ?? null,
            'username' => $username,
        ];

        if($avatarPath) {
            $userData['avatar'] = $avatarPath;
        }
        if($bannerPath) {
            $userData['banner'] = $bannerPath;
        }

        if ($user) {
            if ($user->is_active) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Email sudah terdaftar. Silakan login.'
                ]);
            }
            
            // Update existing inactive user
            $user->update($userData);
        } else {
            // Create New User
            $userData['email'] = $data['email'];
            $userData['role'] = 'runner';
            $userData['is_active'] = false;
            
            $user = User::create($userData);

            // Initialize Wallet
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 15000,
            ]);
        }

        // 6. Generate & Send OTP
        $code = str_pad((string)random_int(0,999999), 6, '0', STR_PAD_LEFT);
        
        \App\Models\OtpToken::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        \App\Helpers\WhatsApp::send($data['whatsapp'], 'Kode OTP 40 Days Challenge: '.$code.' (berlaku 10 menit) Gabung Grup Untuk Pengumuman https://chat.whatsapp.com/Ht9mz3P3Tje9xGBpl73Htg');

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'message' => 'OTP telah dikirim ke WhatsApp Anda.'
        ]);
    }

    /**
     * Verify OTP & Complete Enrollment
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|string',
        ]);

        // 1. Verify OTP
        $token = \App\Models\OtpToken::where('user_id', $request->user_id)
            ->where('code', $request->otp)
            ->where('used', false)
            ->first();

        if (!$token || $token->expires_at->isPast()) {
            return response()->json([
                'success' => false, 
                'message' => 'Kode OTP salah atau sudah kadaluwarsa.'
            ]);
        }

        // 2. Mark OTP as used
        $token->update(['used' => true]);

        // 3. Activate User & Login
        $user = User::find($request->user_id);
        $user->update(['is_active' => true]);
        
        Auth::login($user);

        // 4. Enroll in Program
        $program = Program::where('hardcoded', '40days')->first();

        if ($program) {
            ProgramEnrollment::firstOrCreate(
                ['program_id' => $program->id, 'runner_id' => $user->id],
                [
                    'status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addWeeks($program->duration_weeks ?? 8),
                    'payment_status' => 'paid',
                ]
            );
        } else {
            Log::warning("Challenge program with hardcoded='40days' not found.");
        }

        return response()->json([
            'success' => true,
            'redirect_url' => route('runner.calendar'),
            'message' => 'Registrasi berhasil!'
        ]);
    }
}
