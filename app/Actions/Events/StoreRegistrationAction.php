<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\Transaction;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Coupon;
use App\Services\EventCacheService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
            'participants.*.target_time' => 'nullable|date_format:H:i',
            'participants.*.jersey_size' => 'nullable|string|max:10',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        // Validate coupon if provided
        $coupon = null;
        $discountAmount = 0;
        if (!empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', $validated['coupon_code'])
                ->where('event_id', $event->id)
                ->first();

            if (!$coupon || !$coupon->canBeUsed()) {
                throw new \Exception('Kupon tidak valid atau sudah tidak dapat digunakan');
            }
        }

        // Group participants by category and calculate totals
        $categoryQuantities = [];
        $totalOriginal = 0;
        $now = now();

        foreach ($validated['participants'] as $participant) {
            $categoryId = $participant['category_id'];
            if (!isset($categoryQuantities[$categoryId])) {
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

                if (!$lock->get()) {
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
                    ->whereHas('transaction', function($query) {
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
                'user_id' => auth()->id(),
                'pic_data' => [
                    'name' => $validated['pic_name'],
                    'email' => $validated['pic_email'],
                    'phone' => $validated['pic_phone'],
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
            
            // Request Snap Token from Midtrans
            $snapResult = $this->midtransService->createEventTransaction($transaction);
            
            if ($snapResult['success']) {
                $transaction->update([
                    'snap_token' => $snapResult['snap_token'],
                    'midtrans_order_id' => $snapResult['order_id'],
                ]);
            } else {
                throw new \Exception('Gagal membuat token pembayaran: ' . ($snapResult['message'] ?? 'Unknown error'));
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
        if (!$category->reg_start_at || !$category->reg_end_at) {
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


