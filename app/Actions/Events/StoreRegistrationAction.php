<?php

namespace App\Actions\Events;

use App\Models\Coupon;
use App\Models\Event;
use App\Models\User;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Services\EventCacheService;
use App\Services\MidtransService;
use App\Services\MootaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        if ($request->has('participants')) {
            $participants = $request->participants;
            foreach ($participants as &$p) {
                if (isset($p['email'])) {
                    $p['email'] = trim($p['email']);
                }
            }
            $request->merge(['participants' => $participants]);
        }

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
            'participants.*.id_card' => 'required|string|max:50',
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
            'participants.*.target_time' => ['nullable', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', 'not_in:00:00:00'],
            'participants.*.jersey_size' => 'nullable|string|max:10',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'payment_method' => 'nullable|in:midtrans,cod,moota',
            'participants.*.addons' => 'nullable|array',
            'participants.*.addons.*.name' => 'nullable|string',
            'participants.*.addons.*.selected' => 'nullable',
            'g-recaptcha-response' => [env('RECAPTCHA_SECRET_KEY') ? 'required' : 'nullable', function ($attribute, $value, $fail) use ($request) {
                $secret = env('RECAPTCHA_SECRET_KEY');
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

                if (! $response->json('success')) {
                    $fail('Verifikasi reCAPTCHA gagal. Silakan coba lagi.');
                }
            }],
        ];

        if ($event->hardcoded === 'latbarkamis') {
            $rules['participants.*.date_of_birth'] = 'nullable|date|before:today';
        }

        $validated = $request->validate($rules);

        $paymentMethod = strtolower($validated['payment_method'] ?? 'midtrans');

        // Calculate Addons Price (Per Participant)
        $totalAddonsPrice = 0;
        $participantsWithAddons = []; // Map participant index -> addons data
        $allSelectedAddonsForPic = []; // Aggregate for PIC data

        foreach ($validated['participants'] as $pIndex => $pData) {
            $pAddons = [];
            if (!empty($pData['addons']) && is_array($pData['addons']) && !empty($event->addons)) {
                foreach ($pData['addons'] as $addonInput) {
                    // Check if selected (checkbox)
                    if (isset($addonInput['selected']) && $addonInput['selected']) {
                        // Find matching event addon to get trusted price
                        foreach ($event->addons as $eventAddon) {
                            if ($eventAddon['name'] === $addonInput['name']) {
                                $price = isset($eventAddon['price']) ? (int)$eventAddon['price'] : 0;
                                $totalAddonsPrice += $price;
                                
                                $addonData = [
                                    'name' => $eventAddon['name'],
                                    'price' => $price
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
            }
            // Create or get participant users
            foreach ($validated['participants'] as $participant) {
                $email = strtolower($participant['email']);
                $user = User::where('email', $email)->first();
                if (! $user) {
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

        foreach ($validated['participants'] as $participant) {
            // Check for existing active registration (pending or paid)
            // We removed the database unique constraint to allow retries on failed/expired transactions,
            // so we must enforce the "one active registration per category" rule here.
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
                    'participants' => ["Peserta dengan ID Card {$participant['id_card']} sudah terdaftar (status Pending atau Paid) di kategori ini."],
                ]);
            }

            $categoryId = $participant['category_id'];
            if (! isset($categoryQuantities[$categoryId])) {
                $categoryQuantities[$categoryId] = 0;
            }
            $categoryQuantities[$categoryId]++;
        }

        // Calculate total and validate quota with atomic locks
        DB::beginTransaction();
        try {
            $categoryLocks = [];
            $categories = [];
            $categoryPriceInfo = [];

            // Acquire locks for all categories
            foreach (array_keys($categoryQuantities) as $categoryId) {
                $lockKey = "event:category:{$categoryId}";
                $lock = Cache::lock($lockKey, 5); // 5 second timeout

                if (! $lock->get()) {
                    throw new \Exception('Gagal memperoleh lock untuk kategori. Silakan coba lagi.');
                }

                $categoryLocks[$categoryId] = $lock;

                // Lock category row for update
                $category = RaceCategory::lockForUpdate()->findOrFail($categoryId);
                $categories[$categoryId] = $category;

                // Check quota
                $quantity = $categoryQuantities[$categoryId];

                // Calculate remaining quota (optimized with index)
                $registeredCount = Participant::where('race_category_id', $categoryId)
                    ->whereHas('transaction', function ($query) {
                        $query->whereIn('payment_status', ['paid', 'cod']);
                    })
                    ->count();

                $remainingQuota = $category->quota ? ($category->quota - $registeredCount) : 999999;

                if ($category->quota && $remainingQuota < $quantity) {
                    // Release all locks
                    foreach ($categoryLocks as $lock) {
                        $lock->release();
                    }
                    throw new \Exception("Kuota kategori '{$category->name}' tidak mencukupi. Sisa: {$remainingQuota}");
                }

                // Calculate price based on registration period
                $priceInfo = $this->getCategoryPrice($category, $now);
                $categoryPriceInfo[$categoryId] = $priceInfo;
                $price = (int) ($priceInfo['price'] ?? 0);
                
                // Promo Buy X Get 1 Free
                $paidQuantity = $quantity;
                $isBuyXGet1Active = false;
                if ($event->promo_buy_x && $event->promo_buy_x > 0) {
                    $bundleSize = $event->promo_buy_x + 1;
                    $freeCount = floor($quantity / $bundleSize);
                    if ($freeCount > 0) $isBuyXGet1Active = true;
                    $paidQuantity = $quantity - $freeCount;
                }

                $totalOriginal += $price * $paidQuantity;
            }

            // Add Addons Total
            $totalOriginal += $totalAddonsPrice;

            // Apply coupon discount with concurrency check
            if ($coupon) {
                if ($isBuyXGet1Active && ! $coupon->is_stackable) {
                    throw new \Exception('Kupon ini tidak dapat digabungkan dengan promo Buy X Get Y.');
                }

                if ($coupon->max_uses) {
                    $couponLock = Cache::lock('coupon_usage:'.$coupon->id, 5); // 5s timeout
                    if (! $couponLock->get()) {
                        throw new \Exception('Sedang memverifikasi kupon, silakan coba lagi.');
                    }

                    // Check used_count + pending transactions (from last 1 hour)
                    $pendingCount = $coupon->transactions()
                        ->where('payment_status', 'pending')
                        ->where('created_at', '>', now()->subMinutes(60))
                        ->count();
                    
                    if (($coupon->used_count + $pendingCount) >= $coupon->max_uses) {
                        $couponLock->release();
                        throw new \Exception('Kuota kupon sudah habis (termasuk yang sedang menunggu pembayaran).');
                    }
                }

                // Strict validation with actual amount and user
                if (! $coupon->canBeUsed($event->id, $totalOriginal, auth()->id())) {
                    if (isset($couponLock)) $couponLock->release();
                    throw new \Exception('Kupon tidak valid untuk transaksi ini (cek minimum pembelian atau batas penggunaan).');
                }

                $discountAmount = $coupon->applyDiscount($totalOriginal);
            }
            
            // Calculate Platform Fee
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
            'payment_gateway' => $paymentMethod === 'moota' ? 'moota' : 'midtrans',
            'unique_code' => $uniqueCode > 0 ? $uniqueCode : 0,
        ]);

            // Create participants
            foreach ($validated['participants'] as $pIndex => $participantData) {
                $categoryId = $participantData['category_id'];
                $priceType = $categoryPriceInfo[$categoryId]['type'] ?? 'regular';

                // Create participant
                Participant::create([
                    'transaction_id' => $transaction->id,
                    'race_category_id' => $categoryId,
                    'name' => $participantData['name'],
                    'gender' => $participantData['gender'],
                    'phone' => $participantData['phone'],
                    'email' => $participantData['email'],
                    'id_card' => $participantData['id_card'],
                    'emergency_contact_name' => $participantData['emergency_contact_name'],
                    'emergency_contact_number' => $participantData['emergency_contact_number'],
                    'date_of_birth' => $participantData['date_of_birth'] ?? null,
                    'target_time' => $participantData['target_time'] ?? null,
                    'jersey_size' => $participantData['jersey_size'] ?? null,
                    'addons' => $participantsWithAddons[$pIndex] ?? [],
                    'status' => 'pending',
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

            if ($isZeroAmount) {
                Cache::put($idKey, $transaction->id, now()->addMinutes(10));
                
                // Dispatch emails
                app(\App\Services\EventRegistrationEmailDispatcher::class)->dispatch($transaction);
                
                // Process Paid Event Transaction (Wallet, Stats, etc)
                \App\Jobs\ProcessPaidEventTransaction::dispatch($transaction);
                
                return $transaction;
            }

            if ($paymentMethod === 'cod') {
                $transaction->update(['payment_status' => 'cod']);
                Cache::put($idKey, $transaction->id, now()->addMinutes(10));
                app(\App\Services\EventRegistrationEmailDispatcher::class)->dispatch($transaction);
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
                } else {
                    $transaction->update(['payment_status' => 'failed']);
                    throw new \Exception('Gagal membuat token pembayaran: '.($snapResult['message'] ?? 'Unknown error'));
                }
            }

            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();

            // Release all locks in case of exception
            if (isset($categoryLocks)) {
                foreach ($categoryLocks as $lock) {
                    $lock->release();
                }
            }
            if (isset($couponLock) && $couponLock) {
                $couponLock->release();
            }

            Log::error('StoreRegistrationAction failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get category price based on priority (Early > Regular)
     * Logic:
     * 1. If Early Price > 0 AND Valid (Date & Quota), use Early.
     * 2. Else, use Regular.
     */
    private function getCategoryPrice(RaceCategory $category, $now): array
    {
        $early = (int) ($category->price_early ?? 0);
        $regular = (int) ($category->price_regular ?? 0);
        $late = (int) ($category->price_late ?? 0); // Fallback if needed, but Regular is standard

        // 1. Check Early Bird
        if ($early > 0) {
            $isEarlyValid = true;

            // Check Date
            if ($category->early_bird_end_at && $now->greaterThan($category->early_bird_end_at)) {
                $isEarlyValid = false;
            }

            // Check Quota
            if ($isEarlyValid && $category->early_bird_quota) {
                $earlySold = Participant::where('race_category_id', $category->id)
                    ->where('price_type', 'early')
                    ->whereHas('transaction', function ($q) {
                        $q->whereIn('payment_status', ['pending', 'paid', 'cod']);
                    })
                    ->count();

                if ($earlySold >= $category->early_bird_quota) {
                    $isEarlyValid = false;
                }
            }

            if ($isEarlyValid) {
                return ['price' => $early, 'type' => 'early'];
            }
        }

        // 2. Fallback to Late if Regular is 0 (optional logic from previous code) or just Regular
        if ($late > 0 && $regular === 0) {
             return ['price' => $late, 'type' => 'late'];
        }

        return ['price' => $regular, 'type' => 'regular'];
    }
}
