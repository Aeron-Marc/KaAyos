<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\WorkerDocuments;
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

    protected function getAreas(): array
    {
        return User::where('role', 'worker')
            ->whereNotNull('barangay')
            ->where('barangay', '!=', '')
            ->distinct()
            ->pluck('barangay')
            ->sort()
            ->values()
            ->toArray();
    }

    protected function sortWorkers(array $workers, string $sort): array
    {
        $sorters = [
            'rating'     => fn ($a, $b) => $b['rating']  <=> $a['rating']  ?: $b['reviews'] <=> $a['reviews'],
            'price_low'  => fn ($a, $b) => $a['price']   <=> $b['price']   ?: $b['rating']  <=> $a['rating'],
            'price_high' => fn ($a, $b) => $b['price']   <=> $a['price']   ?: $b['rating']  <=> $a['rating'],
            'reviews'    => fn ($a, $b) => $b['reviews'] <=> $a['reviews'] ?: $b['rating']  <=> $a['rating'],
            'exp'        => fn ($a, $b) => $b['experience'] <=> $a['experience'] ?: $b['rating'] <=> $a['rating'],
        ];

        usort($workers, $sorters[$sort] ?? $sorters['rating']);
        return $workers;
    }

    protected function getDocuments(User $worker): array
    {
        $types = WorkerDocuments::types();
        $userDocs = $worker->workerDocuments->keyBy('document_type');

        return array_map(function ($type) use ($userDocs) {
            $userDoc = $userDocs->get($type['name']);

            return [
                'name'        => $type['name'],
                'description' => $type['description'],
                'icon'        => $type['icon'],
                'status'      => $userDoc
                    ? ($userDoc->status === 'verified' ? 'Verified'
                        : ($userDoc->status === 'pending' ? 'Pending' : 'Not Submitted'))
                    : 'Not Submitted',
                'file'        => $userDoc?->file_path
                    ? basename($userDoc->file_path)
                    : null,
                'id'          => $userDoc?->id,
            ];
        }, $types);
    }

    public function index(Request $request): View
    {
        $query = User::where('role', 'worker')
            ->with('workerProfile.portfolios')
            ->withCount('reviewsReceived')
            ->active()
            ->whereHas('workerProfile', function ($q) {
                $q->whereRaw("JSON_CONTAINS(availability->'$[*].active', 'true') = 1");
            });

        if ($category = $request->query('category')) {
            $category = str_replace('-', ' ', $category);
            $query->where('service_category', 'LIKE', $category);
        }

        if ($q = $request->query('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('service_category', 'LIKE', "%{$q}%");
            });
        }

        if ($area = $request->query('area')) {
            $query->where('barangay', $area);
        }

        $workers = $query->get()->map(fn ($u) => [
            'id'               => $u->id,
            'name'             => $u->name,
            'category'         => $u->service_category ?? 'General',
            'avatar'           => $u->avatar ? \Storage::url($u->avatar) : null,
            'initials'         => strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1)),
            'rating'           => $u->workerProfile?->average_rating ?? 0,
            'reviews'          => $u->reviews_received_count,
            'distance'         => $u->residence,
            'price'            => (float) ($u->workerProfile?->hourly_rate ?? 0),
            'verified'         => $u->workerProfile?->government_id_verified ?? false,
            'skills'           => $u->workerProfile?->skills ?? [],
            'experience'       => (int) ($u->workerProfile?->years_of_experience ?? 0),
            'profile_complete' => $u->workerProfile && (
                $u->workerProfile->bio
                || !empty($u->workerProfile->skills)
                || !empty($u->workerProfile->spoken_languages)
                || ($u->workerProfile->portfolios && $u->workerProfile->portfolios->count() > 0)
            ),
        ])->toArray();

        $sort = $request->query('sort', 'rating');
        $workers = $this->sortWorkers($workers, $sort);

        return view('client.workers.search', [
            'categories'     => $this->getCategories(),
            'areas'          => $this->getAreas(),
            'workers'        => $workers,
            'notifications'  => [],
            'filters'        => [
                'q'        => $request->query('q', ''),
                'category' => $request->query('category', ''),
                'area'     => $request->query('area', ''),
                'sort'     => $sort,
            ],
        ]);
    }

    public function show(User $worker): View
    {
        if ($worker->role !== 'worker') {
            abort(404);
        }

        $worker->load([
            'workerProfile.portfolios',
            'workerDocuments',
            'providerServices.service',
        ]);

        $reviews = $worker->reviewsReceived()->with('client')->latest()->get();

        $existingBooking = auth()->user()->bookingsAsClient()
            ->where('worker_id', $worker->id)
            ->latest()
            ->first();

        $workerServices = $worker->providerServices
            ->filter(fn ($ps) => $ps->service && $ps->is_available)
            ->values();

        return view('client.workers.show', [
            'worker'              => $worker,
            'workerProfile'       => $worker->workerProfile,
            'documents'           => $this->getDocuments($worker),
            'reviews'             => $reviews,
            'canMessage'          => (bool) $existingBooking,
            'workerServices'      => $workerServices,
        ]);
    }
}
