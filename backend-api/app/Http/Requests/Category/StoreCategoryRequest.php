<?php

namespace App\Http\Requests\Category;

use App\DTOs\Category\CreateCategoryDTO;
use App\Enums\CategoryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'status' => ['required', Rule::enum(CategoryStatus::class)],
        ];
    }

    public function toDTO(): CreateCategoryDTO
    {
        return new CreateCategoryDTO(
            name: $this->validated('name'),
            slug: $this->validated('slug'),
            status: CategoryStatus::from($this->validated('status')),
            description: $this->validated('description'),
            parent_id: $this->validated('parent_id'),
        );
    }
}
