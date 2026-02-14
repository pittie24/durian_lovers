<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        // daftar pelanggan + agregat untuk tabel
        $query = User::query();
        
        if ($q) {
            $query->where(function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $customers = $query
            ->withCount('orders as total_pesanan')
            ->withSum('orders as total_pengeluaran', 'total')
            ->withMax('orders as terakhir_belanja', 'created_at')
            ->orderByDesc('terakhir_belanja')
            ->paginate(10)
            ->withQueryString();

        // summary cards
        $totalPelanggan = User::count();
        $pelangganAktif = User::whereHas('orders')->count();
        $rataRataPembelian = Order::avg('total') ?? 0;

        // Check if the request is an AJAX request
        if ($request->ajax()) {
            return view('admin.customers._table', compact(
                'customers',
                'q'
            ));
        }

        return view('admin.customers.index', compact(
            'customers',
            'q',
            'totalPelanggan',
            'pelangganAktif',
            'rataRataPembelian'
        ));
    }

    public function show(User $customer)
    {
        return view('admin.customers.show', [
            'customer' => $customer->load('orders'),
        ]);
    }
}
