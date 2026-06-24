<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Support\WorkerSampleData;
use Illuminate\View\View;

class WorkerController extends Controller
{
    protected function shared(): array
    {
        return [
            'stats' => WorkerSampleData::stats(),
            'jobRequests' => WorkerSampleData::jobRequests(),
            'schedule' => WorkerSampleData::schedule(),
            'notifications' => WorkerSampleData::notifications(),
            'conversations' => WorkerSampleData::conversations(),
            'earnings' => WorkerSampleData::earnings(),
            'documents' => WorkerSampleData::documents(),
        ];
    }

    public function dashboard(): View
    {
        return view('worker.dashboard.overview', $this->shared());
    }

    public function notifications(): View
    {
        return view('worker.dashboard.notifications', $this->shared());
    }

    public function jobs(): View
    {
        return view('worker.jobs.index', $this->shared());
    }

    public function schedule(): View
    {
        return view('worker.schedule.index', $this->shared());
    }

    public function messages(): View
    {
        return view('worker.messages.index', $this->shared());
    }

    public function earnings(): View
    {
        return view('worker.earnings.index', $this->shared());
    }

    public function profile(): View
    {
        return view('worker.profile.index', $this->shared());
    }

    public function documents(): View
    {
        return view('worker.documents.index', $this->shared());
    }
}
