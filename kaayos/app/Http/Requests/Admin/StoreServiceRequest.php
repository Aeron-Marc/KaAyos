<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:service_categories,id',
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:services,slug',
            'description' => 'nullable|string|max:2000',
            'base_price'  => 'nullable|numeric|min:0',
        ];
    }
}
