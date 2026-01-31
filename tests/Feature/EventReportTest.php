<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Transaction;
use App\Models\RaceCategory;
use App\Models\User;
use App\Services\EventReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EventReportTest extends TestCase
{
    use RefreshDatabase;

    protected $event;
    protected $category;
    protected $package;
    protected $reportService;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->reportService = new EventReportService();
        $this->user = User::factory()->create();
        
        // Create event
        $this->event = Event::factory()->create([
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(11),
        ]);

        // Create dummy package
        $this->package = \App\Models\EventPackage::create([
            'event_id' => $this->event->id,
            'name' => 'Regular Package',
            'price' => 100000,
            'quota' => 100,
        ]);

        // Create category with 100 slots manually as Factory might not exist
        $this->category = RaceCategory::create([
            'event_id' => $this->event->id,
            'name' => 'General 5K',
            'code' => '5K',
            'distance_km' => 5,
            'quota' => 100,
            'price_regular' => 100000,
            'min_age' => 12,
            'max_age' => 99,
            'reg_start_at' => now(),
            'reg_end_at' => now()->addDays(5),
            'is_active' => true,
        ]);
    }

    protected function createParticipant($status = 'paid', $priceType = 'regular')
    {
        // Manually create Transaction
        $transaction = Transaction::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'payment_status' => $status,
            'total_original' => 100000,
            'final_amount' => 100000,
            'pic_data' => [],
        ]);

        // Manually create Participant
        return Participant::create([
            'transaction_id' => $transaction->id,
            'event_package_id' => $this->package->id,
            'race_category_id' => $this->category->id,
            'name' => 'John Doe',
            'gender' => 'male',
            'phone' => '08123456789',
            'email' => 'john@example.com',
            'id_card' => '1234567890' . rand(1000, 9999),
            'date_of_birth' => '1990-01-01',
            'price_type' => $priceType,
            'status' => 'active', // Assuming 'status' column exists and matters
        ]);
    }

    /** @test */
    public function it_calculates_total_and_sold_slots_correctly()
    {
        // Register 5 participants with paid transactions
        for ($i = 0; $i < 5; $i++) {
            $this->createParticipant('paid');
        }

        // Register 3 participants with pending transactions
        for ($i = 0; $i < 3; $i++) {
            $this->createParticipant('pending');
        }

        $report = $this->reportService->getEventReport($this->event);

        $this->assertEquals(100, $report['total_slots']);
        $this->assertEquals(5, $report['sold_slots']); 
        $this->assertEquals(3, $report['pending_slots']);
        $this->assertFalse($report['show_warning']);
    }

    /** @test */
    public function it_shows_warning_when_slots_low()
    {
        // Sell 91 slots (9 remaining) -> < 10%
        
        // Optimization: Create one transaction and many participants?
        // Service queries participants whereHas transaction.
        // If they share transaction, it still counts participants.
        
        $transaction = Transaction::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'payment_status' => 'paid',
            'total_original' => 9100000,
            'final_amount' => 9100000,
            'pic_data' => [],
        ]);

        for ($i = 0; $i < 91; $i++) {
             Participant::create([
                'transaction_id' => $transaction->id,
                'event_package_id' => $this->package->id,
                'race_category_id' => $this->category->id,
                'name' => 'Runner ' . $i,
                'gender' => 'male',
                'phone' => '08123456789',
                'email' => "runner{$i}@example.com",
                'id_card' => '1234567890' . $i,
                'date_of_birth' => '1990-01-01',
                'price_type' => 'regular',
                'status' => 'active',
            ]);
        }

        $report = $this->reportService->getEventReport($this->event);

        $this->assertEquals(91, $report['sold_slots']);
        $this->assertTrue($report['show_warning']);
    }

    /** @test */
    public function it_breaks_down_ticket_types()
    {
        // 2 Early Bird
        for ($i = 0; $i < 2; $i++) {
            $this->createParticipant('paid', 'early_bird');
        }

        // 3 Regular
        for ($i = 0; $i < 3; $i++) {
            $this->createParticipant('paid', 'regular');
        }

        $report = $this->reportService->getEventReport($this->event);

        $this->assertEquals(2, $report['breakdown']['early_bird']);
        $this->assertEquals(3, $report['breakdown']['regular']);
        $this->assertEquals(40, $report['percentages']['early_bird']); 
        $this->assertEquals(60, $report['percentages']['regular']);   
    }

    /** @test */
    public function it_caches_report_data()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn([
                'total_slots' => 100,
                'sold_slots' => 50,
                'breakdown' => [],
                'percentages' => [],
                'coupon_usage' => 0,
                'is_unlimited' => false,
                'show_warning' => false
            ]);

        $this->reportService->getEventReport($this->event);
    }

    /** @test */
    public function it_handles_concurrent_bookings_simulation()
    {
        // Simulate concurrent state: 
        // 5 Paid (Sold)
        // 5 Pending (Reserved/Booking in progress)
        // 2 Expired (Should not count as sold/pending)
        // 3 Failed (Should not count)

        for ($i = 0; $i < 5; $i++) $this->createParticipant('paid');
        for ($i = 0; $i < 5; $i++) $this->createParticipant('pending');
        for ($i = 0; $i < 2; $i++) $this->createParticipant('expired');
        for ($i = 0; $i < 3; $i++) $this->createParticipant('failed');

        $report = $this->reportService->getEventReport($this->event);

        // Sold = 5
        $this->assertEquals(5, $report['sold_slots']);
        // Pending = 5
        $this->assertEquals(5, $report['pending_slots']);
        // Total Used = 10
        // Expired/Failed ignored
        
        // Check percentages logic
        // 5 Early Bird (default in createParticipant)
        // Breakdown only counts SOLD slots in current logic? 
        // Let's check Service logic. 
        // "breakdown" is based on $query (which is payment_status IN paid/settlement/capture)
        // So pending shouldn't appear in breakdown.
        
        $this->assertEquals(5, $report['breakdown']['regular']); // 5 paid regulars
    }
}
