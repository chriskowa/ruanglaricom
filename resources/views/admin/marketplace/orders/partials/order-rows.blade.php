@forelse($orders as $order)
<tr class="hover:bg-slate-800/40 transition-colors border-b border-slate-850/60" id="order-row-{{ $order->id }}">
    <td class="px-6 py-4">
        <div class="space-y-1">
            <a href="{{ route('admin.marketplace.orders.show', $order->id) }}" class="text-white font-mono font-bold text-sm hover:text-neon transition flex items-center gap-1.5">
                <span>#{{ $order->invoice_number }}</span>
                <i class="fas fa-external-link-alt text-[10px] text-slate-500"></i>
            </a>
            <p class="text-slate-400 text-xs font-mono">{{ $order->created_at->format('d M Y H:i') }}</p>
        </div>
    </td>
    <td class="px-6 py-4">
        <div class="space-y-1">
            <p class="text-white font-bold text-xs">{{ optional($order->buyer)->name ?? 'Buyer' }}</p>
            <p class="text-slate-400 text-[11px] font-mono">{{ optional($order->buyer)->email ?? '-' }}</p>
        </div>
    </td>
    <td class="px-6 py-4">
        <div class="space-y-1">
            <p class="text-white font-bold text-xs">{{ optional($order->seller)->name ?? 'Seller' }}</p>
            <p class="text-slate-400 text-[11px] font-mono">{{ optional($order->seller)->email ?? '-' }}</p>
        </div>
    </td>
    <td class="px-6 py-4">
        <div class="space-y-1">
            <p class="text-neon font-black font-mono text-sm">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
            <p class="text-slate-400 text-[10px] font-mono">Fee: Rp {{ number_format($order->commission_amount, 0, ',', '.') }}</p>
        </div>
    </td>
    <td class="px-6 py-4">
        @if($order->status === 'paid')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">
                <i class="fas fa-credit-card text-[9px]"></i> Paid (Sudah Dibayar)
            </span>
        @elseif($order->status === 'shipped')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-500/10 text-blue-400 border border-blue-500/30">
                <i class="fas fa-truck text-[9px]"></i> Shipped (Dikirim)
            </span>
        @elseif($order->status === 'completed')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                <i class="fas fa-check-circle text-[9px]"></i> Completed (Selesai)
            </span>
        @elseif($order->status === 'disputed')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-400 border border-rose-500/40 animate-pulse">
                <i class="fas fa-exclamation-triangle text-[9px]"></i> Disputed (Berkendala)
            </span>
        @elseif($order->status === 'cancelled')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">
                <i class="fas fa-times-circle text-[9px]"></i> Cancelled
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/30">
                {{ strtoupper($order->status) }}
            </span>
        @endif

        @if($order->shipping_tracking_number)
            <div class="mt-1 text-[10px] font-mono text-slate-300">
                Resi: <span class="text-white font-bold">{{ $order->shipping_tracking_number }}</span>
            </div>
        @endif
    </td>
    <td class="px-6 py-4 text-right">
        <div class="flex items-center justify-end gap-2">
            <!-- Dispute Resolution Action Button -->
            @if(in_array($order->status, ['disputed', 'shipped', 'paid']))
                <button @click="openDisputeModal({{ json_encode($order) }})" 
                        class="px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 hover:bg-amber-500 hover:text-dark font-bold text-xs transition flex items-center gap-1">
                    <i class="fas fa-gavel text-[10px]"></i>
                    <span>Tindakan Admin</span>
                </button>
            @endif

            <a href="{{ route('admin.marketplace.orders.show', $order->id) }}" 
               class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-bold text-xs transition">
                Detail
            </a>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
        <div class="w-12 h-12 rounded-full bg-slate-900 flex items-center justify-center mx-auto mb-3 text-xl">🛒</div>
        Tidak ada pesanan marketplace yang sesuai dengan filter.
    </td>
</tr>
@endforelse
