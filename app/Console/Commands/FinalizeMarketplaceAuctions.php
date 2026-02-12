<?php

namespace App\Console\Commands;

use App\Models\AppSettings;
use App\Models\Marketplace\MarketplaceBid;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Marketplace\MarketplaceProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinalizeMarketplaceAuctions extends Command
{
    protected $signature = 'marketplace:auctions:finalize';

    protected $description = 'Finalize ended marketplace auctions and create pending orders for winners';

    public function handle(): int
    {
        $now = now();

        MarketplaceProduct::where('sale_type', 'auction')
            ->where('auction_status', 'running')
            ->whereNotNull('auction_end_at')
            ->where('auction_end_at', '<=', $now)
            ->orderBy('auction_end_at')
            ->chunkById(50, function ($products) use ($now) {
                foreach ($products as $product) {
                    DB::transaction(function () use ($product, $now) {
                        $locked = MarketplaceProduct::whereKey($product->id)->lockForUpdate()->first();
                        if (! $locked) {
                            return;
                        }
                        if ($locked->auction_status !== 'running') {
                            return;
                        }
                        if (! $locked->auction_end_at || $locked->auction_end_at->gt($now)) {
                            return;
                        }

                        $topBid = MarketplaceBid::where('product_id', $locked->id)
                            ->orderByDesc('amount')
                            ->orderByDesc('id')
                            ->first();

                        $locked->auction_status = 'ended';
                        $locked->is_active = false;

                        if (! $topBid) {
                            $locked->save();

                            return;
                        }

                        if ($locked->reserve_price && (float) $topBid->amount < (float) $locked->reserve_price) {
                            $locked->save();

                            return;
                        }

                        $winningAmount = (float) $topBid->amount;
                        $commissionRate = (float) AppSettings::get('marketplace_commission_percentage', 1);
                        $commissionAmount = $winningAmount * ($commissionRate / 100);
                        $consignmentFeeRate = (float) AppSettings::get('marketplace_consignment_fee_percentage', 0);
                        $consignmentFeeAmount = 0;
                        if ($locked->fulfillment_mode === 'consignment') {
                            $consignmentFeeAmount = $winningAmount * ($consignmentFeeRate / 100);
                        }
                        $totalFee = $commissionAmount + $consignmentFeeAmount;
                        $sellerAmount = $winningAmount - $totalFee;

                        $order = MarketplaceOrder::create([
                            'invoice_number' => 'INV-RL-AUC-'.strtoupper(Str::random(10)),
                            'buyer_id' => $topBid->user_id,
                            'seller_id' => $locked->user_id,
                            'total_amount' => $winningAmount,
                            'commission_amount' => $totalFee,
                            'seller_amount' => $sellerAmount,
                            'status' => 'pending',
                        ]);

                        $order->items()->create([
                            'product_id' => $locked->id,
                            'product_title_snapshot' => $locked->title,
                            'price_snapshot' => $winningAmount,
                            'quantity' => 1,
                        ]);

                        $locked->auction_winner_id = $topBid->user_id;
                        $locked->current_price = $winningAmount;
                        $locked->stock = 0;
                        if ($locked->fulfillment_mode === 'consignment') {
                            $locked->consignment_status = 'sold';
                        }
                        $locked->save();
                    }, 3);
                }
            });

        return self::SUCCESS;
    }
}
