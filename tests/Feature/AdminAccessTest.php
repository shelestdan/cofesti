<?php

namespace Tests\Feature;

use App\Models\User;
use App\UserRole;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_manager_can_open_operational_admin_dashboard(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);

        $response = $this->actingAs($manager)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Рабочее пространство');
    }
}
