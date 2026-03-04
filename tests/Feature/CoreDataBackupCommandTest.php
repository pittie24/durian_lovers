<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CoreDataBackupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_command_creates_json_file(): void
    {
        $this->seedCoreData();

        $backupPath = base_path('.tmp/test-core-backup.json');

        if (file_exists($backupPath)) {
            unlink($backupPath);
        }

        Artisan::call('backup:data-utama', [
            '--path' => '.tmp/test-core-backup.json',
        ]);

        $this->assertFileExists($backupPath);

        $payload = json_decode(file_get_contents($backupPath), true);

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('tables', $payload);
        $this->assertCount(1, $payload['tables']['admins']);
        $this->assertCount(1, $payload['tables']['users']);
        $this->assertCount(1, $payload['tables']['orders']);
        $this->assertCount(1, $payload['tables']['order_items']);
    }

    public function test_restore_command_restores_deleted_data(): void
    {
        $this->seedCoreData();

        $backupPath = base_path('.tmp/test-core-restore.json');

        if (file_exists($backupPath)) {
            unlink($backupPath);
        }

        Artisan::call('backup:data-utama', [
            '--path' => '.tmp/test-core-restore.json',
        ]);

        OrderItem::query()->delete();
        Order::query()->delete();
        Product::query()->delete();
        User::query()->delete();
        Admin::query()->delete();

        $this->assertDatabaseCount('admins', 0);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);

        Artisan::call('restore:data-utama', [
            'file' => '.tmp/test-core-restore.json',
        ]);

        $this->assertDatabaseCount('admins', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'backup-customer@example.com',
        ]);
    }

    private function seedCoreData(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Backup',
            'email' => 'backup-admin@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->assertNotNull($admin);

        $user = User::factory()->create([
            'email' => 'backup-customer@example.com',
        ]);

        $product = Product::create([
            'name' => 'Produk Backup',
            'category' => 'Durian Segar',
            'description' => 'Produk untuk uji backup',
            'composition' => 'Durian',
            'weight' => '1 kg',
            'price' => 90000,
            'stock' => 10,
            'sold_count' => 1,
            'image_url' => 'images/products/placeholder.jpg',
            'rating_avg' => 0,
            'rating_count' => 0,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'SELESAI',
            'shipping_method' => 'pickup',
            'payment_method' => 'Cash',
            'phone' => '081234567890',
            'shipping_address' => 'Ambil di Toko',
            'subtotal' => 90000,
            'shipping_cost' => 0,
            'total' => 90000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 90000,
            'total' => 90000,
        ]);
    }
}
