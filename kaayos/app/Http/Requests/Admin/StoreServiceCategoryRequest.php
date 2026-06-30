<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:service_categories,slug',
            'description' => 'nullable|string|max:2000',
            'icon'        => 'nullable|string|max:255',
        ];
    }
}
