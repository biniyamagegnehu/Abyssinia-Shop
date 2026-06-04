<?php

namespace App\Http\Requests\Category;

use App\DTOs\Category\UpdateCategoryDTO;
use App\Enums\CategoryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorize via middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'status' => ['required', Rule::enum(CategoryStatus::class)],
        ];
    }

    public function toDTO(): UpdateCategoryDTO
    {
        return new UpdateCategoryDTO(
            name: $this->validated('name'),
            slug: $this->validated('slug'),
            status: CategoryStatus::from($this->validated('status')),
            description: $this->validated('description'),
            parent_id: $this->validated('parent_id'),
        );
    }
}
