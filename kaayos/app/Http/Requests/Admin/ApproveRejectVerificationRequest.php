<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApproveRejectVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        if ($this->routeIs('admin.verification.approve')) {
            return [
                'notes' => 'nullable|string|max:1000',
            ];
        }

        return [
            'rejection_reason' => 'required|string|max:1000',
            'notes'            => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'A rejection reason is required when rejecting an application.',
        ];
    }
}
