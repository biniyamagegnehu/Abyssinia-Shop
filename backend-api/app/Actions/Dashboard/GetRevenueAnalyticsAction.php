<?php

namespace App\Actions\Dashboard;

use App\DTOs\Dashboard\DashboardFilterDTO;
use App\Services\Dashboard\DashboardService;

class GetRevenueAnalyticsAction
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {
    }

    public function execute(DashboardFilterDTO $filter): array
    {
        return $this->dashboardService->getRevenueAnalytics($filter);
    }
}
