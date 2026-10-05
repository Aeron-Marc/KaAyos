<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\User;

class PublicWorkerController extends Controller
{
    public function show(User $worker)
    {
        if ($worker->role !== 'worker') {
            abort(404);
        }

        return $this->renderPublicProfile($worker);
    }

    public function showByCode(string $code)
    {
        $worker = User::where('share_code', strtoupper($code))
            ->where('role', 'worker')
            ->firstOrFail();

        return $this->renderPublicProfile($worker);
    }

    protected function renderPublicProfile(User $worker)
    {
        $worker->load('workerProfile.portfolios', 'workerDocuments');

        $reviews = $worker->reviewsReceived()->with('client')->latest()->get();
        $reviewCount = $reviews->count();
        $averageRating = $reviewCount > 0
            ? (float) round((float) $reviews->avg('rating'), 1)
            : 0.0;

        return view('worker.public-show', [
            'worker'        => $worker,
            'workerProfile' => $worker->workerProfile,
            'documents'     => $worker->workerDocuments,
            'reviews'       => $reviews,
            'reviewCount'   => $reviewCount,
            'averageRating' => $averageRating,
        ]);
    }
}
