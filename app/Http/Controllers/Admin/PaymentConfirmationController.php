<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentConfirmation;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PaymentConfirmationController extends Controller
{
    /**
     * Display list of payment confirmations
     */
    public function index()
    {
        $orders = Order::with(['user', 'payment', 'paymentConfirmation'])
            ->latest()
            ->paginate(20);

        return view('admin.payment-confirmations.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Show confirmation detail
     */
    public function show(PaymentConfirmation $confirmation)
    {
        $confirmation->load(['order.items.product', 'order.user', 'order.payment', 'user', 'verifiedBy']);

        return view('admin.payment-confirmations.show', [
            'confirmation' => $confirmation,
            'order' => $confirmation->order,
        ]);
    }

    public function showOrder(Order $order)
    {
        $order->load(['items.product', 'user', 'payment', 'paymentConfirmation.user', 'paymentConfirmation.verifiedBy']);

        return view('admin.payment-confirmations.show', [
            'confirmation' => $order->paymentConfirmation,
            'order' => $order,
        ]);
    }

    public function createManual()
    {
        $products = Product::orderBy('name')->get();

        return view('admin.payment-confirmations.create-manual', [
            'products' => $products,
        ]);
    }

    public function storeManual(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'quantities' => ['required', 'array'],
        ]);
        $data['shipping_method'] = 'pickup';
        $data['shipping_address'] = 'Ambil di Toko';

        $selectedProducts = [];
        $subtotal = 0;

        foreach ($request->input('quantities', []) as $productId => $quantity) {
            $qty = (int) $quantity;

            if ($qty < 1) {
                continue;
            }

            $product = Product::find($productId);

            if (!$product) {
                continue;
            }

            if ($product->stock < $qty) {
                return back()
                    ->withErrors([
                        'quantities' => "Stok {$product->name} tidak mencukupi. Stok tersedia: {$product->stock}",
                    ])
                    ->withInput();
            }

            $selectedProducts[] = [
                'product' => $product,
                'quantity' => $qty,
            ];

            $subtotal += ($product->price * $qty);
        }

        if (empty($selectedProducts)) {
            return back()
                ->withErrors(['quantities' => 'Pilih minimal satu produk dengan jumlah lebih dari 0.'])
                ->withInput();
        }

        $shippingCost = 0;
        $placeholderUser = $this->resolveManualCustomerUser();

        $order = DB::transaction(function () use ($data, $selectedProducts, $subtotal, $shippingCost, $placeholderUser) {
            $order = Order::create([
                'user_id' => $placeholderUser->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?: null,
                'customer_phone' => $data['customer_phone'] ?: null,
                'status' => 'SELESAI',
                'shipping_method' => $data['shipping_method'],
                'payment_method' => 'Cash',
                'phone' => $data['customer_phone'] ?: '-',
                'shipping_address' => $data['shipping_method'] === 'delivery'
                    ? $data['shipping_address']
                    : 'Ambil di Toko',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $subtotal + $shippingCost,
            ]);

            foreach ($selectedProducts as $selectedProduct) {
                $product = $selectedProduct['product'];
                $quantity = $selectedProduct['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $product->price * $quantity,
                ]);

                $product->decrement('stock', $quantity);
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'cash',
                'status' => 'PAID',
                'payment_method' => 'Cash',
                'paid_at' => now(),
            ]);

            InvoiceGeneratorService::generate($order->fresh(['items.product', 'user']), $payment);

            return $order;
        });

        return redirect()
            ->route('admin.payment-confirmations.order.show', $order)
            ->with('success', 'Pesanan cash berhasil dibuat.');
    }

    /**
     * Update order fulfillment status from admin order page.
     */
    public function updateOrderStatus(Request $request, PaymentConfirmation $confirmation)
    {
        $data = $request->validate([
            'status' => ['required', 'in:PESANAN_DITERIMA,SEDANG_DIPROSES,SIAP_DIAMBIL_DIKIRIM,SELESAI'],
        ]);

        $order = $confirmation->order;

        if ($order->status === 'SELESAI') {
            return redirect()->route('admin.payment-confirmations.show', $confirmation)
                ->withErrors(['status' => 'Pesanan sudah selesai dan tidak bisa diubah lagi.']);
        }

        if ($data['status'] !== 'PESANAN_DITERIMA' && !$confirmation->isApproved()) {
            return redirect()->route('admin.payment-confirmations.show', $confirmation)
                ->withErrors(['status' => 'Setujui pembayaran terlebih dahulu sebelum mengubah status pesanan.']);
        }

        if (!$this->isSequentialStatusUpdateAllowed($order->status, $data['status'])) {
            return redirect()->route('admin.payment-confirmations.show', $confirmation)
                ->withErrors(['status' => 'Status pesanan harus diubah berurutan: Diterima -> Dikemas -> Siap Dikirim/Diambil -> Selesai.']);
        }

        $labels = [
            'PESANAN_DITERIMA' => 'Pesanan diterima.',
            'SEDANG_DIPROSES' => 'Pesanan dikemas.',
            'SIAP_DIAMBIL_DIKIRIM' => 'Pesanan siap dikirim/diambil.',
            'SELESAI' => 'Pesanan selesai.',
        ];

        $order->update([
            'status' => $data['status'],
        ]);

        return redirect()->route('admin.payment-confirmations.show', $confirmation)
            ->with('success', $labels[$data['status']] ?? 'Status pesanan diperbarui.');
    }

    public function updateOrderStatusForOrder(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:PESANAN_DITERIMA,SEDANG_DIPROSES,SIAP_DIAMBIL_DIKIRIM,SELESAI'],
        ]);

        if ($order->status === 'SELESAI') {
            return redirect()->route('admin.payment-confirmations.order.show', $order)
                ->withErrors(['status' => 'Pesanan sudah selesai dan tidak bisa diubah lagi.']);
        }

        $payment = $order->payment;
        $isPaid = $payment && in_array(strtoupper((string) $payment->status), ['PAID', 'SETTLED', 'SETTLEMENT', 'CAPTURE'], true);

        if ($data['status'] !== 'PESANAN_DITERIMA' && !$isPaid) {
            return redirect()->route('admin.payment-confirmations.order.show', $order)
                ->withErrors(['status' => 'Pesanan belum lunas dan belum bisa diproses.']);
        }

        if (!$this->isSequentialStatusUpdateAllowed($order->status, $data['status'])) {
            return redirect()->route('admin.payment-confirmations.order.show', $order)
                ->withErrors(['status' => 'Status pesanan harus diubah berurutan: Diterima -> Dikemas -> Siap Dikirim/Diambil -> Selesai.']);
        }

        $labels = [
            'PESANAN_DITERIMA' => 'Pesanan diterima.',
            'SEDANG_DIPROSES' => 'Pesanan dikemas.',
            'SIAP_DIAMBIL_DIKIRIM' => 'Pesanan siap dikirim/diambil.',
            'SELESAI' => 'Pesanan selesai.',
        ];

        $order->update([
            'status' => $data['status'],
        ]);

        return redirect()->route('admin.payment-confirmations.order.show', $order)
            ->with('success', $labels[$data['status']] ?? 'Status pesanan diperbarui.');
    }

    /**
     * Stream proof image directly from storage.
     */
    public function proofImage(PaymentConfirmation $confirmation)
    {
        if (!$confirmation->proof_image || !Storage::disk('public')->exists($confirmation->proof_image)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($confirmation->proof_image);
        $mimeType = Storage::disk('public')->mimeType($confirmation->proof_image) ?: 'application/octet-stream';

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Approve payment confirmation
     */
    public function approve(Request $request, PaymentConfirmation $confirmation)
    {
        $admin = Auth::guard('admin')->user();

        $confirmation->approve($admin->id);

        return redirect()->route('admin.payment-confirmations.index')
            ->with('success', 'Konfirmasi pembayaran berhasil disetujui.');
    }

    /**
     * Reject payment confirmation
     */
    public function reject(Request $request, PaymentConfirmation $confirmation)
    {
        $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $admin = Auth::guard('admin')->user();

        $confirmation->reject($admin->id, $request->notes);

        return redirect()->route('admin.payment-confirmations.index')
            ->with('success', 'Konfirmasi pembayaran ditolak.');
    }

    /**
     * Delete confirmation and proof image
     */
    public function destroy(PaymentConfirmation $confirmation)
    {
        // Delete proof image
        if ($confirmation->proof_image) {
            Storage::disk('public')->delete($confirmation->proof_image);
        }

        $confirmation->delete();

        return redirect()->route('admin.payment-confirmations.index')
            ->with('success', 'Konfirmasi pembayaran dihapus.');
    }

    private function resolveManualCustomerUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'walkin.customer@durianlovers.local'],
            [
                'name' => 'Pelanggan Toko',
                'phone' => '-',
                'address' => 'Pelanggan pesanan manual admin',
                'password' => Hash::make(bin2hex(random_bytes(16))),
            ]
        );
    }

    private function isSequentialStatusUpdateAllowed(string $currentStatus, string $targetStatus): bool
    {
        $sequence = [
            'PESANAN_DITERIMA',
            'SEDANG_DIPROSES',
            'SIAP_DIAMBIL_DIKIRIM',
            'SELESAI',
        ];

        $normalizedCurrent = $currentStatus === 'MENUNGGU_PEMBAYARAN'
            ? 'PESANAN_DITERIMA'
            : $currentStatus;

        $currentIndex = array_search($normalizedCurrent, $sequence, true);
        $targetIndex = array_search($targetStatus, $sequence, true);

        if ($currentIndex === false || $targetIndex === false) {
            return false;
        }

        if ($currentIndex === $targetIndex) {
            return true;
        }

        return $targetIndex === ($currentIndex + 1);
    }
}
