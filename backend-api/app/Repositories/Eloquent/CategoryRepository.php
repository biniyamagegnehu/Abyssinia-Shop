<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(protected Category $model)
    {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->withCount('children')->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Category
    {
        return $this->model->withCount('children')->find($id);
    }

    public function create(array $data): Category
    {
        return $this->model->create($data);
    }

    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = $this->model->where('slug', $slug);
        
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        
        return $query->exists();
    }

    public function hasChildren(Category $category): bool
    {
        return $category->children()->exists();
    }

    public function hasProducts(Category $category): bool
    {
        return $category->products()->exists();
    }
}
