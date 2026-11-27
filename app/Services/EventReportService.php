<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPayout;
use App\Models\Participant;
use App\Models\Transaction;
use App\Models\WalletWithdrawal;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
     * Build detailed financial breakdown for EO participants page:
     * - Summary KPIs (Verified Wallet Net, Midtrans Manual/Non-Settlement, Offline/Manual, Coupon Discount, Gross & Fee)
     * - Net per Category (supports multi-category transactions proportionally)
     * - Payment Channel Breakdown (with wallet eligibility status)
     * - Coupon & Promo Breakdown
     * - Non-settled Midtrans audit list
     */
    public function getFinancialBreakdown(Event $event): array
    {
        $categories = $event->categories()
            ->select(['id', 'name', 'quota', 'price_early', 'price_regular', 'price_late'])
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        $paidTransactions = Transaction::query()
            ->where('event_id', $event->id)
            ->where('payment_status', 'paid')
            ->with([
                'coupon:id,code,type,value,max_uses,used_count',
                'participants:id,transaction_id,race_category_id,price_type,name,addons',
            ])
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get();

        $categoryRows = [];
        foreach ($categories as $cat) {
            $categoryRows[$cat->id] = [
                'category_id' => $cat->id,
                'name' => $cat->name,
                'quota' => $cat->quota !== null ? (int) $cat->quota : null,
                'participants_count' => 0,
                'tx_ids' => [],
                'total_original' => 0.0,
                'discount_amount' => 0.0,
                'platform_fee' => 0.0,
                'gross_paid' => 0.0,
                'wallet_net' => 0.0,
                'non_wallet_net' => 0.0,
                'total_net' => 0.0,
                'midtrans_settled_count' => 0,
                'midtrans_manual_count' => 0,
                'offline_count' => 0,
                'free_count' => 0,
            ];
        }

        $channelDefinitions = [
            'midtrans_settled' => [
                'key' => 'midtrans_settled',
                'label' => 'Midtrans Otomatis (Settlement)',
                'sublabel' => 'Production settlement / capture',
                'wallet_status' => 'wallet',
                'wallet_badge' => 'Masuk Wallet',
                'filter_gateway' => 'midtrans',
                'always_show' => true,
            ],
            'midtrans_manual' => [
                'key' => 'midtrans_manual',
                'label' => 'Midtrans Manual / Non-Settlement',
                'sublabel' => 'Status pending, expire, kosong, atau sandbox',
                'wallet_status' => 'warning',
                'wallet_badge' => 'Tidak Masuk Wallet',
                'filter_gateway' => 'midtrans',
                'always_show' => true,
            ],
            'moota_verified' => [
                'key' => 'moota_verified',
                'label' => 'Moota Otomatis (Terverifikasi)',
                'sublabel' => 'Mutasi bank otomatis',
                'wallet_status' => 'wallet',
                'wallet_badge' => 'Masuk Wallet',
                'filter_gateway' => 'moota',
                'always_show' => false,
            ],
            'moota_manual' => [
                'key' => 'moota_manual',
                'label' => 'Moota Manual (Belum Terverifikasi)',
                'sublabel' => 'Tanpa ID mutasi Moota',
                'wallet_status' => 'warning',
                'wallet_badge' => 'Tidak Masuk Wallet',
                'filter_gateway' => 'moota',
                'always_show' => false,
            ],
            'manual_transfer' => [
                'key' => 'manual_transfer',
                'label' => 'Transfer Manual ke EO',
                'sublabel' => 'Bukti transfer langsung ke rekening EO',
                'wallet_status' => 'offline',
                'wallet_badge' => 'Langsung ke EO',
                'filter_gateway' => 'manual_transfer',
                'always_show' => false,
            ],
            'manual' => [
                'key' => 'manual',
                'label' => 'Input Manual (Admin / EO)',
                'sublabel' => 'Ditambahkan langsung dari panel peserta',
                'wallet_status' => 'offline',
                'wallet_badge' => 'Langsung ke EO',
                'filter_gateway' => 'manual',
                'always_show' => true,
            ],
            'manual_csv' => [
                'key' => 'manual_csv',
                'label' => 'Import CSV Massal',
                'sublabel' => 'Data impor offline',
                'wallet_status' => 'offline',
                'wallet_badge' => 'Langsung ke EO',
                'filter_gateway' => 'manual_csv',
                'always_show' => false,
            ],
            'cod' => [
                'key' => 'cod',
                'label' => 'Bayar di Tempat (COD)',
                'sublabel' => 'Pembayaran tunai saat pengambilan',
                'wallet_status' => 'offline',
                'wallet_badge' => 'Langsung ke EO',
                'filter_gateway' => 'cod',
                'always_show' => false,
            ],
            'free' => [
                'key' => 'free',
                'label' => 'Kupon 100% / Gratis',
                'sublabel' => 'Transaksi registrasi Rp 0',
                'wallet_status' => 'free',
                'wallet_badge' => 'Gratis',
                'filter_gateway' => 'free',
                'always_show' => true,
            ],
            'other' => [
                'key' => 'other',
                'label' => 'Lainnya',
                'sublabel' => 'Kanal pembayaran lainnya',
                'wallet_status' => 'offline',
                'wallet_badge' => 'Tidak Masuk Wallet',
                'filter_gateway' => null,
                'always_show' => false,
            ],
        ];

        $channels = [];
        foreach ($channelDefinitions as $key => $def) {
            $channels[$key] = array_merge($def, [
                'tx_count' => 0,
                'participants_count' => 0,
                'total_original' => 0.0,
                'discount_amount' => 0.0,
                'platform_fee' => 0.0,
                'gross_paid' => 0.0,
                'net_amount' => 0.0,
            ]);
        }

        $coupons = [];
        $nonSettledMidtrans = [];

        $summary = [
            'total_tx' => 0,
            'total_participants' => 0,
            'total_original' => 0.0,
            'gross_paid' => 0.0,
            'platform_fee_total' => 0.0,
            'platform_fee_verified' => 0.0,
            'total_net' => 0.0,
            'verified_wallet_net' => 0.0,
            'verified_wallet_tx' => 0,
            'verified_wallet_participants' => 0,
            'midtrans_manual_net' => 0.0,
            'midtrans_manual_gross' => 0.0,
            'midtrans_manual_tx' => 0,
            'midtrans_manual_participants' => 0,
            'offline_net' => 0.0,
            'offline_tx' => 0,
            'offline_participants' => 0,
            'non_wallet_total_net' => 0.0,
            'coupon_discount_total' => 0.0,
            'coupon_tx_count' => 0,
            'coupon_participants_count' => 0,
            'coupon_free_tx_count' => 0,
        ];

        foreach ($paidTransactions as $tx) {
            $orig = max(0.0, (float) ($tx->total_original ?? 0));
            $disc = max(0.0, (float) ($tx->discount_amount ?? 0));
            $final = max(0.0, (float) ($tx->final_amount ?? 0));
            $rawFee = max(0.0, (float) ($tx->admin_fee ?? 0));
            $effectiveFee = $final > 0 ? min($final, $rawFee) : 0.0;
            $net = max(0.0, $final - $effectiveFee);

            $txParticipants = $tx->participants;
            $pCount = $txParticipants->count();

            $gateway = strtolower(trim((string) ($tx->payment_gateway ?? '')));
            $mode = strtolower(trim((string) ($tx->midtrans_mode ?? 'production')));
            $orderId = trim((string) ($tx->midtrans_order_id ?? ''));
            $mtStatus = strtolower(trim((string) ($tx->midtrans_transaction_status ?? '')));

            $isSandbox = ($mode === 'sandbox')
                || str_starts_with($orderId, 'EVENT-SBX-')
                || str_starts_with($orderId, 'TEST-');

            $entersWallet = false;
            if ($gateway === 'midtrans') {
                if ($final <= 0) {
                    $channelKey = 'free';
                } elseif (! $isSandbox && $orderId !== '' && in_array($mtStatus, ['settlement', 'capture'], true)) {
                    $channelKey = 'midtrans_settled';
                    $entersWallet = true;
                } else {
                    $channelKey = 'midtrans_manual';
                }
            } elseif ($gateway === 'moota') {
                if ($final <= 0) {
                    $channelKey = 'free';
                } elseif (! empty($tx->moota_transaction_id) || ! empty($tx->payment_gateway_reference)) {
                    $channelKey = 'moota_verified';
                    $entersWallet = true;
                } else {
                    $channelKey = 'moota_manual';
                }
            } elseif (array_key_exists($gateway, $channels)) {
                $channelKey = $gateway;
            } elseif ($final <= 0) {
                $channelKey = 'free';
            } else {
                $channelKey = 'other';
            }

            // Summary aggregation
            $summary['total_tx']++;
            $summary['total_participants'] += $pCount;
            $summary['total_original'] += $orig;
            $summary['gross_paid'] += $final;
            $summary['platform_fee_total'] += $effectiveFee;
            $summary['total_net'] += $net;

            if ($entersWallet) {
                $summary['verified_wallet_net'] += $net;
                $summary['verified_wallet_tx']++;
                $summary['verified_wallet_participants'] += $pCount;
                $summary['platform_fee_verified'] += $effectiveFee;
            } else {
                $summary['non_wallet_total_net'] += $net;
                if ($channelKey === 'midtrans_manual') {
                    $summary['midtrans_manual_net'] += $net;
                    $summary['midtrans_manual_gross'] += $final;
                    $summary['midtrans_manual_tx']++;
                    $summary['midtrans_manual_participants'] += $pCount;

                    $reason = $isSandbox
                        ? 'Sandbox / Testing'
                        : ($orderId === ''
                            ? 'Tanpa Order ID Midtrans (Paid Manual)'
                            : 'Status Midtrans: '.strtoupper($mtStatus ?: 'KOSONG'));

                    $picData = is_array($tx->pic_data) ? $tx->pic_data : [];
                    $nonSettledMidtrans[] = [
                        'id' => $tx->id,
                        'public_ref' => $tx->public_ref ?: ('REG-'.$tx->id),
                        'midtrans_order_id' => $orderId !== '' ? $orderId : '-',
                        'midtrans_mode' => $mode ?: 'production',
                        'midtrans_status' => $mtStatus !== '' ? $mtStatus : 'NULL',
                        'reason' => $reason,
                        'pic_name' => (string) ($picData['name'] ?? '-'),
                        'participants_names' => $txParticipants->pluck('name')->filter()->implode(', ') ?: '(Tidak ada peserta)',
                        'participants_count' => $pCount,
                        'gross_paid' => $final,
                        'platform_fee' => $effectiveFee,
                        'net_amount' => $net,
                        'paid_at' => $tx->paid_at ? $tx->paid_at->format('d M Y H:i') : ($tx->created_at ? $tx->created_at->format('d M Y H:i') : '-'),
                    ];
                } elseif ($channelKey !== 'free') {
                    $summary['offline_net'] += $net;
                    $summary['offline_tx']++;
                    $summary['offline_participants'] += $pCount;
                }
            }

            // Channel aggregation
            $channels[$channelKey]['tx_count']++;
            $channels[$channelKey]['participants_count'] += $pCount;
            $channels[$channelKey]['total_original'] += $orig;
            $channels[$channelKey]['discount_amount'] += $disc;
            $channels[$channelKey]['platform_fee'] += $effectiveFee;
            $channels[$channelKey]['gross_paid'] += $final;
            $channels[$channelKey]['net_amount'] += $net;

            // Coupon aggregation
            if ($tx->coupon_id || $disc > 0) {
                $summary['coupon_discount_total'] += $disc;
                $summary['coupon_tx_count']++;
                $summary['coupon_participants_count'] += $pCount;
                if ($final <= 0) {
                    $summary['coupon_free_tx_count']++;
                }

                $couponKey = $tx->coupon_id ? 'id_'.$tx->coupon_id : 'manual_discount';
                if (! isset($coupons[$couponKey])) {
                    $cModel = $tx->coupon;
                    $typeLabel = '-';
                    if ($cModel) {
                        $typeLabel = $cModel->type === 'percent'
                            ? rtrim(rtrim(number_format((float) $cModel->value, 2, ',', '.'), '0'), ',').'%'
                            : 'Rp '.number_format((float) $cModel->value, 0, ',', '.');
                    }
                    $coupons[$couponKey] = [
                        'coupon_id' => $tx->coupon_id,
                        'code' => $cModel ? $cModel->code : 'Diskon Manual',
                        'value_label' => $typeLabel,
                        'max_uses' => $cModel && $cModel->max_uses !== null ? (int) $cModel->max_uses : null,
                        'used_count' => $cModel ? (int) $cModel->used_count : null,
                        'tx_count' => 0,
                        'participants_count' => 0,
                        'free_tx_count' => 0,
                        'total_original' => 0.0,
                        'discount_amount' => 0.0,
                        'gross_paid' => 0.0,
                        'net_amount' => 0.0,
                    ];
                }
                $coupons[$couponKey]['tx_count']++;
                $coupons[$couponKey]['participants_count'] += $pCount;
                if ($final <= 0) {
                    $coupons[$couponKey]['free_tx_count']++;
                }
                $coupons[$couponKey]['total_original'] += $orig;
                $coupons[$couponKey]['discount_amount'] += $disc;
                $coupons[$couponKey]['gross_paid'] += $final;
                $coupons[$couponKey]['net_amount'] += $net;
            }

            // Category proportional allocation (exact 1:1 for single-category tx, zero rounding drift for multi-category tx)
            $ensureCategoryBucket = function ($catId, $fallbackName = 'Tanpa Kategori / Terhapus') use (&$categoryRows, $categories) {
                $key = $catId ?: 0;
                if (! isset($categoryRows[$key])) {
                    $catModel = $catId ? $categories->get($catId) : null;
                    $categoryRows[$key] = [
                        'category_id' => $catId ?: null,
                        'name' => $catModel ? $catModel->name : $fallbackName,
                        'quota' => $catModel && $catModel->quota !== null ? (int) $catModel->quota : null,
                        'participants_count' => 0,
                        'tx_ids' => [],
                        'total_original' => 0.0,
                        'discount_amount' => 0.0,
                        'platform_fee' => 0.0,
                        'gross_paid' => 0.0,
                        'wallet_net' => 0.0,
                        'non_wallet_net' => 0.0,
                        'total_net' => 0.0,
                        'midtrans_settled_count' => 0,
                        'midtrans_manual_count' => 0,
                        'offline_count' => 0,
                        'free_count' => 0,
                    ];
                }

                return $key;
            };

            if ($txParticipants->isEmpty()) {
                $bucketKey = $ensureCategoryBucket(0, 'Tanpa Peserta / Data Terhapus');
                $categoryRows[$bucketKey]['tx_ids'][$tx->id] = true;
                $categoryRows[$bucketKey]['total_original'] += $orig;
                $categoryRows[$bucketKey]['discount_amount'] += $disc;
                $categoryRows[$bucketKey]['platform_fee'] += $effectiveFee;
                $categoryRows[$bucketKey]['gross_paid'] += $final;
                $categoryRows[$bucketKey]['total_net'] += $net;
                if ($entersWallet) {
                    $categoryRows[$bucketKey]['wallet_net'] += $net;
                } else {
                    $categoryRows[$bucketKey]['non_wallet_net'] += $net;
                }
            } else {
                $catWeights = [];
                $catCounts = [];
                $totalWeight = 0.0;

                foreach ($txParticipants as $p) {
                    $cKey = $ensureCategoryBucket($p->race_category_id);
                    $catModel = $p->race_category_id ? $categories->get($p->race_category_id) : null;
                    $unitPrice = 0.0;
                    if ($catModel) {
                        $pType = strtolower(trim((string) ($p->price_type ?? 'regular')));
                        if (in_array($pType, ['early', 'early_bird'], true) && (float) ($catModel->price_early ?? 0) > 0) {
                            $unitPrice = (float) $catModel->price_early;
                        } elseif (in_array($pType, ['late', 'late_bird'], true) && (float) ($catModel->price_late ?? 0) > 0) {
                            $unitPrice = (float) $catModel->price_late;
                        } else {
                            $unitPrice = (float) ($catModel->price_regular ?? $catModel->price_early ?? $catModel->price_late ?? 0);
                        }
                    }

                    $addonsPrice = 0.0;
                    if (is_array($p->addons)) {
                        foreach ($p->addons as $addon) {
                            if (is_array($addon)) {
                                $addonsPrice += (float) ($addon['price'] ?? $addon['value'] ?? 0);
                            }
                        }
                    }

                    $weight = $unitPrice + $addonsPrice;
                    $catWeights[$cKey] = ($catWeights[$cKey] ?? 0.0) + $weight;
                    $catCounts[$cKey] = ($catCounts[$cKey] ?? 0) + 1;
                    $totalWeight += $weight;
                }

                $bucketKeys = array_keys($catCounts);
                $bucketTotal = count($bucketKeys);

                $remOrig = $orig;
                $remDisc = $disc;
                $remFee = $effectiveFee;
                $remFinal = $final;
                $remNet = $net;

                foreach ($bucketKeys as $idx => $cKey) {
                    $isLast = ($idx === $bucketTotal - 1);
                    $ratio = $totalWeight > 0
                        ? ($catWeights[$cKey] / $totalWeight)
                        : ($catCounts[$cKey] / max(1, $pCount));

                    $allocOrig = $isLast ? $remOrig : round($orig * $ratio, 2);
                    $allocDisc = $isLast ? $remDisc : round($disc * $ratio, 2);
                    $allocFee = $isLast ? $remFee : round($effectiveFee * $ratio, 2);
                    $allocFinal = $isLast ? $remFinal : round($final * $ratio, 2);
                    $allocNet = $isLast ? $remNet : round($net * $ratio, 2);

                    $remOrig = round($remOrig - $allocOrig, 2);
                    $remDisc = round($remDisc - $allocDisc, 2);
                    $remFee = round($remFee - $allocFee, 2);
                    $remFinal = round($remFinal - $allocFinal, 2);
                    $remNet = round($remNet - $allocNet, 2);

                    $categoryRows[$cKey]['participants_count'] += $catCounts[$cKey];
                    $categoryRows[$cKey]['tx_ids'][$tx->id] = true;
                    $categoryRows[$cKey]['total_original'] += $allocOrig;
                    $categoryRows[$cKey]['discount_amount'] += $allocDisc;
                    $categoryRows[$cKey]['platform_fee'] += $allocFee;
                    $categoryRows[$cKey]['gross_paid'] += $allocFinal;
                    $categoryRows[$cKey]['total_net'] += $allocNet;

                    if ($entersWallet) {
                        $categoryRows[$cKey]['wallet_net'] += $allocNet;
                        $categoryRows[$cKey]['midtrans_settled_count'] += $catCounts[$cKey];
                    } else {
                        $categoryRows[$cKey]['non_wallet_net'] += $allocNet;
                        if ($channelKey === 'midtrans_manual') {
                            $categoryRows[$cKey]['midtrans_manual_count'] += $catCounts[$cKey];
                        } elseif ($channelKey === 'free' || $final <= 0) {
                            $categoryRows[$cKey]['free_count'] += $catCounts[$cKey];
                        } else {
                            $categoryRows[$cKey]['offline_count'] += $catCounts[$cKey];
                        }
                    }
                }
            }
        }

        // Finalize category rows
        $finalCategories = [];
        foreach ($categoryRows as $row) {
            $row['tx_count'] = count($row['tx_ids']);
            unset($row['tx_ids']);
            $finalCategories[] = $row;
        }

        // Keep channels that either have transactions or are marked always_show
        $finalChannels = array_values(array_filter($channels, function ($ch) {
            return $ch['tx_count'] > 0 || ! empty($ch['always_show']);
        }));

        // Sort coupons by total discount desc, then participants desc
        $finalCoupons = array_values($coupons);
        usort($finalCoupons, function ($a, $b) {
            return ($b['discount_amount'] <=> $a['discount_amount'])
                ?: ($b['participants_count'] <=> $a['participants_count']);
        });

        // Admin Payouts (per-event) & EO Wallet Withdrawals (per-EO account)
        $eventPayoutRows = [];
        $eventPayoutCompleted = 0.0;
        $eventPayoutCompletedCount = 0;
        $lastEventPayoutAt = null;

        if (Schema::hasTable('event_payouts')) {
            $payoutRecords = EventPayout::query()
                ->where('event_id', $event->id)
                ->orderByDesc(DB::raw('COALESCE(paid_at, created_at)'))
                ->get();

            foreach ($payoutRecords as $p) {
                $amt = (float) ($p->amount ?? 0);
                $status = strtolower((string) ($p->status ?? 'completed'));
                $dateObj = $p->paid_at ?? $p->created_at;
                if ($status === 'completed') {
                    $eventPayoutCompleted += $amt;
                    $eventPayoutCompletedCount++;
                    if ($lastEventPayoutAt === null && $dateObj) {
                        $lastEventPayoutAt = $dateObj->format('d M Y');
                    }
                }
                $eventPayoutRows[] = [
                    'id' => $p->id,
                    'amount' => $amt,
                    'method' => $p->method ?: 'Transfer Bank',
                    'participants_count' => $p->participants_count,
                    'status' => $status,
                    'notes' => $p->notes ?: '-',
                    'paid_at' => $dateObj ? $dateObj->format('d M Y H:i') : '-',
                ];
            }
        }

        $eoWalletBalance = 0.0;
        $eoWalletLocked = 0.0;
        $eoEventCount = 1;
        $wdApprovedTotal = 0.0;
        $wdApprovedCount = 0;
        $wdPendingTotal = 0.0;
        $wdPendingCount = 0;
        $withdrawalRows = [];

        if ($event->user_id) {
            $eoEventCount = Event::query()->where('user_id', $event->user_id)->count();
            $eoWallet = \App\Models\Wallet::query()->where('user_id', $event->user_id)->first();
            if ($eoWallet) {
                $eoWalletBalance = (float) $eoWallet->balance;
                $eoWalletLocked = (float) $eoWallet->locked_balance;
            }

            if (Schema::hasTable('wallet_withdrawals')) {
                $wdRecords = WalletWithdrawal::query()
                    ->where('user_id', $event->user_id)
                    ->orderByDesc('created_at')
                    ->get();

                foreach ($wdRecords as $wd) {
                    $amt = (float) ($wd->amount ?? 0);
                    $status = strtolower((string) ($wd->status ?? 'pending'));
                    if (in_array($status, ['approved', 'completed'], true)) {
                        $wdApprovedTotal += $amt;
                        $wdApprovedCount++;
                    } elseif ($status === 'pending') {
                        $wdPendingTotal += $amt;
                        $wdPendingCount++;
                    }
                    $withdrawalRows[] = [
                        'id' => $wd->id,
                        'amount' => $amt,
                        'bank_name' => $wd->bank_name ?: '-',
                        'bank_account_name' => $wd->bank_account_name ?: '-',
                        'bank_account_number' => $wd->bank_account_number ?: '-',
                        'status' => $status,
                        'notes' => $wd->notes ?: '-',
                        'created_at' => $wd->created_at ? $wd->created_at->format('d M Y H:i') : '-',
                    ];
                }
            }
        }

        $remainingEventPayout = max(0.0, round($summary['verified_wallet_net'] - $eventPayoutCompleted, 2));

        $settlement = [
            'event_payout_paid' => $eventPayoutCompleted,
            'event_payout_count' => $eventPayoutCompletedCount,
            'last_event_payout_at' => $lastEventPayoutAt,
            'remaining_event_payout' => $remainingEventPayout,
            'wd_approved_total' => $wdApprovedTotal,
            'wd_approved_count' => $wdApprovedCount,
            'wd_pending_total' => $wdPendingTotal,
            'wd_pending_count' => $wdPendingCount,
            'eo_wallet_balance' => $eoWalletBalance,
            'eo_wallet_locked' => $eoWalletLocked,
            'eo_event_count' => $eoEventCount,
            'event_payouts' => $eventPayoutRows,
            'wallet_withdrawals' => $withdrawalRows,
        ];

        return [
            'summary' => $summary,
            'settlement' => $settlement,
            'categories' => $finalCategories,
            'channels' => $finalChannels,
            'coupons' => $finalCoupons,
            'non_settled_midtrans' => $nonSettledMidtrans,
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
