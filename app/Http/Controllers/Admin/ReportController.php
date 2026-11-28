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
<<<<<<< HEAD
        $quoteRequestCount = (clone $periodOrders)
            ->where(function ($query) {
                $query->where('status', Order::STATUS_QUOTE_REQUESTED)
                    ->orWhereNotNull('quoted_total')
                    ->orWhereHas('items', fn ($query) => $query->whereNull('unit_price'));
            })
            ->distinct()
            ->count();
        $orders = (clone $periodOrders)->with('user')->latest()->paginate(20)->withQueryString();
=======
        $perPage = max(10, min(100, (int) $request->input('per_page', 25)));
        $orders = (clone $periodOrders)->with(['user', 'items'])->latest()->paginate($perPage)->withQueryString();
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
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
            'quoteRequestCount',
            'orders',
            'catalogCounts',
        ));
    }
}