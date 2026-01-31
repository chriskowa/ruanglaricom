<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EventReportService
{
    /**
     * Get event report data with caching
     */
    public function getEventReport(Event $event, array $filters = [])
    {
        // Cache key based on event ID and filters
        // Use a shorter cache duration (e.g. 30 seconds) for "real-time" feel while saving DB hits
        $cacheKey = 'event_report_' . $event->id . '_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, 30, function () use ($event, $filters) {
            return $this->generateReportData($event, $filters);
        });
    }

    /**
     * Generate the actual report data
     */
    protected function generateReportData(Event $event, array $filters)
    {
        // 1. Total Slots
        $categories = $event->categories;
        $totalSlots = 0;
        $isUnlimited = false;

        foreach ($categories as $category) {
            if (!$category->quota) {
                $isUnlimited = true;
            } else {
                $totalSlots += $category->quota;
            }
        }

        // Base Query for Sold Slots (Paid Transactions)
        $query = Participant::query()
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id)
                  ->whereIn('payment_status', ['paid', 'settlement', 'capture']);
            });

        // Query for Pending Slots (Reserved but not yet Paid)
        $pendingQuery = Participant::query()
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id)
                  ->where('payment_status', 'pending');
            });

        // Apply Filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
            $pendingQuery->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
            $pendingQuery->whereDate('created_at', '<=', $filters['end_date']);
        }
        if (!empty($filters['ticket_type'])) {
            $query->where('price_type', $filters['ticket_type']);
            $pendingQuery->where('price_type', $filters['ticket_type']);
        }
        
        // 2. Total Sold & Pending
        $soldSlots = $query->count();
        $pendingSlots = $pendingQuery->count();

        // 3. Breakdown by Ticket Type (Price Type)
        // We clone the query to keep filters but change select
        $breakdownQuery = clone $query;
        $breakdown = $breakdownQuery->select('price_type', DB::raw('count(*) as total'))
            ->groupBy('price_type')
            ->pluck('total', 'price_type')
            ->toArray();

        // 4. Coupon Usage
        // Count participants whose transaction has a coupon_id
        $couponQuery = clone $query;
        $couponCount = $couponQuery->whereHas('transaction', function($q) {
            $q->whereNotNull('coupon_id');
        })->count();

        // Add Coupon to breakdown for display if requested, 
        // though strictly it's an attribute, not a mutually exclusive type with price_type.
        // We'll pass it separately.

        // Calculate Percentages
        $percentages = [];
        if ($soldSlots > 0) {
            foreach ($breakdown as $type => $count) {
                $percentages[$type] = round(($count / $soldSlots) * 100, 1);
            }
            $percentages['coupon'] = round(($couponCount / $soldSlots) * 100, 1);
        }

        // 5. Remaining Slots Warning
        // True remaining includes pending slots as they consume quota
        $usedSlots = $soldSlots + $pendingSlots;
        $remainingSlots = $isUnlimited ? 999999 : ($totalSlots - $usedSlots);
        $warning = false;
        if (!$isUnlimited && $totalSlots > 0) {
            $percentageRemaining = ($remainingSlots / $totalSlots) * 100;
            if ($percentageRemaining < 10) {
                $warning = true;
            }
        }

        return [
            'total_slots' => $isUnlimited ? 'Unlimited' : $totalSlots,
            'sold_slots' => $soldSlots,
            'pending_slots' => $pendingSlots,
            'remaining_slots' => $isUnlimited ? 'Unlimited' : $remainingSlots,
            'is_unlimited' => $isUnlimited,
            'breakdown' => $breakdown,
            'coupon_usage' => $couponCount,
            'percentages' => $percentages,
            'show_warning' => $warning,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Clear report cache for an event
     */
    public function clearCache(Event $event)
    {
        // Since we have MD5 keys for filters, we can't easily delete specific keys.
        // But we can use Cache Tags if using Redis/Memcached.
        // For file driver (common in dev), we can't easily clear wildcards without iterating.
        // For now, the short TTL (30s) handles "clearing" naturally.
        // Or we could store a 'last_updated' timestamp in cache and check against it.
    }
}
