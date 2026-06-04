<?php

namespace App\Actions\Category;

use App\DTOs\Category\UpdateCategoryDTO;
use App\Models\Category;
use App\Services\Category\CategoryService;

class UpdateCategoryAction
{
    public function __construct(
        protected CategoryService $service
    ) {}

    public function execute(Category $category, UpdateCategoryDTO $dto): Category
    {
        return $this->service->update($category, $dto);
    }
}
