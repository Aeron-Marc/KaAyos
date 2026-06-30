<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'slug'        => ['required', 'string', 'max:255', Rule::unique('service_categories', 'slug')->ignore($this->route('category'))],
            'description' => 'nullable|string|max:2000',
            'icon'        => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ];
    }
}
