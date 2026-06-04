<?php

namespace App\Services\Dashboard;

use App\DTOs\Dashboard\DashboardFilterDTO;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository
    ) {
    }

    /**
     * Aggregate all summary metrics.
     */
    public function getSummary(): array
    {
        return [
            'total_sales' => $this->dashboardRepository->getTotalSales(),
            'total_orders' => $this->dashboardRepository->getTotalOrders(),
            'total_customers' => $this->dashboardRepository->getTotalCustomers(),
            'total_products' => $this->dashboardRepository->getTotalProducts(),
            'average_order_value' => $this->dashboardRepository->getAverageOrderValue(),
        ];
    }

    /**
     * Get order counts grouped by status.
     */
    public function getOrderStatistics(): array
    {
        return $this->dashboardRepository->getOrderStatistics();
    }

    /**
     * Get revenue analytics for a given period.
     */
    public function getRevenueAnalytics(DashboardFilterDTO $filter): array
    {
        $data = match ($filter->period) {
            'daily' => $this->dashboardRepository->getDailyRevenue(),
            'weekly' => $this->dashboardRepository->getWeeklyRevenue(),
            'monthly' => $this->dashboardRepository->getMonthlyRevenue(),
        };

        return [
            'period' => $filter->period,
            'data' => $data->toArray(),
        ];
    }

    /**
     * Get latest orders.
     */
    public function getRecentOrders(int $limit = 10): Collection
    {
        return $this->dashboardRepository->getRecentOrders($limit);
    }

    /**
     * Get products with low stock.
     */
    public function getLowStockProducts(int $threshold = 5): Collection
    {
        return $this->dashboardRepository->getLowStockProducts($threshold);
    }
}
