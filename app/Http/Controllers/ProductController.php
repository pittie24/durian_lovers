<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category', 'Semua Produk');

        $topProducts = Product::orderByDesc('sold_count')->take(4)->get();

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
        $ratings = $product->ratings()->latest()->get();

        return view('customer.products.show', [
            'product' => $product,
            'ratings' => $ratings,
        ]);
    }
}
