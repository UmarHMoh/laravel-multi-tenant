<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PlatformTransaction;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Services\Payments\PlatformTransactionService;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $paymentStatus = $request->query('payment_status');
        $search = trim((string) $request->query('search', ''));

        $ordersQuery = \App\Models\Order::query()
            ->with('items')
            ->latest();

        if ($status) {
            $ordersQuery->where('status', $status);
        }

        if ($paymentStatus) {
            $ordersQuery->where('payment_status', $paymentStatus);
        }

        if ($search !== '') {
            $ordersQuery->where(function ($query) use ($search) {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhere('billing_name', 'like', "%{$search}%")
                    ->orWhere('billing_email', 'like', "%{$search}%")
                    ->orWhere('billing_phone', 'like', "%{$search}%");
            });
        }

        return Inertia::render('tenant/orders/Index', [
            'orders' => $ordersQuery->paginate(15)->withQueryString(),
            'stats' => [
                'total_orders' => \App\Models\Order::count(),
                'pending_orders' => \App\Models\Order::where('status', 'pending')->count(),
                'processing_orders' => \App\Models\Order::where('status', 'processing')->count(),
                'completed_orders' => \App\Models\Order::where('status', 'completed')->count(),
                'paid_orders' => \App\Models\Order::where('payment_status', 'paid')->count(),
                'unpaid_orders' => \App\Models\Order::whereIn('payment_status', ['pending', 'unpaid'])->count(),
                'revenue' => (float) \App\Models\Order::where('payment_status', 'paid')->sum('total'),
            ],
            'filters' => [
                'status' => $status,
                'payment_status' => $paymentStatus,
                'search' => $search,
            ],
            'statusOptions' => ['pending', 'processing', 'completed', 'cancelled'],
            'paymentStatusOptions' => ['pending', 'paid', 'failed', 'refunded'],
        ]);
    }

    /**
     * Display the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Inertia\Response
     */
    public function show(\App\Models\Order $order)
    {
        $order->load(['items.product']);

        return Inertia::render('tenant/orders/Show', [
            'order' => $order,
            'statusOptions' => ['pending', 'processing', 'completed', 'cancelled'],
            'paymentStatusOptions' => ['pending', 'paid', 'failed', 'refunded'],
        ]);
    }

    /**
     * Update the status of the specified order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        $order->update([
            'status' => $request->status,
        ]);

        return redirect()->back()
            ->with('success', 'Order status updated successfully');
    }

    /**
     * Update the payment status of the specified order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|string|in:pending,paid,failed,refunded',
        ]);

        $wasAlreadyPaid = $order->payment_status === 'paid';

        $order->update([
            'payment_status' => $request->payment_status,
            'paid_at' => $request->payment_status === 'paid' ? ($order->paid_at ?? now()) : $order->paid_at,
        ]);

        if ($request->payment_status === 'paid' && ! $wasAlreadyPaid) {
            $this->createPlatformTransactionForPaidOrder($order, 'manual_paid_status');
        }

        return redirect()->back()
            ->with('success', 'Payment status updated successfully');
    }

    private function createPlatformTransactionForPaidOrder(Order $order, string $provider): void

    {

        $tenantId = tenant('id');

        tenancy()->central(function () use ($tenantId, $order, $provider) {

            $tenant = Tenant::with('currentSubscription.plan')->findOrFail($tenantId);

            app(PlatformTransactionService::class)->recordPaidOrder(

                tenant: $tenant,

                order: $order,

                provider: $provider

            );

        });

    }



    /**
     * Get the available order status options.
     *
     * @return array
     */
    private function getStatusOptions()
    {
        return [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'processing', 'label' => 'Processing'],
            ['value' => 'completed', 'label' => 'Completed'],
            ['value' => 'cancelled', 'label' => 'Cancelled'],
        ];
    }

    /**
     * Get the available payment status options.
     *
     * @return array
     */
    private function getPaymentStatusOptions()
    {
        return [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'paid', 'label' => 'Paid'],
            ['value' => 'failed', 'label' => 'Failed'],
            ['value' => 'refunded', 'label' => 'Refunded'],
        ];
    }

    /**
     * Get order statistics for the dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardStats()
    {
        // Get current date and relevant date ranges for comparisons
        $now = now();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $lastWeekStart = $now->copy()->subWeek()->startOfWeek();
        $lastWeekEnd = $now->copy()->subWeek()->endOfWeek();

        // This week's orders
        $thisWeekOrders = Order::whereBetween('created_at', [$weekStart, $weekEnd])->get();
        $thisWeekCount = $thisWeekOrders->count();
        $thisWeekRevenue = $thisWeekOrders->sum('total');

        // Last week's orders for comparison
        $lastWeekOrders = Order::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->get();
        $lastWeekCount = $lastWeekOrders->count();
        $lastWeekRevenue = $lastWeekOrders->sum('total');

        // Calculate trends (percentage change)
        $ordersTrend = $lastWeekCount > 0
            ? round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100, 1)
            : 0;

        $revenueTrend = $lastWeekRevenue > 0
            ? round((($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100, 1)
            : 0;

        // Get total orders and revenue
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total');

        // Get pending orders count
        $pendingOrders = Order::where('status', 'pending')->count();

        // Calculate average order value
        $aov = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Calculate AOV trend
        $thisWeekAOV = $thisWeekCount > 0 ? $thisWeekRevenue / $thisWeekCount : 0;
        $lastWeekAOV = $lastWeekCount > 0 ? $lastWeekRevenue / $lastWeekCount : 0;
        $aovTrend = $lastWeekAOV > 0
            ? round((($thisWeekAOV - $lastWeekAOV) / $lastWeekAOV) * 100, 1)
            : 0;

        // Get recent orders for display
        $recentOrders = Order::with(['user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'stats' => [
                'totalOrders' => $totalOrders,
                'totalRevenue' => $totalRevenue,
                'pendingOrders' => $pendingOrders,
                'averageOrderValue' => $aov,
                'ordersTrend' => $ordersTrend,
                'revenueTrend' => $revenueTrend,
                'aovTrend' => $aovTrend,
            ],
            'recentOrders' => $recentOrders,
            'statusOptions' => $this->getStatusOptions(),
            'paymentStatusOptions' => $this->getPaymentStatusOptions(),
        ]);
    }
}
