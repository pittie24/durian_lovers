<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
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
                'image_url' => 'images/products/pancake-durian.jpeg',
                'rating_avg' => 4.70,
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
                'image_url' => 'images/products/durian-montong.jpeg',
                'rating_avg' => 4.60,
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
                'image_url' => 'images/products/ice-cream-durian.jpeg',
                'rating_avg' => 4.50,
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
                'image_url' => 'images/products/pancake-durian-mini.jpeg',
                'rating_avg' => 4.40,
                'rating_count' => 60,
            ],
            [
                'name' => 'Pancake Durian Roll',
                'category' => 'Pancake Durian',
                'description' => 'Pancake durian roll dengan tekstur lembut dan isi melimpah.',
                'composition' => 'Durian, susu, tepung, krim',
                'weight' => '400 gram',
                'price' => 55000,
                'stock' => 18,
                'sold_count' => 110,
                'image_url' => 'images/products/pancake-durian-roll.jpeg',
                'rating_avg' => 4.55,
                'rating_count' => 44,
            ],
            [
                'name' => 'Daging Durian Beku',
                'category' => 'Durian Segar',
                'description' => 'Daging durian siap santap, praktis disimpan di freezer.',
                'composition' => '100% daging durian',
                'weight' => '500 gram',
                'price' => 78000,
                'stock' => 22,
                'sold_count' => 90,
                'image_url' => 'images/products/daging-durian.jpeg',
                'rating_avg' => 4.35,
                'rating_count' => 31,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
