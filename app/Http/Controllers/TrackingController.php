<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    /**
     * Display list of all user orders for tracking
     */
    public function index()
    {
        $orders = Order::with(['items.product', 'payment', 'invoice', 'paymentConfirmation'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        foreach ($orders as $order) {
            $this->syncPaymentStatus($order);
        }

        $steps = [
            'PESANAN_DITERIMA' => 1,
            'MENUNGGU_PEMBAYARAN' => 1,
            'SEDANG_DIPROSES' => 2,
            'SIAP_DIAMBIL_DIKIRIM' => 3,
            'SELESAI' => 4,
            'DIBATALKAN' => 1,
        ];

        $orders = Order::with(['items.product', 'payment', 'invoice', 'paymentConfirmation'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->each(function ($order) use ($steps) {
                $order->currentStep = $steps[$order->status] ?? 1;
            });

        return view('customer.tracking.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Sync payment status with Midtrans API
     */
    private function syncPaymentStatus(Order $order)
    {
        // Only sync if payment is pending
        if ($order->payment && in_array(strtolower($order->payment->status), ['pending', 'pending'])) {
            try {
                $serverKey = config('services.midtrans.server_key');
                $isProduction = config('services.midtrans.is_production');
                
                $baseUrl = $isProduction 
                    ? 'https://api.midtrans.com' 
                    : 'https://api.sandbox.midtrans.com';
                
                $response = Http::withBasicAuth($serverKey, '')
                    ->get("{$baseUrl}/v2/{$order->id}/status");
                
                if ($response->successful()) {
                    $transactionData = $response->json();
                    $transactionStatus = $transactionData['transaction_status'] ?? null;
                    
                    if ($transactionStatus) {
                        $paymentStatus = strtoupper($transactionStatus);
                        
                        // Update payment status
                        $order->payment->update([
                            'status' => $paymentStatus,
                            'payment_method' => $transactionData['payment_type'] ?? $order->payment->payment_method,
                        ]);
                        
                        // Update order status if payment successful - OTOMATIS SELESAI (Opsi B)
                        if (in_array($transactionStatus, ['settlement', 'capture'])) {
                            $order->update(['status' => 'SELESAI']);
                            
                            // Generate invoice if not exists
                            if (!$order->invoice) {
                                try {
                                    \App\Services\InvoiceGeneratorService::generate($order, $order->payment);
                                } catch (\Throwable $e) {
                                    Log::error('Failed to generate invoice', [
                                        'order_id' => $order->id,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            }
                        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                            $order->update(['status' => 'DIBATALKAN']);
                            
                            // Restore stock
                            foreach ($order->items as $item) {
                                \App\Models\Product::where('id', $item->product_id)
                                    ->increment('stock', $item->quantity);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to sync payment status in tracking', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue silently - don't break the page
            }
        }
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Sync payment status before showing tracking page
        $this->syncPaymentStatus($order);

        // Reload order to get updated status
        $order->refresh();

        $steps = [
            'PESANAN_DITERIMA' => 1,
            'SEDANG_DIPROSES' => 2,
            'SIAP_DIAMBIL_DIKIRIM' => 3,
            'SELESAI' => 4,
        ];

        $currentStep = $steps[$order->status] ?? 1;

        return view('customer.tracking.show', [
            'order' => $order->load(['items.product', 'payment', 'invoice', 'paymentConfirmation']),
            'currentStep' => $currentStep,
        ]);
    }
}
