<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilePhoneUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_update_phone_number()
    {
        $response = $this->postJson(route('profile.update-phone'), [
            'phone' => '081234567890',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_phone_number()
    {
        $user = User::factory()->create([
            'phone' => null,
        ]);

        $response = $this->actingAs($user)->postJson(route('profile.update-phone'), [
            'phone' => '081234567890',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Nomor HP berhasil diperbarui!',
        ]);

        $this->assertEquals('081234567890', $user->fresh()->phone);
    }

    public function test_phone_number_is_required_and_validated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('profile.update-phone'), [
            'phone' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }
}
