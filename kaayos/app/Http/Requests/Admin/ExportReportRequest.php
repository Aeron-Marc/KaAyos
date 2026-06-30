<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ExportReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'type'      => 'required|in:bookings,payments,verifications',
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
        ];
    }
}
