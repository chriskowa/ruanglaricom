<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMenuTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_update_menu_item()
    {
        $menu = Menu::create(['name' => 'Main', 'location' => 'header']);
        $item = $menu->items()->create([
            'title' => 'Home',
            'url' => '/',
            'target' => '_self',
            'order' => 1
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.menus.items.update', $item), [
                'title' => 'Home Updated',
                'url' => '/home',
                'target' => '_blank'
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'title' => 'Home Updated',
            'url' => '/home',
            'target' => '_blank'
        ]);
    }

    public function test_admin_can_delete_menu_item()
    {
        $menu = Menu::create(['name' => 'Main', 'location' => 'header']);
        $item = $menu->items()->create([
            'title' => 'Home',
            'url' => '/',
            'target' => '_self',
            'order' => 1
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.menus.items.destroy', $item));

        $response->assertRedirect();
        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
    }
}
