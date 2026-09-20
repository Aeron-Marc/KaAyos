<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServiceCategory;
use App\Support\TuyBarangays;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    private const PER_PAGE = 12;

    private const TRADE_SYNONYMS = [
        'Plumbing'   => ['plumber', 'plumbers', 'pipe', 'leak'],
        'Electrical' => ['electrician', 'electricians', 'wiring', 'circuit'],
        'Cleaning'   => ['cleaner', 'cleaners', 'house cleaning'],
        'Carpentry'  => ['carpenter', 'carpenters', 'wood'],
        'Painting'   => ['painter', 'painters'],
        'Aircon'     => ['aircon', 'air conditioning'],
    ];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'q'        => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $query    = $validated['q'] ?? null;
        $category = $validated['category'] ?? null;
        $location = $validated['location'] ?? null;

        $workersQuery = User::where('role', 'worker')
            ->with(['workerProfile.portfolios'])
            ->withCount('reviewsReceived')
            ->active()
            ->whereHas('workerProfile', function ($q) {
                $q->whereRaw("JSON_CONTAINS(availability->'$[*].active', 'true') = 1");
            });

        if ($category) {
            $workersQuery->where('service_category', 'LIKE', $category);
        }

        if ($query) {
            $categories = $this->categoriesForKeyword($query);

            $workersQuery->where(function ($q) use ($query, $categories) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%")
                  ->orWhere('service_category', 'LIKE', "%{$query}%");

                foreach ($categories as $categoryName) {
                    $q->orWhere('service_category', 'LIKE', "%{$categoryName}%");
                }
            });
        }

        $locationBarangay = null;
        $locationNotice   = null;

        if ($location) {
            $locationBarangay = TuyBarangays::parseLocation($location);

            if ($locationBarangay === null) {
                $locationNotice = "We couldn't find a barangay matching “{$location}”. Showing all matching workers instead.";
            }
        }

        $lat = null;
        $lng = null;
        if ($locationBarangay) {
            [$lat, $lng] = TuyBarangays::pointForStatic($locationBarangay);
        }

        $rows = $workersQuery->get()->map(function (User $u) use ($lat, $lng) {
            $distance = null;

            if ($lat !== null && $lng !== null) {
                $wLat = $u->workerProfile?->current_latitude;
                $wLng = $u->workerProfile?->current_longitude;

                if ($wLat !== null && $wLng !== null) {
                    $distance = TuyBarangays::distanceKm($lat, $lng, (float) $wLat, (float) $wLng);
                }
            }

            return ['worker' => $u, 'distance' => $distance];
        });

        if ($lat !== null && $lng !== null) {
            $rows = $rows->filter(function (array $row) {
                $radius = $row['worker']->workerProfile?->service_radius_km;

                if ($radius !== null && $row['distance'] !== null && $row['distance'] > $radius) {
                    return false;
                }

                return true;
            });

            $rows = $rows->sortBy(fn (array $row) => $row['distance'] ?? PHP_FLOAT_MAX);
        }

        $rows = $rows->values();

        $page    = Paginator::resolveCurrentPage();
        $perPage = self::PER_PAGE;

        $workers = new LengthAwarePaginator(
            $rows->forPage($page, $perPage),
            $rows->count(),
            $perPage,
            $page,
            [
                'path'  => Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $workers->through(fn (array $row) => $this->formatWorker($row['worker'], $row['distance']));

        $categories = ServiceCategory::active()->get();

        return view('search.index', compact(
            'workers',
            'categories',
            'query',
            'category',
            'location',
            'locationBarangay',
            'locationNotice',
        ));
    }

    protected function categoriesForKeyword(?string $query): array
    {
        if (!$query) {
            return [];
        }

        $needle = strtolower(trim($query));

        $matches = [];

        foreach (self::TRADE_SYNONYMS as $category => $aliases) {
            if (strtolower($category) === $needle || in_array($needle, $aliases, true)) {
                $matches[] = $category;
            }
        }

        return $matches;
    }

    protected function formatWorker(User $u, ?float $distance): array
    {
        return [
            'id'          => $u->id,
            'name'        => $u->name,
            'category'    => $u->service_category ?? 'General',
            'avatar'      => $u->avatar ? Storage::url($u->avatar) : null,
            'initials'    => strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1)),
            'rating'      => $u->workerProfile?->average_rating ?? 0,
            'reviews'     => $u->reviews_received_count,
            'distance'    => $u->residence,
            'distance_km' => $distance !== null ? round($distance, 1) : null,
            'price'       => $u->workerProfile?->hourly_rate ?? 0,
            'verified'    => $u->workerProfile?->government_id_verified ?? false,
            'skills'      => $u->workerProfile?->skills ?? [],
            'works'       => $u->workerProfile?->portfolios?->take(3)->map(fn($p) => [
                'photo'   => $p->photo_path ? Storage::url($p->photo_path) : null,
                'caption' => $p->caption,
            ])->toArray() ?? [],
        ];
    }
}