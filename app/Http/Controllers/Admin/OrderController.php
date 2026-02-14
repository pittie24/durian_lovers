<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

public function index(Request $request)
{
    $status = $request->get('status', 'SEMUA');

    // Query utama untuk tabel
    $query = Order::with('user');

    if ($status !== 'SEMUA') {
        $query->where('status', $status);
    }

    $orders = $query->latest()->get();

    // Hitung jumlah per status untuk badge (0) di tab
    $counts = [
        'SEMUA' => Order::count(),
        'PESANAN_DITERIMA' => Order::where('status', 'PESANAN_DITERIMA')->count(),
        'SEDANG_DIPROSES' => Order::where('status', 'SEDANG_DIPROSES')->count(),
        'SIAP_DIAMBIL_DIKIRIM' => Order::where('status', 'SIAP_DIAMBIL_DIKIRIM')->count(),
        'SELESAI' => Order::where('status', 'SELESAI')->count(),
    ];

    return view('admin.orders.index', [
        'orders' => $orders,
        'status' => $status,
        'counts' => $counts,
    ]);
}


    public function show(Order $order)
    {
        return view('admin.orders.show', [
            'order' => $order->load('items.product', 'user'),
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required'],
        ]);

        $order->update([
            'status' => $data['status'],
        ]);

        return back()->with('success', 'Status pesanan diperbarui.');
    }
}
