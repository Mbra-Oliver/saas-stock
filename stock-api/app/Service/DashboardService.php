<?php

namespace App\Service;

use App\Models\Product;
use App\Models\Order;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get dashboard overview statistics
     */
    public function getOverview($companyId)
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        return [
            'total_products' => Product::where('company_id', $companyId)->count(),
            'active_products' => Product::where('company_id', $companyId)->where('status', 'active')->count(),
            'low_stock_products' => Stock::whereHas('product', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->whereColumn('quantity', '<=', 'alert_quantity')->count(),
            'out_of_stock_products' => Stock::whereHas('product', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->where('quantity', 0)->count(),

            'total_orders_today' => Order::where('company_id', $companyId)->whereDate('created_at', $today)->count(),
            'total_orders_month' => Order::where('company_id', $companyId)->whereDate('created_at', '>=', $thisMonth)->count(),
            'pending_orders' => Order::where('company_id', $companyId)->where('status', 'pending')->count(),

            'sales_today' => Order::where('company_id', $companyId)
                ->where('type', 'sale')
                ->where('status', 'confirmed')
                ->whereDate('created_at', $today)
                ->sum('total'),
            'sales_month' => Order::where('company_id', $companyId)
                ->where('type', 'sale')
                ->where('status', 'confirmed')
                ->whereDate('created_at', '>=', $thisMonth)
                ->sum('total'),

            'purchases_today' => Order::where('company_id', $companyId)
                ->where('type', 'purchase')
                ->where('status', 'confirmed')
                ->whereDate('created_at', $today)
                ->sum('total'),
            'purchases_month' => Order::where('company_id', $companyId)
                ->where('type', 'purchase')
                ->where('status', 'confirmed')
                ->whereDate('created_at', '>=', $thisMonth)
                ->sum('total'),

            'unpaid_amount' => Order::where('company_id', $companyId)
                ->where('status', 'confirmed')
                ->where('payment_status', 'unpaid')
                ->sum('total'),
        ];
    }

    /**
     * Get sales chart data
     */
    public function getSalesChart($companyId, $period = 'week')
    {
        $query = Order::where('company_id', $companyId)
            ->where('type', 'sale')
            ->where('status', 'confirmed');

        if ($period === 'week') {
            $startDate = now()->subDays(7);
            $query->whereDate('created_at', '>=', $startDate);
            $groupBy = DB::raw('DATE(created_at)');
        } elseif ($period === 'month') {
            $startDate = now()->subDays(30);
            $query->whereDate('created_at', '>=', $startDate);
            $groupBy = DB::raw('DATE(created_at)');
        } else { // year
            $startDate = now()->subMonths(12);
            $query->whereDate('created_at', '>=', $startDate);
            $groupBy = DB::raw('DATE_FORMAT(created_at, "%Y-%m")');
        }

        return $query->select(
            $groupBy . ' as date',
            DB::raw('SUM(total) as total_sales'),
            DB::raw('COUNT(*) as orders_count')
        )->groupBy('date')->orderBy('date')->get();
    }

    /**
     * Get top selling products
     */
    public function getTopSellingProducts($companyId, $limit = 10)
    {
        return Product::where('company_id', $companyId)
            ->withCount(['orderItems as total_sold' => function ($query) {
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
            }])
            ->with('category')
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent stock movements
     */
    public function getRecentStockMovements($companyId, $limit = 10)
    {
        return StockMovement::with(['product', 'warehouse', 'user'])
            ->whereHas('product', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders($companyId, $limit = 10)
    {
        return Order::with(['items.product', 'warehouse'])
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get stock value by warehouse
     */
    public function getStockValueByWarehouse($companyId)
    {
        return Stock::with('warehouse')
            ->whereHas('product', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->select(
                'warehouses.id',
                'warehouses.name',
                DB::raw('SUM(stocks.quantity * products.purchase_price) as total_value'),
                DB::raw('SUM(stocks.quantity) as total_quantity')
            )
            ->groupBy('warehouses.id', 'warehouses.name')
            ->get();
    }

    /**
     * Get inventory turnover
     */
    public function getInventoryTurnover($companyId, $period = 'month')
    {
        $startDate = $period === 'month' ? now()->subMonth() : now()->subYear();

        $costOfGoodsSold = Order::where('company_id', $companyId)
            ->where('type', 'sale')
            ->where('status', 'confirmed')
            ->whereDate('created_at', '>=', $startDate)
            ->sum('total');

        $averageInventory = Stock::whereHas('product', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
        ->join('products', 'stocks.product_id', '=', 'products.id')
        ->sum(DB::raw('stocks.quantity * products.purchase_price'));

        $turnoverRatio = $averageInventory > 0 ? $costOfGoodsSold / $averageInventory : 0;

        return [
            'cost_of_goods_sold' => $costOfGoodsSold,
            'average_inventory' => $averageInventory,
            'turnover_ratio' => round($turnoverRatio, 2),
            'period' => $period
        ];
    }
}
