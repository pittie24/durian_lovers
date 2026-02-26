<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    // Ongkir 10k hanya kalau "delivery"
    private const SHIPPING_DELIVERY = 10000;

    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);

        // Default shipping_method untuk tampilan pertama kali
        $shippingMethod = $request->get('shipping_method', 'delivery');

        $summary = $this->calculateSummary($cart, $shippingMethod);

        return view('customer.checkout.index', [
            'cart' => $cart,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request, MidtransService $midtrans)
    {
        // Validasi dasar dulu
        $data = $request->validate([
            'shipping_method' => ['required', 'in:delivery,pickup'],
            'payment_method'  => ['required'],
            'phone'           => ['nullable', 'min:10'],
            'address'         => ['nullable'],
        ]);

        // Kalau delivery, phone & address wajib
        if ($data['shipping_method'] === 'delivery') {
            $request->validate([
                'phone'   => ['required', 'min:10'],
                'address' => ['required'],
            ]);
        } else {
            // pickup: kita amanin supaya tidak null
            $data['phone'] = $data['phone'] ?? '-';
            $data['address'] = $data['address'] ?? 'Ambil di Toko';
        }

        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect('/keranjang')->withErrors(['cart' => 'Keranjang masih kosong.']);
        }

        // Validasi stok untuk setiap item di keranjang
        foreach ($cart as $item) {
            $product = \App\Models\Product::find($item['id']);
            if (!$product) {
                return redirect('/keranjang')->withErrors(['stock' => 'Produk tidak ditemukan.']);
            }
            if ($product->stock < $item['quantity']) {
                return redirect('/keranjang')->withErrors([
                    'stock' => "Stok {$product->name} tidak mencukupi. Stok tersedia: {$product->stock}"
                ]);
            }
        }

        // Hitung summary sesuai pilihan shipping_method
        $summary = $this->calculateSummary($cart, $data['shipping_method']);

        $order = Order::create([
            'user_id'          => Auth::id(),
            'status'           => 'MENUNGGU_PEMBAYARAN',
            'shipping_method'  => $data['shipping_method'],
            'payment_method'   => $data['payment_method'],
            'phone'            => $data['phone'],
            'shipping_address' => $data['address'],
            'subtotal'         => $summary['subtotal'],
            'shipping_cost'    => $summary['shipping'],
            'total'            => $summary['total'],
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item['id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'total'      => $item['price'] * $item['quantity'],
            ]);

            // Kurangi stok produk
            \App\Models\Product::where('id', $item['id'])
                ->decrement('stock', $item['quantity']);
        }

        $payment = Payment::create([
            'order_id'        => $order->id,
            'provider'        => 'midtrans',
            'status'          => 'PENDING',
            'payment_method'  => $data['payment_method'],
        ]);

        $snapToken = $midtrans->createSnapToken($order);
        $payment->update(['snap_token' => $snapToken]);

        $request->session()->forget('cart');

        return redirect('/pembayaran/' . $order->id);
    }

    /**
     * @param array $cart
     * @param string $shippingMethod delivery|pickup
     */
    private function calculateSummary(array $cart, string $shippingMethod = 'delivery'): array
    {
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shipping = ($shippingMethod === 'delivery') ? self::SHIPPING_DELIVERY : 0;

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total'    => $subtotal + $shipping,
        ];
    }
}