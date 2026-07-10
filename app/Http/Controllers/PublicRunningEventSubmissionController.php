<?php

namespace App\Http\Controllers;

use App\Models\EventSubmission;
use App\Models\EventSubmissionOtp;
use App\Models\Notification;
use App\Models\User;
use App\Models\RaceDistance;
use App\Helpers\WhatsApp;
use App\Mail\AdminEventSubmissionAlertMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class PublicRunningEventSubmissionController extends Controller
{
    public function requestOtp(Request $request)
    {
        $this->ensureNotBot($request);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'g-recaptcha-response' => [
                (env('RECAPTCHA_SECRET_KEY_v3') ?: env('RECAPTCHA_SECRET_KEY')) ? 'required' : 'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $secret = env('RECAPTCHA_SECRET_KEY_v3') ?: env('RECAPTCHA_SECRET_KEY');
                    if (! $secret) {
                        return;
                    }

                    if (! $value) {
                        $fail('Silakan verifikasi reCAPTCHA terlebih dahulu.');

                        return;
                    }

                    $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => $secret,
                        'response' => $value,
                        'remoteip' => $request->ip(),
                    ]);

                    $resJson = $response->json();
                    if (! ($resJson['success'] ?? false)) {
                        $fail('Verifikasi reCAPTCHA gagal. Silakan coba lagi.');

                        return;
                    }

                    if (isset($resJson['score']) && $resJson['score'] < 0.5) {
                        $fail('Keamanan mendeteksi aktivitas mencurigakan. Silakan coba lagi.');
                    }
                },
            ],
        ]);

        $email = Str::lower(trim((string) $validated['email']));

        $emailKey = 'event_submit:otp_email:'.hash('sha256', $email);
        if (RateLimiter::tooManyAttempts($emailKey, 3)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak permintaan OTP. Silakan coba lagi beberapa menit.',
            ], 429);
        }
        RateLimiter::hit($emailKey, 10 * 60);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otp = EventSubmissionOtp::create([
            'id' => (string) Str::uuid(),
            'email' => $email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
            'max_attempts' => 5,
            'used_at' => null,
            'ip_hash' => $this->hashIp($request->ip()),
            'ua_hash' => $this->hashUa((string) $request->userAgent()),
        ]);

        try {
            Mail::raw('Kode OTP submit event lari kamu: '.$code.' (berlaku 10 menit). Jangan bagikan kode ini ke siapa pun.', function ($message) use ($email) {
                $message->to($email)->subject('OTP Submit Event Lari');
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim OTP. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'otp_id' => $otp->id,
            'expires_in_seconds' => 10 * 60,
            'message' => 'OTP sudah dikirim ke email kamu.',
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureNotBot($request);

        $validated = $request->validate([
            'otp_id' => 'required|uuid|exists:event_submission_otps,id',
            'otp_code' => 'required|string|size:6',

            'event_name' => 'required|string|max:255',
            'banner' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,webp',
            'event_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',

            'location_name' => 'required|string|max:255',
            'location_address' => 'nullable|string|max:500',

            'city_id' => 'nullable|exists:cities,id',
            'city_text' => 'nullable|string|max:255',

            'race_type_id' => 'nullable|exists:race_types,id',
            'race_distance_ids' => 'nullable|array|max:10',
            'race_distance_ids.*' => 'integer|exists:race_distances,id',
            'custom_distances' => 'nullable|string|max:500',

            'registration_link' => 'nullable|url|max:255',
            'social_media_link' => 'nullable|url|max:255',

            'organizer_name' => 'nullable|string|max:255',
            'organizer_contact' => 'nullable|string|max:255',

            'contributor_name' => 'nullable|string|max:255',
            'contributor_email' => 'required|email|max:255',
            'contributor_phone' => 'nullable|string|max:30',

            'notes' => 'nullable|string|max:2000',

            'started_at' => 'required|integer|min:0',

            'g-recaptcha-response' => [
                (env('RECAPTCHA_SECRET_KEY_v3') ?: env('RECAPTCHA_SECRET_KEY')) ? 'required' : 'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $secret = env('RECAPTCHA_SECRET_KEY_v3') ?: env('RECAPTCHA_SECRET_KEY');
                    if (! $secret) {
                        return;
                    }

                    if (! $value) {
                        $fail('Silakan verifikasi reCAPTCHA terlebih dahulu.');

                        return;
                    }

                    $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => $secret,
                        'response' => $value,
                        'remoteip' => $request->ip(),
                    ]);

                    $resJson = $response->json();
                    if (! ($resJson['success'] ?? false)) {
                        $fail('Verifikasi reCAPTCHA gagal. Silakan coba lagi.');

                        return;
                    }

                    if (isset($resJson['score']) && $resJson['score'] < 0.5) {
                        $fail('Keamanan mendeteksi aktivitas mencurigakan. Silakan coba lagi.');
                    }
                },
            ],
        ]);

        $minSeconds = 3;
        $nowMs = (int) floor(microtime(true) * 1000);
        $elapsedSeconds = (int) floor(($nowMs - (int) $validated['started_at']) / 1000);
        if ($elapsedSeconds < $minSeconds) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
            ], 422);
        }

        $email = Str::lower(trim((string) $validated['contributor_email']));

        $otp = EventSubmissionOtp::whereKey($validated['otp_id'])
            ->where('email', $email)
            ->first();

        if (! $otp || $otp->used_at || $otp->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid atau kedaluwarsa.',
            ], 422);
        }

        if ($otp->attempts >= $otp->max_attempts) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan OTP. Silakan minta OTP baru.',
            ], 429);
        }

        $ipHash = $this->hashIp($request->ip());
        $uaHash = $this->hashUa((string) $request->userAgent());
        if ($otp->ip_hash && $otp->ip_hash !== $ipHash) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid atau kedaluwarsa.',
            ], 422);
        }

        if (! Hash::check($validated['otp_code'], $otp->code_hash)) {
            $otp->increment('attempts');

            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid atau kedaluwarsa.',
            ], 422);
        }

        $otp->update([
            'used_at' => now(),
        ]);

        $fingerprint = hash('sha256', implode('|', [
            Str::lower(trim((string) $validated['event_name'])),
            (string) $validated['event_date'],
            (string) ($validated['city_id'] ?? ''),
            Str::lower(trim((string) $validated['location_name'])),
            Str::lower(trim((string) ($validated['registration_link'] ?? ''))),
            $email,
        ]));

        $recentDuplicate = EventSubmission::where('fingerprint', $fingerprint)
            ->where('created_at', '>=', now()->subDays(7))
            ->exists();

        if ($recentDuplicate) {
            return response()->json([
                'success' => true,
                'message' => 'Submit kamu sudah kami terima. Terima kasih!',
            ]);
        }

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            try {
                $file = $request->file('banner');
                $filename = Str::uuid().'.webp';

                $manager = new ImageManager(new Driver);
                $image = $manager->read($file);

                // Resize if too large (max width 1000px)
                if ($image->width() > 1000) {
                    $image->scale(width: 1000);
                }

                $encoded = $image->toWebp(quality: 80);

                Storage::disk('public')->put('event-submissions/'.$filename, (string) $encoded);
                $bannerPath = 'event-submissions/'.$filename;
            } catch (\Exception $e) {
                // Fail silently for image processing, or log it
                // We don't want to block submission if image fails?
                // Better to nullify path
                $bannerPath = null;
            }
        }

        $distanceIds = $validated['race_distance_ids'] ?? [];
        if (!empty($validated['custom_distances'])) {
            $customItems = explode(',', $validated['custom_distances']);
            foreach ($customItems as $item) {
                $itemClean = trim($item);
                if ($itemClean !== '') {
                    $distanceIds[] = $this->parseOrCreateDistance($itemClean);
                }
            }
        }
        $distanceIds = array_values(array_unique($distanceIds));

        $submission = EventSubmission::create([
            'status' => 'pending',
            'event_name' => $validated['event_name'],
            'banner' => $bannerPath,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'] ?? null,
            'location_name' => $validated['location_name'],
            'location_address' => $validated['location_address'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
            'city_text' => $validated['city_text'] ?? null,
            'race_type_id' => $validated['race_type_id'] ?? null,
            'race_distance_ids' => count($distanceIds) > 0 ? $distanceIds : null,
            'registration_link' => $validated['registration_link'] ?? null,
            'social_media_link' => $validated['social_media_link'] ?? null,
            'organizer_name' => $validated['organizer_name'] ?? null,
            'organizer_contact' => $validated['organizer_contact'] ?? null,
            'contributor_name' => $validated['contributor_name'] ?? null,
            'contributor_email' => $email,
            'contributor_phone' => $validated['contributor_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'fingerprint' => $fingerprint,
            'ip_hash' => $ipHash,
            'ua_hash' => $uaHash,
        ]);

        $admins = User::query()->where('role', 'admin')->get();
        $adminIds = $admins->pluck('id')->all();
        if ($adminIds) {
            $title = 'Submit Event Lari Baru';
            $message = $submission->event_name.' • '.optional($submission->event_date)->format('d M Y');
            $now = now();
            $rows = [];
            foreach ($adminIds as $adminId) {
                $rows[] = [
                    'user_id' => $adminId,
                    'type' => 'system',
                    'title' => $title,
                    'message' => $message,
                    'reference_type' => 'EventSubmission',
                    'reference_id' => $submission->id,
                    'is_read' => false,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            Notification::insert($rows);

            // Send WhatsApp & Mail Alert to each admin
            foreach ($admins as $admin) {
                if ($admin->email) {
                    try {
                        Mail::to($admin->email)->send(new AdminEventSubmissionAlertMail($submission));
                    } catch (\Exception $e) {
                        \Log::error('Failed to send event submission email to admin: ' . $e->getMessage());
                    }
                }

                if ($admin->phone) {
                    try {
                        $waMsg = "*[RuangLari] Pengajuan Event Baru*\n\n"
                            . "Halo Admin, ada pengajuan event lari baru yang membutuhkan review Anda:\n\n"
                            . "• Nama Event: *" . $submission->event_name . "*\n"
                            . "• Tanggal: " . ($submission->event_date ? $submission->event_date->format('d M Y') : '-') . "\n"
                            . "• Lokasi: " . $submission->location_name . "\n"
                            . "• Kota: " . ($submission->city_text ?: (optional($submission->city)->name ?: '-')) . "\n"
                            . "• Kontributor: " . $submission->contributor_name . " (" . $submission->contributor_email . ")\n\n"
                            . "Mohon lakukan review detail di dashboard admin:\n"
                            . route('admin.event-submissions.show', $submission);

                        WhatsApp::send($admin->phone, $waMsg);
                    } catch (\Exception $e) {
                        \Log::error('Failed to send event submission WhatsApp to admin: ' . $e->getMessage());
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Submit kamu berhasil. Tim kami akan review sebelum tampil di jadwal lari.',
        ]);
    }

    private function ensureNotBot(Request $request): void
    {
        if ($request->filled('website')) {
            abort(422);
        }
    }

    private function hashIp(?string $ip): ?string
    {
        if (! $ip) {
            return null;
        }

        return hash('sha256', $ip.'|'.(string) config('app.key'));
    }

    private function hashUa(string $ua): ?string
    {
        $ua = trim($ua);
        if ($ua === '') {
            return null;
        }

        return hash('sha256', $ua.'|'.(string) config('app.key'));
    }

    private function parseOrCreateDistance(string $rawName): int
    {
        $name = trim($rawName);
        $slug = Str::slug($name);

        $existing = RaceDistance::where('slug', $slug)
            ->orWhere('name', 'like', $name)
            ->first();

        if ($existing) {
            return $existing->id;
        }

        $meters = 0;
        $cleanName = strtolower($name);
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(?:k|km|kilometer|kilometers)$/', $cleanName, $matches)) {
            $meters = (int) (floatval($matches[1]) * 1000);
        } elseif (preg_match('/^(\d+(?:\.\d+)?)\s*(?:m|meter|meters)$/', $cleanName, $matches)) {
            $meters = (int) floatval($matches[1]);
        } elseif (preg_match('/^(\d+(?:\.\d+)?)\s*(?:mil|mile|miles)$/', $cleanName, $matches)) {
            $meters = (int) (floatval($matches[1]) * 1609.34);
        } elseif ($cleanName === 'half marathon' || $cleanName === 'half-marathon' || $cleanName === '21k' || $cleanName === '21.1k') {
            $meters = 21097;
        } elseif ($cleanName === 'marathon' || $cleanName === 'full marathon' || $cleanName === 'full-marathon' || $cleanName === '42k' || $cleanName === '42.2k') {
            $meters = 42195;
        }

        $newDistance = RaceDistance::create([
            'name' => $name,
            'slug' => $slug,
            'distance_meter' => $meters,
        ]);

        return $newDistance->id;
    }
}
