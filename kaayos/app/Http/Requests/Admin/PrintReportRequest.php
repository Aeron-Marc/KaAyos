<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PrintReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:bookings,revenue,users,worker_performance,verifications,disputes,service_popularity,reviews',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ];
    }
}