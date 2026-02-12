<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\FinalizeMarketplaceAuctions;
use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceBid;
use App\Models\Marketplace\MarketplaceProduct;
use Illuminate\Support\Facades\DB;

class MarketplaceAuctionController extends Controller
{
    public function index()
    {
        $auctions = MarketplaceProduct::with(['seller', 'primaryImage'])
            ->where('sale_type', 'auction')
            ->orderByRaw("CASE auction_status WHEN 'running' THEN 0 WHEN 'draft' THEN 1 WHEN 'ended' THEN 2 ELSE 3 END")
            ->orderBy('auction_end_at')
            ->paginate(20);

        return view('admin.marketplace.auctions.index', compact('auctions'));
    }

    public function show(MarketplaceProduct $product)
    {
        if ($product->sale_type !== 'auction') {
            abort(404);
        }

        $bids = MarketplaceBid::with('bidder')
            ->where('product_id', $product->id)
            ->orderByDesc('amount')
            ->orderByDesc('id')
            ->paginate(50);

        return view('admin.marketplace.auctions.show', compact('product', 'bids'));
    }

    public function cancel(MarketplaceProduct $product)
    {
        if ($product->sale_type !== 'auction') {
            abort(404);
        }

        DB::transaction(function () use ($product) {
            $locked = MarketplaceProduct::whereKey($product->id)->lockForUpdate()->first();
            if (! $locked) {
                return;
            }
            $locked->auction_status = 'cancelled';
            $locked->is_active = false;
            $locked->save();
        }, 3);

        return back()->with('success', 'Auction cancelled.');
    }

    public function finalize(MarketplaceProduct $product)
    {
        if ($product->sale_type !== 'auction') {
            abort(404);
        }

        if ($product->auction_status !== 'running') {
            return back()->with('success', 'Auction already finalized.');
        }

        $cmd = app(FinalizeMarketplaceAuctions::class);
        $cmd->handle();

        return back()->with('success', 'Finalize triggered.');
    }
}
