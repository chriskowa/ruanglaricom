<?php

namespace App\Models\Marketplace;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MarketplaceOrder extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dispute_proof_images' => 'array',
            'processed_at' => 'datetime',
            'packed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'completed_at' => 'datetime',
            'disputed_at' => 'datetime',
            'returned_at' => 'datetime',
            'refunded_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'seller_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
        ];
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items()
    {
        return $this->hasMany(MarketplaceOrderItem::class, 'order_id');
    }

    public function getGrandTotalAttribute()
    {
        return (float) ($this->attributes['total_amount'] ?? 0);
    }

    public function getSubtotalAttribute()
    {
        return max(0, (float) ($this->attributes['total_amount'] ?? 0) - (float) ($this->attributes['shipping_cost'] ?? 0));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Sudah Dibayar',
            'processing' => 'Diproses Penjual',
            'packing' => 'Sedang Dikemas',
            'shipped' => 'Sedang Dikirim',
            'delivered' => 'Sampai (Pengecekan Barang)',
            'completed' => 'Pesanan Selesai',
            'disputed' => 'Komplain / Sengketa',
            'return_in_progress' => 'Proses Pengembalian Barang',
            'refunded' => 'Dana Dikembalikan',
            'cancelled' => 'Dibatalkan',
            default => strtoupper((string) $this->status),
        };
    }
}
