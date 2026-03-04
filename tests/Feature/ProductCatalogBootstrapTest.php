<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductCatalogBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_product_page_bootstraps_default_catalog_when_empty(): void
    {
        $this->assertDatabaseCount('products', 0);

        $response = $this->get('/produk');

        $response->assertOk();
        $this->assertDatabaseCount('products', 6);
        $response->assertSee('Pancake Durian Premium');
    }

    public function test_admin_product_page_bootstraps_default_catalog_when_empty(): void
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->assertDatabaseCount('products', 0);

        $response = $this->actingAs($admin, 'admin')->get('/admin/produk');

        $response->assertOk();
        $this->assertDatabaseCount('products', 6);
        $response->assertSee('Pancake Durian Premium');
    }
}
