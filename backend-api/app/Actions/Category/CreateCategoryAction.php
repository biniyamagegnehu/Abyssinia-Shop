<?php

namespace App\Actions\Category;

use App\DTOs\Category\CreateCategoryDTO;
use App\Models\Category;
use App\Services\Category\CategoryService;

class CreateCategoryAction
{
    public function __construct(
        protected CategoryService $service
    ) {}

    public function execute(CreateCategoryDTO $dto): Category
    {
        return $this->service->create($dto);
    }
}
