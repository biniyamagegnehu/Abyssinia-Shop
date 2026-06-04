<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Actions\Dashboard\GetDashboardSummaryAction;
use App\Actions\Dashboard\GetRevenueAnalyticsAction;
use App\DTOs\Dashboard\DashboardFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\DashboardSummaryResource;
use App\Http\Resources\Dashboard\LowStockProductResource;
use App\Http\Resources\Dashboard\RecentOrderResource;
use App\Http\Resources\Dashboard\RevenueAnalyticsResource;
use App\Services\Dashboard\DashboardService;
use App\Traits\HasApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use HasApiResponses;

    public function __construct(
        private readonly GetDashboardSummaryAction $summaryAction,
        private readonly GetRevenueAnalyticsAction $revenueAction,
        private readonly DashboardService $dashboardService
    ) {
    }

    /**
     * GET /admin/dashboard/summary
     */
    public function summary(): JsonResponse
    {
        $data = $this->summaryAction->execute();

        return $this->successResponse(
            new DashboardSummaryResource($data),
            'Dashboard summary retrieved successfully.'
        );
    }

    /**
     * GET /admin/dashboard/orders
     */
    public function orders(): JsonResponse
    {
        $stats = $this->dashboardService->getOrderStatistics();

        return $this->successResponse($stats, 'Order statistics retrieved successfully.');
    }

    /**
     * GET /admin/dashboard/revenue?period=monthly
     */
    public function revenue(Request $request): JsonResponse
    {
        $filter = DashboardFilterDTO::fromRequest($request->all());

        if (!$filter->isValidPeriod()) {
            return $this->errorResponse('Invalid revenue period. Use: daily, weekly, or monthly.', 400);
        }

        $data = $this->revenueAction->execute($filter);

        return $this->successResponse(
            new RevenueAnalyticsResource($data),
            'Revenue analytics retrieved successfully.'
        );
    }

    /**
     * GET /admin/dashboard/recent-orders
     */
    public function recentOrders(): JsonResponse
    {
        $orders = $this->dashboardService->getRecentOrders();

        return $this->successResponse(
            RecentOrderResource::collection($orders),
            'Recent orders retrieved successfully.'
        );
    }

    /**
     * GET /admin/dashboard/low-stock?threshold=5
     */
    public function lowStock(Request $request): JsonResponse
    {
        $threshold = (int) $request->input('threshold', 5);
        $products = $this->dashboardService->getLowStockProducts($threshold);

        return $this->successResponse(
            LowStockProductResource::collection($products),
            'Low stock products retrieved successfully.'
        );
    }
}
