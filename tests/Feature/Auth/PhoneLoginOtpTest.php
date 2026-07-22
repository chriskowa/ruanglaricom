<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\OtpToken;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhoneLoginOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\AppSettings::set('whatsapp_is_active', true);
    }

    public function test_user_can_request_otp_with_valid_phone()
    {
        $user = User::factory()->create([
            'phone' => '628123456789',
            'role' => 'runner',
        ]);

        $response = $this->postJson(route('login.phone.request'), [
            'phone' => '08123456789',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Kode OTP berhasil dikirim ke nomor WhatsApp Anda.',
        ]);

        $this->assertDatabaseHas('otp_tokens', [
            'user_id' => $user->id,
            'used' => false,
        ]);
    }

    public function test_user_can_request_otp_with_valid_phone_stored_as_zero()
    {
        $user = User::factory()->create([
            'phone' => '08123456789',
            'role' => 'runner',
        ]);

        $response = $this->postJson(route('login.phone.request'), [
            'phone' => '628123456789',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Kode OTP berhasil dikirim ke nomor WhatsApp Anda.',
        ]);

        $this->assertDatabaseHas('otp_tokens', [
            'user_id' => $user->id,
            'used' => false,
        ]);
    }

    public function test_user_cannot_request_otp_with_unregistered_phone()
    {
        $response = $this->postJson(route('login.phone.request'), [
            'phone' => '08999999999',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Nomor WhatsApp tidak terdaftar.',
        ]);
    }

    public function test_user_can_login_with_valid_otp()
    {
        $user = User::factory()->create([
            'phone' => '628123456789',
            'role' => 'runner',
        ]);

        $otpToken = OtpToken::create([
            'user_id' => $user->id,
            'code' => '123456',
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        $response = $this->postJson(route('login.phone.verify'), [
            'user_id' => $user->id,
            'code' => '123456',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($otpToken->fresh()->used);
    }

    public function test_user_cannot_login_with_invalid_otp()
    {
        $user = User::factory()->create([
            'phone' => '628123456789',
            'role' => 'runner',
        ]);

        $otpToken = OtpToken::create([
            'user_id' => $user->id,
            'code' => '123456',
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        $response = $this->postJson(route('login.phone.verify'), [
            'user_id' => $user->id,
            'code' => '654321',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Kode OTP tidak valid atau kedaluwarsa.',
        ]);

        $this->assertGuest();
        $this->assertFalse($otpToken->fresh()->used);
    }

    public function test_user_cannot_request_otp_more_than_once_every_5_minutes()
    {
        $user = User::factory()->create([
            'phone' => '628123456789',
            'role' => 'runner',
        ]);

        // First request
        $response1 = $this->postJson(route('login.phone.request'), [
            'phone' => '08123456789',
        ]);
        $response1->assertStatus(200);

        // Second request immediately
        $response2 = $this->postJson(route('login.phone.request'), [
            'phone' => '08123456789',
        ]);
        $response2->assertStatus(422);
        $response2->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('Silakan tunggu', $response2->json('message'));
    }
}
