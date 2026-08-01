<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CatalogController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = ServiceCategory::active()
            ->orderBy('name')
            ->get();

        $counts = User::where('role', 'worker')
            ->whereIn('service_category', $categories->pluck('name'))
            ->active()
            ->selectRaw('service_category, COUNT(*) AS cnt')
            ->groupBy('service_category')
            ->pluck('cnt', 'service_category');

        $result = $categories
            ->map(function ($c) use ($counts) {
                return [
                    'id'          => $c->slug,
                    'name'        => $c->name,
                    'icon'        => $c->icon,
                    'description' => $c->description,
                    'count'       => (int) ($counts[$c->name] ?? 0),
                ];
            })
            ->values();

        return response()->json(['success' => true, 'categories' => $result]);
    }

    public function workers(Request $request): JsonResponse
    {
        $query = User::where('role', 'worker')
            ->with('workerProfile')
            ->withCount('reviewsReceived')
            ->active();

        if ($category = $request->input('category')) {
            $query->where('service_category', $category);
        }

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            });
        }

        $workers = $query->take(50)->get();

        $completedCounts = Booking::whereIn('worker_id', $workers->pluck('id'))
            ->where('status', Booking::STATUS_COMPLETED)
            ->selectRaw('worker_id, COUNT(*) AS cnt')
            ->groupBy('worker_id')
            ->pluck('cnt', 'worker_id');

        $result = $workers
            ->map(fn ($u) => $this->workerPayload($u, (int) ($completedCounts[$u->id] ?? 0)))
            ->values();

        return response()->json(['success' => true, 'workers' => $result]);
    }

    public function worker(int $id): JsonResponse
    {
        $user = User::with('workerProfile.portfolios')
            ->withCount('reviewsReceived')
            ->where('role', 'worker')
            ->find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Worker not found.'], 404);
        }

        $completedJobs = Booking::where('worker_id', $user->id)
            ->where('status', Booking::STATUS_COMPLETED)
            ->count();

        $reviews = Review::where('worker_id', $user->id)
            ->with('client', 'booking')
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($r) {
                return [
                    'id'       => $r->id,
                    'rating'   => $r->rating,
                    'comment'  => $r->comment,
                    'client'   => $r->client?->name ?? 'Anonymous',
                    'service'  => $r->booking?->service_category,
                    'date'     => $r->created_at->format('M d, Y'),
                    'photo_url' => $r->photo_url,
                ];
            });

        $services = $user->providerServices()
            ->with('service.category')
            ->get()
            ->map(function ($ps) {
                return [
                    'id'           => $ps->id,
                    'name'         => $ps->service?->name,
                    'description'  => $ps->service?->description,
                    'category'     => $ps->service?->category?->name,
                    'base_price'   => $ps->service?->base_price,
                    'custom_price' => $ps->custom_price,
                    'price'        => $ps->custom_price ?? $ps->service?->base_price,
                ];
            });

        $payload = $this->workerPayload($user, $completedJobs);

        return response()->json([
            'success' => true,
            'worker'  => $payload,
            'reviews' => $reviews,
            'services'=> $services,
        ]);
    }

    protected function workerPayload(User $u, ?int $completedJobs = null): array
    {
        $profile = $u->workerProfile;

        return [
            'id'              => $u->id,
            'name'            => $u->name,
            'first_name'      => $u->first_name,
            'last_name'       => $u->last_name,
            'category'        => $u->service_category ?? 'General',
            'avatar_url'      => $u->avatar ? Storage::url($u->avatar) : null,
            'initials'        => strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1)),
            'rating'          => (float) ($profile?->average_rating ?? 0),
            'review_count'    => (int) ($u->reviews_received_count ?? 0),
            'hourly_rate'     => (float) ($profile?->hourly_rate ?? 0),
            'verified'        => (bool) ($profile?->government_id_verified ?? false),
            'skills'          => $profile?->skills ?? [],
            'experience_years'=> (int) ($profile?->years_of_experience ?? 0),
            'service_areas'   => $profile?->service_areas ?? [],
            'city'            => $u->city,
            'bio'             => $profile?->bio ?? '',
            'spoken_languages'=> $profile?->spoken_languages ?? [],
            'available_days'  => $profile?->available_days,
            'preferred_hours' => $profile?->preferred_hours,
            'available'       => $u->isActive(),
            'completed_jobs'  => $completedJobs ?? $u->bookingsAsWorker()->where('status', Booking::STATUS_COMPLETED)->count(),
        ];
    }
}
