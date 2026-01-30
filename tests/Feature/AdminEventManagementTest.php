<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAudit;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminEventManagementTest extends TestCase
{
    private function resetSchema(): void
    {
        Schema::dropIfExists('event_audits');
        Schema::dropIfExists('events');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('location_name');
            $table->string('hero_image_url')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('organizer_name')->nullable();
            $table->string('status')->default('published');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unsignedInteger('lock_version')->default(0);
        });

        Schema::create('event_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('admin_id');
            $table->string('action');
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function test_only_admin_can_access_event_management(): void
    {
        $this->resetSchema();

        $admin = User::factory()->create(['role' => 'admin']);
        $eo = User::factory()->create(['role' => 'eo']);

        $event = Event::factory()->for($eo, 'user')->create([
            'is_featured' => false,
            'status' => 'published',
            'is_active' => true,
        ]);

        $lockVersion = (int) $event->lock_version;

        $this->actingAs($admin)
            ->postJson(route('admin.events.toggle-featured', $event), ['lock_version' => $lockVersion])
            ->assertOk();

        $this->actingAs($eo)
            ->postJson(route('admin.events.toggle-featured', $event), ['lock_version' => $lockVersion])
            ->assertForbidden();
    }

    public function test_admin_can_toggle_featured_and_audit_is_written(): void
    {
        $this->resetSchema();

        $admin = User::factory()->create(['role' => 'admin']);
        $eo = User::factory()->create(['role' => 'eo']);

        $event = Event::factory()->for($eo, 'user')->create([
            'is_featured' => false,
            'status' => 'published',
            'is_active' => true,
        ]);

        $lockVersion = (int) $event->lock_version;

        $response = $this->actingAs($admin)->postJson(
            route('admin.events.toggle-featured', $event),
            ['lock_version' => $lockVersion]
        );

        $response->assertOk()->assertJson(['ok' => true]);

        $event->refresh();
        $this->assertTrue((bool) $event->is_featured);

        $audit = EventAudit::query()->where('action', 'toggle_featured')->first();
        $this->assertNotNull($audit);
        $this->assertSame($admin->id, $audit->admin_id);
        $this->assertSame($event->id, $audit->event_id);
        $this->assertSame(['is_featured' => false], $audit->before);
        $this->assertSame(['is_featured' => true], $audit->after);
    }

    public function test_admin_status_change_uses_optimistic_concurrency(): void
    {
        $this->resetSchema();

        $admin = User::factory()->create(['role' => 'admin']);
        $eo = User::factory()->create(['role' => 'eo']);

        $event = Event::factory()->for($eo, 'user')->create([
            'status' => 'published',
            'is_featured' => false,
        ]);

        $oldLockVersion = (int) $event->lock_version;

        $this->actingAs($admin)->postJson(
            route('admin.events.set-status', $event),
            ['status' => 'draft', 'lock_version' => $oldLockVersion]
        )->assertOk()->assertJson(['ok' => true]);

        $this->actingAs($admin)->postJson(
            route('admin.events.toggle-featured', $event),
            ['lock_version' => $oldLockVersion]
        )->assertStatus(409);
    }
}
