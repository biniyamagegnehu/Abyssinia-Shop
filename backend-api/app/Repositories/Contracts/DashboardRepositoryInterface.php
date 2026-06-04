<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    /**
     * Get total sales from delivered orders.
     */
    public function getTotalSales(): string;

    /**
     * Get total number of orders.
     */
    public function getTotalOrders(): int;

    /**
     * Get total number of registered customers.
     */
    public function getTotalCustomers(): int;

    /**
     * Get total number of products.
     */
    public function getTotalProducts(): int;

    /**
     * Get average order value from delivered orders.
     */
    public function getAverageOrderValue(): string;

    /**
     * Get order count grouped by status.
     */
    public function getOrderStatistics(): array;

    /**
     * Get daily revenue for the current month (delivered orders).
     */
    public function getDailyRevenue(): Collection;

    /**
     * Get weekly revenue for the current year (delivered orders).
     */
    public function getWeeklyRevenue(): Collection;

    /**
     * Get monthly revenue for the current year (delivered orders).
     */
    public function getMonthlyRevenue(): Collection;

    /**
     * Get the most recent orders.
     */
    public function getRecentOrders(int $limit = 10): Collection;

    /**
     * Get products with stock at or below the given threshold.
     */
    public function getLowStockProducts(int $threshold = 5): Collection;
}
