<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
        $payment = $order->payment;

        return view('customer.payment.show', [
            'order' => $order,
            'payment' => $payment,
        ]);
    }

    public function proceed(Order $order, MidtransService $midtrans)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
        $payment = $order->payment;
        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'midtrans',
                'status' => 'PENDING',
            ]);
        }

        if (!$payment->snap_token) {
            try {
                $snapToken = $midtrans->createSnapToken($order);
                $payment->update([
                    'snap_token' => $snapToken,
                ]);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal mendapatkan token pembayaran: ' . $e->getMessage());
            }
        }

        // Redirect ke halaman payment untuk menampilkan popup Midtrans
        return redirect()->route('pembayaran.show', $order)->with('success', 'Silakan lakukan pembayaran.');
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Signature') ?? '';

        // Verifikasi signature key dari Midtrans
        $serverKey = config('services.midtrans.server_key');
        $computedSignature = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . $serverKey);

        if ($signature !== $computedSignature) {
            Log::warning('Midtrans webhook invalid signature', $payload);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (!$orderId) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // Cari order berdasarkan ID (karena order_id kita menggunakan ID lokal)
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Tentukan status berdasarkan response Midtrans
        $orderStatus = $order->status;
        $paymentStatus = strtoupper($transactionStatus ?? 'PENDING');

        // Mapping status pembayaran ke status order
        if ($fraudStatus === 'accept') {
            if ($transactionStatus === 'capture') {
                $orderStatus = 'PESANAN_DITERIMA';
            } elseif ($transactionStatus === 'settlement') {
                $orderStatus = 'SEDANG_DIPROSES';
            }
        } else {
            $statusMap = [
                'capture' => 'PESANAN_DITERIMA',
                'settlement' => 'SEDANG_DIPROSES',
                'pending' => 'MENUNGGU_PEMBAYARAN',
                'deny' => 'DIBATALKAN',
                'cancel' => 'DIBATALKAN',
                'expire' => 'DIBATALKAN',
            ];
            $orderStatus = $statusMap[$transactionStatus] ?? $order->status;
        }

        // Jika pembayaran gagal/dibatalkan/expired, kembalikan stok
        if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            foreach ($order->items as $item) {
                \App\Models\Product::where('id', $item->product_id)
                    ->increment('stock', $item->quantity);
            }
        }

        $order->update([
            'status' => $orderStatus,
        ]);

        if ($order->payment) {
            $order->payment->update([
                'status' => $paymentStatus,
                'payment_method' => $payload['payment_type'] ?? null,
            ]);
        }

        Log::info('Midtrans webhook received', $payload);

        return response()->json(['message' => 'ok']);
    }
}
