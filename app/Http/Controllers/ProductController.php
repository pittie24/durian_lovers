<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category', 'Semua Produk');

        // Produk terlaris (tetap 4)
        $topProducts = Product::orderByDesc('sold_count')
            ->take(4)
            ->get();

        // Semua produk / filter kategori
        $query = Product::query();
        if ($category !== 'Semua Produk') {
            $query->where('category', $category);
        }

        $products = $query->orderByDesc('created_at')->get();

        return view('customer.products.index', [
            'category' => $category,
            'topProducts' => $topProducts,
            'products' => $products,
        ]);
    }

    public function show(Product $product)
    {
        // Ratings & ulasan
        $ratings = $product->ratings()->latest()->get();

        // Produk terkait (kategori sama, selain produk ini)
        $relatedProducts = Product::where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('customer.products.show', [
            'product' => $product,
            'ratings' => $ratings,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
