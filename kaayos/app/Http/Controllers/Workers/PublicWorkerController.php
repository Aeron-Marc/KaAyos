<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Models\User;

class PublicWorkerController extends Controller
{
    public function show(User $worker)
    {
        if ($worker->role !== 'worker') {
            abort(404);
        }

        $worker->load('workerProfile', 'workerDocuments');

        $reviews = $worker->reviewsReceived()->with('client')->latest()->get();

        return view('workers.public-show', [
            'worker'        => $worker,
            'workerProfile' => $worker->workerProfile,
            'documents'     => $worker->workerDocuments,
            'reviews'       => $reviews,
        ]);
    }
}
