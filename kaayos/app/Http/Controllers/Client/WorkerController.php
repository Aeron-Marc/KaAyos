<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class WorkerController extends Controller
{
    public function index(): View
    {
        return view('client.workers.search', [
            'categories' => \App\Support\ClientSampleData::categories(),
            'workers' => \App\Support\ClientSampleData::workers(),
            'notifications' => \App\Support\ClientSampleData::notifications(),
        ]);
    }
}