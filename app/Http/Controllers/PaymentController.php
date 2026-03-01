<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Invoice;
use App\Services\InvoiceGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    /**
     * Check payment status from Midtrans API (polling)
     */
    private function checkPaymentStatusFromMidtrans($orderId)
    {
        try {
            $serverKey = config('services.midtrans.server_key');
            $isProduction = config('services.midtrans.is_production');
            
            $baseUrl = $isProduction 
                ? 'https://api.midtrans.com' 
                : 'https://api.sandbox.midtrans.com';
            
            $response = Http::withBasicAuth($serverKey, '')
                ->get("{$baseUrl}/v2/{$orderId}/status");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to check payment status from Midtrans', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Update order and payment status from Midtrans API response
     */
    private function updateOrderStatusFromMidtrans(Order $order, $transactionData)
    {
        if (!$transactionData) {
            return false;
        }

        $transactionStatus = $transactionData['transaction_status'] ?? null;
        $fraudStatus = $transactionData['fraud_status'] ?? null;
        
        // Map Midtrans status to our payment status
        $paymentStatus = strtoupper($transactionStatus ?? 'PENDING');
        
        // Map to order status - OTOMATIS SELESAI SETELAH BAYAR (Opsi B)
        $orderStatus = $order->status;
        if (in_array($transactionStatus, ['settlement', 'capture'])) {
            // Pembayaran sukses → LANGSUNG SELESAI
            $orderStatus = 'SELESAI';
        } elseif ($transactionStatus === 'pending') {
            $orderStatus = 'MENUNGGU_PEMBAYARAN';
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            $orderStatus = 'DIBATALKAN';
        }

        // Update payment record
        $payment = $order->payment;
        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'midtrans',
                'status' => $paymentStatus,
                'payment_method' => $transactionData['payment_type'] ?? null,
            ]);
        } else {
            $payment->update([
                'status' => $paymentStatus,
                'payment_method' => $transactionData['payment_type'] ?? null,
            ]);
        }

        // Update order status
        $order->update([
            'status' => $orderStatus,
        ]);

        // Generate invoice if payment is successful
        if (in_array($paymentStatus, ['PAID', 'SETTLED']) && !$order->invoice) {
            try {
                InvoiceGeneratorService::generate($order, $payment);
                Log::info('Invoice generated for order', ['order_id' => $order->id]);
            } catch (\Exception $e) {
                Log::error('Failed to generate invoice', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // If payment failed/cancelled/expired, restore stock
        if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            foreach ($order->items as $item) {
                \App\Models\Product::where('id', $item->product_id)
                    ->increment('stock', $item->quantity);
            }
        }

        return true;
    }

    /**
     * Sync payment status with Midtrans (called on page load)
     */
    public function syncStatus(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $payment = $order->payment;
        
        // Only check if payment is still pending
        if ($payment && in_array($payment->status, ['PENDING', 'pending'])) {
            $transactionData = $this->checkPaymentStatusFromMidtrans($order->id);
            if ($transactionData) {
                $this->updateOrderStatusFromMidtrans($order, $transactionData);
            }
        }

        return response()->json([
            'order_id' => $order->id,
            'payment_status' => $order->payment?->status ?? 'NO_PAYMENT',
            'order_status' => $order->status,
            'has_invoice' => $order->invoice ? true : false,
        ]);
    }

    /**
     * Handle return URL after payment (customer redirected from Midtrans)
     */
    public function return(Request $request)
    {
        $orderId = $request->input('order_id');
        
        if (!$orderId) {
            return redirect()->route('riwayat.index')
                ->with('error', 'Order ID tidak ditemukan');
        }

        $order = Order::find($orderId);
        
        if (!$order) {
            return redirect()->route('riwayat.index')
                ->with('error', 'Order tidak ditemukan');
        }

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Check status from Midtrans API
        $transactionData = $this->checkPaymentStatusFromMidtrans($order->id);
        
        if ($transactionData) {
            $this->updateOrderStatusFromMidtrans($order, $transactionData);
            
            $transactionStatus = $transactionData['transaction_status'] ?? null;
            
            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                return redirect()->route('tracking.show', $order)
                    ->with('success', 'Pembayaran berhasil! Invoice sudah dibuat.');
            } elseif (in_array($transactionStatus, ['pending'])) {
                return redirect()->route('pembayaran.show', $order)
                    ->with('info', 'Pembayaran masih menunggu, silakan selesaikan pembayaran.');
            } else {
                return redirect()->route('riwayat.index')
                    ->with('error', 'Pembayaran gagal atau dibatalkan.');
            }
        }

        return redirect()->route('pembayaran.show', $order)
            ->with('info', 'Silakan cek status pembayaran.');
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
        
        // Auto-sync status when viewing payment page
        $payment = $order->payment;
        if ($payment && in_array($payment->status, ['PENDING', 'pending'])) {
            $transactionData = $this->checkPaymentStatusFromMidtrans($order->id);
            if ($transactionData) {
                $this->updateOrderStatusFromMidtrans($order, $transactionData);
            }
        }

        return view('customer.payment.show', [
            'order' => $order,
            'payment' => $order->payment,
        ]);
    }

    public function proceed(Order $order)
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
                $midtrans = new \App\Services\MidtransService();
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

        // Mapping status pembayaran ke status order - OTOMATIS SELESAI (Opsi B)
        if (in_array($transactionStatus, ['settlement', 'capture'])) {
            // Pembayaran sukses → LANGSUNG SELESAI
            $orderStatus = 'SELESAI';
        } elseif ($transactionStatus === 'pending') {
            $orderStatus = 'MENUNGGU_PEMBAYARAN';
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            $orderStatus = 'DIBATALKAN';
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
            
            // Generate invoice otomatis jika pembayaran berhasil (PAID/SETTLED)
            if (in_array($paymentStatus, ['PAID', 'SETTLED']) && !$order->invoice) {
                try {
                    InvoiceGeneratorService::generate($order, $order->payment);
                    Log::info('Invoice generated for order', ['order_id' => $order->id]);
                } catch (\Exception $e) {
                    Log::error('Failed to generate invoice', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info('Midtrans webhook received', $payload);

        return response()->json(['message' => 'ok']);
    }
}
