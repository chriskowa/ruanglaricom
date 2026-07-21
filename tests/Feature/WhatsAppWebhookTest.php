<?php

namespace Tests\Feature;

use App\Models\User;
use App\Helpers\WhatsApp;
use App\Services\OpenAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\AppSettings::set('whatsapp_is_active', true);
        \App\Models\AppSettings::set('whatsapp_app_key', 'test_key');
        \App\Models\AppSettings::set('whatsapp_auth_key', 'test_auth');
        
        // Set WHATSAPP_WEBHOOK_TOKEN to null or match the test request
        // Since env() is cached or read from the actual environment, we can pass the token in the query parameter
        // or we can mock/override the env helper or config if we change the controller to use config().
        // Let's pass the token in the query parameter to make it work cleanly.
    }

    public function test_webhook_handles_stop_command()
    {
        $user = User::factory()->create([
            'phone' => '628123456789',
            'is_receive_wa' => true,
        ]);

        $response = $this->postJson(route('webhook.whatsapp', ['token' => '4pp124ngl4r1']), [
            'from' => '628123456789',
            'message' => 'STOP',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertFalse($user->fresh()->is_receive_wa);
    }

    public function test_webhook_handles_start_command()
    {
        $user = User::factory()->create([
            'phone' => '628123456789',
            'is_receive_wa' => false,
        ]);

        $response = $this->postJson(route('webhook.whatsapp', ['token' => '4pp124ngl4r1']), [
            'from' => '628123456789',
            'message' => 'START',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertTrue($user->fresh()->is_receive_wa);
    }

    public function test_webhook_replies_using_openai_for_running_questions()
    {
        $user = User::factory()->create([
            'phone' => '628123456789',
        ]);

        $mockOpenAi = $this->createMock(OpenAiService::class);
        $mockOpenAi->expects($this->once())
            ->method('getAiResponse')
            ->with(
                'bagaimana cara latihan interval?',
                $this->stringContains('AI Running Coach dari Ruang Lari'),
                'gpt-4'
            )
            ->willReturn('Latihan interval dilakukan dengan lari cepat diselingi istirahat.');

        $this->app->instance(OpenAiService::class, $mockOpenAi);

        $response = $this->postJson(route('webhook.whatsapp', ['token' => '4pp124ngl4r1']), [
            'from' => '628123456789',
            'message' => 'bagaimana cara latihan interval?',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
