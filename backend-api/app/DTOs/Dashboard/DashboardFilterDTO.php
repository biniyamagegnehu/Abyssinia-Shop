<?php

namespace App\DTOs\Dashboard;

class DashboardFilterDTO
{
    public function __construct(
        public readonly string $period = 'monthly',
        public readonly int $threshold = 5,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            period: $data['period'] ?? 'monthly',
            threshold: (int) ($data['threshold'] ?? 5),
        );
    }

    public function isValidPeriod(): bool
    {
        return in_array($this->period, ['daily', 'weekly', 'monthly']);
    }
}
