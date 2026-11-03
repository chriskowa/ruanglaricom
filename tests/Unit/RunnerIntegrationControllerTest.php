<?php

namespace Tests\Unit;

use App\Http\Controllers\Runner\IntegrationController;
use App\Models\StravaActivity;
use App\Models\User;
use Tests\TestCase;

class RunnerIntegrationControllerTest extends TestCase
{
    public function test_build_strava_data_identifies_connected_user(): void
    {
        $controller = new IntegrationController();

        $user = new User([
            'strava_id' => '12345678',
            'strava_access_token' => 'test_token',
            'strava_expires_at' => now()->addDays(10)->toIso8601String(),
        ]);

        $activity = new StravaActivity([
            'name' => 'Morning 5K',
            'distance_m' => 5000,
        ]);

        $data = $controller->buildStravaData($user, 12, $activity);

        $this->assertTrue($data['is_connected']);
        $this->assertEquals('12345678', $data['athlete_id']);
        $this->assertEquals(12, $data['synced_count']);
        $this->assertFalse($data['is_expired']);
        $this->assertEquals('Morning 5K', $data['last_activity']->name);
        $this->assertEquals('https://www.strava.com/athletes/12345678', $data['athlete_url']);
    }

    public function test_build_strava_data_handles_disconnected_user(): void
    {
        $controller = new IntegrationController();

        $user = new User([
            'strava_id' => null,
            'strava_access_token' => null,
            'strava_expires_at' => null,
        ]);

        $data = $controller->buildStravaData($user, 0, null);

        $this->assertFalse($data['is_connected']);
        $this->assertNull($data['athlete_id']);
        $this->assertEquals(0, $data['synced_count']);
        $this->assertNull($data['last_activity']);
    }

    public function test_build_email_data_identifies_verified_and_unverified_email(): void
    {
        $controller = new IntegrationController();

        $verifiedUser = new User([
            'email' => 'runner@ruanglari.com',
        ]);
        $verifiedUser->email_verified_at = '2026-02-01 10:00:00';
        $verifiedData = $controller->buildEmailData($verifiedUser);

        $this->assertEquals('runner@ruanglari.com', $verifiedData['address']);
        $this->assertTrue($verifiedData['is_verified']);
        $this->assertNotNull($verifiedData['verified_at']);

        $unverifiedUser = new User([
            'email' => 'newrunner@ruanglari.com',
            'email_verified_at' => null,
        ]);
        $unverifiedData = $controller->buildEmailData($unverifiedUser);

        $this->assertEquals('newrunner@ruanglari.com', $unverifiedData['address']);
        $this->assertFalse($unverifiedData['is_verified']);
        $this->assertNull($unverifiedData['verified_at']);
    }

    public function test_build_whatsapp_data_identifies_phone_and_subscription_status(): void
    {
        $controller = new IntegrationController();

        $userWithWa = new User([
            'phone' => '081234567890',
            'is_receive_wa' => true,
        ]);
        $waData = $controller->buildWhatsAppData($userWithWa);

        $this->assertEquals('081234567890', $waData['phone']);
        $this->assertTrue($waData['has_phone']);
        $this->assertTrue($waData['is_receive_wa']);

        $userWithoutWa = new User([
            'phone' => null,
            'is_receive_wa' => false,
        ]);
        $emptyWaData = $controller->buildWhatsAppData($userWithoutWa);

        $this->assertNull($emptyWaData['phone']);
        $this->assertFalse($emptyWaData['has_phone']);
        $this->assertFalse($emptyWaData['is_receive_wa']);
    }
}
