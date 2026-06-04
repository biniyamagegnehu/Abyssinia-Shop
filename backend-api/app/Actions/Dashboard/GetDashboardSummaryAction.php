<?php

namespace App\Actions\Dashboard;

use App\Services\Dashboard\DashboardService;

class GetDashboardSummaryAction
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {
    }

    public function execute(): array
    {
        return $this->dashboardService->getSummary();
    }
}
