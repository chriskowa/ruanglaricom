<?php

namespace Tests\Feature;

use App\Models\Marketplace\MarketplaceCategory;
use App\Models\Marketplace\MarketplaceConsignmentIntake;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Marketplace\MarketplaceProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketplaceAuctionAndConsignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_bidding_enforces_minimum_and_extends_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-22 10:00:00'));

        $seller = User::factory()->create(['role' => 'runner']);
        $bidder = User::factory()->create(['role' => 'runner']);

        $category = MarketplaceCategory::create([
            'name' => 'Shoes',
            'slug' => 'shoes',
        ]);

        $product = MarketplaceProduct::create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'Rare Shoe',
            'slug' => 'rare-shoe-'.Str::random(6),
            'description' => 'Test',
            'price' => 100000,
            'stock' => 1,
            'condition' => 'used',
            'type' => 'physical',
            'is_active' => true,
            'sale_type' => 'auction',
            'auction_status' => 'running',
            'auction_start_at' => now()->subMinute(),
            'auction_end_at' => now()->addMinute(),
            'starting_price' => 100000,
            'current_price' => 100000,
            'min_increment' => 10000,
        ]);

        $r1 = $this->actingAs($bidder)->post(route('marketplace.auction.bid', $product->slug), [
            'amount' => 100000,
        ]);
        $r1->assertRedirect();

        $product->refresh();
        $this->assertEquals(100000, (int) $product->current_price);
        $this->assertEquals('2026-01-22 10:04:00', $product->auction_end_at->format('Y-m-d H:i:s'));

        $r2 = $this->actingAs($bidder)->post(route('marketplace.auction.bid', $product->slug), [
            'amount' => 105000,
        ]);
        $r2->assertRedirect();
        $r2->assertSessionHas('error');

        $r3 = $this->actingAs($bidder)->post(route('marketplace.auction.bid', $product->slug), [
            'amount' => 110000,
        ]);
        $r3->assertRedirect();

        $product->refresh();
        $this->assertEquals(110000, (int) $product->current_price);
    }

    public function test_finalize_creates_order_for_winner(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-22 12:00:00'));

        $seller = User::factory()->create(['role' => 'runner']);
        $b1 = User::factory()->create(['role' => 'runner']);
        $b2 = User::factory()->create(['role' => 'runner']);

        $category = MarketplaceCategory::create([
            'name' => 'Watch',
            'slug' => 'watch',
        ]);

        $product = MarketplaceProduct::create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'Garmin',
            'slug' => 'garmin-'.Str::random(6),
            'description' => 'Test',
            'price' => 500000,
            'stock' => 1,
            'condition' => 'used',
            'type' => 'physical',
            'is_active' => true,
            'sale_type' => 'auction',
            'auction_status' => 'running',
            'auction_start_at' => now()->subDay(),
            'auction_end_at' => now()->subMinute(),
            'starting_price' => 500000,
            'current_price' => 500000,
            'min_increment' => 10000,
        ]);

        $product->bids()->create(['user_id' => $b1->id, 'amount' => 510000]);
        $product->bids()->create(['user_id' => $b2->id, 'amount' => 530000]);

        $this->artisan('marketplace:auctions:finalize')->assertExitCode(0);

        $product->refresh();
        $this->assertEquals('ended', $product->auction_status);
        $this->assertEquals($b2->id, $product->auction_winner_id);
        $this->assertEquals(0, (int) $product->stock);
        $this->assertFalse((bool) $product->is_active);

        $order = MarketplaceOrder::where('buyer_id', $b2->id)->where('seller_id', $seller->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(530000, (int) $order->total_amount);
        $this->assertEquals(1, $order->items()->count());
        $this->assertEquals(530000, (int) $order->items()->first()->price_snapshot);
    }

    public function test_consignment_request_creates_intake_and_admin_can_publish(): void
    {
        $seller = User::factory()->create(['role' => 'runner']);
        $admin = User::factory()->create(['role' => 'admin']);

        $category = MarketplaceCategory::create([
            'name' => 'Apparel',
            'slug' => 'apparel',
        ]);

        $payload = [
            'title' => 'Jersey Event',
            'category_id' => $category->id,
            'price' => 150000,
            'stock' => 1,
            'condition' => 'used',
            'type' => 'physical',
            'description' => 'Test',
            'sale_type' => 'fixed',
            'fulfillment_mode' => 'consignment',
            'dropoff_method' => 'Dropoff',
            'dropoff_location' => 'Jakarta',
            'image' => UploadedFile::fake()->image('x.jpg'),
        ];

        $r = $this->actingAs($seller)->post(route('marketplace.seller.products.store'), $payload);
        $r->assertRedirect();

        $product = MarketplaceProduct::first();
        $this->assertNotNull($product);
        $this->assertEquals('consignment', $product->fulfillment_mode);
        $this->assertEquals('requested', $product->consignment_status);
        $this->assertFalse((bool) $product->is_active);

        $intake = MarketplaceConsignmentIntake::where('product_id', $product->id)->first();
        $this->assertNotNull($intake);
        $this->assertEquals('requested', $intake->status);

        $r2 = $this->actingAs($admin)->post(route('admin.marketplace.consignments.listed', $intake->id));
        $r2->assertRedirect();

        $product->refresh();
        $intake->refresh();
        $this->assertEquals('listed', $product->consignment_status);
        $this->assertTrue((bool) $product->is_active);
        $this->assertEquals('listed', $intake->status);
    }
}
