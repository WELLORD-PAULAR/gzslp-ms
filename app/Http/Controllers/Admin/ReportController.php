<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportFilterRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(ReportFilterRequest $request): View
    {
        $filters = $request->validated();
        $dateFrom = CarbonImmutable::parse($filters['date_from'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $dateTo = CarbonImmutable::parse($filters['date_to'] ?? now()->toDateString())->endOfDay();
        $periodOrders = Order::query()->whereBetween('created_at', [$dateFrom, $dateTo]);
        $statusCounts = (clone $periodOrders)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $completedOrders = (clone $periodOrders)->where('status', Order::STATUS_COMPLETED);
        $completedOrderCount = (clone $completedOrders)->count();
        $completedOrderValue = (clone $completedOrders)
            ->selectRaw('COALESCE(SUM(CASE WHEN quoted_total IS NOT NULL THEN quoted_total ELSE subtotal END), 0) as total')
            ->value('total');
        $orders = (clone $periodOrders)->with(['user', 'items'])->latest()->paginate(50)->withQueryString();
        $catalogCounts = [
            'active_products' => Product::where('is_active', true)->count(),
            'hidden_products' => Product::where('is_active', false)->count(),
            'active_services' => Service::where('is_active', true)->count(),
            'hidden_services' => Service::where('is_active', false)->count(),
        ];

        return view('admin.reports.index', compact(
            'dateFrom',
            'dateTo',
            'statusCounts',
            'completedOrderCount',
            'completedOrderValue',
            'orders',
            'catalogCounts',
        ));
    }
}