<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use stdClass;

class CustomerController extends Controller
{
    private const WALKIN_EMAIL = 'walkin.customer@durianlovers.local';

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $allCustomers = $this->buildCustomerDirectory();
        $filteredCustomers = $this->filterCustomerDirectory($allCustomers, $q);
        $customers = $this->paginateCustomerDirectory($filteredCustomers, $request);

        // summary cards
        $totalPelanggan = $allCustomers->count();
        $pelangganAktif = $allCustomers->where('total_pesanan', '>', 0)->count();
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

    public function show(string $customer)
    {
        $customer = $this->resolveCustomerEntry($customer);

        return view('admin.customers.show', [
            'customer' => $customer,
        ]);
    }

    private function buildCustomerDirectory(): Collection
    {
        $registeredCustomers = User::query()
            ->where('email', '!=', self::WALKIN_EMAIL)
            ->with([
                'orders' => function ($query) {
                    $query->latest();
                },
                'orders.items.product',
            ])
            ->get()
            ->map(function (User $customer) {
                return $this->makeRegisteredCustomerEntry($customer);
            });

        $manualCustomers = $this->buildManualCustomerEntries();

        return $registeredCustomers
            ->concat($manualCustomers)
            ->sortByDesc(function (stdClass $customer) {
                return $customer->terakhir_belanja?->timestamp ?? 0;
            })
            ->values();
    }

    private function buildManualCustomerEntries(): Collection
    {
        $orders = Order::query()
            ->with(['items.product', 'user'])
            ->where('payment_method', 'Cash')
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->latest()
            ->get();

        return $orders
            ->groupBy(function (Order $order) {
                return $this->manualCustomerIdentity($order);
            })
            ->map(function (Collection $orders, string $identity) {
                return $this->makeManualCustomerEntry($orders, $identity);
            })
            ->values();
    }

    private function filterCustomerDirectory(Collection $customers, string $query): Collection
    {
        if ($query === '') {
            return $customers;
        }

        $needle = mb_strtolower($query);

        return $customers
            ->filter(function (stdClass $customer) use ($needle) {
                $haystacks = [
                    $customer->display_name,
                    $customer->display_email,
                    $customer->display_phone,
                ];

                foreach ($customer->orders as $order) {
                    $haystacks[] = $order->customer_display_name;
                    $haystacks[] = $order->customer_display_email;
                    $haystacks[] = $order->customer_display_phone;
                }

                foreach ($haystacks as $haystack) {
                    if ($haystack !== null && str_contains(mb_strtolower((string) $haystack), $needle)) {
                        return true;
                    }
                }

                return false;
            })
            ->values();
    }

    private function paginateCustomerDirectory(Collection $customers, Request $request): LengthAwarePaginator
    {
        $perPage = 10;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $items = $customers->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $customers->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function resolveCustomerEntry(string $customerKey): stdClass
    {
        if (str_starts_with($customerKey, 'user-')) {
            $userId = (int) substr($customerKey, 5);

            $customer = User::query()
                ->where('email', '!=', self::WALKIN_EMAIL)
                ->with([
                    'orders' => function ($query) {
                        $query->latest();
                    },
                    'orders.items.product',
                ])
                ->findOrFail($userId);

            return $this->makeRegisteredCustomerEntry($customer);
        }

        if (str_starts_with($customerKey, 'manual-')) {
            $identity = $this->decodeManualCustomerKey(substr($customerKey, 7));
            $entry = $this->buildManualCustomerEntries()
                ->first(fn (stdClass $customer) => $customer->identity_key === $identity);

            if ($entry) {
                return $entry;
            }
        }

        abort(404);
    }

    private function makeRegisteredCustomerEntry(User $customer): stdClass
    {
        $orders = $customer->orders->sortByDesc('created_at')->values();
        $latestOrder = $orders->first();

        return $this->makeCustomerEntry([
            'detail_key' => 'user-' . $customer->id,
            'identity_key' => 'user-' . $customer->id,
            'display_name' => $customer->name,
            'display_email' => $customer->email,
            'display_phone' => $customer->phone ?? '-',
            'address' => $customer->address ?? '-',
            'total_pesanan' => $orders->count(),
            'total_pengeluaran' => (int) $orders->sum('total'),
            'terakhir_belanja' => $latestOrder?->created_at,
            'orders' => $orders,
        ]);
    }

    private function makeManualCustomerEntry(Collection $orders, string $identity): stdClass
    {
        $sortedOrders = $orders->sortByDesc('created_at')->values();
        $latestOrder = $sortedOrders->first();

        return $this->makeCustomerEntry([
            'detail_key' => 'manual-' . $this->encodeManualCustomerKey($identity),
            'identity_key' => $identity,
            'display_name' => $latestOrder?->customer_display_name ?? 'Pelanggan Cash',
            'display_email' => $latestOrder?->customer_display_email ?? '-',
            'display_phone' => $latestOrder?->customer_display_phone ?? '-',
            'address' => $latestOrder?->shipping_address ?? 'Ambil di Toko',
            'total_pesanan' => $sortedOrders->count(),
            'total_pengeluaran' => (int) $sortedOrders->sum('total'),
            'terakhir_belanja' => $latestOrder?->created_at,
            'orders' => $sortedOrders,
        ]);
    }

    private function makeCustomerEntry(array $attributes): stdClass
    {
        $entry = new stdClass();

        foreach ($attributes as $key => $value) {
            $entry->{$key} = $value;
        }

        return $entry;
    }

    private function manualCustomerIdentity(Order $order): string
    {
        $email = trim((string) ($order->customer_email ?? ''));
        $phone = trim((string) ($order->customer_phone ?? ''));
        $name = trim((string) ($order->customer_name ?? ''));

        if ($email !== '') {
            return 'email:' . mb_strtolower($email);
        }

        if ($phone !== '') {
            return 'phone:' . preg_replace('/\s+/', '', $phone);
        }

        return 'name:' . mb_strtolower($name);
    }

    private function encodeManualCustomerKey(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function decodeManualCustomerKey(string $value): string
    {
        $normalized = strtr($value, '-_', '+/');
        $padding = strlen($normalized) % 4;

        if ($padding > 0) {
            $normalized .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($normalized, true);

        return $decoded === false ? '' : $decoded;
    }
}
