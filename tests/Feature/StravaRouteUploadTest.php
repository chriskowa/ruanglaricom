<?php

namespace Tests\Feature;

use App\Models\Admin\StravaConfig;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StravaRouteUploadTest extends TestCase
{
    public function test_guest_cannot_upload_route_to_strava(): void
    {
        $response = $this->postJson(route('tools.buat-rute-lari.strava-upload'), [
            'points' => [
                ['lat' => -6.2, 'lng' => 106.8],
                ['lat' => -6.21, 'lng' => 106.81],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_trigger_strava_upload_with_fake_http(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('strava_configs');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->boolean('is_active')->default(true);
            $table->bigInteger('strava_id')->nullable();
            $table->text('strava_access_token')->nullable();
            $table->text('strava_refresh_token')->nullable();
            $table->timestamp('strava_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('strava_configs', function (Blueprint $table) {
            $table->id();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('club_id')->nullable();
            $table->timestamps();
        });

        StravaConfig::create([
            'client_id' => 'dummy',
            'client_secret' => 'dummy',
        ]);

        $user = User::create([
            'name' => 'Runner Test',
            'email' => 'runner_test@example.com',
            'password' => Hash::make('password'),
            'role' => 'runner',
            'is_active' => true,
            'strava_access_token' => 'at_123',
            'strava_refresh_token' => 'rt_123',
            'strava_expires_at' => now()->addHours(1),
        ]);

        Http::fake([
            'https://www.strava.com/api/v3/uploads' => Http::response([
                'id' => 999,
                'external_id' => 'dummy.gpx',
                'status' => 'Your activity is still being processed.',
                'error' => null,
            ], 201),
        ]);

        $response = $this->actingAs($user)->postJson(route('tools.buat-rute-lari.strava-upload'), [
            'name' => 'Test Route',
            'activity_type' => 'run',
            'pace_sec_per_km' => 360,
            'points' => [
                ['lat' => -6.200000, 'lng' => 106.816666],
                ['lat' => -6.201000, 'lng' => 106.817500],
                ['lat' => -6.202000, 'lng' => 106.818200],
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.strava.com/api/v3/uploads'
                && $request->hasHeader('Authorization', 'Bearer at_123');
        });
    }
}
