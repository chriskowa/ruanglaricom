<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_send_whatsapp_to_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $runner = User::factory()->create([
            'role' => 'runner',
            'phone' => '628123456789',
        ]);

        \App\Models\AppSettings::set('whatsapp_is_active', true);
        \App\Models\AppSettings::set('whatsapp_app_key', 'test_key');
        \App\Models\AppSettings::set('whatsapp_auth_key', 'test_auth');

        $response = $this->actingAs($admin)->post(route('admin.users.send-whatsapp', $runner), [
            'message' => 'Halo runner, semangat latihan!',
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pesan WhatsApp berhasil dikirim.',
        ]);
    }

    public function test_admin_cannot_send_whatsapp_if_user_has_no_phone()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $runner = User::factory()->create([
            'role' => 'runner',
            'phone' => null,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.send-whatsapp', $runner), [
            'message' => 'Halo runner!',
        ], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'User tidak memiliki nomor telepon terdaftar.',
        ]);
    }
}
