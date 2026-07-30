<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AiTools
{
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
                    'description' => 'Search for workers by category, name keyword, or get all workers',
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

    public function searchWorkers(?string $category = null, ?string $keyword = null, int $limit = 10): array
    {
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

        $workers = $query->orderBy('name')->limit($limit)->get();

        return $workers->map(fn($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'category' => $u->service_category,
            'rating' => (float) ($u->workerProfile?->average_rating ?? 0),
            'hourly_rate' => (float) ($u->workerProfile?->hourly_rate ?? 0),
            'verified' => (bool) ($u->workerProfile?->government_id_verified ?? false),
            'years_experience' => $u->workerProfile?->years_of_experience ?? 0,
            'skills' => $u->workerProfile?->skills ?? [],
            'city' => $u->city,
        ])->toArray();
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
                'search_workers' => json_encode($this->searchWorkers(
                    $args['category'] ?? null,
                    $args['keyword'] ?? null,
                    $args['limit'] ?? 10
                )),
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
