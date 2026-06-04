<?php

namespace App\Http\Controllers\Api\V1\Category;

use App\Actions\Category\CreateCategoryAction;
use App\Actions\Category\DeleteCategoryAction;
use App\Actions\Category\UpdateCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Traits\HasApiResponses;

class CategoryController extends Controller
{
    use HasApiResponses;

    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = $this->repository->paginate();
        
        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request, CreateCategoryAction $action)
    {
        $category = $action->execute($request->toDTO());

        return $this->successResponse(
            new CategoryResource($category),
            'Category created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category = $this->repository->findById($category->id);
        
        return $this->successResponse(
            new CategoryResource($category),
            'Category retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategoryAction $action)
    {
        $updatedCategory = $action->execute($category, $request->toDTO());

        return $this->successResponse(
            new CategoryResource($updatedCategory),
            'Category updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category, DeleteCategoryAction $action)
    {
        $action->execute($category);

        return $this->successResponse(
            null,
            'Category deleted successfully'
        );
    }
}
