<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_admin_account_is_bootstrapped_and_can_log_in(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@durianlovers.com',
            'password' => 'Admin@2026',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs(\App\Models\Admin::first(), 'admin');
    }

    public function test_customer_guard_cannot_access_admin_routes(): void
    {
        $customer = \App\Models\User::factory()->create();

        $response = $this->actingAs($customer)->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }
}
