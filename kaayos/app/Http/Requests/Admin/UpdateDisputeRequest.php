<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'status'           => ['required', Rule::in(['open', 'under_review', 'resolved'])],
            'resolution_notes' => 'required_if:status,resolved|nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'resolution_notes.required_if' => 'Resolution notes are required when resolving a dispute.',
        ];
    }
}
