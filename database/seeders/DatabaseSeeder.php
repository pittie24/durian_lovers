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
        Admin::create([
            'name' => 'Admin Durian Lovers',
            'email' => 'admin@durianlovers.test',
            'password' => Hash::make('admin123'),
        ]);

        User::create([
            'name' => 'Pelanggan Demo',
            'email' => 'pelanggan@durianlovers.test',
            'phone' => '081234567890',
            'address' => 'Jl. Durian No. 10, Bandung',
            'password' => Hash::make('password'),
        ]);

        $products = [
            [
                'name' => 'Pancake Durian Premium',
                'category' => 'Pancake Durian',
                'description' => 'Pancake durian lembut dengan daging montong asli.',
                'composition' => 'Durian montong, susu, tepung, gula',
                'weight' => '500 gram',
                'price' => 65000,
                'stock' => 25,
                'sold_count' => 200,
                'image_url' => 'https://images.unsplash.com/photo-1481391032119-d89fee407e44?q=80&w=1200&auto=format&fit=crop',
                'rating_avg' => 4.7,
                'rating_count' => 120,
            ],
            [
                'name' => 'Durian Montong Segar',
                'category' => 'Durian Segar',
                'description' => 'Durian montong pilihan, daging tebal dan legit.',
                'composition' => '100% durian segar',
                'weight' => '1.5 kg',
                'price' => 120000,
                'stock' => 14,
                'sold_count' => 180,
                'image_url' => 'https://images.unsplash.com/photo-1502741338009-cac2772e18bc?q=80&w=1200&auto=format&fit=crop',
                'rating_avg' => 4.6,
                'rating_count' => 95,
            ],
            [
                'name' => 'Ice Cream Durian',
                'category' => 'Ice Cream',
                'description' => 'Es krim durian creamy dan manis.',
                'composition' => 'Durian, susu, krim, gula',
                'weight' => '300 ml',
                'price' => 45000,
                'stock' => 40,
                'sold_count' => 160,
                'image_url' => 'https://images.unsplash.com/photo-1497034825429-c343d7c6a68f?q=80&w=1200&auto=format&fit=crop',
                'rating_avg' => 4.5,
                'rating_count' => 80,
            ],
            [
                'name' => 'Pancake Durian Mini',
                'category' => 'Pancake Durian',
                'description' => 'Pancake durian ukuran mini untuk camilan.',
                'composition' => 'Durian, susu, tepung',
                'weight' => '250 gram',
                'price' => 35000,
                'stock' => 30,
                'sold_count' => 140,
                'image_url' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?q=80&w=1200&auto=format&fit=crop',
                'rating_avg' => 4.4,
                'rating_count' => 60,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
