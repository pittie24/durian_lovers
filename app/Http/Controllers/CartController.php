<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $summary = $this->calculateSummary($cart);

        return view('customer.cart.index', [
            'cart' => $cart,
            'summary' => $summary,
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $quantity = $data['quantity'] ?? 1;

        $cart = $request->session()->get('cart', []);
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image_url' => $product->image_url,
                'quantity' => $quantity,
                'weight' => $product->weight,
            ];
        }

        $request->session()->put('cart', $cart);

        return redirect('/keranjang')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $request->session()->get('cart', []);
        if (isset($cart[$data['product_id']])) {
            $cart[$data['product_id']]['quantity'] = $data['quantity'];
            $request->session()->put('cart', $cart);
        }

        return back();
    }

    public function remove(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required'],
        ]);

        $cart = $request->session()->get('cart', []);
        unset($cart[$data['product_id']]);
        $request->session()->put('cart', $cart);

        return back();
    }

    private function calculateSummary(array $cart): array
    {
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        return [
            'subtotal' => $subtotal,
            'shipping' => 0,
            'total' => $subtotal,
        ];
    }
}
