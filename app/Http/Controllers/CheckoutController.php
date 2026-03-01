<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    // Ongkir 10k hanya kalau "delivery"
    private const SHIPPING_DELIVERY = 10000;

    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return redirect('/produk')->withErrors([
                'cart' => 'Silakan belanja terlebih dahulu sebelum membuka halaman pembayaran.',
            ]);
        }

        // Default shipping_method untuk tampilan pertama kali
        $shippingMethod = $request->get('shipping_method', 'delivery');

        $summary = $this->calculateSummary($cart, $shippingMethod);

        return view('customer.checkout.index', [
            'cart' => $cart,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request)
    {
        // Validasi dasar dulu
        $data = $request->validate([
            'shipping_method' => ['required', 'in:delivery,pickup'],
            'payment_method'  => ['required'],
            'phone'           => ['nullable', 'min:10'],
            'address'         => ['nullable'],
            // Payment confirmation fields
            'account_name'    => ['required', 'string', 'max:255'],
            'transfer_amount' => ['required', 'numeric', 'min:0'],
            'proof_image'     => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
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

        // Create payment record with PENDING status
        Payment::create([
            'order_id'        => $order->id,
            'provider'        => 'manual_transfer',
            'status'          => 'PENDING',
            'payment_method'  => $data['payment_method'],
        ]);

        // Upload proof image and create payment confirmation
        $paymentConfirmationSaved = false;
        if ($request->hasFile('proof_image')) {
            $image = $request->file('proof_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('payment-confirmations', $imageName, 'public');

            if (Schema::hasTable('payment_confirmations')) {
                PaymentConfirmation::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'proof_image' => $imagePath,
                    'bank_name' => $data['payment_method'],
                    'account_name' => $data['account_name'],
                    'transfer_amount' => $data['transfer_amount'],
                    'status' => 'PENDING',
                ]);
                $paymentConfirmationSaved = true;
            } else {
                Storage::disk('public')->delete($imagePath);

                Log::warning('Payment confirmation table is missing during checkout.', [
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                ]);
            }
        }

        $request->session()->forget('cart');

        // Redirect to tracking page
        $message = $paymentConfirmationSaved
            ? 'Pesanan berhasil dibuat! Bukti pembayaran sudah diupload dan menunggu verifikasi admin.'
            : 'Pesanan berhasil dibuat. Jika bukti pembayaran belum tersimpan, silakan hubungi admin.';

        return redirect('/status-pesanan/' . $order->id)->with('success', $message);
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
