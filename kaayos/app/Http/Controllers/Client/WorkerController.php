<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkerController extends Controller
{
    protected function getCategories(): array
    {
        return ServiceCategory::orderBy('name')->get()->map(fn ($c) => [
            'id'    => strtolower($c->slug ?? $c->name),
            'name'  => $c->name,
            'icon'  => $c->icon ?? 'fa-wrench',
            'color' => 'ic-b',
        ])->toArray();
    }

    public function index(Request $request): View
    {
        $query = User::where('role', 'worker')
            ->with('workerProfile')
            ->active();

        if ($category = $request->query('category')) {
            $query->where('service_category', 'LIKE', "%{$category}%");
        }

        if ($q = $request->query('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('service_category', 'LIKE', "%{$q}%");
            });
        }

        $workers = $query->get()->map(fn ($u) => [
            'id'       => $u->id,
            'name'     => $u->name,
            'category' => $u->service_category ?? 'General',
            'avatar'   => $u->avatar ? \Storage::url($u->avatar) : null,
            'initials' => strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1)),
            'rating'   => $u->workerProfile?->average_rating ?? 0,
            'reviews'  => $u->reviewsReceived()->count(),
            'distance' => 'Tuy, Batangas',
            'price'    => $u->workerProfile?->hourly_rate ?? 0,
            'verified' => $u->workerProfile?->govt_id_verified ?? false,
            'skills'   => $u->workerProfile?->skills ?? [],
        ])->toArray();

        return view('client.workers.search', [
            'categories' => $this->getCategories(),
            'workers'    => $workers,
            'notifications' => [],
        ]);
    }

    public function show(User $worker): View
    {
        if ($worker->role !== 'worker') {
            abort(404);
        }

        $worker->load('workerProfile', 'workerDocuments');

        $reviews = $worker->reviewsReceived()->with('client')->latest()->get();

        $userId = auth()->id();
        $existingBooking = auth()->user()->bookingsAsClient()
            ->where('worker_id', $worker->id)
            ->latest()
            ->first();

        $bookingIdForMessage = null;
        if ($existingBooking) {
            $msgBookingId = \App\Models\Message::where('booking_id', $existingBooking->id)
                ->where(function ($q) use ($userId) {
                    $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
                })
                ->value('booking_id');
            $bookingIdForMessage = $msgBookingId ?? $existingBooking->id;
        }

        return view('client.workers.show', [
            'worker'              => $worker,
            'workerProfile'       => $worker->workerProfile,
            'documents'           => $worker->workerDocuments,
            'reviews'             => $reviews,
            'bookingIdForMessage' => $bookingIdForMessage,
        ]);
    }
}
