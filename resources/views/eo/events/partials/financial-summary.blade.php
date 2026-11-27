@php
    $finSummary = $financialBreakdown['summary'] ?? [];
    $finSettlement = $financialBreakdown['settlement'] ?? [];
    $finCategories = $financialBreakdown['categories'] ?? [];
    $finChannels = $financialBreakdown['channels'] ?? [];
    $finCoupons = $financialBreakdown['coupons'] ?? [];
    $nonSettledList = $financialBreakdown['non_settled_midtrans'] ?? [];

    $fmtRp = static fn ($val) => 'Rp&nbsp;' . number_format((float) ($val ?? 0), 0, ',', '.');
    $nonSettledCount = count($nonSettledList);
    $couponCount = count($finCoupons);
    $payoutRecords = $finSettlement['event_payouts'] ?? [];
    $wdRecords = $finSettlement['wallet_withdrawals'] ?? [];
    $settlementHistoryCount = count($payoutRecords) + count($wdRecords);
@endphp

<section aria-labelledby="eoFinancialHeading" class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6 mb-8 relative z-10 print:hidden">
    <!-- Section Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-800">
        <div class="min-w-0">
            <h2 id="eoFinancialHeading" class="text-lg font-bold text-white tracking-tight" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                Ringkasan &amp; Rincian Keuangan Event
            </h2>
            <p class="text-xs sm:text-sm text-slate-300 mt-0.5">
                Pemisahan dana masuk wallet (Midtrans/Moota terverifikasi), pembayaran manual/offline, potongan kupon, dan net per kategori.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($nonSettledCount > 0)
                <button
                    type="button"
                    onclick="openNonSettledMidtransModal()"
                    class="min-h-[40px] px-3 py-2 rounded-md bg-amber-950 hover:bg-amber-900 border border-amber-800 text-amber-200 text-xs font-semibold transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
                >
                    Cek {{ $nonSettledCount }} Tx Midtrans Non-Settlement
                </button>
            @endif
            <button
                type="button"
                id="eoFinDetailToggleBtn"
                onclick="toggleEoFinancialDetails()"
                aria-expanded="true"
                aria-controls="eoFinDetailPanel"
                class="min-h-[40px] px-3.5 py-2 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs font-semibold transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
            >
                Sembunyikan Rincian
            </button>
        </div>
    </div>

    <!-- Layer 1: 4 Core KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5">
        <!-- 1. Verified Wallet Net -->
        <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-xs font-semibold text-emerald-300">Net Masuk Wallet</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-950 text-emerald-300 text-xs font-medium">Terverifikasi</span>
                </div>
                <p class="text-2xl font-bold font-mono tabular-nums text-white tracking-tight">
                    {!! $fmtRp($finSummary['verified_wallet_net'] ?? 0) !!}
                </p>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-800 space-y-1 text-xs text-slate-300">
                <div class="flex justify-between gap-2">
                    <span class="text-slate-400">Transaksi Settlement</span>
                    <span class="font-mono tabular-nums text-slate-200">{{ number_format($finSummary['verified_wallet_tx'] ?? 0) }} tx ({{ number_format($finSummary['verified_wallet_participants'] ?? 0) }} org)</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-slate-400">Fee Platform</span>
                    <span class="font-mono tabular-nums text-slate-200">{!! $fmtRp($finSummary['platform_fee_verified'] ?? 0) !!}</span>
                </div>
            </div>
        </div>

        <!-- 2. Midtrans Manual & Offline -->
        <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-xs font-semibold text-amber-300">Manual &amp; Non-Settlement</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-200 text-xs font-medium">Non-Wallet</span>
                </div>
                <p class="text-2xl font-bold font-mono tabular-nums text-white tracking-tight">
                    {!! $fmtRp($finSummary['non_wallet_total_net'] ?? 0) !!}
                </p>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-800 space-y-1 text-xs text-slate-300">
                <div class="flex justify-between gap-2">
                    <span class="text-slate-400">Midtrans Manual/Non-Settled</span>
                    <span class="font-mono tabular-nums {{ ($finSummary['midtrans_manual_tx'] ?? 0) > 0 ? 'text-amber-300 font-semibold' : 'text-slate-200' }}">
                        {!! $fmtRp($finSummary['midtrans_manual_net'] ?? 0) !!} ({{ number_format($finSummary['midtrans_manual_tx'] ?? 0) }} tx)
                    </span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-slate-400">Manual / CSV / COD</span>
                    <span class="font-mono tabular-nums text-slate-200">
                        {!! $fmtRp($finSummary['offline_net'] ?? 0) !!} ({{ number_format($finSummary['offline_tx'] ?? 0) }} tx)
                    </span>
                </div>
            </div>
        </div>

        <!-- 3. Coupon / Promo Discounts -->
        <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-xs font-semibold text-slate-200">Total Diskon Kupon</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-200 text-xs font-medium">{{ number_format($couponCount) }} Kode</span>
                </div>
                <p class="text-2xl font-bold font-mono tabular-nums text-white tracking-tight">
                    {!! $fmtRp($finSummary['coupon_discount_total'] ?? 0) !!}
                </p>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-800 space-y-1 text-xs text-slate-300">
                <div class="flex justify-between gap-2">
                    <span class="text-slate-400">Pemakaian Kupon (Paid)</span>
                    <span class="font-mono tabular-nums text-slate-200">{{ number_format($finSummary['coupon_tx_count'] ?? 0) }} tx ({{ number_format($finSummary['coupon_participants_count'] ?? 0) }} org)</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-slate-400">Kupon Gratis 100%</span>
                    <span class="font-mono tabular-nums text-slate-200">{{ number_format($finSummary['coupon_free_tx_count'] ?? 0) }} tx</span>
                </div>
            </div>
        </div>

        <!-- 4. Gross Paid & Total Net -->
        <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-xs font-semibold text-slate-200">Gross Paid &amp; Total Net</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-200 text-xs font-medium">{{ number_format($finSummary['total_tx'] ?? 0) }} Tx</span>
                </div>
                <p class="text-2xl font-bold font-mono tabular-nums text-white tracking-tight">
                    {!! $fmtRp($finSummary['gross_paid'] ?? 0) !!}
                </p>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-800 space-y-1 text-xs text-slate-300">
                <div class="flex justify-between gap-2">
                    <span class="text-slate-400">Total Net Semua Paid</span>
                    <span class="font-mono tabular-nums text-white font-semibold">{!! $fmtRp($finSummary['total_net'] ?? 0) !!}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-slate-400">Total Platform Fee</span>
                    <span class="font-mono tabular-nums text-slate-200">{!! $fmtRp($finSummary['platform_fee_total'] ?? 0) !!}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Layer 1B: Status Pencairan (Sudah Dibayar Admin / Withdraw EO & Sisa) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 mt-3.5">
        <!-- Sudah Dibayar Admin (Event Payout) -->
        <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <span class="text-xs font-semibold text-slate-200">Sudah Dibayar Admin (Payout Event)</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-200 text-xs font-mono tabular-nums">{{ number_format($finSettlement['event_payout_count'] ?? 0) }}x</span>
                </div>
                <p class="text-xl font-bold font-mono tabular-nums text-white tracking-tight">
                    {!! $fmtRp($finSettlement['event_payout_paid'] ?? 0) !!}
                </p>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-800 flex justify-between gap-2 text-xs text-slate-300">
                <span class="text-slate-400">Payout Terakhir</span>
                <span class="font-mono tabular-nums text-slate-200">{{ $finSettlement['last_event_payout_at'] ?? 'Belum ada' }}</span>
            </div>
        </div>

        <!-- Sudah Di-Withdraw (Wallet EO) -->
        <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <span class="text-xs font-semibold text-slate-200">Total Withdraw Wallet EO</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-200 text-xs font-mono tabular-nums">{{ number_format($finSettlement['wd_approved_count'] ?? 0) }} WD</span>
                </div>
                <p class="text-xl font-bold font-mono tabular-nums text-white tracking-tight">
                    {!! $fmtRp($finSettlement['wd_approved_total'] ?? 0) !!}
                </p>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-800 flex justify-between gap-2 text-xs text-slate-300">
                @if(($finSettlement['wd_pending_total'] ?? 0) > 0)
                    <span class="text-amber-300">Menunggu Proses ({{ $finSettlement['wd_pending_count'] }} WD)</span>
                    <span class="font-mono tabular-nums text-amber-300 font-semibold">{!! $fmtRp($finSettlement['wd_pending_total'] ?? 0) !!}</span>
                @else
                    <span class="text-slate-400">Cakupan Akun EO</span>
                    <span class="text-slate-200">{{ ($finSettlement['eo_event_count'] ?? 1) > 1 ? ($finSettlement['eo_event_count'] . ' event') : 'Event ini saja' }}</span>
                @endif
            </div>
        </div>

        <!-- Sisa Belum Dibayar & Saldo Wallet Aktif -->
        <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <span class="text-xs font-semibold text-emerald-300">Sisa Net Event Belum Dibayar</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-950 text-emerald-300 text-xs font-medium">Estimasi</span>
                </div>
                <p class="text-xl font-bold font-mono tabular-nums text-emerald-300 tracking-tight">
                    {!! $fmtRp($finSettlement['remaining_event_payout'] ?? 0) !!}
                </p>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-800 flex justify-between gap-2 text-xs text-slate-300">
                <span class="text-slate-400">Saldo Wallet EO Saat Ini</span>
                <span class="font-mono tabular-nums text-white font-semibold">{!! $fmtRp($finSettlement['eo_wallet_balance'] ?? 0) !!}</span>
            </div>
        </div>
    </div>

    <!-- Layer 2: Collapsible Tabbed Financial Details -->
    <div id="eoFinDetailPanel" class="mt-5 pt-5 border-t border-slate-800">
        <!-- Tab Bar (horizontal scroll on narrow screens, inline on desktop) -->
        <div role="tablist" aria-label="Rincian Keuangan Event" class="flex items-center gap-2 overflow-x-auto pb-1 mb-4">
            <button
                type="button"
                role="tab"
                id="eoFinTabBtn-categories"
                aria-selected="true"
                aria-controls="eoFinTabPanel-categories"
                onclick="switchEoFinancialTab('categories')"
                class="eo-fin-tab-btn shrink-0 min-h-[40px] px-3.5 py-2 rounded-md text-xs font-semibold bg-amber-400 text-slate-950 transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
            >
                Net per Kategori ({{ count($finCategories) }})
            </button>
            <button
                type="button"
                role="tab"
                id="eoFinTabBtn-channels"
                aria-selected="false"
                aria-controls="eoFinTabPanel-channels"
                onclick="switchEoFinancialTab('channels')"
                class="eo-fin-tab-btn shrink-0 min-h-[40px] px-3.5 py-2 rounded-md text-xs font-semibold bg-slate-800 text-slate-200 hover:bg-slate-700 transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
            >
                Sumber Pembayaran ({{ count($finChannels) }})
            </button>
            <button
                type="button"
                role="tab"
                id="eoFinTabBtn-coupons"
                aria-selected="false"
                aria-controls="eoFinTabPanel-coupons"
                onclick="switchEoFinancialTab('coupons')"
                class="eo-fin-tab-btn shrink-0 min-h-[40px] px-3.5 py-2 rounded-md text-xs font-semibold bg-slate-800 text-slate-200 hover:bg-slate-700 transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
            >
                Kupon &amp; Promo ({{ $couponCount }})
            </button>
            <button
                type="button"
                role="tab"
                id="eoFinTabBtn-payouts"
                aria-selected="false"
                aria-controls="eoFinTabPanel-payouts"
                onclick="switchEoFinancialTab('payouts')"
                class="eo-fin-tab-btn shrink-0 min-h-[40px] px-3.5 py-2 rounded-md text-xs font-semibold bg-slate-800 text-slate-200 hover:bg-slate-700 transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
            >
                Payout &amp; Withdraw ({{ $settlementHistoryCount }})
            </button>
        </div>

        <!-- TAB 1: NET PER KATEGORI -->
        <div id="eoFinTabPanel-categories" role="tabpanel" aria-labelledby="eoFinTabBtn-categories" class="eo-fin-tab-panel">
            @if(empty($finCategories))
                <div class="bg-slate-950 border border-slate-800 rounded-lg p-6 text-center">
                    <p class="text-sm text-slate-300">Belum ada kategori lomba atau transaksi lunas pada event ini.</p>
                </div>
            @else
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto bg-slate-950 border border-slate-800 rounded-lg">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-xs font-semibold text-slate-300 bg-slate-900">
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-3 text-right">Peserta / Kuota</th>
                                <th class="py-3 px-3 text-right">Harga Asli</th>
                                <th class="py-3 px-3 text-right">Diskon Kupon</th>
                                <th class="py-3 px-3 text-right">Platform Fee</th>
                                <th class="py-3 px-3 text-right">Net Wallet</th>
                                <th class="py-3 px-4 text-right">Total Net Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            @foreach($finCategories as $cat)
                                <tr class="hover:bg-slate-900 transition-colors duration-150">
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-white">{{ $cat['name'] }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5 font-mono tabular-nums">
                                            Midtrans: {{ $cat['midtrans_settled_count'] }}
                                            @if($cat['midtrans_manual_count'] > 0)
                                                &bull; <span class="text-amber-300">Midtrans Manual: {{ $cat['midtrans_manual_count'] }}</span>
                                            @endif
                                            @if($cat['offline_count'] > 0)
                                                &bull; Manual/Offline: {{ $cat['offline_count'] }}
                                            @endif
                                            @if($cat['free_count'] > 0)
                                                &bull; Gratis/Kupon 100%: {{ $cat['free_count'] }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono tabular-nums text-slate-200">
                                        <span class="text-white font-semibold">{{ number_format($cat['participants_count']) }}</span>
                                        @if($cat['quota'] !== null && $cat['quota'] > 0)
                                            <span class="text-slate-400">/ {{ number_format($cat['quota']) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono tabular-nums text-slate-200">{!! $fmtRp($cat['total_original']) !!}</td>
                                    <td class="py-3 px-3 text-right font-mono tabular-nums {{ $cat['discount_amount'] > 0 ? 'text-amber-300' : 'text-slate-400' }}">
                                        {{ $cat['discount_amount'] > 0 ? '-' : '' }}{!! $fmtRp($cat['discount_amount']) !!}
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono tabular-nums text-slate-300">{!! $fmtRp($cat['platform_fee']) !!}</td>
                                    <td class="py-3 px-3 text-right font-mono tabular-nums text-emerald-300 font-semibold">{!! $fmtRp($cat['wallet_net']) !!}</td>
                                    <td class="py-3 px-4 text-right font-mono tabular-nums text-white font-bold">
                                        {!! $fmtRp($cat['total_net']) !!}
                                        @if($cat['non_wallet_net'] > 0)
                                            <span class="block text-xs font-normal text-amber-300">+{!! $fmtRp($cat['non_wallet_net']) !!} non-wallet</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-700 bg-slate-900 text-sm font-bold text-white">
                                <td class="py-3 px-4">Total Semua Kategori</td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums">{{ number_format($finSummary['total_participants'] ?? 0) }} org</td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums">{!! $fmtRp($finSummary['total_original'] ?? 0) !!}</td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums text-amber-300">
                                    {{ ($finSummary['coupon_discount_total'] ?? 0) > 0 ? '-' : '' }}{!! $fmtRp($finSummary['coupon_discount_total'] ?? 0) !!}
                                </td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums text-slate-200">{!! $fmtRp($finSummary['platform_fee_total'] ?? 0) !!}</td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums text-emerald-300">{!! $fmtRp($finSummary['verified_wallet_net'] ?? 0) !!}</td>
                                <td class="py-3 px-4 text-right font-mono tabular-nums text-white">{!! $fmtRp($finSummary['total_net'] ?? 0) !!}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile Stacked Cards -->
                <div class="md:hidden space-y-3">
                    @foreach($finCategories as $cat)
                        <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold text-white truncate">{{ $cat['name'] }}</h3>
                                    <p class="text-xs text-slate-300 font-mono tabular-nums mt-0.5">
                                        {{ number_format($cat['participants_count']) }}@if($cat['quota'] !== null && $cat['quota'] > 0)/{{ number_format($cat['quota']) }}@endif peserta paid
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="block text-xs text-slate-400">Total Net</span>
                                    <span class="text-base font-bold font-mono tabular-nums text-white">{!! $fmtRp($cat['total_net']) !!}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5 pt-2.5 border-t border-slate-800 text-xs">
                                <div>
                                    <span class="block text-slate-400">Net Masuk Wallet</span>
                                    <span class="font-mono tabular-nums font-semibold text-emerald-300">{!! $fmtRp($cat['wallet_net']) !!}</span>
                                </div>
                                <div>
                                    <span class="block text-slate-400">Non-Wallet / Manual</span>
                                    <span class="font-mono tabular-nums {{ $cat['non_wallet_net'] > 0 ? 'text-amber-300 font-semibold' : 'text-slate-300' }}">{!! $fmtRp($cat['non_wallet_net']) !!}</span>
                                </div>
                                <div>
                                    <span class="block text-slate-400">Harga Asli</span>
                                    <span class="font-mono tabular-nums text-slate-200">{!! $fmtRp($cat['total_original']) !!}</span>
                                </div>
                                <div>
                                    <span class="block text-slate-400">Diskon / Fee</span>
                                    <span class="font-mono tabular-nums text-slate-200">
                                        -{!! $fmtRp($cat['discount_amount']) !!} / {!! $fmtRp($cat['platform_fee']) !!}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- TAB 2: SUMBER PEMBAYARAN -->
        <div id="eoFinTabPanel-channels" role="tabpanel" aria-labelledby="eoFinTabBtn-channels" class="eo-fin-tab-panel hidden">
            @if($nonSettledCount > 0)
                <div class="mb-3.5 p-3.5 rounded-md bg-amber-950 border border-amber-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <p class="text-xs sm:text-sm text-amber-100 leading-relaxed">
                        Terdapat <strong class="font-mono tabular-nums text-white">{{ $nonSettledCount }}</strong> transaksi Midtrans berstatus non-settlement / sandbox senilai <strong class="font-mono tabular-nums text-white">{!! $fmtRp($finSummary['midtrans_manual_net'] ?? 0) !!}</strong>. Transaksi ini tidak otomatis masuk saldo wallet EO.
                    </p>
                    <button
                        type="button"
                        onclick="openNonSettledMidtransModal()"
                        class="shrink-0 min-h-[38px] px-3 py-1.5 rounded-md bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs font-bold transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300"
                    >
                        Lihat Daftar Transaksi
                    </button>
                </div>
            @endif

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto bg-slate-950 border border-slate-800 rounded-lg">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs font-semibold text-slate-300 bg-slate-900">
                            <th class="py-3 px-4">Sumber Pembayaran</th>
                            <th class="py-3 px-3">Status Wallet</th>
                            <th class="py-3 px-3 text-right">Tx</th>
                            <th class="py-3 px-3 text-right">Peserta</th>
                            <th class="py-3 px-3 text-right">Harga Asli</th>
                            <th class="py-3 px-3 text-right">Diskon</th>
                            <th class="py-3 px-3 text-right">Fee</th>
                            <th class="py-3 px-4 text-right">Net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-sm">
                        @foreach($finChannels as $ch)
                            @php
                                $badgeClass = match ($ch['wallet_status']) {
                                    'wallet' => 'bg-emerald-950 text-emerald-300',
                                    'warning' => 'bg-amber-950 text-amber-300',
                                    default => 'bg-slate-800 text-slate-200',
                                };
                            @endphp
                            <tr class="hover:bg-slate-900 transition-colors duration-150">
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-white">{{ $ch['label'] }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">{{ $ch['sublabel'] }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $badgeClass }}">
                                        {{ $ch['wallet_badge'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums text-slate-200">{{ number_format($ch['tx_count']) }}</td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums text-slate-200">{{ number_format($ch['participants_count']) }}</td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums text-slate-200">{!! $fmtRp($ch['total_original']) !!}</td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums {{ $ch['discount_amount'] > 0 ? 'text-amber-300' : 'text-slate-400' }}">
                                    {{ $ch['discount_amount'] > 0 ? '-' : '' }}{!! $fmtRp($ch['discount_amount']) !!}
                                </td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums text-slate-300">{!! $fmtRp($ch['platform_fee']) !!}</td>
                                <td class="py-3 px-4 text-right font-mono tabular-nums font-bold {{ $ch['wallet_status'] === 'wallet' ? 'text-emerald-300' : 'text-white' }}">
                                    {!! $fmtRp($ch['net_amount']) !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-700 bg-slate-900 text-sm font-bold text-white">
                            <td class="py-3 px-4" colspan="2">Total Semua Sumber</td>
                            <td class="py-3 px-3 text-right font-mono tabular-nums">{{ number_format($finSummary['total_tx'] ?? 0) }}</td>
                            <td class="py-3 px-3 text-right font-mono tabular-nums">{{ number_format($finSummary['total_participants'] ?? 0) }}</td>
                            <td class="py-3 px-3 text-right font-mono tabular-nums">{!! $fmtRp($finSummary['total_original'] ?? 0) !!}</td>
                            <td class="py-3 px-3 text-right font-mono tabular-nums text-amber-300">
                                {{ ($finSummary['coupon_discount_total'] ?? 0) > 0 ? '-' : '' }}{!! $fmtRp($finSummary['coupon_discount_total'] ?? 0) !!}
                            </td>
                            <td class="py-3 px-3 text-right font-mono tabular-nums text-slate-200">{!! $fmtRp($finSummary['platform_fee_total'] ?? 0) !!}</td>
                            <td class="py-3 px-4 text-right font-mono tabular-nums">{!! $fmtRp($finSummary['total_net'] ?? 0) !!}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Mobile Stacked Cards -->
            <div class="md:hidden space-y-3">
                @foreach($finChannels as $ch)
                    @php
                        $badgeClass = match ($ch['wallet_status']) {
                            'wallet' => 'bg-emerald-950 text-emerald-300',
                            'warning' => 'bg-amber-950 text-amber-300',
                            default => 'bg-slate-800 text-slate-200',
                        };
                    @endphp
                    <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-white">{{ $ch['label'] }}</h3>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-xs font-medium {{ $badgeClass }}">{{ $ch['wallet_badge'] }}</span>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="block text-xs text-slate-400">Net</span>
                                <span class="text-base font-bold font-mono tabular-nums {{ $ch['wallet_status'] === 'wallet' ? 'text-emerald-300' : 'text-white' }}">{!! $fmtRp($ch['net_amount']) !!}</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 pt-2.5 border-t border-slate-800 text-xs">
                            <div>
                                <span class="block text-slate-400">Transaksi / Peserta</span>
                                <span class="font-mono tabular-nums text-slate-200">{{ number_format($ch['tx_count']) }} tx &bull; {{ number_format($ch['participants_count']) }} org</span>
                            </div>
                            <div>
                                <span class="block text-slate-400">Harga Asli</span>
                                <span class="font-mono tabular-nums text-slate-200">{!! $fmtRp($ch['total_original']) !!}</span>
                            </div>
                            <div>
                                <span class="block text-slate-400">Diskon Kupon</span>
                                <span class="font-mono tabular-nums {{ $ch['discount_amount'] > 0 ? 'text-amber-300' : 'text-slate-300' }}">{!! $fmtRp($ch['discount_amount']) !!}</span>
                            </div>
                            <div>
                                <span class="block text-slate-400">Platform Fee</span>
                                <span class="font-mono tabular-nums text-slate-200">{!! $fmtRp($ch['platform_fee']) !!}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TAB 3: KUPON & PROMO -->
        <div id="eoFinTabPanel-coupons" role="tabpanel" aria-labelledby="eoFinTabBtn-coupons" class="eo-fin-tab-panel hidden">
            @if(empty($finCoupons))
                <div class="bg-slate-950 border border-slate-800 rounded-lg p-6 text-center">
                    <p class="text-sm text-slate-300">Belum ada transaksi lunas yang menggunakan kupon atau promo pada event ini.</p>
                </div>
            @else
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto bg-slate-950 border border-slate-800 rounded-lg">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-xs font-semibold text-slate-300 bg-slate-900">
                                <th class="py-3 px-4">Kode Kupon</th>
                                <th class="py-3 px-3">Nilai</th>
                                <th class="py-3 px-3 text-right">Tx</th>
                                <th class="py-3 px-3 text-right">Peserta</th>
                                <th class="py-3 px-3 text-right">Nilai Asli</th>
                                <th class="py-3 px-3 text-right">Total Potongan</th>
                                <th class="py-3 px-4 text-right">Dibayar (Net)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            @foreach($finCoupons as $cp)
                                <tr class="hover:bg-slate-900 transition-colors duration-150">
                                    <td class="py-3 px-4">
                                        <span class="font-mono font-bold text-white">{{ $cp['code'] }}</span>
                                        @if($cp['max_uses'] !== null)
                                            <span class="block text-xs text-slate-400 font-mono tabular-nums mt-0.5">Terpakai {{ number_format($cp['used_count'] ?? $cp['tx_count']) }} / {{ number_format($cp['max_uses']) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 font-mono tabular-nums text-slate-200">{{ $cp['value_label'] }}</td>
                                    <td class="py-3 px-3 text-right font-mono tabular-nums text-slate-200">{{ number_format($cp['tx_count']) }}</td>
                                    <td class="py-3 px-3 text-right font-mono tabular-nums text-slate-200">{{ number_format($cp['participants_count']) }}</td>
                                    <td class="py-3 px-3 text-right font-mono tabular-nums text-slate-200">{!! $fmtRp($cp['total_original']) !!}</td>
                                    <td class="py-3 px-3 text-right font-mono tabular-nums font-semibold text-amber-300">-{!! $fmtRp($cp['discount_amount']) !!}</td>
                                    <td class="py-3 px-4 text-right font-mono tabular-nums text-white font-semibold">{!! $fmtRp($cp['net_amount']) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Stacked Cards -->
                <div class="md:hidden space-y-3">
                    @foreach($finCoupons as $cp)
                        <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 space-y-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="font-mono text-sm font-bold text-white">{{ $cp['code'] }}</span>
                                    <span class="ml-1.5 px-2 py-0.5 rounded bg-slate-800 text-slate-200 text-xs font-mono">{{ $cp['value_label'] }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-xs text-slate-400">Potongan</span>
                                    <span class="font-mono tabular-nums text-sm font-bold text-amber-300">-{!! $fmtRp($cp['discount_amount']) !!}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-2.5 border-t border-slate-800 text-xs">
                                <div>
                                    <span class="block text-slate-400">Pemakaian</span>
                                    <span class="font-mono tabular-nums text-slate-200">{{ number_format($cp['tx_count']) }} tx &bull; {{ number_format($cp['participants_count']) }} org</span>
                                </div>
                                <div>
                                    <span class="block text-slate-400">Nilai Asli / Net</span>
                                    <span class="font-mono tabular-nums text-slate-200">{!! $fmtRp($cp['total_original']) !!} / {!! $fmtRp($cp['net_amount']) !!}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- TAB 4: RIWAYAT PAYOUT ADMIN & WITHDRAW WALLET -->
        <div id="eoFinTabPanel-payouts" role="tabpanel" aria-labelledby="eoFinTabBtn-payouts" class="eo-fin-tab-panel hidden space-y-5">
            <!-- Sub A: Payout Event oleh Admin -->
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
                    <h3 class="text-sm font-semibold text-white">Riwayat Pembayaran Admin (Payout Event Ini)</h3>
                    <span class="text-xs font-mono tabular-nums text-slate-300">Total Selesai: <strong class="text-white">{!! $fmtRp($finSettlement['event_payout_paid'] ?? 0) !!}</strong></span>
                </div>

                @if(empty($payoutRecords))
                    <div class="bg-slate-950 border border-slate-800 rounded-lg p-5 text-center">
                        <p class="text-xs sm:text-sm text-slate-300">Belum ada pencatatan payout admin untuk event ini.</p>
                    </div>
                @else
                    <div class="hidden md:block overflow-x-auto bg-slate-950 border border-slate-800 rounded-lg">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-800 bg-slate-900 text-xs font-semibold text-slate-300">
                                    <th class="py-2.5 px-4">Tanggal</th>
                                    <th class="py-2.5 px-3">Metode</th>
                                    <th class="py-2.5 px-3">Status</th>
                                    <th class="py-2.5 px-3">Catatan</th>
                                    <th class="py-2.5 px-4 text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                @foreach($payoutRecords as $p)
                                    @php
                                        $pBadge = $p['status'] === 'completed'
                                            ? 'bg-emerald-950 text-emerald-300'
                                            : 'bg-slate-800 text-slate-300';
                                    @endphp
                                    <tr class="hover:bg-slate-900 transition-colors duration-150">
                                        <td class="py-2.5 px-4 font-mono tabular-nums text-xs text-slate-200">{{ $p['paid_at'] }}</td>
                                        <td class="py-2.5 px-3 text-slate-200">{{ $p['method'] }}</td>
                                        <td class="py-2.5 px-3">
                                            <span class="inline-block px-2 py-0.5 rounded text-xs font-medium uppercase {{ $pBadge }}">{{ $p['status'] }}</span>
                                        </td>
                                        <td class="py-2.5 px-3 text-xs text-slate-300">{{ $p['notes'] }}</td>
                                        <td class="py-2.5 px-4 text-right font-mono tabular-nums font-semibold text-white">{!! $fmtRp($p['amount']) !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="md:hidden space-y-2.5">
                        @foreach($payoutRecords as $p)
                            @php
                                $pBadge = $p['status'] === 'completed'
                                    ? 'bg-emerald-950 text-emerald-300'
                                    : 'bg-slate-800 text-slate-300';
                            @endphp
                            <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5 space-y-1.5 text-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono tabular-nums text-slate-300">{{ $p['paid_at'] }}</span>
                                    <span class="font-mono tabular-nums text-sm font-bold text-white">{!! $fmtRp($p['amount']) !!}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-slate-200">{{ $p['method'] }}</span>
                                    <span class="px-2 py-0.5 rounded uppercase font-medium {{ $pBadge }}">{{ $p['status'] }}</span>
                                </div>
                                @if($p['notes'] !== '-')
                                    <p class="text-slate-400 pt-1 border-t border-slate-800">{{ $p['notes'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Sub B: Riwayat Withdraw Wallet EO -->
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
                    <h3 class="text-sm font-semibold text-white">Riwayat Penarikan Wallet (Withdraw Akun EO)</h3>
                    <span class="text-xs font-mono tabular-nums text-slate-300">Disetujui: <strong class="text-white">{!! $fmtRp($finSettlement['wd_approved_total'] ?? 0) !!}</strong></span>
                </div>

                @if(empty($wdRecords))
                    <div class="bg-slate-950 border border-slate-800 rounded-lg p-5 text-center">
                        <p class="text-xs sm:text-sm text-slate-300">Belum ada permintaan withdraw wallet pada akun EO ini.</p>
                    </div>
                @else
                    <div class="hidden md:block overflow-x-auto bg-slate-950 border border-slate-800 rounded-lg">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-800 bg-slate-900 text-xs font-semibold text-slate-300">
                                    <th class="py-2.5 px-4">Tanggal</th>
                                    <th class="py-2.5 px-3">Rekening Tujuan</th>
                                    <th class="py-2.5 px-3">Status</th>
                                    <th class="py-2.5 px-3">Catatan</th>
                                    <th class="py-2.5 px-4 text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                @foreach($wdRecords as $wd)
                                    @php
                                        $wdBadge = match ($wd['status']) {
                                            'approved', 'completed' => 'bg-emerald-950 text-emerald-300',
                                            'pending' => 'bg-amber-950 text-amber-300',
                                            default => 'bg-rose-950 text-rose-300',
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-900 transition-colors duration-150">
                                        <td class="py-2.5 px-4 font-mono tabular-nums text-xs text-slate-200">{{ $wd['created_at'] }}</td>
                                        <td class="py-2.5 px-3 text-xs text-slate-200">
                                            <span class="font-semibold text-white">{{ $wd['bank_name'] }}</span>
                                            <span class="text-slate-400"> &bull; {{ $wd['bank_account_name'] }} ({{ $wd['bank_account_number'] }})</span>
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="inline-block px-2 py-0.5 rounded text-xs font-medium uppercase {{ $wdBadge }}">{{ $wd['status'] }}</span>
                                        </td>
                                        <td class="py-2.5 px-3 text-xs text-slate-300">{{ $wd['notes'] }}</td>
                                        <td class="py-2.5 px-4 text-right font-mono tabular-nums font-semibold text-white">{!! $fmtRp($wd['amount']) !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="md:hidden space-y-2.5">
                        @foreach($wdRecords as $wd)
                            @php
                                $wdBadge = match ($wd['status']) {
                                    'approved', 'completed' => 'bg-emerald-950 text-emerald-300',
                                    'pending' => 'bg-amber-950 text-amber-300',
                                    default => 'bg-rose-950 text-rose-300',
                                };
                            @endphp
                            <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5 space-y-1.5 text-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono tabular-nums text-slate-300">{{ $wd['created_at'] }}</span>
                                    <span class="font-mono tabular-nums text-sm font-bold text-white">{!! $fmtRp($wd['amount']) !!}</span>
                                </div>
                                <div class="text-slate-200">{{ $wd['bank_name'] }} &bull; {{ $wd['bank_account_name'] }} ({{ $wd['bank_account_number'] }})</div>
                                <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-800">
                                    <span class="text-slate-400">{{ $wd['notes'] }}</span>
                                    <span class="px-2 py-0.5 rounded uppercase font-medium {{ $wdBadge }}">{{ $wd['status'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@if($nonSettledCount > 0)
<!-- Modal Audit Transaksi Midtrans Non-Settlement -->
<div id="eoNonSettledMidtransModal" class="fixed inset-0 z-50 hidden print:hidden" role="dialog" aria-modal="true" aria-labelledby="eoNonSettledModalTitle">
    <div class="fixed inset-0 bg-slate-950/80" onclick="closeNonSettledMidtransModal()"></div>
    <div class="fixed inset-0 overflow-y-auto p-4 sm:p-6 flex items-center justify-center">
        <div class="relative w-full max-w-4xl bg-slate-900 border border-slate-800 rounded-lg shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between gap-3">
                <div>
                    <h3 id="eoNonSettledModalTitle" class="text-base font-bold text-white" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                        Daftar Transaksi Midtrans Non-Settlement ({{ $nonSettledCount }})
                    </h3>
                    <p class="text-xs text-slate-300 mt-0.5">
                        Transaksi berikut berstatus paid di sistem tetapi belum tercatat settlement/capture Midtrans Production sehingga tidak masuk saldo wallet.
                    </p>
                </div>
                <button
                    type="button"
                    onclick="closeNonSettledMidtransModal()"
                    aria-label="Tutup modal transaksi non-settlement"
                    class="min-w-[40px] min-h-[40px] inline-flex items-center justify-center rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
                >
                    &times;
                </button>
            </div>

            <div class="p-5 max-h-[70vh] overflow-y-auto space-y-3">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto bg-slate-950 border border-slate-800 rounded-lg">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 bg-slate-900 text-slate-300 font-semibold">
                                <th class="py-2.5 px-3">TX / Order ID</th>
                                <th class="py-2.5 px-3">PIC &amp; Peserta</th>
                                <th class="py-2.5 px-3">Keterangan Status</th>
                                <th class="py-2.5 px-3">Tanggal Paid</th>
                                <th class="py-2.5 px-3 text-right">Net</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach($nonSettledList as $item)
                                <tr>
                                    <td class="py-2.5 px-3 font-mono text-white">
                                        <div>#{{ $item['id'] }} ({{ $item['public_ref'] }})</div>
                                        <div class="text-slate-400 mt-0.5">{{ $item['midtrans_order_id'] }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-200">
                                        <div class="font-semibold text-white">{{ $item['pic_name'] }}</div>
                                        <div class="text-slate-400 mt-0.5">{{ $item['participants_names'] }}</div>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <span class="inline-block px-2 py-0.5 rounded bg-amber-950 text-amber-300 font-medium">{{ $item['reason'] }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono tabular-nums text-slate-300">{{ $item['paid_at'] }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono tabular-nums font-semibold text-white">{!! $fmtRp($item['net_amount']) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden space-y-2.5">
                    @foreach($nonSettledList as $item)
                        <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5 space-y-2 text-xs">
                            <div class="flex items-start justify-between gap-2">
                                <div class="font-mono text-white font-semibold">#{{ $item['id'] }} &bull; {{ $item['public_ref'] }}</div>
                                <div class="font-mono tabular-nums font-bold text-white">{!! $fmtRp($item['net_amount']) !!}</div>
                            </div>
                            <div class="text-slate-300 font-mono break-all">{{ $item['midtrans_order_id'] }}</div>
                            <div class="text-slate-200">{{ $item['pic_name'] }} — <span class="text-slate-400">{{ $item['participants_names'] }}</span></div>
                            <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-800">
                                <span class="px-2 py-0.5 rounded bg-amber-950 text-amber-300 font-medium">{{ $item['reason'] }}</span>
                                <span class="font-mono tabular-nums text-slate-400">{{ $item['paid_at'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="px-5 py-3.5 border-t border-slate-800 bg-slate-950 flex justify-end">
                <button
                    type="button"
                    onclick="closeNonSettledMidtransModal()"
                    class="min-h-[40px] px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs font-semibold transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    (function() {
        const STORAGE_KEY = 'eo_fin_detail_open_v1';

        function applyEoFinancialDetailState(isOpen) {
            const panel = document.getElementById('eoFinDetailPanel');
            const btn = document.getElementById('eoFinDetailToggleBtn');
            if (!panel || !btn) return;
            panel.classList.toggle('hidden', !isOpen);
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            btn.textContent = isOpen ? 'Sembunyikan Rincian' : 'Lihat Rincian';
        }

        window.toggleEoFinancialDetails = function() {
            const panel = document.getElementById('eoFinDetailPanel');
            if (!panel) return;
            const nextOpen = panel.classList.contains('hidden');
            applyEoFinancialDetailState(nextOpen);
            try {
                localStorage.setItem(STORAGE_KEY, nextOpen ? '1' : '0');
            } catch (e) {}
        };

        window.switchEoFinancialTab = function(tabKey) {
            document.querySelectorAll('.eo-fin-tab-panel').forEach(function(el) {
                el.classList.add('hidden');
            });
            document.querySelectorAll('.eo-fin-tab-btn').forEach(function(btn) {
                btn.setAttribute('aria-selected', 'false');
                btn.classList.remove('bg-amber-400', 'text-slate-950');
                btn.classList.add('bg-slate-800', 'text-slate-200', 'hover:bg-slate-700');
            });

            const activePanel = document.getElementById('eoFinTabPanel-' + tabKey);
            const activeBtn = document.getElementById('eoFinTabBtn-' + tabKey);
            if (activePanel) activePanel.classList.remove('hidden');
            if (activeBtn) {
                activeBtn.setAttribute('aria-selected', 'true');
                activeBtn.classList.remove('bg-slate-800', 'text-slate-200', 'hover:bg-slate-700');
                activeBtn.classList.add('bg-amber-400', 'text-slate-950');
            }
        };

        window.openNonSettledMidtransModal = function() {
            const modal = document.getElementById('eoNonSettledMidtransModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        window.closeNonSettledMidtransModal = function() {
            const modal = document.getElementById('eoNonSettledMidtransModal');
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        };

        // Default open on desktop (>= 768px), collapsed on mobile unless user toggled manually
        let initialOpen = window.innerWidth >= 768;
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved === '1') initialOpen = true;
            if (saved === '0') initialOpen = false;
        } catch (e) {}
        applyEoFinancialDetailState(initialOpen);
    })();
</script>
