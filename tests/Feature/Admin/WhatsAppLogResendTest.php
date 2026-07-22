<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\WhatsAppLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppLogResendTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_resend_failed_whatsapp_log()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = WhatsAppLog::create([
            'to' => '628123456789',
            'message' => 'Test resend message',
            'status' => 'failed',
            'http_code' => 401,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.whatsapp-logs.resend', $log));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Pesan WhatsApp berhasil dikirim ulang.');
    }

    public function test_non_admin_cannot_resend_whatsapp_log()
    {
        $runner = User::factory()->create(['role' => 'runner']);
        $log = WhatsAppLog::create([
            'to' => '628123456789',
            'message' => 'Test resend message',
            'status' => 'failed',
            'http_code' => 401,
        ]);

        $response = $this->actingAs($runner)
            ->post(route('admin.whatsapp-logs.resend', $log));

        $response->assertStatus(403);
    }
}
