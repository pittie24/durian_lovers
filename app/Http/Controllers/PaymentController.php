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
            $payment->update([
                'snap_token' => $midtrans->createSnapToken($order),
            ]);
        }

        // Placeholder redirect target: in production, redirect to Midtrans Snap.
        return redirect('/tracking/' . $order->id)->with('success', 'Pembayaran diproses. Menunggu konfirmasi.');
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;

        if (!$orderId) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $statusMap = [
            'capture' => 'PESANAN_DITERIMA',
            'settlement' => 'SEDANG_DIPROSES',
            'pending' => 'MENUNGGU_PEMBAYARAN',
            'deny' => 'DIBATALKAN',
            'cancel' => 'DIBATALKAN',
            'expire' => 'DIBATALKAN',
        ];

        $order->update([
            'status' => $statusMap[$transactionStatus] ?? $order->status,
        ]);

        if ($order->payment) {
            $order->payment->update([
                'status' => strtoupper($transactionStatus ?? 'PENDING'),
            ]);
        }

        Log::info('Midtrans webhook received', $payload);

        return response()->json(['message' => 'ok']);
    }
}
