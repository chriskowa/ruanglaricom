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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StoreRegistrationAction
{
    protected $cacheService;

    protected $midtransService;

    public function __construct(EventCacheService $cacheService, MidtransService $midtransService)
    {
        $this->cacheService = $cacheService;
        $this->midtransService = $midtransService;
    }

    public function execute(Request $request, Event $event): Transaction
    {
        // Validate input
        $validated = $request->validate([
            'pic_name' => 'required|string|max:255',
            'pic_email' => 'required|email|max:255',
            'pic_phone' => 'required|string|max:20',
            'participants' => 'required|array|min:1',
            'participants.*.name' => 'required|string|max:255',
            'participants.*.email' => 'required|email|max:255',
            'participants.*.phone' => 'required|string|max:20',
            'participants.*.id_card' => 'required|string|max:50',
            'participants.*.category_id' => 'required|exists:race_categories,id',
            'participants.*.target_time' => 'nullable|string|max:50',
            'participants.*.jersey_size' => 'nullable|string|max:10',
            'coupon_code' => 'nullable|string|max:50',
            'payment_method' => 'nullable|in:midtrans,cod',
            'addons' => 'nullable|array',
            'addons.*.name' => 'required|string',
            'addons.*.price' => 'required|numeric',
        ]);

        $paymentMethod = strtolower($validated['payment_method'] ?? 'midtrans');

        // Calculate Addons Price
        $addonsPricePerParticipant = 0;
        $selectedAddons = [];
        if (!empty($validated['addons']) && !empty($event->addons)) {
            foreach ($validated['addons'] as $addonInput) {
                foreach ($event->addons as $eventAddon) {
                    if ($eventAddon['name'] === $addonInput['name']) {
                        $price = isset($eventAddon['price']) ? (int)$eventAddon['price'] : 0;
                        $addonsPricePerParticipant += $price;
                        $selectedAddons[] = [
                            'name' => $eventAddon['name'],
                            'price' => $price
                        ];
                        break;
                    }
                }
            }
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
        if (! empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', $validated['coupon_code'])
                ->where('event_id', $event->id)
                ->first();

            if (! $coupon || ! $coupon->canBeUsed()) {
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
                ->whereHas('transaction', function ($query) {
                    $query->whereIn('payment_status', ['pending', 'paid']);
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
                        $query->whereIn('payment_status', ['pending', 'paid']);
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
                $price = $this->getCategoryPrice($category, $now);
                $totalOriginal += $price * $quantity;
            }

            // Apply coupon discount
            if ($coupon) {
                $discountAmount = $coupon->applyDiscount($totalOriginal);
            }
            $finalAmount = $totalOriginal - $discountAmount;

        // Create transaction
        $transaction = Transaction::create([
            'event_id' => $event->id,
            'user_id' => auth()->id() ?: (isset($picUser) ? $picUser->id : null),
            'pic_data' => [
                'name' => $validated['pic_name'],
                'email' => $validated['pic_email'],
                'phone' => $validated['pic_phone'],
                'created_users' => array_keys($createdUsers),
                'addons' => $selectedAddons,
            ],
            'total_original' => $totalOriginal,
            'coupon_id' => $coupon?->id,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'payment_status' => 'pending',
        ]);

            // Create participants
            foreach ($validated['participants'] as $participantData) {
                $categoryId = $participantData['category_id'];

                // Create participant
                Participant::create([
                    'transaction_id' => $transaction->id,
                    'race_category_id' => $categoryId,
                    'name' => $participantData['name'],
                    'phone' => $participantData['phone'],
                    'email' => $participantData['email'],
                    'id_card' => $participantData['id_card'],
                    'target_time' => $participantData['target_time'] ?? null,
                    'jersey_size' => $participantData['jersey_size'] ?? null,
                    'addons' => $selectedAddons,
                    'status' => 'pending',
                ]);
            }

            // Release all locks
            foreach ($categoryLocks as $lock) {
                $lock->release();
            }

            DB::commit();

            // Invalidate cache for updated categories
            foreach ($categories as $category) {
                $this->cacheService->invalidateCategoryCache($category);
            }

            // Load participants with category for Midtrans
            $transaction->load(['participants.category']);

            if ($paymentMethod === 'cod') {
                $transaction->update(['payment_status' => 'cod']);
                Cache::put($idKey, $transaction->id, now()->addMinutes(10));
                \App\Jobs\SendEventRegistrationNotification::dispatch($transaction);
                return $transaction;
            } else {
                $snapResult = $this->midtransService->createEventTransaction($transaction);
                if ($snapResult['success']) {
                    $transaction->update([
                        'snap_token' => $snapResult['snap_token'],
                        'midtrans_order_id' => $snapResult['order_id'],
                    ]);
                    Cache::put($idKey, $transaction->id, now()->addMinutes(10));
                } else {
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

            Log::error('StoreRegistrationAction failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get category price based on registration period
     */
    private function getCategoryPrice(RaceCategory $category, $now): int
    {
        // If no registration period, use regular or early price
        if (! $category->reg_start_at || ! $category->reg_end_at) {
            return $category->price_regular ?? $category->price_early ?? 0;
        }

        $regStart = $category->reg_start_at;
        $regEnd = $category->reg_end_at;

        if ($now < $regStart) {
            // Registration not open yet
            return $category->price_regular ?? $category->price_early ?? 0;
        } elseif ($now >= $regStart && $now < $regEnd) {
            // Early bird period
            return $category->price_early ?? $category->price_regular ?? 0;
        } else {
            // Late period
            return $category->price_late ?? $category->price_regular ?? 0;
        }
    }
}
