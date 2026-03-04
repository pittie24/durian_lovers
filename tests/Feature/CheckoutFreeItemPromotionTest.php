<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutFreeItemPromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_gets_free_item_when_checkout_reaches_threshold(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create();
        $paidProduct = $this->createProduct('Durian Montong Premium', 150000, 10);
        $freeProduct = $this->createProduct('Pancake Durian Mini', 35000, 5);

        $response = $this->actingAs($customer)->withSession([
            'cart' => [
                $paidProduct->id => [
                    'id' => $paidProduct->id,
                    'name' => $paidProduct->name,
                    'price' => $paidProduct->price,
                    'image_url' => $paidProduct->image_url,
                    'quantity' => 2,
                    'weight' => $paidProduct->weight,
                ],
            ],
        ])->post('/pembayaran', [
            'shipping_method' => 'pickup',
            'payment_method' => 'BRI',
            'phone' => '081234567890',
            'address' => 'Ambil di Toko',
            'account_name' => 'Tester',
            'transfer_amount' => 300000,
            'proof_image' => $this->fakeProofImage(),
        ]);

        $order = Order::with('items')->first();

        $response->assertRedirect('/status-pesanan/' . $order->id);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $freeProduct->id,
            'quantity' => 1,
            'price' => 0,
            'total' => 0,
        ]);
        $this->assertSame(4, (int) $freeProduct->fresh()->stock);
    }

    public function test_customer_does_not_get_free_item_below_threshold(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create();
        $paidProduct = $this->createProduct('Durian Montong Premium', 149000, 10);
        $freeProduct = $this->createProduct('Pancake Durian Mini', 35000, 5);

        $response = $this->actingAs($customer)->withSession([
            'cart' => [
                $paidProduct->id => [
                    'id' => $paidProduct->id,
                    'name' => $paidProduct->name,
                    'price' => $paidProduct->price,
                    'image_url' => $paidProduct->image_url,
                    'quantity' => 2,
                    'weight' => $paidProduct->weight,
                ],
            ],
        ])->post('/pembayaran', [
            'shipping_method' => 'pickup',
            'payment_method' => 'BRI',
            'phone' => '081234567890',
            'address' => 'Ambil di Toko',
            'account_name' => 'Tester',
            'transfer_amount' => 298000,
            'proof_image' => $this->fakeProofImage(),
        ]);

        $order = Order::with('items')->first();

        $response->assertRedirect('/status-pesanan/' . $order->id);
        $this->assertDatabaseMissing('order_items', [
            'order_id' => $order->id,
            'product_id' => $freeProduct->id,
            'price' => 0,
        ]);
        $this->assertSame(5, (int) $freeProduct->fresh()->stock);
    }

    private function createProduct(string $name, int $price, int $stock): Product
    {
        return Product::create([
            'name' => $name,
            'category' => 'Durian Segar',
            'description' => 'Produk uji promo',
            'composition' => 'Durian',
            'weight' => '1 kg',
            'price' => $price,
            'stock' => $stock,
            'sold_count' => 0,
            'image_url' => 'images/products/placeholder.jpg',
            'rating_avg' => 0,
            'rating_count' => 0,
        ]);
    }

    private function fakeProofImage(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'proof');

        file_put_contents(
            $path,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wn7xV8AAAAASUVORK5CYII=')
        );

        return new UploadedFile(
            $path,
            'proof.png',
            'image/png',
            null,
            true
        );
    }
}
