<?php

namespace App\Services\Category;

use App\DTOs\Category\CreateCategoryDTO;
use App\DTOs\Category\UpdateCategoryDTO;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {}

    public function create(CreateCategoryDTO $dto): Category
    {
        if ($this->repository->slugExists($dto->slug)) {
            throw ValidationException::withMessages([
                'slug' => ['The slug has already been taken.'],
            ]);
        }

        // Validate parent_id existence is already done in form request, 
        // but if parent is provided, we could check if it exists.

        return $this->repository->create([
            'name' => $dto->name,
            'slug' => $dto->slug,
            'status' => $dto->status,
            'description' => $dto->description,
            'parent_id' => $dto->parent_id,
        ]);
    }

    public function update(Category $category, UpdateCategoryDTO $dto): Category
    {
        if ($this->repository->slugExists($dto->slug, $category->id)) {
            throw ValidationException::withMessages([
                'slug' => ['The slug has already been taken.'],
            ]);
        }

        if ($dto->parent_id !== null) {
            if ($category->id === $dto->parent_id) {
                throw ValidationException::withMessages([
                    'parent_id' => ['A category cannot be its own parent.'],
                ]);
            }

            if ($this->isDescendant($category, $dto->parent_id)) {
                throw ValidationException::withMessages([
                    'parent_id' => ['A category cannot be assigned to one of its descendants.'],
                ]);
            }
        }

        $this->repository->update($category, [
            'name' => $dto->name,
            'slug' => $dto->slug,
            'status' => $dto->status,
            'description' => $dto->description,
            'parent_id' => $dto->parent_id,
        ]);

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        if ($this->repository->hasChildren($category)) {
            throw ValidationException::withMessages([
                'category' => ['Category has children and cannot be deleted.'],
            ]);
        }

        if ($this->repository->hasProducts($category)) {
            throw ValidationException::withMessages([
                'category' => ['Category has products and cannot be deleted.'],
            ]);
        }

        $this->repository->delete($category);
    }

    /**
     * Check if a potential parent ID is actually a descendant of the category.
     */
    protected function isDescendant(Category $category, int $potentialParentId): bool
    {
        $potentialParent = $this->repository->findById($potentialParentId);

        while ($potentialParent !== null && $potentialParent->parent_id !== null) {
            if ($potentialParent->parent_id === $category->id) {
                return true;
            }
            $potentialParent = $this->repository->findById($potentialParent->parent_id);
        }

        return false;
    }
}
