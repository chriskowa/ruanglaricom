<?php

namespace App\Actions\Events;

use App\Models\Coupon;
use App\Models\Event;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Services\EventCacheService;
use App\Services\MidtransService;
use App\Services\MootaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreRegistrationAction
{
    protected $cacheService;

    protected $midtransService;

    protected $mootaService;

    public function __construct(EventCacheService $cacheService, MidtransService $midtransService, MootaService $mootaService)
    {
        $this->cacheService = $cacheService;
        $this->midtransService = $midtransService;
        $this->mootaService = $mootaService;
    }

    public function execute(Request $request, Event $event): Transaction
    {
        // Sanitize inputs
        if ($request->has('pic_email')) {
            $request->merge(['pic_email' => trim($request->pic_email)]);
        }
        if ($request->has('coupon_code')) {
            $couponCode = trim((string) $request->coupon_code);
            if ($couponCode === '') {
                $couponCode = null;
            }
            $request->merge(['coupon_code' => $couponCode]);
        }
        if ($request->has('participants')) {
            $participants = $request->participants;
            foreach ($participants as &$p) {
                if (isset($p['email'])) {
                    $p['email'] = trim($p['email']);
                }
                if (isset($p['target_time'])) {
                    $p['target_time'] = trim((string) $p['target_time']);
                    if ($p['target_time'] === '') {
                        $p['target_time'] = null;
                    }
                }
                if (isset($p['blood_type'])) {
                    $p['blood_type'] = trim((string) $p['blood_type']);
                    if ($p['blood_type'] === '') {
                        $p['blood_type'] = null;
                    }
                }
                if (isset($p['strava_url'])) {
                    $p['strava_url'] = trim((string) $p['strava_url']);
                    if ($p['strava_url'] === '') {
                        $p['strava_url'] = null;
                    }
                }
                if (isset($p['strava_activity'])) {
                    $p['strava_activity'] = trim((string) $p['strava_activity']);
                    if ($p['strava_activity'] === '') {
                        $p['strava_activity'] = null;
                    }
                    if (empty($p['strava_url']) && !empty($p['strava_activity'])) {
                        $p['strava_url'] = $p['strava_activity'];
                    }
                }
            }
            $request->merge(['participants' => $participants]);
        }

        $recaptchaSecret = env('RECAPTCHA_SECRET_KEY_v3') ?? env('RECAPTCHA_SECRET_KEY');

        // Validate input
        $rules = [
            'pic_name' => 'required|string|max:255',
            'pic_email' => 'required|email|max:255',
            'pic_phone' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            'participants' => 'required|array|min:1',
            'participants.*.name' => 'required|string|max:255',
            'participants.*.gender' => 'required|in:male,female',
            'participants.*.email' => 'required|email|max:255',
            'participants.*.phone' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            'participants.*.id_card' => 'required|string|max:50|distinct',
            'participants.*.address' => 'required|string|max:500',
            'participants.*.category_id' => [
                'required',
                'exists:race_categories,id',
                function ($attribute, $value, $fail) use ($event) {
                    // Custom validation to ensure category belongs to event
                    $category = \App\Models\RaceCategory::find($value);
                    if (! $category || (int) $category->event_id !== (int) $event->id) {
                        $fail('Kategori tidak valid untuk event ini.');
                    }
                },
            ],
            'participants.*.emergency_contact_name' => 'required|string|max:255',
            'participants.*.emergency_contact_number' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            'participants.*.date_of_birth' => 'required|date|before:today',
            'participants.*.target_time' => ['nullable', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/'],
            'participants.*.jersey_size' => 'required|string|max:10',
            'participants.*.blood_type' => 'nullable|string|in:A,B,AB,O',
            'participants.*.strava_url' => 'nullable|string|max:500',
            'participants.*.strava_activity' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'payment_method' => 'nullable|in:midtrans,cod,moota',
            'participants.*.addons' => 'nullable|array',
            'participants.*.addons.*.name' => 'nullable|string',
            'participants.*.addons.*.selected' => 'nullable',
            'participants.*.photo' => 'nullable|string', // Base64
            'g-recaptcha-response' => [$recaptchaSecret ? 'required' : 'nullable', function ($attribute, $value, $fail) use ($request, $recaptchaSecret) {
                if (! $recaptchaSecret) {
                    return;
                }

                if (! $value) {
                    $fail('Silakan verifikasi reCAPTCHA terlebih dahulu.');

                    return;
                }

                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $recaptchaSecret,
                    'response' => $value,
                    'remoteip' => $request->ip(),
                ]);

                $body = $response->json();

                if (! ($body['success'] ?? false)) {
                    $errorCodes = isset($body['error-codes']) ? implode(', ', $body['error-codes']) : 'no-codes';
                    Log::error('reCAPTCHA verification failed in StoreRegistrationAction', ['body' => $body]);
                    $fail('Verifikasi reCAPTCHA gagal ('. $errorCodes .'). Silakan coba lagi.');

                    return;
                }

                if (env('RECAPTCHA_SECRET_KEY_v3') && isset($body['score'])) {
                    if ($body['score'] < 0.1) {
                        $fail('Verifikasi keamanan gagal (skor sangat rendah).');
                    }
                }
            }],
        ];

        if ($event->hardcoded === 'latbarkamis') {
            $rules['participants.*.date_of_birth'] = 'nullable|date|before:today';
        }

        $validated = $request->validate($rules);

        if (isset($validated['participants']) && is_array($validated['participants'])) {
            foreach ($validated['participants'] as $k => $p) {
                if (isset($p['jersey_size'])) {
                    $sz = strtoupper(trim((string)$p['jersey_size']));
                    if ($sz === 'XXL') {
                        $sz = '2XL';
                    } elseif ($sz === 'XXXL') {
                        $sz = '3XL';
                    }
                    $validated['participants'][$k]['jersey_size'] = $sz;
                }
            }
        }

        $paymentMethod = strtolower($validated['payment_method'] ?? 'midtrans');

        // For COD payment method, photo upload is mandatory
        if ($paymentMethod === 'cod') {
            foreach ($validated['participants'] as $pIdx => $pItem) {
                if (empty($pItem['photo'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "participants.{$pIdx}.photo" => ["Untuk pembayaran COD (Bayar di Tempat), peserta wajib mengunggah foto identitas/wajah."],
                    ]);
                }
            }
        }

        // Calculate Addons Price (Per Participant)
        $totalAddonsPrice = 0;
        $participantsWithAddons = []; // Map participant index -> addons data
        $allSelectedAddonsForPic = []; // Aggregate for PIC data

        foreach ($validated['participants'] as $pIndex => $pData) {
            $pAddons = [];
            if (! empty($pData['addons']) && is_array($pData['addons']) && ! empty($event->addons)) {
                foreach ($pData['addons'] as $addonInput) {
                    // Check if selected (checkbox)
                    if (isset($addonInput['selected']) && $addonInput['selected']) {
                        // Find matching event addon to get trusted price
                        foreach ($event->addons as $eventAddon) {
                            if ($eventAddon['name'] === $addonInput['name']) {
                                $price = isset($eventAddon['price']) ? (int) $eventAddon['price'] : 0;
                                $totalAddonsPrice += $price;

                                $addonData = [
                                    'name' => $eventAddon['name'],
                                    'price' => $price,
                                ];

                                $pAddons[] = $addonData;
                                $allSelectedAddonsForPic[] = $addonData;
                                break;
                            }
                        }
                    }
                }
            }
            $participantsWithAddons[$pIndex] = $pAddons;
        }

        // Special flow for latbarkamis: auto-create runner accounts
        $createdUsers = [];
        if ($event->hardcoded === 'latbarkamis') {
            // Create or get PIC user
            $picEmail = strtolower($validated['pic_email']);
            $picUser = User::where('email', $picEmail)->first();
            if (! $picUser) {
                try {
                    $picPassword = Str::random(10);
                    $picUser = User::create([
                        'name' => $validated['pic_name'],
                        'email' => $picEmail,
                        'phone' => $validated['pic_phone'],
                        'password' => $picPassword,
                        'role' => 'runner',
                        'is_active' => true,
                    ]);
                    Cache::put('new_user_password:'.$picEmail, $picPassword, now()->addHours(6));
                    $createdUsers[$picEmail] = true;
                } catch (\Illuminate\Database\QueryException $e) {
                    $driverCode = $e->errorInfo[1] ?? null;
                    if ((int) $driverCode === 1062) {
                        Log::warning('PIC user creation skipped due to duplicate email', ['email' => $picEmail]);
                        $picUser = User::where('email', $picEmail)->first();
                    } else {
                        throw $e;
                    }
                }
            }
            // Create or get participant users
            foreach ($validated['participants'] as $participant) {
                $email = strtolower($participant['email']);
                $user = User::where('email', $email)->first();
                if (! $user) {
                    try {
                        $password = Str::random(10);
                        User::create([
                            'name' => $participant['name'],
                            'email' => $email,
                            'phone' => $participant['phone'],
                            'password' => $password,
                            'role' => 'runner',
                            'is_active' => true,
                        ]);
                        Cache::put('new_user_password:'.$email, $password, now()->addHours(6));
                        $createdUsers[$email] = true;
                    } catch (\Illuminate\Database\QueryException $e) {
                        $driverCode = $e->errorInfo[1] ?? null;
                        if ((int) $driverCode === 1062) {
                            Log::warning('Participant user creation skipped due to duplicate email', ['email' => $email]);
                        } else {
                            throw $e;
                        }
                    }
                }
            }
        }

        $fingerprint = [
            'event_id' => $event->id,
            'pic_email' => strtolower($validated['pic_email']),
            'participants' => collect($validated['participants'])->map(function ($p) {
                return [
                    'email' => strtolower($p['email']),
                    'id_card' => $p['id_card'],
                    'category_id' => $p['category_id'],
                ];
            })->sortBy(fn ($p) => $p['email'].':'.$p['category_id'])->values()->toArray(),
            'coupon' => $validated['coupon_code'] ?? null,
        ];
        $idKey = 'reg:idempoten:'.md5(json_encode($fingerprint));
        $existingTxId = Cache::get($idKey);
        if ($existingTxId) {
            $existing = Transaction::find($existingTxId);
            if ($existing) {
                return $existing;
            }
        }

        // Validate coupon if provided
        $coupon = null;
        $discountAmount = 0;
        $couponLock = null;

        if (! empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', $validated['coupon_code'])
                ->where(function ($query) use ($event) {
                    $query->where('event_id', $event->id)
                        ->orWhereNull('event_id');
                })
                ->first();

            if (! $coupon || ! $coupon->canBeUsed($event->id, null, auth()->id())) {
                throw new \Exception('Kupon tidak valid atau sudah tidak dapat digunakan');
            }
        }

        // Group participants by category and calculate totals
        $categoryQuantities = [];
        $totalOriginal = 0;
        $now = now();

        foreach ($validated['participants'] as $index => $participant) {
            $activeParticipantExists = Participant::where('race_category_id', $participant['category_id'])
                ->where('id_card', $participant['id_card'])
                ->whereHas('transaction', function ($query) use ($event) {
                    $query->whereIn('payment_status', ['paid', 'cod']);
                    if ($event->hardcoded === 'latbarkamis') {
                        if ($event->registration_open_at) {
                            $query->where('created_at', '>=', $event->registration_open_at);
                        }
                        if ($event->registration_close_at) {
                            $query->where('created_at', '<=', $event->registration_close_at);
                        }
                    }
                })
                ->exists();

            if ($activeParticipantExists) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "participants.{$index}.id_card" => ["Peserta dengan ID Card {$participant['id_card']} sudah terdaftar (status Pending atau Paid) di kategori ini."],
                ]);
            }

            $categoryId = $participant['category_id'];
            if (! isset($categoryQuantities[$categoryId])) {
                $categoryQuantities[$categoryId] = 0;
            }
            $categoryQuantities[$categoryId]++;
        }

        // Calculate total and validate quota with atomic locks
        $categoryLocks = [];
        $categories = [];
        $categoryPriceInfo = []; // Map category_id -> ['price' => X, 'type' => 'early|regular|late']

        try {
            foreach ($categoryQuantities as $categoryId => $quantity) {
                $lockKey = "category_quota_lock:{$categoryId}";
                $lock = Cache::lock($lockKey, 10);

                if (! $lock->get()) {
                    throw new \Exception('Sistem sedang sibuk, silakan coba lagi');
                }

                $categoryLocks[] = $lock;

                $category = RaceCategory::find($categoryId);
                if (! $category) {
                    throw new \Exception('Kategori tidak ditemukan');
                }

                $categories[$categoryId] = $category;

                // Validate quota if set
                if ($category->quota !== null) {
                    $paidCount = Participant::where('race_category_id', $categoryId)
                        ->whereHas('transaction', function ($query) {
                            $query->whereIn('payment_status', ['paid', 'cod']);
                        })
                        ->count();

                    if (($paidCount + $quantity) > $category->quota) {
                        $available = max(0, $category->quota - $paidCount);
                        throw new \Exception("Kuota untuk kategori {$category->name} tidak mencukupi (sisa: {$available})");
                    }
                }

                // Determine price type and price based on dates
                $price = $category->price_regular;
                $priceType = 'regular';

                // Check Early Bird
                if ($category->early_bird_end_at && $now->lte($category->early_bird_end_at) && $category->price_early) {
                    $price = $category->price_early;
                    $priceType = 'early';
                }
                // Check Late Bird
                elseif ($category->late_bird_start_at && $now->gte($category->late_bird_start_at) && $category->price_late) {
                    $price = $category->price_late;
                    $priceType = 'late';
                }

                $categoryPriceInfo[$categoryId] = [
                    'price' => $price,
                    'type' => $priceType,
                ];

                $totalOriginal += ($price * $quantity);
            }

            // Include addons in total original
            $totalOriginal += $totalAddonsPrice;

            // Calculate discount if coupon is present
            if ($coupon) {
                $couponLockKey = "coupon_usage_lock:{$coupon->id}";
                $couponLock = Cache::lock($couponLockKey, 10);

                if (! $couponLock->get()) {
                    throw new \Exception('Sistem sedang sibuk memproses kupon, silakan coba lagi');
                }

                // Double check coupon validity under lock
                if (! $coupon->canBeUsed($event->id, $totalOriginal, auth()->id())) {
                    throw new \Exception('Kupon sudah tidak dapat digunakan');
                }

                $discountAmount = $coupon->applyDiscount($totalOriginal);

                // Increment coupon usage count immediately under lock
                $coupon->increment('used_count');
            }

            // Start Database Transaction
            DB::beginTransaction();

            $totalParticipants = count($validated['participants']);
            $platformFeePerParticipant = $event->platform_fee ?? 0;

            // If ticket is fully discounted (<= 0), waive the platform fee
            if (($totalOriginal - $discountAmount) <= 0) {
                $totalAdminFee = 0;
            } else {
                $totalAdminFee = $platformFeePerParticipant * $totalParticipants;
            }

            $finalAmount = ($totalOriginal - $discountAmount) + $totalAdminFee;

            // Handle Zero Amount (100% Discount)
            $isZeroAmount = $finalAmount <= 0;
            if ($isZeroAmount) {
                $finalAmount = 0;
            }

            $uniqueCode = 0;
            if ($paymentMethod === 'moota' && ! $isZeroAmount) {
                $uniqueCode = $this->mootaService->generateUniqueCode($finalAmount);
                $finalAmount += $uniqueCode;
            }

            // Create transaction
            $transaction = Transaction::create([
                'event_id' => $event->id,
                'user_id' => auth()->id() ?: (isset($picUser) ? $picUser->id : null),
                'pic_data' => [
                    'name' => $validated['pic_name'],
                    'email' => $validated['pic_email'],
                    'phone' => $validated['pic_phone'],
                    'created_users' => array_keys($createdUsers),
                    'addons' => $allSelectedAddonsForPic,
                ],
                'total_original' => $totalOriginal,
                'coupon_id' => $coupon?->id,
                'discount_amount' => $discountAmount,
                'admin_fee' => $totalAdminFee,
                'final_amount' => $finalAmount,
                'payment_status' => $isZeroAmount ? 'paid' : 'pending',
                'paid_at' => $isZeroAmount ? now() : null,
                'payment_gateway' => $paymentMethod === 'moota' ? 'moota' : ($paymentMethod === 'cod' ? 'cod' : 'midtrans'),
                'unique_code' => $uniqueCode > 0 ? $uniqueCode : 0,
            ]);

            // Create participants
            foreach ($validated['participants'] as $pIndex => $participantData) {
                $categoryId = $participantData['category_id'];
                $priceType = $categoryPriceInfo[$categoryId]['type'] ?? 'regular';

                // Handle Photo Upload (Base64)
                $photoPath = null;
                if (! empty($participantData['photo'])) {
                    $image = $participantData['photo'];
                    if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                        $image = substr($image, strpos($image, ',') + 1);
                        $type = strtolower($type[1]); // jpg, png, gif

                        if (in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                            $image = str_replace(' ', '+', $image);
                            $imageName = 'participant_'.time().'_'.Str::random(10).'.'.$type;
                            Storage::disk('public')->put('participants/'.$imageName, base64_decode($image));
                            $photoPath = 'participants/'.$imageName;
                        }
                    }
                }

                $requiresApproval = ! empty($event->premium_amenities['requires_approval']) || ($paymentMethod === 'cod');

                // Create participant
                Participant::create([
                    'transaction_id' => $transaction->id,
                    'race_category_id' => $categoryId,
                    'name' => $participantData['name'],
                    'gender' => $participantData['gender'],
                    'phone' => $participantData['phone'],
                    'email' => $participantData['email'],
                    'id_card' => $participantData['id_card'],
                    'address' => $participantData['address'],
                    'emergency_contact_name' => $participantData['emergency_contact_name'],
                    'emergency_contact_number' => $participantData['emergency_contact_number'],
                    'date_of_birth' => $participantData['date_of_birth'] ?? null,
                    'target_time' => $participantData['target_time'] ?? null,
                    'jersey_size' => $participantData['jersey_size'] ?? null,
                    'blood_type' => $participantData['blood_type'] ?? null,
                    'strava_url' => $participantData['strava_url'] ?? ($participantData['strava_activity'] ?? null),
                    'photo' => $photoPath,
                    'addons' => $participantsWithAddons[$pIndex] ?? [],
                    'status' => 'pending',
                    'isApproved' => $requiresApproval ? 0 : 1,
                    'price_type' => $priceType,
                ]);
            }

            // Release all locks
            foreach ($categoryLocks as $lock) {
                $lock->release();
            }
            if (isset($couponLock) && $couponLock) {
                $couponLock->release();
            }

            DB::commit();

            // Invalidate cache for updated categories
            foreach ($categories as $category) {
                $this->cacheService->invalidateCategoryCache($category);
            }

            // Load participants with category for Midtrans
            $transaction->load(['participants.category']);

            $requiresApproval = ! empty($event->premium_amenities['requires_approval']) || ($paymentMethod === 'cod');

            if ($isZeroAmount) {
                Cache::put($idKey, $transaction->id, now()->addMinutes(10));

                if (! $requiresApproval) {
                    // Dispatch emails only if no manual approval required
                    app(\App\Services\EventRegistrationEmailDispatcher::class)->dispatch($transaction);
                }

                // Process Paid Event Transaction (Wallet, Stats, etc)
                \App\Jobs\ProcessPaidEventTransaction::dispatch($transaction);

                return $transaction;
            }

            if ($paymentMethod === 'cod') {
                $transaction->update(['payment_status' => 'cod']);
                Cache::put($idKey, $transaction->id, now()->addMinutes(10));

                if (! $requiresApproval) {
                    app(\App\Services\EventRegistrationEmailDispatcher::class)->dispatch($transaction);
                }

                return $transaction;
            } elseif ($paymentMethod === 'moota') {
                Cache::put($idKey, $transaction->id, now()->addMinutes(10));

                // Notification/Email can be sent here if needed
                return $transaction;
            } else {
                $snapResult = $this->midtransService->createEventTransaction($transaction);
                if ($snapResult['success']) {
                    $transaction->update([
                        'snap_token' => $snapResult['snap_token'],
                        'midtrans_order_id' => $snapResult['order_id'],
                        'midtrans_mode' => $snapResult['midtrans_mode'] ?? 'production',
                    ]);

                    Cache::put($idKey, $transaction->id, now()->addMinutes(10));

                    return $transaction;
                } else {
                    throw new \Exception($snapResult['error'] ?? 'Gagal membuat transaksi pembayaran');
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            // Release all locks on error
            foreach ($categoryLocks as $lock) {
                $lock->release();
            }
            if (isset($couponLock) && $couponLock) {
                // Rollback coupon usage if it was incremented
                if ($coupon) {
                    $coupon->decrement('used_count');
                }
                $couponLock->release();
            }

            throw $e;
        }
    }
}
