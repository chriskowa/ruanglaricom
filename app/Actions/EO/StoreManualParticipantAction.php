<?php

namespace App\Actions\EO;

use App\Models\Event;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreManualParticipantAction
{
    public function execute(Event $event, array $validated, User $operator): Transaction
    {
        $email = strtolower(trim($validated['email']));
        $picEmail = strtolower(trim($validated['pic_email'] ?? $email));

        $lockKey = 'eo:events:'.$event->id.':participants:'.md5($email);
        $lock = Cache::lock($lockKey, 10);
        if (! $lock->get()) {
            throw ValidationException::withMessages([
                'email' => ['Sedang memproses email ini, silakan coba lagi.'],
            ]);
        }

        try {
            return DB::transaction(function () use ($event, $validated, $operator, $email, $picEmail) {
                $duplicate = Participant::whereRaw('LOWER(email) = ?', [$email])
                    ->whereHas('transaction', function ($q) use ($event) {
                        $q->where('event_id', $event->id);
                    })
                    ->exists();

                if ($duplicate) {
                    throw ValidationException::withMessages([
                        'email' => ['Email sudah terdaftar untuk event ini.'],
                    ]);
                }

                $category = RaceCategory::lockForUpdate()->findOrFail((int) $validated['category_id']);
                if ((int) $category->event_id !== (int) $event->id) {
                    throw ValidationException::withMessages([
                        'category_id' => ['Kategori tidak valid untuk event ini.'],
                    ]);
                }

                $registeredCount = Participant::where('race_category_id', $category->id)
                    ->whereHas('transaction', function ($query) {
                        $query->whereIn('payment_status', ['paid', 'cod']);
                    })
                    ->count();

                if ($category->quota && ($registeredCount + 1) > $category->quota) {
                    throw ValidationException::withMessages([
                        'category_id' => ["Kuota kategori '{$category->name}' tidak mencukupi."],
                    ]);
                }

                $priceInfo = $this->getCategoryPrice($category);
                $amount = (int) $priceInfo['price'];

                $transaction = Transaction::create([
                    'event_id' => $event->id,
                    'user_id' => $operator->id,
                    'pic_data' => [
                        'name' => $validated['pic_name'] ?? $validated['name'],
                        'email' => $picEmail,
                        'phone' => $validated['pic_phone'] ?? $validated['phone'],
                        'manual_entry' => true,
                        'send_whatsapp' => $validated['send_whatsapp'] ?? true,
                    ],
                    'total_original' => $amount,
                    'coupon_id' => null,
                    'discount_amount' => 0,
                    'admin_fee' => 0,
                    'final_amount' => $amount,
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'payment_gateway' => 'manual',
                    'unique_code' => 0,
                ]);

                Participant::create([
                    'transaction_id' => $transaction->id,
                    'race_category_id' => $category->id,
                    'name' => $validated['name'],
                    'gender' => $validated['gender'] ?? null,
                    'phone' => $validated['phone'],
                    'email' => $email,
                    'id_card' => $validated['id_card'],
                    'address' => $validated['address'],
                    'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                    'emergency_contact_number' => $validated['emergency_contact_number'] ?? null,
                    'date_of_birth' => $validated['date_of_birth'] ?? null,
                    'target_time' => $validated['target_time'] ?? null,
                    'jersey_size' => $validated['jersey_size'] ?? null,
                    'price_type' => $priceInfo['type'],
                    'addons' => [],
                    'status' => 'pending',
                    'is_picked_up' => false,
                ]);

                return $transaction->load(['participants.category']);
            }, 3);
        } finally {
            $lock->release();
        }
    }

    /**
     * Get category price based on priority (Early > Regular)
     * Logic:
     * 1. If Early Price > 0 AND Valid (Date & Quota), use Early.
     * 2. Else, use Regular.
     */
    private function getCategoryPrice(RaceCategory $category): array
    {
        $now = now();
        $early = (int) ($category->price_early ?? 0);
        $regular = (int) ($category->price_regular ?? 0);
        $late = (int) ($category->price_late ?? 0);

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

        // 2. Fallback to Late if Regular is 0
        if ($late > 0 && $regular === 0) {
             return ['price' => $late, 'type' => 'late'];
        }

        return ['price' => $regular, 'type' => 'regular'];
    }
}
