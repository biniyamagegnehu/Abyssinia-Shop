<?php

namespace App\Repositories\Eloquent;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getTotalSales(): string
    {
        $total = Order::where('status', OrderStatus::DELIVERED->value)
            ->sum('total_amount');

        return number_format((float) $total, 2, '.', '');
    }

    public function getTotalOrders(): int
    {
        return Order::count();
    }

    public function getTotalCustomers(): int
    {
        return User::count();
    }

    public function getTotalProducts(): int
    {
        return Product::count();
    }

    public function getAverageOrderValue(): string
    {
        $avg = Order::where('status', OrderStatus::DELIVERED->value)
            ->avg('total_amount');

        return number_format((float) ($avg ?? 0), 2, '.', '');
    }

    public function getOrderStatistics(): array
    {
        $stats = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses present, default to 0
        $result = [];
        foreach (OrderStatus::cases() as $status) {
            $result[$status->value] = $stats[$status->value] ?? 0;
        }

        return $result;
    }

    public function getDailyRevenue(): Collection
    {
        $driver = DB::getDriverName();
        $dayExpr = $driver === 'sqlite'
            ? "cast(strftime('%d', created_at) as integer)"
            : 'DAY(created_at)';
        $monthExpr = $driver === 'sqlite'
            ? "cast(strftime('%m', created_at) as integer)"
            : 'MONTH(created_at)';
        $yearExpr = $driver === 'sqlite'
            ? "cast(strftime('%Y', created_at) as integer)"
            : 'YEAR(created_at)';

        return Order::where('status', OrderStatus::DELIVERED->value)
            ->whereRaw("{$monthExpr} = ?", [now()->month])
            ->whereRaw("{$yearExpr} = ?", [now()->year])
            ->select(
                DB::raw("{$dayExpr} as day"),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => [
                'label' => 'Day ' . $row->day,
                'revenue' => number_format((float) $row->revenue, 2, '.', ''),
            ]);
    }

    public function getWeeklyRevenue(): Collection
    {
        $driver = DB::getDriverName();
        $weekExpr = $driver === 'sqlite'
            ? "cast(strftime('%W', created_at) as integer)"
            : 'WEEK(created_at)';
        $yearExpr = $driver === 'sqlite'
            ? "cast(strftime('%Y', created_at) as integer)"
            : 'YEAR(created_at)';

        return Order::where('status', OrderStatus::DELIVERED->value)
            ->whereRaw("{$yearExpr} = ?", [now()->year])
            ->select(
                DB::raw("{$weekExpr} as week"),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->map(fn ($row) => [
                'label' => 'Week ' . $row->week,
                'revenue' => number_format((float) $row->revenue, 2, '.', ''),
            ]);
    }

    public function getMonthlyRevenue(): Collection
    {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
        ];

        $driver = DB::getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "cast(strftime('%m', created_at) as integer)"
            : 'MONTH(created_at)';
        $yearExpr = $driver === 'sqlite'
            ? "cast(strftime('%Y', created_at) as integer)"
            : 'YEAR(created_at)';

        return Order::where('status', OrderStatus::DELIVERED->value)
            ->whereRaw("{$yearExpr} = ?", [now()->year])
            ->select(
                DB::raw("{$monthExpr} as month"),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'label' => $months[(int) $row->month] ?? $row->month,
                'revenue' => number_format((float) $row->revenue, 2, '.', ''),
            ]);
    }

    public function getRecentOrders(int $limit = 10): Collection
    {
        return Order::with('user:id,name')
            ->select('id', 'user_id', 'order_number', 'status', 'total_amount', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getLowStockProducts(int $threshold = 5): Collection
    {
        return Product::select('id', 'name', 'sku', 'quantity')
            ->where('quantity', '<=', $threshold)
            ->orderBy('quantity', 'asc')
            ->get();
    }
}
