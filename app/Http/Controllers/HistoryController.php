<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index()
    {
        $orders = Order::with([
                'items.product' // eager load item + product
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

                // ===== NORMALISASI STATUS =====
                $order->status = strtolower($order->status ?? 'diproses');

                return $order;
            });

        return view('customer.history.index', compact('orders'));
    }
}
