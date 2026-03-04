<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_log_in_and_is_redirected_to_products(): void
    {
        User::factory()->create([
            'email' => 'pelanggan@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'pelanggan@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/produk');
        $this->assertAuthenticated();
    }

    public function test_authenticated_customer_is_redirected_away_from_login_page(): void
    {
        $customer = User::factory()->create();

        $response = $this->actingAs($customer)->get('/login');

        $response->assertRedirect('/produk');
    }
}
