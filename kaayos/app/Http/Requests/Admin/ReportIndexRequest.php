<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReportIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'type' => 'nullable|in:bookings,revenue,users,worker_performance,verifications,disputes,service_popularity,reviews',
            'preset' => 'nullable|in:today,7d,30d,this_month,last_month,this_year,all',
            'date_from' => 'nullable|date|required_with:date_to',
            'date_to' => 'nullable|date|required_with:date_from|after_or_equal:date_from',
            'sort_col' => 'nullable|integer|min:0',
            'sort_dir' => 'nullable|in:asc,desc',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.reports.index', array_filter(['type' => $this->input('type')]));
    }
}
