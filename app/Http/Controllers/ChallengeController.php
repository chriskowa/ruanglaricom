<?php

namespace App\Http\Controllers;

use App\Models\ChallengeActivity;
use App\Models\LeaderboardStat;
use App\Models\OtpToken;
use App\Models\ProgramEnrollment;
use App\Models\User;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ChallengeController extends Controller
{
    public function index(Request $request)
    {
        // Get sort parameter, default to percentage
        $sortBy = $request->get('sort', 'percentage');

        $query = LeaderboardStat::with('user');

        switch ($sortBy) {
            case 'streak':
                $query->orderBy('streak', 'desc');
                break;
            case 'pace':
                $query->orderBy('pace', 'asc');
                break;
            case 'percentage':
            default:
                $query->orderBy('percentage', 'desc');
                break;
        }

        // Secondary sort by active_days desc
        $query->orderBy('active_days', 'desc');

        $runners = $query->get();

        // Prepare data for Vue to avoid complex Blade logic
        $runnersJson = $runners->map(function ($stat) {
            $user = $stat->user;
            $avatar = null;
            if ($user && $user->avatar) {
                $raw = $user->avatar;
                if (str_starts_with($raw, 'http')) {
                    $avatar = $raw;
                } elseif (Str::startsWith($raw, ['storage/', '/storage/'])) {
                    $avatar = url('/').'/'.ltrim($raw, '/');
                } else {
                    $avatar = asset('storage/'.ltrim($raw, '/'));
                }
            } else {
                $avatar = 'https://ui-avatars.com/api/?name='.urlencode($user->name ?? 'Runner');
            }

            return [
                'user_id' => $stat->user_id,
                'name' => $user->name ?? 'Runner',
                'avatar' => $avatar,
                'active_days' => $stat->active_days,
                'percentage' => $stat->percentage,
                'streak' => $stat->streak,
                'qualified' => $stat->qualified,
                'old_pb' => $stat->old_pb,
                'new_pb' => $stat->new_pb,
                'gap' => $stat->gap,
                'pace' => $stat->pace ?? '0:00',
            ];
        });

        return view('challenge.index', compact('runners', 'sortBy', 'runnersJson'));
    }

    public function create()
    {
        // Check if user is enrolled
        $activities = collect([]);

        if (Auth::check()) {
            $activities = ChallengeActivity::where('user_id', Auth::id())
                ->orderBy('date', 'desc')
                ->get();
        }

        return view('challenge.submit', compact('activities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'distance' => 'required|numeric|min:0.01',
            'duration_hours' => 'required|integer|min:0',
            'duration_minutes' => 'required|integer|min:0|max:59',
            'duration_seconds' => 'required|integer|min:0|max:59',
            'image' => 'required|image|max:5120',
            'strava_link' => 'nullable|url',
            'time_hour' => 'required|integer|min:1|max:12',
            'time_minute' => 'required|integer|min:0|max:59',
            'time_ampm' => 'required|in:AM,PM',
        ]);

        $user = Auth::user();

        $hour12 = (int) $request->time_hour;
        $minute = (int) $request->time_minute;
        $ampm = $request->time_ampm;
        $hour24 = $hour12 % 12;
        if ($ampm === 'PM') {
            $hour24 += 12;
        }
        $activityTime = sprintf('%02d:%02d:00', $hour24, $minute);

        $existing = ChallengeActivity::where('user_id', $user->id)
            ->where('date', $request->date)
            ->where('activity_time', $activityTime)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Duplikat aktivitas: tanggal dan waktu yang sama sudah disetor.',
            ], 422);
        }

        $totalSeconds = ($request->duration_hours * 3600) + ($request->duration_minutes * 60) + $request->duration_seconds;

        if ($totalSeconds <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Durasi tidak boleh 0!',
            ], 422);
        }

        $imagePath = $request->file('image')->store('activity_proofs', 'public');

        ChallengeActivity::create([
            'user_id' => $user->id,
            'date' => $request->date,
            'activity_time' => $activityTime,
            'distance' => $request->distance,
            'duration_seconds' => $totalSeconds,
            'image_path' => $imagePath,
            'strava_link' => $request->strava_link,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lari berhasil disetor dan menunggu persetujuan admin!',
            'is_pb' => false,
        ]);
    }

    private function comparePace($pace1, $pace2)
    {
        // Returns -1 if pace1 < pace2 (faster), 0 if equal, 1 if pace1 > pace2 (slower)
        [$m1, $s1] = explode(':', $pace1);
        [$m2, $s2] = explode(':', $pace2);

        $sec1 = $m1 * 60 + $s1;
        $sec2 = $m2 * 60 + $s2;

        return $sec1 <=> $sec2;
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'whatsapp' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'gender' => 'required|in:Pria,Wanita',
            'pb_5km' => 'nullable|string',
            'strava_url' => 'nullable|url',
            'avatar' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'valid_proof' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Format Phone
        $phone = preg_replace('/[^0-9]/', '', $data['whatsapp']);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        } elseif (! str_starts_with($phone, '62')) {
            $phone = '62'.$phone;
        }

        // Check if user exists by phone
        if (User::where('phone', $phone)->exists()) {
            return response()->json(['success' => false, 'message' => 'Nomor WhatsApp sudah terdaftar.'], 422);
        }

        // Upload Avatar
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        // Upload Proof
        $proofPath = $request->file('valid_proof')->store('challenge_proofs', 'public');

        // Normalize PB 5K (accept MM:SS or HH:MM:SS)
        $pb5k = null;
        if (! empty($data['pb_5km'])) {
            $raw = trim($data['pb_5km']);
            if (preg_match('/^\d{1,2}:\d{2}$/', $raw)) {
                [$m, $s] = explode(':', $raw);
                $pb5k = sprintf('%02d:%02d:%02d', 0, (int) $m, (int) $s);
            } elseif (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $raw)) {
                [$h, $m, $s] = explode(':', $raw);
                $pb5k = sprintf('%02d:%02d:%02d', (int) $h, (int) $m, (int) $s);
            }
        }

        // Generate username from name (fallback unique suffix)
        $usernameBase = Str::slug($data['name']);
        $username = $usernameBase ?: 'runner';
        if (User::where('username', $username)->exists()) {
            $username = $username.'-'.substr(uniqid('', true), -4);
        }

        // Create User
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $username,
            'phone' => $phone,
            'password' => Hash::make($data['password']),
            'role' => 'runner',
            'gender' => strtolower($data['gender']) === 'pria' ? 'male' : 'female',
            'avatar' => $avatarPath,
            'is_active' => false,
            'strava_url' => $data['strava_url'] ?? null,
            'pb_5k' => $pb5k,
            'audit_history' => [
                [
                    'at' => now()->toIsoString(),
                    'context' => 'challenge_registration',
                    'pb_5km' => $data['pb_5km'] ?? null,
                    'valid_proof' => $proofPath
                ]
            ]
        ]);

        // Generate OTP
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        OtpToken::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        // Send OTP
        $otpChannel = env('OTP_CHANNEL', 'whatsapp');
        $successMsg = 'Kami telah mengirim OTP ke WhatsApp Anda.';

        if ($otpChannel === 'email') {
            try {
                Mail::raw('Kode OTP 40 Days Challenge Anda: '.$code.' (berlaku 10 menit)', function ($message) use ($user) {
                    $message->to($user->email)->subject('Kode OTP 40 Days Challenge');
                });
                $successMsg = 'Kami telah mengirim OTP ke Email Anda.';
            } catch (\Exception $e) {
                Log::error('Email OTP failed: '.$e->getMessage());
            }
        } else {
            \App\Helpers\WhatsApp::send($data['whatsapp'], 'Kode OTP 40 Days Challenge: '.$code.' (berlaku 10 menit) Gabung Grup Untuk Pengumuman `https://chat.whatsapp.com/Ht9mz3P3Tje9xGBpl73Htg` ');
        }

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'message' => $successMsg
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'otp' => 'required|string|size:6',
        ]);

        $token = OtpToken::where('user_id', $request->user_id)
            ->where('code', $request->otp)
            ->where('used', false)
            ->first();

        if (! $token || $token->expires_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'Kode OTP tidak valid atau kedaluwarsa.'], 422);
        }

        $token->update(['used' => true]);
        $user = User::findOrFail($request->user_id);
        $user->update(['is_active' => true]);

        Auth::login($user);

        // Enroll in Program
        $program = Program::where('hardcoded', '40days')->first();

        if ($program) {
            $exists = ProgramEnrollment::where('runner_id', $user->id)
                ->where('program_id', $program->id)
                ->exists();

            if (! $exists) {
                ProgramEnrollment::create([
                    'runner_id' => $user->id,
                    'program_id' => $program->id,
                    'status' => 'active',
                    'payment_status' => 'paid',
                    'start_date' => now()->addDay(),
                    'end_date' => now()->addWeeks($program->duration_weeks ?? 8),
                ]);
                $program->increment('enrolled_count');
            }
        }

        return response()->json([
            'success' => true,
            'redirect_url' => route('runner.calendar'),
            'message' => 'Verifikasi berhasil!'
        ]);
    }

    public function join(Request $request)
    {
        // Placeholder for join method if called directly, but logic seems to be in verifyOtp enrollment
        return response()->json(['success' => false, 'message' => 'Silakan registrasi melalui form.'], 400);
    }
}
