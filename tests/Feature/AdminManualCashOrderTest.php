<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManualCashOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_cash_order_is_created_as_completed(): void
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $product = Product::create([
            'name' => 'Durian Test',
            'category' => 'Durian Segar',
            'description' => 'Produk uji',
            'composition' => 'Durian',
            'weight' => '1 kg',
            'price' => 50000,
            'stock' => 10,
            'sold_count' => 0,
            'image_url' => 'images/products/placeholder.jpg',
            'rating_avg' => 0,
            'rating_count' => 0,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.payment-confirmations.manual.store'), [
            'customer_name' => 'Pembeli Cash',
            'customer_email' => 'cash@example.com',
            'customer_phone' => '081234567890',
            'quantities' => [
                $product->id => 2,
            ],
        ]);

        $order = Order::first();

        $response->assertRedirect(route('admin.payment-confirmations.order.show', $order));
        $this->assertNotNull($order);
        $this->assertSame('SELESAI', $order->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'PAID',
            'payment_method' => 'Cash',
        ]);
    }
}
