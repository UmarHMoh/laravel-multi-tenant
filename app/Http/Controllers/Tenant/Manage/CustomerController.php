<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $orders = Order::query()
            ->latest()
            ->get();

        $customers = $this->buildCustomersFromOrders($orders);

        if ($search !== '') {
            $customers = $customers->filter(function ($customer) use ($search) {
                $haystack = strtolower(implode(' ', [
                    $customer['name'] ?? '',
                    $customer['email'] ?? '',
                    $customer['phone'] ?? '',
                ]));

                return str_contains($haystack, strtolower($search));
            })->values();
        }

        $paginatedCustomers = $this->paginateCollection(
            $customers,
            perPage: 15,
            page: (int) $request->query('page', 1),
            path: '/manage/customer',
            query: $request->query()
        );

        return Inertia::render('tenant/customers/Index', [
            'customers' => $paginatedCustomers,
            'stats' => [
                'total_customers' => $this->buildCustomersFromOrders($orders)->count(),
                'customers_with_orders' => $this->buildCustomersFromOrders($orders)->filter(fn ($customer) => ($customer['order_count'] ?? 0) > 0)->count(),
                'total_customer_revenue' => (float) Order::where('payment_status', 'paid')->sum('total'),
                'total_orders' => Order::count(),
            ],
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function show(string $customer)
    {
        $customerKey = rawurldecode($customer);

        $ordersQuery = Order::query()
            ->where('billing_email', $customerKey)
            ->latest();

        $orders = $ordersQuery->paginate(10);

        $latestOrder = Order::query()
            ->where('billing_email', $customerKey)
            ->latest()
            ->first();

        $customerProfile = [
            'id' => rawurlencode($customerKey),
            'name' => $latestOrder?->billing_name,
            'email' => $latestOrder?->billing_email ?: $customerKey,
            'phone' => $latestOrder?->billing_phone,
            'address' => $latestOrder?->billing_address,
            'created_at' => optional(Order::query()->where('billing_email', $customerKey)->oldest()->first())->created_at,
        ];

        return Inertia::render('tenant/customers/Show', [
            'customer' => $customerProfile,
            'orders' => $orders,
            'stats' => [
                'order_count' => Order::where('billing_email', $customerKey)->count(),
                'paid_order_count' => Order::where('billing_email', $customerKey)->where('payment_status', 'paid')->count(),
                'total_spent' => (float) Order::where('billing_email', $customerKey)->where('payment_status', 'paid')->sum('total'),
                'latest_order_at' => optional(Order::where('billing_email', $customerKey)->latest()->first())->created_at,
            ],
        ]);
    }

    private function buildCustomersFromOrders(Collection $orders): Collection
    {
        return $orders
            ->filter(fn ($order) => filled($order->billing_email))
            ->groupBy(fn ($order) => strtolower((string) $order->billing_email))
            ->map(function (Collection $customerOrders, string $email) {
                $latestOrder = $customerOrders->sortByDesc('created_at')->first();
                $oldestOrder = $customerOrders->sortBy('created_at')->first();

                return [
                    'id' => rawurlencode($email),
                    'name' => $latestOrder?->billing_name,
                    'email' => $latestOrder?->billing_email,
                    'phone' => $latestOrder?->billing_phone,
                    'address' => $latestOrder?->billing_address,
                    'created_at' => $oldestOrder?->created_at,
                    'order_count' => $customerOrders->count(),
                    'total_spent' => (float) $customerOrders
                        ->filter(fn ($order) => $order->payment_status === 'paid')
                        ->sum('total'),
                    'latest_order_at' => $latestOrder?->created_at,
                ];
            })
            ->values();
    }

    private function paginateCollection(Collection $items, int $perPage, int $page, string $path, array $query = []): LengthAwarePaginator
    {
        $page = max($page, 1);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $path,
                'query' => $query,
            ]
        );
    }
}
