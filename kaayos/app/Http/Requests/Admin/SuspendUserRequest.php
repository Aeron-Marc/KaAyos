<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SuspendUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'A suspension reason is required.',
        ];
    }
}
