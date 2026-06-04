<?php

namespace App\DTOs\Category;

use App\Enums\CategoryStatus;

readonly class CreateCategoryDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public CategoryStatus $status,
        public ?string $description = null,
        public ?int $parent_id = null,
    ) {}
}
