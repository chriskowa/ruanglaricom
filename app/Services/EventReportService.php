<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Transaction;
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
        $cacheKey = 'event_report_'.$event->id.'_'.md5(json_encode($filters));

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
            if (! $category->quota) {
                $isUnlimited = true;
            } else {
                $totalSlots += $category->quota;
            }
        }

        $soldStatuses = ['paid', 'settlement', 'capture', 'cod'];
        $activeStatuses = array_values(array_unique(array_merge($soldStatuses, ['pending'])));

        $baseQuery = Participant::query()
            ->join('transactions', 'transactions.id', '=', 'participants.transaction_id')
            ->where('transactions.event_id', $event->id)
            ->select('participants.*');

        // Apply Filters
        if (! empty($filters['start_date'])) {
            $baseQuery->whereDate('transactions.created_at', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $baseQuery->whereDate('transactions.created_at', '<=', $filters['end_date']);
        }
        if (! empty($filters['ticket_type'])) {
            $baseQuery->where('participants.price_type', $filters['ticket_type']);
        }

        $paymentCounts = (clone $baseQuery)
            ->select('transactions.payment_status', DB::raw('count(*) as total'))
            ->groupBy('transactions.payment_status')
            ->pluck('total', 'transactions.payment_status')
            ->toArray();

        $soldSlots = 0;
        foreach ($soldStatuses as $st) {
            $soldSlots += (int) ($paymentCounts[$st] ?? 0);
        }
        $pendingSlots = (int) ($paymentCounts['pending'] ?? 0);
        $failedSlots = (int) ($paymentCounts['failed'] ?? 0);
        $expiredSlots = (int) ($paymentCounts['expired'] ?? 0);
        $codSlots = (int) ($paymentCounts['cod'] ?? 0);

        // 3. Breakdown by Ticket Type (Price Type)
        // We clone the query to keep filters but change select
        $breakdownQuery = (clone $baseQuery)->whereIn('transactions.payment_status', $soldStatuses);
        $breakdown = $breakdownQuery->select('price_type', DB::raw('count(*) as total'))
            ->groupBy('price_type')
            ->pluck('total', 'price_type')
            ->toArray();

        // 4. Coupon Usage
        $couponSoldCount = (clone $baseQuery)
            ->whereIn('transactions.payment_status', $soldStatuses)
            ->whereNotNull('transactions.coupon_id')
            ->count();
        $couponActiveCount = (clone $baseQuery)
            ->whereIn('transactions.payment_status', $activeStatuses)
            ->whereNotNull('transactions.coupon_id')
            ->count();

        // Add Coupon to breakdown for display if requested,
        // though strictly it's an attribute, not a mutually exclusive type with price_type.
        // We'll pass it separately.

        // Calculate Percentages
        $percentages = [];
        if ($soldSlots > 0) {
            foreach ($breakdown as $type => $count) {
                $percentages[$type] = round(($count / $soldSlots) * 100, 1);
            }
            $percentages['coupon'] = round(($couponSoldCount / $soldSlots) * 100, 1);
        }

        $pickupCounts = (clone $baseQuery)
            ->whereIn('transactions.payment_status', $soldStatuses)
            ->select('participants.is_picked_up', DB::raw('count(*) as total'))
            ->groupBy('participants.is_picked_up')
            ->pluck('total', 'participants.is_picked_up')
            ->toArray();

        $pickedUpCount = (int) ($pickupCounts[1] ?? $pickupCounts['1'] ?? 0);
        $notPickedUpCount = (int) ($pickupCounts[0] ?? $pickupCounts['0'] ?? 0);

        $jerseySizeCounts = (clone $baseQuery)
            ->where('transactions.payment_status', 'paid')
            ->whereNotNull('participants.jersey_size')
            ->select('participants.jersey_size', DB::raw('count(*) as total'))
            ->groupBy('participants.jersey_size')
            ->orderByDesc(DB::raw('count(*)'))
            ->pluck('total', 'participants.jersey_size')
            ->toArray();

        $jerseySizeTotalActive = 0;
        foreach ($jerseySizeCounts as $cnt) {
            $jerseySizeTotalActive += (int) $cnt;
        }

        $couponTxQuery = Transaction::query()
            ->join('coupons', 'coupons.id', '=', 'transactions.coupon_id')
            ->where('transactions.event_id', $event->id)
            ->whereNotNull('transactions.coupon_id')
            ->whereIn('transactions.payment_status', $activeStatuses)
            ->select([
                'coupons.code as code',
                DB::raw('count(*) as total_transactions'),
                DB::raw('COALESCE(sum(transactions.discount_amount), 0) as total_discount'),
                DB::raw('COALESCE(sum(transactions.total_original), 0) as total_original'),
                DB::raw('COALESCE(sum(transactions.final_amount), 0) as total_final'),
            ])
            ->groupBy('coupons.code')
            ->orderByDesc('total_transactions');

        if (! empty($filters['start_date'])) {
            $couponTxQuery->whereDate('transactions.created_at', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $couponTxQuery->whereDate('transactions.created_at', '<=', $filters['end_date']);
        }
        if (! empty($filters['ticket_type'])) {
            $ticketType = (string) $filters['ticket_type'];
            $couponTxQuery->whereHas('participants', function ($q) use ($ticketType) {
                $q->where('price_type', $ticketType);
            });
        }

        $couponTxAgg = $couponTxQuery->get();

        $couponParticipantAgg = Participant::query()
            ->join('transactions', 'transactions.id', '=', 'participants.transaction_id')
            ->join('coupons', 'coupons.id', '=', 'transactions.coupon_id')
            ->where('transactions.event_id', $event->id)
            ->whereNotNull('transactions.coupon_id')
            ->whereIn('transactions.payment_status', $activeStatuses)
            ->select([
                'coupons.code as code',
                DB::raw('count(participants.id) as participants_count'),
            ])
            ->groupBy('coupons.code');

        if (! empty($filters['start_date'])) {
            $couponParticipantAgg->whereDate('transactions.created_at', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $couponParticipantAgg->whereDate('transactions.created_at', '<=', $filters['end_date']);
        }
        if (! empty($filters['ticket_type'])) {
            $couponParticipantAgg->where('participants.price_type', (string) $filters['ticket_type']);
        }

        $couponParticipantAgg = $couponParticipantAgg
            ->pluck('participants_count', 'code')
            ->toArray();

        $couponUsageByCode = $couponTxAgg
            ->map(function ($row) use ($couponParticipantAgg) {
                $code = (string) ($row->code ?? '');

                return [
                    'code' => $code,
                    'total_transactions' => (int) ($row->total_transactions ?? 0),
                    'participants_count' => (int) ($couponParticipantAgg[$code] ?? 0),
                    'total_discount' => (float) ($row->total_discount ?? 0),
                    'total_original' => (float) ($row->total_original ?? 0),
                    'total_final' => (float) ($row->total_final ?? 0),
                ];
            })
            ->values()
            ->toArray();

        $couponTotals = [
            'total_transactions' => (int) $couponTxAgg->sum('total_transactions'),
            'participants_count' => (int) array_sum(array_map('intval', $couponParticipantAgg)),
            'total_discount' => (float) $couponTxAgg->sum('total_discount'),
        ];

        $addonsSummary = [
            'participants_with_addons' => 0,
            'addon_items' => 0,
            'total_amount' => 0,
            'by_name' => [],
        ];

        $addonsQuery = (clone $baseQuery)
            ->whereIn('transactions.payment_status', $soldStatuses)
            ->select(['participants.id', 'participants.addons'])
            ->orderBy('participants.id');

        $addonsQuery->chunk(500, function ($rows) use (&$addonsSummary) {
            foreach ($rows as $p) {
                $addons = is_array($p->addons) ? $p->addons : [];
                if (! $addons || count($addons) === 0) {
                    continue;
                }

                $addonsSummary['participants_with_addons']++;

                foreach ($addons as $a) {
                    if (! is_array($a)) {
                        continue;
                    }

                    $name = (string) ($a['name'] ?? '');
                    if ($name === '') {
                        $name = 'Unknown';
                    }

                    $price = (int) ($a['price'] ?? 0);

                    $addonsSummary['addon_items']++;
                    $addonsSummary['total_amount'] += $price;

                    if (! isset($addonsSummary['by_name'][$name])) {
                        $addonsSummary['by_name'][$name] = [
                            'count' => 0,
                            'total_amount' => 0,
                        ];
                    }

                    $addonsSummary['by_name'][$name]['count'] += 1;
                    $addonsSummary['by_name'][$name]['total_amount'] += $price;
                }
            }
        });

        uasort($addonsSummary['by_name'], function ($a, $b) {
            return ((int) ($b['count'] ?? 0)) <=> ((int) ($a['count'] ?? 0));
        });

        $sortDir = strtolower((string) ($filters['sort_dir'] ?? 'desc'));
        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $transactionsQuery = Transaction::query()
            ->where('event_id', $event->id)
            ->with('coupon');

        if (! empty($filters['start_date'])) {
            $transactionsQuery->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $transactionsQuery->whereDate('created_at', '<=', $filters['end_date']);
        }
        if (! empty($filters['ticket_type'])) {
            $ticketType = (string) $filters['ticket_type'];
            $transactionsQuery->whereHas('participants', function ($q) use ($ticketType) {
                $q->where('price_type', $ticketType);
            });
            $transactionsQuery->withCount(['participants as slots_count' => function ($q) use ($ticketType) {
                $q->where('price_type', $ticketType);
            }]);
        } else {
            $transactionsQuery->withCount(['participants as slots_count']);
        }

        $transactions = $transactionsQuery
            ->orderBy('created_at', $sortDir)
            ->limit(20)
            ->get()
            ->map(function (Transaction $t) {
                return [
                    'id' => $t->id,
                    'public_ref' => $t->public_ref ?? ('REG-'.$t->id),
                    'created_at' => $t->created_at ? $t->created_at->format('d M Y H:i') : '-',
                    'payment_status' => $t->payment_status ?? '-',
                    'final_amount' => (float) ($t->final_amount ?? 0),
                    'coupon_code' => $t->coupon ? $t->coupon->code : null,
                    'slots' => (int) ($t->slots_count ?? 0),
                ];
            })
            ->toArray();

        // 5. Remaining Slots Warning
        // True remaining includes pending slots as they consume quota
        $usedSlots = $soldSlots + $pendingSlots;
        $remainingSlots = $isUnlimited ? 999999 : ($totalSlots - $usedSlots);
        $warning = false;
        if (! $isUnlimited && $totalSlots > 0) {
            $percentageRemaining = ($remainingSlots / $totalSlots) * 100;
            if ($percentageRemaining < 10) {
                $warning = true;
            }
        }

        // Jersey Stock Quotas
        $jerseyStock = $event->jerseyStock;
        $jerseyStockData = [];
        if ($jerseyStock) {
            foreach (['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'] as $size) {
                $col = strtolower($size);
                $quota = $jerseyStock->$col ?? null;
                if ($quota !== null) {
                    $jerseyStockData[$size] = (int) $quota;
                }
            }
        }

        return [
            'total_slots' => $isUnlimited ? 'Unlimited' : $totalSlots,
            'sold_slots' => $soldSlots,
            'pending_slots' => $pendingSlots,
            'failed_slots' => $failedSlots,
            'expired_slots' => $expiredSlots,
            'cod_slots' => $codSlots,
            'remaining_slots' => $isUnlimited ? 'Unlimited' : $remainingSlots,
            'is_unlimited' => $isUnlimited,
            'breakdown' => $breakdown,
            'coupon_usage' => $couponSoldCount,
            'coupon_usage_active' => $couponActiveCount,
            'pickup' => [
                'picked_up' => $pickedUpCount,
                'not_picked_up' => $notPickedUpCount,
            ],
            'jersey_sizes' => $jerseySizeCounts,
            'jersey_sizes_total_active' => $jerseySizeTotalActive,
            'jersey_stock_quotas' => $jerseyStockData,
            'payment_counts' => $paymentCounts,
            'percentages' => $percentages,
            'show_warning' => $warning,
            'sort_dir' => $sortDir,
            'transactions' => $transactions,
            'coupon_usage_by_code' => $couponUsageByCode,
            'coupon_totals' => $couponTotals,
            'addons_report' => $addonsSummary,
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
