<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private const WALKIN_EMAIL = 'walkin.customer@durianlovers.local';

    public function index(Request $request)
    {
        $q = $request->get('q');

        // daftar pelanggan + agregat untuk tabel
        $query = User::query();
        
        if ($q) {
            $query->where(function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhereHas('orders', function ($orders) use ($q) {
                      $orders->where('customer_name', 'like', "%{$q}%")
                          ->orWhere('customer_email', 'like', "%{$q}%")
                          ->orWhere('customer_phone', 'like', "%{$q}%");
                  });
            });
        }

        $customers = $query
            ->withCount('orders as total_pesanan')
            ->withSum('orders as total_pengeluaran', 'total')
            ->withMax('orders as terakhir_belanja', 'created_at')
            ->orderByDesc('terakhir_belanja')
            ->paginate(10)
            ->withQueryString();

        $customers->getCollection()->load('orders');
        $customers->setCollection(
            $customers->getCollection()->map(function (User $customer) {
                return $this->decorateCustomer($customer);
            })
        );

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
        $customer->load('orders');
        $customer = $this->decorateCustomer($customer);

        return view('admin.customers.show', [
            'customer' => $customer,
        ]);
    }

    private function decorateCustomer(User $customer): User
    {
        $customer->setAttribute('display_name', $customer->name);
        $customer->setAttribute('display_email', $customer->email);
        $customer->setAttribute('display_phone', $customer->phone ?? '-');

        if ($customer->email !== self::WALKIN_EMAIL) {
            return $customer;
        }

        $latestCashOrder = $customer->orders
            ->sortByDesc('created_at')
            ->first(function (Order $order) {
                return strtoupper((string) $order->payment_method) === 'CASH'
                    && $order->customer_name;
            });

        if (!$latestCashOrder) {
            return $customer;
        }

        $customer->setAttribute('display_name', $latestCashOrder->customer_display_name);
        $customer->setAttribute('display_email', $latestCashOrder->customer_display_email);
        $customer->setAttribute('display_phone', $latestCashOrder->customer_display_phone);

        return $customer;
    }
}
