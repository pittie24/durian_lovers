<?php

namespace App\Services;

use App\Models\Product;
use Database\Seeders\ProductCatalogSeeder;

class ProductCatalogBootstrapService
{
    public function ensureSeeded(): void
    {
        if (Product::query()->exists()) {
            return;
        }

        app(ProductCatalogSeeder::class)->run();
    }
}
