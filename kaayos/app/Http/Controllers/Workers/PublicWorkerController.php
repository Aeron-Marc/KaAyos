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

        $worker->load('workerProfile.portfolios', 'workerDocuments');

        $reviews = $worker->reviewsReceived()->with('client')->latest()->get();
        $reviewCount = $reviews->count();
        $averageRating = $reviewCount > 0
            ? (float) round((float) $reviews->avg('rating'), 1)
            : 0.0;

        return view('workers.public-show', [
            'worker'        => $worker,
            'workerProfile' => $worker->workerProfile,
            'documents'     => $worker->workerDocuments,
            'reviews'       => $reviews,
            'reviewCount'   => $reviewCount,
            'averageRating' => $averageRating,
        ]);
    }
}
