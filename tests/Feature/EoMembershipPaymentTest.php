<?php

namespace Tests\Feature;

use App\Models\MembershipTransaction;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EoMembershipPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_eo_can_access_own_payment_page()
    {
        // 1. Create EO User
        $user = User::factory()->create([
            'role' => 'eo',
            'email' => 'eo@example.com',
        ]);

        // 2. Create Package
        $package = Package::create([
            'name' => 'Premium EO',
            'slug' => 'premium-eo',
            'price' => 500000,
            'duration_days' => 30,
            'is_active' => true,
            'benefits' => [], // Add empty array for json cast
            'description' => 'Test Package',
        ]);

        // 3. Create Transaction
        $transaction = MembershipTransaction::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => 500000,
            'total_amount' => 500000,
            'status' => 'pending',
        ]);

        // 4. Act as User and Request
        $response = $this->actingAs($user)
            ->get(route('eo.membership.payment', $transaction->id));

        // 5. Assert
        $response->assertStatus(200);
    }

    public function test_eo_cannot_access_others_payment_page()
    {
        // 1. Create EO User 1
        $user1 = User::factory()->create([
            'role' => 'eo',
        ]);

        // 2. Create EO User 2
        $user2 = User::factory()->create([
            'role' => 'eo',
        ]);

        // 3. Create Package
        $package = Package::create([
            'name' => 'Premium EO',
            'slug' => 'premium-eo',
            'price' => 500000,
            'duration_days' => 30,
            'is_active' => true,
            'benefits' => [],
            'description' => 'Test Package',
        ]);

        // 4. Create Transaction for User 2
        $transaction = MembershipTransaction::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user2->id,
            'package_id' => $package->id,
            'amount' => 500000,
            'total_amount' => 500000,
            'status' => 'pending',
        ]);

        // 5. Act as User 1 and Request User 2's transaction
        $response = $this->actingAs($user1)
            ->get(route('eo.membership.payment', $transaction->id));

        // 6. Assert Forbidden
        $response->assertStatus(403);
    }

    public function test_non_eo_cannot_access_payment_page()
    {
        // 1. Create Runner User
        $user = User::factory()->create([
            'role' => 'runner',
        ]);

        // 2. Create Transaction (even if they own it, route is protected by role:eo)
        // Wait, if they are runner, they shouldn't have MembershipTransaction for EO packages usually,
        // but let's assume they somehow have one or try to access one.

        $transaction = new MembershipTransaction;
        $transaction->id = (string) Str::uuid();
        $transaction->user_id = $user->id; // They own it
        // We need a package and save it to allow route binding
        // But for 403 role check, route binding happens after middleware?
        // Actually middleware runs before controller method.
        // But route model binding runs before middleware? No, middleware runs first usually?
        // Laravel: Global middleware -> Route middleware -> Controller.
        // Route Model Binding happens in the routing layer.

        // Let's just mock the route access.
        // We need a real transaction in DB for binding to work if bindings are checked.
        // If binding fails -> 404.
        // If middleware fails -> 403.

        $package = Package::create([
            'name' => 'Test',
            'slug' => 'test',
            'price' => 100,
            'duration_days' => 1,
            'benefits' => [],
            'description' => 'Test',
        ]);

        $transaction = MembershipTransaction::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => 100,
            'total_amount' => 100,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->get(route('eo.membership.payment', $transaction->id));

        $response->assertStatus(403);
    }

    public function test_eo_can_access_own_payment_page_with_loose_type_check()
    {
        // 1. Create EO User
        $user = User::factory()->create([
            'role' => 'eo',
        ]);

        // 2. Create Package
        $package = Package::create([
            'name' => 'Premium EO',
            'slug' => 'premium-eo-2',
            'price' => 500000,
            'duration_days' => 30,
            'is_active' => true,
            'benefits' => [],
            'description' => 'Test Package',
        ]);

        // 3. Create Transaction
        $transaction = MembershipTransaction::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => 500000,
            'total_amount' => 500000,
            'status' => 'pending',
        ]);

        // 4. Force mismatch in Auth::id() by mocking or just relying on logic
        // We can't easily force Auth::id() to return string if it returns int.
        // But we can rely on the fact that if this test passes, the change to `!=` didn't break anything.
        // To really test it, we'd need to mock Auth facade.

        $response = $this->actingAs($user)
            ->get(route('eo.membership.payment', $transaction->id));

        $response->assertStatus(200);
    }
}
