<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Pacer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PacerRegistrationController extends Controller
{
    public function create()
    {
        $cities = City::with('province')->get();

        return view('pacer.register', compact('cities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:50'],
            'pace' => ['required', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'bio' => ['nullable', 'string'],
            'tags' => ['nullable', 'string'],
            'race_portfolio' => ['nullable', 'string'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'strava_url' => ['nullable', 'url', 'max:255'],
            'gender' => ['nullable', 'in:male,female,other'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'pb5k' => ['nullable', 'string', 'max:20'],
            'pb10k' => ['nullable', 'string', 'max:20'],
            'pbhm' => ['nullable', 'string', 'max:20'],
            'pbfm' => ['nullable', 'string', 'max:20'],
            'total_races' => ['nullable', 'integer', 'min:0'],
        ]);

        // Format Phone Number to start with 62
        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        } elseif (! str_starts_with($phone, '62')) {
            $phone = '62'.$phone;
        }
        $data['phone'] = $phone;

        // Handle image upload and convert to webp
        $imageUrl = null;
        if (isset($data['image'])) {
            $path = $request->file('image')->getRealPath();
            $imgInfo = getimagesize($path);
            if ($imgInfo) {
                $dstPath = storage_path('app/public/pacers/'.uniqid('pacer_').'.webp');
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
                    $imageUrl = '/pacers/'.basename($dstPath);
                }
            }
        }

        // Generate slug/username
        $slugBase = $data['name'].'-'.($data['nickname'] ?? 'pacer');
        $slug = Str::slug($slugBase);
        $i = 1;
        while (Pacer::where('seo_slug', $slug)->exists() || \App\Models\User::where('username', $slug)->exists()) {
            $slug = Str::slug($slugBase.'-'.$i);
            $i++;
        }

        // Create user as runner
        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'runner',
            'gender' => $data['gender'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'instagram_url' => $data['instagram_url'] ?? null,
            'facebook_url' => $data['facebook_url'] ?? null,
            'tiktok_url' => $data['tiktok_url'] ?? null,
            'strava_url' => $data['strava_url'] ?? null,
            'avatar' => $imageUrl,
            'username' => $slug,
            'pb_5k' => $data['pb5k'] ?? null,
            'pb_10k' => $data['pb10k'] ?? null,
            'pb_hm' => $data['pbhm'] ?? null,
            'pb_fm' => $data['pbfm'] ?? null,
            'is_pacer' => true,
            'is_active' => false,
        ]);

        $pacer = Pacer::create([
            'user_id' => $user->id,
            'seo_slug' => $slug,
            'nickname' => $data['nickname'] ?? null,
            'category' => $data['category'],
            'pace' => $data['pace'],
            'image_url' => $imageUrl,
            'whatsapp' => $data['whatsapp'] ?? $data['phone'],
            'verified' => false,
            'total_races' => $data['total_races'] ?? 0,
            'bio' => $data['bio'] ?? null,
            'stats' => [
                'pb5k' => $data['pb5k'] ?? null,
                'pb10k' => $data['pb10k'] ?? null,
                'pbhm' => $data['pbhm'] ?? null,
                'pbfm' => $data['pbfm'] ?? null,
            ],
            'tags' => isset($data['tags']) ? array_filter(array_map('trim', explode(',', $data['tags']))) : [],
            'race_portfolio' => isset($data['race_portfolio']) ? array_filter(array_map('trim', explode(',', $data['race_portfolio']))) : [],
        ]);

        if (!env('LOGIN_OTP_ENABLED', true)) {
            \Illuminate\Support\Facades\Auth::login($user);
            return redirect()->route('dashboard'); // Assuming general dashboard or pacer specific
        }

        // Create OTP
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        \App\Models\OtpToken::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        // Send OTP based on ENV
        $otpChannel = env('OTP_CHANNEL', 'whatsapp');
        $successMsg = 'Kami telah mengirim OTP ke WhatsApp Anda.';

        if ($otpChannel === 'email') {
            try {
                Mail::raw('Kode OTP PacerHub Anda: '.$code.' (berlaku 10 menit)', function ($message) use ($user) {
                    $message->to($user->email)->subject('Kode OTP PacerHub');
                });
                $successMsg = 'Kami telah mengirim OTP ke Email Anda.';
            } catch (\Exception $e) {
                // Fallback or log error
                \Illuminate\Support\Facades\Log::error('Email OTP failed: '.$e->getMessage());
            }
        } else {
            \App\Helpers\WhatsApp::send($data['phone'], 'Kode OTP PacerHub Anda: '.$code.' (berlaku 10 menit)');
        }

        return redirect()->route('pacer.otp', ['user' => $user->id])->with('success', $successMsg);
    }
}
