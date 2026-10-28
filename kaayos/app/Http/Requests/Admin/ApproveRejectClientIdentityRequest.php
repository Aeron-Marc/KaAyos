<?php

namespace App\Http\Requests\Admin;

use App\Models\ClientIdentityVerification;
use Illuminate\Foundation\Http\FormRequest;

class ApproveRejectClientIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $action = $this->route()->getActionMethod();

        if ($action === 'approve') {
            return [
                'notes' => 'nullable|string|max:1000',
            ];
        }

        if ($action === 'reject') {
            return [
                'rejection_reason' => 'required|string|max:1000',
                'notes'            => 'nullable|string|max:1000',
            ];
        }

        if ($action === 'requestResubmission') {
            return [
                'rejection_reason' => 'required|string|max:1000',
                'notes'            => 'nullable|string|max:1000',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'A reason is required when rejecting or requesting resubmission.',
        ];
    }
}
