<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCustomerDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_products_bought_in_customer_order_history(): void
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $customer = User::factory()->create();

        $firstProduct = Product::create([
            'name' => 'Pancake Durian Premium',
            'category' => 'Pancake Durian',
            'description' => 'Produk uji',
            'composition' => 'Durian',
            'weight' => '500 gram',
            'price' => 65000,
            'stock' => 20,
            'sold_count' => 0,
            'image_url' => 'images/products/placeholder.jpg',
            'rating_avg' => 0,
            'rating_count' => 0,
        ]);

        $secondProduct = Product::create([
            'name' => 'Ice Cream Durian',
            'category' => 'Ice Cream',
            'description' => 'Produk uji',
            'composition' => 'Durian',
            'weight' => '300 ml',
            'price' => 45000,
            'stock' => 15,
            'sold_count' => 0,
            'image_url' => 'images/products/placeholder.jpg',
            'rating_avg' => 0,
            'rating_count' => 0,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'status' => 'SELESAI',
            'shipping_method' => 'pickup',
            'payment_method' => 'Cash',
            'phone' => '081234567890',
            'shipping_address' => 'Ambil di Toko',
            'subtotal' => 175000,
            'shipping_cost' => 0,
            'total' => 175000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $firstProduct->id,
            'quantity' => 2,
            'price' => $firstProduct->price,
            'total' => $firstProduct->price * 2,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $secondProduct->id,
            'quantity' => 1,
            'price' => $secondProduct->price,
            'total' => $secondProduct->price,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.pelanggan.show', 'user-' . $customer->id));

        $response->assertOk();
        $response->assertSee('Produk Dibeli');
        $response->assertSee('Pancake Durian Premium');
        $response->assertSee('Ice Cream Durian');
        $response->assertSee('x2');
        $response->assertSee('x1');
    }

    public function test_manual_cash_customer_detail_only_shows_matching_customer_orders(): void
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $placeholderCustomer = User::factory()->create([
            'email' => 'walkin.customer@durianlovers.local',
            'name' => 'Pelanggan Toko',
        ]);

        Order::create([
            'user_id' => $placeholderCustomer->id,
            'customer_name' => 'Sarif',
            'customer_email' => 'sarif@gmail.com',
            'customer_phone' => '081111111111',
            'status' => 'SELESAI',
            'shipping_method' => 'pickup',
            'payment_method' => 'Cash',
            'phone' => '081111111111',
            'shipping_address' => 'Ambil di Toko',
            'subtotal' => 168000,
            'shipping_cost' => 0,
            'total' => 168000,
        ]);

        Order::create([
            'user_id' => $placeholderCustomer->id,
            'customer_name' => 'Pelanggan Lain',
            'customer_email' => 'lain@example.com',
            'customer_phone' => '082222222222',
            'status' => 'SELESAI',
            'shipping_method' => 'pickup',
            'payment_method' => 'Cash',
            'phone' => '082222222222',
            'shipping_address' => 'Ambil di Toko',
            'subtotal' => 416000,
            'shipping_cost' => 0,
            'total' => 416000,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(
            route('admin.pelanggan.show', 'manual-' . $this->encodeManualKey('email:sarif@gmail.com'))
        );

        $response->assertOk();
        $response->assertSee('sarif@gmail.com');
        $response->assertSee('Rp 168.000');
        $response->assertDontSee('Rp 416.000');
    }

    public function test_dashboard_shows_real_name_for_manual_cash_orders(): void
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $placeholderCustomer = User::factory()->create([
            'email' => 'walkin.customer@durianlovers.local',
            'name' => 'Pelanggan Toko',
        ]);

        Order::create([
            'user_id' => $placeholderCustomer->id,
            'customer_name' => 'Sarif',
            'customer_email' => 'sarif@gmail.com',
            'customer_phone' => '081111111111',
            'status' => 'SELESAI',
            'shipping_method' => 'pickup',
            'payment_method' => 'Cash',
            'phone' => '081111111111',
            'shipping_address' => 'Ambil di Toko',
            'subtotal' => 168000,
            'shipping_cost' => 0,
            'total' => 168000,
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Sarif');
        $response->assertDontSee('Pelanggan Toko');
    }

    private function encodeManualKey(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
