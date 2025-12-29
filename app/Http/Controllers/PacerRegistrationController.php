<?php

namespace App\Http\Controllers;

use App\Models\Pacer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PacerRegistrationController extends Controller
{
    public function create()
    {
        return view('pacer.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:120','unique:users,email'],
            'phone' => ['required','string','max:20'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:50'],
            'pace' => ['required', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'image' => ['nullable','file','mimes:jpg,jpeg,png,webp','max:1024'],
            'bio' => ['nullable', 'string'],
            'tags' => ['nullable', 'string'],
            'race_portfolio' => ['nullable', 'string'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'strava_url' => ['nullable', 'url', 'max:255'],
            'pb5k' => ['nullable', 'string', 'max:20'],
            'pb10k' => ['nullable', 'string', 'max:20'],
            'pbhm' => ['nullable', 'string', 'max:20'],
            'pbfm' => ['nullable', 'string', 'max:20'],
        ]);

        // Create user as runner
        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => bcrypt(str()->random(12)),
            'role' => 'runner',
            'instagram_url' => $data['instagram_url'] ?? null,
            'facebook_url' => $data['facebook_url'] ?? null,
            'tiktok_url' => $data['tiktok_url'] ?? null,
            'strava_url' => $data['strava_url'] ?? null,
        ]);

        // Handle image upload and convert to webp
        $imageUrl = null;
        if (isset($data['image'])) {
            $path = $request->file('image')->getRealPath();
            $imgInfo = getimagesize($path);
            if ($imgInfo) {
                $dstPath = storage_path('app/public/pacers/'.uniqid('pacer_').'.webp');
                if (!is_dir(dirname($dstPath))) mkdir(dirname($dstPath), 0775, true);
                $src = null;
                switch ($imgInfo['mime']) {
                    case 'image/jpeg': $src = imagecreatefromjpeg($path); break;
                    case 'image/png': $src = imagecreatefrompng($path); break;
                    case 'image/webp': $src = imagecreatefromwebp($path); break;
                }
                if ($src) {
                    imagepalettetotruecolor($src);
                    imagealphablending($src, true);
                    imagesavealpha($src, true);
                    imagewebp($src, $dstPath, 70);
                    imagedestroy($src);
                    $imageUrl = '/storage/pacers/'.basename($dstPath);
                }
            }
        }

        // Create pacer record
        $slugBase = $user->name . '-' . ($data['nickname'] ?? 'pacer');
        $slug = Str::slug($slugBase);
        $i = 1;
        while (Pacer::where('seo_slug', $slug)->exists()) { $slug = Str::slug($slugBase.'-'.$i); $i++; }

        $pacer = Pacer::create([
            'user_id' => $user->id,
            'seo_slug' => $slug,
            'nickname' => $data['nickname'] ?? null,
            'category' => $data['category'],
            'pace' => $data['pace'],
            'image_url' => $imageUrl,
            'whatsapp' => $data['whatsapp'] ?? $data['phone'],
            'verified' => false,
            'total_races' => 0,
            'bio' => $data['bio'] ?? null,
            'stats' => [
                'pb5k' => $data['pb5k'] ?? null, 
                'pb10k' => $data['pb10k'] ?? null, 
                'pbhm' => $data['pbhm'] ?? null, 
                'pbfm' => $data['pbfm'] ?? null
            ],
            'tags' => isset($data['tags']) ? array_filter(array_map('trim', explode(',', $data['tags']))) : [],
            'race_portfolio' => isset($data['race_portfolio']) ? array_filter(array_map('trim', explode(',', $data['race_portfolio']))) : [],
        ]);

        // Create OTP and send via WhatsApp
        $code = str_pad((string)random_int(0,999999), 6, '0', STR_PAD_LEFT);
        \App\Models\OtpToken::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        \App\Helpers\WhatsApp::send($data['phone'], 'Kode OTP PacerHub Anda: '.$code.' (berlaku 10 menit)');

        return redirect()->route('pacer.otp', ['user' => $user->id])->with('success','Kami telah mengirim OTP ke WhatsApp Anda.');
    }
}
