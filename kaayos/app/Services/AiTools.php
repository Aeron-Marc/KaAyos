<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\TuyBarangays;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiTools
{
    protected ?User $user = null;

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getDefinitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_categories',
                    'description' => 'Get all service categories with their service count and list of available services',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object)[],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_services',
                    'description' => 'Get all services under a specific category',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'category_name' => [
                                'type' => 'string',
                                'description' => 'The name of the category (e.g., Plumbing, Electrical, Cleaning)',
                            ],
                        ],
                        'required' => ['category_name'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_workers',
                    'description' => 'Search for workers by category, name keyword, or get all workers. Workers are sorted by proximity when a location is provided. Each result includes distance_km, barangay, and service_radius_km when location is given.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'category' => [
                                'type' => 'string',
                                'description' => 'Filter by service category (e.g., Plumbing, Electrical). Leave empty for all.',
                            ],
                            'keyword' => [
                                'type' => 'string',
                                'description' => 'Search by worker name or skill keyword. Leave empty for all.',
                            ],
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'Maximum number of results to return (default 10)',
                            ],
                            'barangay' => [
                                'type' => 'string',
                                'description' => "The client's barangay name (e.g., 'Luna', 'Bolboc'). Used to compute distance to each worker. If not provided, results are not sorted by proximity.",
                            ],
                            'latitude' => [
                                'type' => 'number',
                                'description' => "The client's latitude. If barangay is not provided, use this with longitude to specify the client's location.",
                            ],
                            'longitude' => [
                                'type' => 'number',
                                'description' => "The client's longitude. If barangay is not provided, use this with latitude to specify the client's location.",
                            ],
                            'max_distance_km' => [
                                'type' => 'integer',
                                'description' => 'Maximum distance in km to filter workers. Workers beyond this distance are excluded.',
                            ],
                        ],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_worker_detail',
                    'description' => 'Get detailed information about a specific worker including their rating, reviews, skills, and services',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'worker_id' => [
                                'type' => 'integer',
                                'description' => 'The worker user ID',
                            ],
                            'worker_name' => [
                                'type' => 'string',
                                'description' => 'The worker name (used if ID is not known)',
                            ],
                        ],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'check_worker_bookings',
                    'description' => 'Check if a worker has existing bookings on a given date (for availability)',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'worker_id' => [
                                'type' => 'integer',
                                'description' => 'The worker user ID',
                            ],
                            'date' => [
                                'type' => 'string',
                                'description' => 'The date to check (YYYY-MM-DD format)',
                            ],
                        ],
                        'required' => ['worker_id', 'date'],
                    ],
                ],
            ],
        ];
    }

    public function getCategories(): array
    {
        $cats = ServiceCategory::withCount('services')->where('is_active', true)->orderBy('name')->get();
        return $cats->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'description' => $c->description,
            'service_count' => $c->services_count,
        ])->toArray();
    }

    public function getServices(string $categoryName): array
    {
        $cat = ServiceCategory::where('name', 'like', $categoryName)->orWhere('slug', 'like', $categoryName)->first();
        if (!$cat) {
            return [];
        }
        return $cat->services()->where('is_active', true)->orderBy('name')->get()->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'description' => $s->description,
            'base_price' => (float) ($s->base_price ?? 0),
        ])->toArray();
    }

    public function searchWorkers(
        ?string $category = null,
        ?string $keyword = null,
        int $limit = 10,
        ?string $barangay = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?int $maxDistanceKm = null,
    ): array {
        $query = User::where('role', 'worker')
            ->with('workerProfile')
            ->whereHas('workerProfile');

        if ($category) {
            $query->where('service_category', 'like', "%{$category}%");
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('first_name', 'like', "%{$keyword}%")
                  ->orWhere('last_name', 'like', "%{$keyword}%");
            });
        }

        $workers = $query->orderBy('name')->limit($limit * 3)->get();

        if ($latitude !== null && $longitude !== null) {
            $results = [];
            foreach ($workers as $u) {
                $wLat = $u->workerProfile?->current_latitude;
                $wLng = $u->workerProfile?->current_longitude;

                if ($wLat === null || $wLng === null) {
                    $distanceKm = null;
                } else {
                    $distanceKm = $this->haversineKm($latitude, $longitude, (float) $wLat, (float) $wLng);
                }

                $serviceRadius = $u->workerProfile?->service_radius_km ?? null;
                if ($maxDistanceKm !== null && $distanceKm !== null && $distanceKm > $maxDistanceKm) {
                    continue;
                }
                if ($serviceRadius !== null && $distanceKm !== null && $distanceKm > $serviceRadius) {
                    continue;
                }

                $results[] = [
                    'worker'      => $u,
                    'distance_km' => $distanceKm !== null ? round($distanceKm, 2) : null,
                ];
            }

            usort($results, fn($a, $b) => ($a['distance_km'] ?? 9999) <=> ($b['distance_km'] ?? 9999));
            $results = array_slice($results, 0, $limit);

            return array_map(fn($r) => $this->formatWorker($r['worker'], $r['distance_km']), $results);
        }

        if ($barangay !== null) {
            [$lat, $lng] = TuyBarangays::pointForStatic($barangay);
            return $this->searchWorkers($category, $keyword, $limit, null, $lat, $lng, $maxDistanceKm);
        }

        return $workers->take($limit)->map(fn($u) => $this->formatWorker($u, null))->toArray();
    }

    protected function formatWorker(User $u, ?float $distanceKm): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'category' => $u->service_category,
            'rating' => (float) ($u->workerProfile?->average_rating ?? 0),
            'hourly_rate' => (float) ($u->workerProfile?->hourly_rate ?? 0),
            'verified' => (bool) ($u->workerProfile?->government_id_verified ?? false),
            'years_experience' => $u->workerProfile?->years_of_experience ?? 0,
            'skills' => $u->workerProfile?->skills ?? [],
            'city' => $u->city,
            'barangay' => $u->barangay,
            'distance_km' => $distanceKm,
            'service_radius_km' => $u->workerProfile?->service_radius_km,
        ];
    }

    protected function callSearchWorkers(array $args): string
    {
        $barangay = $args['barangay'] ?? null;
        $lat = isset($args['latitude']) ? (float) $args['latitude'] : null;
        $lng = isset($args['longitude']) ? (float) $args['longitude'] : null;
        $maxDist = isset($args['max_distance_km']) ? (int) $args['max_distance_km'] : null;

        if ($barangay === null && $lat === null && $this->user?->barangay) {
            $barangay = $this->user->barangay;
        }

        return json_encode($this->searchWorkers(
            $args['category'] ?? null,
            $args['keyword'] ?? null,
            (int) ($args['limit'] ?? 10),
            $barangay,
            $lat,
            $lng,
            $maxDist,
        ));
    }

    protected function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthR = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return 2 * $earthR * asin(sqrt($a));
    }

    public function getWorkerDetail(?int $workerId = null, ?string $workerName = null): ?array
    {
        $query = User::where('role', 'worker')->with([
            'workerProfile', 'workerDocuments', 'reviewsReceived.client',
            'providerServices.service',
        ]);

        if ($workerId) {
            $query->where('id', $workerId);
        } elseif ($workerName) {
            $query->where('name', 'like', "%{$workerName}%");
        } else {
            return null;
        }

        $u = $query->first();
        if (!$u) return null;

        $totalJobs = $u->bookingsAsWorker()->count();
        $completedJobs = $u->bookingsAsWorker()->where('status', Booking::STATUS_COMPLETED)->count();

        return [
            'id' => $u->id,
            'name' => $u->name,
            'category' => $u->service_category,
            'bio' => $u->workerProfile?->bio,
            'rating' => (float) ($u->workerProfile?->average_rating ?? 0),
            'hourly_rate' => (float) ($u->workerProfile?->hourly_rate ?? 0),
            'verified' => (bool) ($u->workerProfile?->government_id_verified ?? false),
            'years_experience' => $u->workerProfile?->years_of_experience ?? 0,
            'skills' => $u->workerProfile?->skills ?? [],
            'languages' => $u->workerProfile?->spoken_languages ?? [],
            'available_days' => $u->workerProfile?->available_days ?? '',
            'total_jobs' => $totalJobs,
            'completed_jobs' => $completedJobs,
            'services' => $u->providerServices->map(fn($ps) => [
                'name' => $ps->service?->name,
                'price' => $ps->custom_price ?? $ps->service?->base_price,
                'available' => $ps->is_available,
            ]),
            'reviews' => $u->reviewsReceived->map(fn($r) => [
                'rating' => $r->rating,
                'comment' => $r->comment,
                'client_name' => $r->client?->name ?? 'Anonymous',
                'date' => $r->created_at?->toDateString(),
            ]),
            'documents_verified' => $u->workerDocuments->where('status', 'verified')->count(),
        ];
    }

    public function checkWorkerBookings(int $workerId, string $date): array
    {
        $bookings = Booking::where('worker_id', $workerId)
            ->whereDate('scheduled_at', $date)
            ->whereIn('status', [Booking::STATUS_NEW, Booking::STATUS_ACCEPTED, Booking::STATUS_EN_ROUTE, Booking::STATUS_IN_PROGRESS])
            ->get();

        return [
            'worker_id' => $workerId,
            'date' => $date,
            'has_bookings' => $bookings->isNotEmpty(),
            'booking_count' => $bookings->count(),
            'bookings' => $bookings->map(fn($b) => [
                'time' => $b->scheduled_at?->format('H:i'),
                'status' => $b->status,
                'service' => $b->service_category,
            ]),
        ];
    }

    public function execute(string $functionName, array $args): string
    {
        try {
            return match ($functionName) {
                'get_categories' => json_encode($this->getCategories()),
                'get_services' => json_encode($this->getServices($args['category_name'] ?? '')),
                'search_workers' => $this->callSearchWorkers($args),
                'get_worker_detail' => json_encode($this->getWorkerDetail(
                    $args['worker_id'] ?? null,
                    $args['worker_name'] ?? null
                )),
                'check_worker_bookings' => json_encode($this->checkWorkerBookings(
                    (int) ($args['worker_id'] ?? 0),
                    $args['date'] ?? ''
                )),
                default => json_encode(['error' => "Unknown function: {$functionName}"]),
            };
        } catch (\Exception $e) {
            Log::error("AI tool error: {$functionName}", ['error' => $e->getMessage()]);
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}
