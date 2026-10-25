<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Monster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->member = User::create([
            'name' => 'Member Test',
            'email' => 'member_test@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_admin_panel(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_member_cannot_access_admin_panel(): void
    {
        $response = $this->actingAs($this->member)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_dashboard_and_crud_indexes(): void
    {
        $this->actingAs($this->admin)->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/categories')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/monsters')->assertStatus(200);
    }

    public function test_admin_can_create_and_delete_category(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Kaiju Baru',
            'description' => 'Deskripsi kaiju baru',
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['name' => 'Kaiju Baru']);

        $category = Category::where('name', 'Kaiju Baru')->first();
        $delResponse = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");
        $delResponse->assertRedirect('/admin/categories');
        $this->assertDatabaseMissing('categories', ['name' => 'Kaiju Baru']);
    }

    public function test_admin_can_create_and_sync_multi_category_monster(): void
    {
        $cat1 = Category::create(['name' => 'Colossal']);
        $cat2 = Category::create(['name' => 'Aquatic']);

        $response = $this->actingAs($this->admin)->post('/admin/monsters', [
            'monster_code' => 'TEST-001',
            'name' => 'Leviathan Prime',
            'origin_type' => 'Mythical',
            'condition' => 'Contained',
            'threat_level' => 'High',
            'status' => 'available',
            'description' => 'Monster laut kolosal',
            'categories' => [$cat1->id, $cat2->id],
        ]);

        $response->assertRedirect('/admin/monsters');
        $this->assertDatabaseHas('monsters', ['monster_code' => 'TEST-001']);

        $monster = Monster::where('monster_code', 'TEST-001')->first();
        $this->assertCount(2, $monster->categories);
    }

    public function test_cannot_delete_borrowed_monster(): void
    {
        $cat = Category::create(['name' => 'Kaiju']);
        $monster = Monster::create([
            'monster_code' => 'BORROWED-001',
            'name' => 'Active Monster',
            'origin_type' => 'Kaiju',
            'condition' => 'Contained',
            'threat_level' => 'Extreme',
            'status' => 'borrowed',
        ]);
        $monster->categories()->sync([$cat->id]);

        $response = $this->actingAs($this->admin)->delete("/admin/monsters/{$monster->id}");
        $response->assertRedirect('/admin/monsters');
        $this->assertDatabaseHas('monsters', ['monster_code' => 'BORROWED-001']);
    }
}
