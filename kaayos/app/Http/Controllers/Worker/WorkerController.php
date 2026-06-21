<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class WorkerController extends Controller
{
    public function dashboard(): View
    {
        return view('worker.dashboard.overview');
    }
}