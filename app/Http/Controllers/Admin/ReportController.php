<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $totalRevenue = Order::sum('total');
        $totalOrders = Order::count();
        $bestProducts = Product::orderByDesc('sold_count')->take(5)->get();

        $daily = Order::select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as total'))
            ->groupBy('day')
            ->orderBy('day', 'desc')
            ->take(7)
            ->get()
            ->reverse();

        return view('admin.reports.index', [
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'bestProducts' => $bestProducts,
            'daily' => $daily,
        ]);
    }
}
