<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HistoryController extends Controller
{
    private const STATUS_MAP = [
        'MENUNGGU_PEMBAYARAN' => ['Menunggu Pembayaran', 'default'],
        'SEDANG_DIPROSES' => ['Dikemas', 'process'],
        'SIAP_DIAMBIL_DIKIRIM' => ['Dikirim', 'shipped'],
        'SELESAI' => ['Selesai', 'done'],
        'DIBATALKAN' => ['Dibatalkan', 'cancel'],
    ];

    /**
     * Check payment status from Midtrans API for pending orders
     */
    private function syncPendingPayments($orders)
    {
        foreach ($orders as $order) {
            // Only check orders with pending payment
            if ($order->payment && in_array(strtolower($order->payment->status), ['pending', 'pENDING'])) {
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
                            
                            // Selaraskan dengan alur admin: pembayaran sukses masuk ke tahap proses.
                            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                                $order->update(['status' => 'SEDANG_DIPROSES']);
                                
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
                    Log::error('Failed to sync payment status', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue silently - don't break the page
                }
            }
        }
    }

    public function index()
    {
        $orders = Order::with([
                'items.product',
                'payment',
                'invoice',
                'paymentConfirmation'
            ])
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($order) {

                // ===== FORMAT ORDER CODE =====
                if (!$order->order_code) {
                    $order->order_code = 'ORD-' . $order->created_at->format('Ymd') . '-' . str_pad($order->id, 3, '0', STR_PAD_LEFT);
                }

                // ===== TOTAL FALLBACK (kalau total null) =====
                if (!$order->total) {
                    $order->total = $order->items->sum(function ($item) {
                        return ($item->price ?? 0) * ($item->quantity ?? 1);
                    });
                }

                $this->applyStatusPresentation($order);

                return $order;
            });

        // ===== SYNC PENDING PAYMENTS =====
        // Auto-check status for orders with pending payment
        $this->syncPendingPayments($orders);

        // Refresh orders after sync to get updated status
        $orders = Order::with([
                'items.product',
                'payment',
                'invoice',
                'paymentConfirmation'
            ])
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($order) {
                // ===== FORMAT ORDER CODE =====
                if (!$order->order_code) {
                    $order->order_code = 'ORD-' . $order->created_at->format('Ymd') . '-' . str_pad($order->id, 3, '0', STR_PAD_LEFT);
                }

                // ===== TOTAL FALLBACK (kalau total null) =====
                if (!$order->total) {
                    $order->total = $order->items->sum(function ($item) {
                        return ($item->price ?? 0) * ($item->quantity ?? 1);
                    });
                }

                $this->applyStatusPresentation($order);

                return $order;
            });

        return view('customer.history.index', compact('orders'));
    }

    private function applyStatusPresentation(Order $order): void
    {
        $statusKey = strtoupper((string) ($order->status ?? 'MENUNGGU_PEMBAYARAN'));
        [$label, $class] = self::STATUS_MAP[$statusKey] ?? [
            ucwords(strtolower(str_replace('_', ' ', $statusKey))),
            'default',
        ];

        $order->status_label = $label;
        $order->status_class = $class;
        $order->status_key = $statusKey;
    }
}
