<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ===== Summary angka atas =====
        $totalOrders = Order::count();
        $totalRevenue = (int) Order::sum('total');
        $totalCustomers = User::count();
        $averageRevenue = $totalOrders > 0 ? (int) ($totalRevenue / $totalOrders) : 0;

        // ===== Pesanan terbaru =====
        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        // ===== Produk terlaris (untuk list + bar chart) =====
        $topProducts = Product::orderByDesc('sold_count')
            ->take(5)
            ->get();

        $topProductLabels = $topProducts->pluck('name')->values();
        $topProductSales  = $topProducts->pluck('sold_count')->values();

        // ===== Grafik Penjualan 7 Hari Terakhir (line chart) =====
        $start = Carbon::now()->subDays(6)->startOfDay();
        $end   = Carbon::now()->endOfDay();

        // Ambil agregat per hari: jumlah order + sum total
        $daily = Order::selectRaw('DATE(created_at) as d, COUNT(*) as c, COALESCE(SUM(total),0) as s')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $salesLabels  = [];
        $salesOrders  = [];
        $salesRevenue = [];

        // Isi 7 hari (termasuk yang kosong = 0)
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $salesLabels[] = Carbon::parse($date)->format('d M');

            $salesOrders[]  = isset($daily[$date]) ? (int) $daily[$date]->c : 0;
            $salesRevenue[] = isset($daily[$date]) ? (int) $daily[$date]->s : 0;
        }

        return view('admin.dashboard', [
            // summary
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'totalCustomers' => $totalCustomers,
            'averageRevenue' => $averageRevenue,

            // tabel & list
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,

            // chart bar produk
            'topProductLabels' => $topProductLabels,
            'topProductSales' => $topProductSales,

            // chart line 7 hari
            'salesLabels' => $salesLabels,
            'salesOrders' => $salesOrders,
            'salesRevenue' => $salesRevenue,
        ]);
    }
}
