<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_page_shows_active_member_cards_only(): void
    {
        $this->seed(\Database\Seeders\PagesSeeder::class);
        TeamMember::create(['name' => 'Alex Example', 'role' => 'Trip planner', 'bio' => 'Plans routes.', 'sort' => 1, 'is_active' => true]);
        TeamMember::create(['name' => 'Hidden Person', 'sort' => 2, 'is_active' => false]);

        $this->get('/meet-our-team')->assertOk()
            ->assertSee('The people behind your trip')->assertSee('Alex Example')->assertSee('Trip planner')
            ->assertDontSee('Hidden Person');
    }

    public function test_admin_can_manage_team_members(): void
    {
        $admin = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery', 'role' => 'admin']);
        $this->actingAs($admin, 'admin')->get('/admin/team')->assertOk();
        $this->get('/admin/team/create')->assertOk();
        $this->post('/admin/team', ['name' => 'Sam Sample', 'role' => 'Guide', 'bio' => 'Hello', 'sort' => 1, 'is_active' => '1'])
            ->assertRedirect('/admin/team');
        $this->assertDatabaseHas('team_members', ['name' => 'Sam Sample', 'is_active' => true]);
    }
}
