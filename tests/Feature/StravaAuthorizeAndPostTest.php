<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StravaAuthorizeAndPostTest extends TestCase
{
    public function test_authorize_and_post_redirects_to_strava_connect_and_sets_pending_key(): void
    {
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('strava_access_token')->nullable();
            $table->text('strava_refresh_token')->nullable();
            $table->timestamp('strava_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        $user = User::create([
            'name' => 'Runner Test',
            'email' => 'runner_auth_post@example.com',
            'password' => Hash::make('password'),
            'role' => 'runner',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('tools.buat-rute-lari.strava-authorize-and-post'), [
            'name' => 'Easy Run',
            'points_json' => json_encode([
                ['lat' => -6.200000, 'lng' => 106.816666],
                ['lat' => -6.201000, 'lng' => 106.817500],
            ]),
            'pace_text' => '6:00',
            'private' => '0',
        ]);

        $response->assertRedirect();
        $this->assertNotNull(session('strava_pending_upload_key'));
        $this->assertStringContainsString('/calendar/strava/connect', $response->headers->get('Location'));
    }
}
