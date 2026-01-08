<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\User;
use App\Models\OtpToken;
use App\Helpers\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CoachRegistrationController extends Controller
{
    public function create()
    {
        return view('coach.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'specialization' => ['required', 'string', 'max:100'],
            'experience_years' => ['required', 'integer', 'min:0'],
            'certifications' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'bio' => ['nullable', 'string'],
        ]);

        $phone = preg_replace('/\D+/', '', $data['phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        } elseif (! str_starts_with($phone, '62')) {
            $phone = '62'.$phone;
        }
        if (User::where('phone', $phone)->exists()) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp sudah terdaftar.'])->withInput();
        }

        // Create user as coach
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $phone,
            'password' => bcrypt(str()->random(12)),
            'role' => 'coach',
            'is_active' => !env('LOGIN_OTP_ENABLED', true),
            'city' => $data['city'],
        ]);

        // Handle image upload and convert to webp
        $imageUrl = null;
        if (isset($data['image'])) {
            $path = $request->file('image')->getRealPath();
            $imgInfo = getimagesize($path);
            if ($imgInfo) {
                $dstPath = storage_path('app/public/coaches/'.uniqid('coach_').'.webp');
                if (! is_dir(dirname($dstPath))) {
                    mkdir(dirname($dstPath), 0775, true);
                }
                $src = null;
                switch ($imgInfo['mime']) {
                    case 'image/jpeg': $src = imagecreatefromjpeg($path);
                        break;
                    case 'image/png': $src = imagecreatefrompng($path);
                        break;
                    case 'image/webp': $src = imagecreatefromwebp($path);
                        break;
                }
                if ($src) {
                    imagepalettetotruecolor($src);
                    imagealphablending($src, true);
                    imagesavealpha($src, true);
                    imagewebp($src, $dstPath, 70);
                    imagedestroy($src);
                    $imageUrl = '/storage/coaches/'.basename($dstPath);
                }
            }
        }

        // Create coach record
        // Note: Assuming Coach model exists or needs to be created.
        // If Coach model doesn't exist yet, we might store this in user_meta or a new table.
        // For now, I'll assume we can store basic info in user or create a Coach model.

        // Check if Coach model exists, if not create it or use user meta
        if (class_exists('App\Models\Coach')) {
            $slugBase = $user->name;
            $slug = Str::slug($slugBase);
            $i = 1;
            while (Coach::where('slug', $slug)->exists()) {
                $slug = Str::slug($slugBase.'-'.$i);
                $i++;
            }

            Coach::create([
                'user_id' => $user->id,
                'slug' => $slug,
                'specialization' => $data['specialization'],
                'experience_years' => $data['experience_years'],
                'certifications' => isset($data['certifications']) ? array_filter(array_map('trim', explode(',', $data['certifications']))) : [],
                'bio' => $data['bio'] ?? null,
                'image_url' => $imageUrl,
                'verified' => false,
                'rating' => 0,
                'review_count' => 0,
                'student_count' => 0,
            ]);
        } else {
            // Fallback if Coach model doesn't exist (though ideally it should)
            // We can just rely on the User model for now if simple
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
                Mail::raw('Kode OTP Coach RuangLari Anda: '.$code.' (berlaku 10 menit)', function ($message) use ($user) {
                    $message->to($user->email)->subject('Kode OTP RuangLari');
                });
                $successMsg = 'Kami telah mengirim OTP ke Email Anda.';
            } catch (\Exception $e) {
                Log::error('Email OTP failed: '.$e->getMessage());
            }
        } else {
            WhatsApp::send($phone, 'Kode OTP Coach RuangLari Anda: '.$code.' (berlaku 10 menit)');
        }

        // Reuse pacer OTP view/route for simplicity, or create a specific coach one if needed
        // For now, redirecting to pacer.otp is fine as it just asks for OTP for a user_id
        return redirect()->route('pacer.otp', ['user' => $user->id])->with('success', $successMsg);
    }
}
