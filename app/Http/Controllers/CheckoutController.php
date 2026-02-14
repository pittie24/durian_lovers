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
    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $summary = $this->calculateSummary($cart);

        return view('customer.checkout.index', [
            'cart' => $cart,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request, MidtransService $midtrans)
    {
        $data = $request->validate([
            'shipping_method' => ['required'],
            'phone' => ['required', 'min:10'],
            'address' => ['required'],
            'payment_method' => ['required'],
        ]);

        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect('/keranjang')->withErrors(['cart' => 'Keranjang masih kosong.']);
        }

        $summary = $this->calculateSummary($cart);

        $order = Order::create([
            'user_id' => Auth::id(),
            'status' => 'MENUNGGU_PEMBAYARAN',
            'shipping_method' => $data['shipping_method'],
            'payment_method' => $data['payment_method'],
            'phone' => $data['phone'],
            'shipping_address' => $data['address'],
            'subtotal' => $summary['subtotal'],
            'shipping_cost' => $summary['shipping'],
            'total' => $summary['total'],
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['price'] * $item['quantity'],
            ]);
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'status' => 'PENDING',
            'payment_method' => $data['payment_method'],
        ]);

        $snapToken = $midtrans->createSnapToken($order);
        $payment->update(['snap_token' => $snapToken]);

        $request->session()->forget('cart');

        return redirect('/pembayaran/' . $order->id);
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
