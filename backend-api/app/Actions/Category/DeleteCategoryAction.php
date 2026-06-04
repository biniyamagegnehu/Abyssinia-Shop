<?php

namespace App\Actions\Category;

use App\Models\Category;
use App\Services\Category\CategoryService;

class DeleteCategoryAction
{
    public function __construct(
        protected CategoryService $service
    ) {}

    public function execute(Category $category): void
    {
        $this->service->delete($category);
    }
}
