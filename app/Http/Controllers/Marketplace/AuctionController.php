<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceBid;
use App\Models\Marketplace\MarketplaceProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    public function bid(Request $request, $slug)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();

        $result = DB::transaction(function () use ($request, $slug, $userId) {
            $product = MarketplaceProduct::where('slug', $slug)->lockForUpdate()->firstOrFail();

            if ($product->sale_type !== 'auction') {
                return ['ok' => false, 'message' => 'Produk ini bukan lelang.'];
            }

            if (! $product->is_active) {
                return ['ok' => false, 'message' => 'Produk tidak tersedia.'];
            }

            if ($product->user_id === $userId) {
                return ['ok' => false, 'message' => 'Tidak bisa bidding di produk sendiri.'];
            }

            if ($product->auction_status !== 'running') {
                return ['ok' => false, 'message' => 'Lelang belum berjalan atau sudah berakhir.'];
            }

            $now = now();
            if ($product->auction_start_at && $now->lt($product->auction_start_at)) {
                return ['ok' => false, 'message' => 'Lelang belum dimulai.'];
            }
            if ($product->auction_end_at && $now->gte($product->auction_end_at)) {
                return ['ok' => false, 'message' => 'Lelang sudah berakhir.'];
            }

            $amount = (float) $request->amount;
            $starting = (float) ($product->starting_price ?? $product->price);
            $current = (float) ($product->current_price ?? $starting);
            $minIncrement = (float) ($product->min_increment ?? 0);

            $hasAnyBid = MarketplaceBid::where('product_id', $product->id)->exists();
            $minAllowed = $hasAnyBid ? ($current + $minIncrement) : $starting;

            if ($amount < $minAllowed) {
                return ['ok' => false, 'message' => 'Nominal bid minimal Rp ' . number_format($minAllowed, 0, ',', '.') . '.'];
            }

            MarketplaceBid::create([
                'product_id' => $product->id,
                'user_id' => $userId,
                'amount' => $amount,
            ]);

            $product->current_price = $amount;

            if ($product->auction_end_at && $product->auction_end_at->diffInSeconds($now) <= 120) {
                $product->auction_end_at = $product->auction_end_at->copy()->addMinutes(3);
            }

            $product->save();

            return ['ok' => true];
        }, 3);

        if (! $result['ok']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', 'Bid berhasil dikirim.');
    }
}
