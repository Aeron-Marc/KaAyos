<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssueCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'slug'             => ['required', 'string', 'max:255', Rule::unique('issue_categories', 'slug')->ignore($this->route('issueCategory'))],
            'description'      => 'nullable|string|max:2000',
            'icon'             => 'nullable|string|max:255',
            'service_category' => 'nullable|string|in:' . implode(',', \App\Models\IssueCategory::TRADES),
            'is_active'        => 'boolean',
        ];
    }
}
