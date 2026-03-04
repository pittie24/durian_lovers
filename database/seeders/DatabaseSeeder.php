<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Admin;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Admin::updateOrCreate(
            ['email' => 'admin@durianlovers.test'],
            [
                'name' => 'Admin Durian Lovers',
                'password' => Hash::make('admin123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'pelanggan@durianlovers.test'],
            [
                'name' => 'Pelanggan Demo',
                'phone' => '081234567890',
                'address' => 'Jl. Durian No. 10, Bandung',
                'password' => Hash::make('password'),
            ]
        );

        if (! Product::query()->exists()) {
            $this->call(ProductCatalogSeeder::class);
        }
    }
}
