<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Support\TuyBarangays;
use App\Support\TuyRoadNodes;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoTravelService
{
    /**
     * Road tortuosity factor for Tuy, Batangas rural and municipal roads.
     * Road distance is typically ~25% longer than straight-line Haversine.
     */
    public const ROAD_FACTOR = 1.25;

    /**
     * Average municipal transit speed (tricycle/motorcycle) in km/h.
     */
    public const AVG_SPEED_KMH = 30.0;

    /**
     * Base buffer for parking, navigation, and prep (in minutes).
     */
    public const BASE_TRANSIT_BUFFER_MINUTES = 5;

    /**
     * Calculate road distance and transit time between two coordinates.
     */
    public function calculateTravel(float $lat1, float $lng1, float $lat2, float $lng2): array
    {
        $straightKm = TuyBarangays::distanceKm($lat1, $lng1, $lat2, $lng2);

        if ($straightKm < 0.05) {
            return [
                'straight_distance_km' => 0.0,
                'road_distance_km'     => 0.0,
                'estimated_minutes'    => 0,
                'formatted_distance'   => 'Same location',
                'formatted_time'       => '0 mins',
            ];
        }

        $roadKm = round(max(0.2, $straightKm * self::ROAD_FACTOR), 2);
        $transitMinutes = (int) ceil(($roadKm / self::AVG_SPEED_KMH) * 60) + self::BASE_TRANSIT_BUFFER_MINUTES;

        $formattedDist = $roadKm < 1.0
            ? round($roadKm * 1000) . ' m'
            : $roadKm . ' km';

        $formattedTime = $transitMinutes >= 60
            ? floor($transitMinutes / 60) . 'h ' . ($transitMinutes % 60) . 'm'
            : $transitMinutes . ' mins';

        return [
            'straight_distance_km' => round($straightKm, 2),
            'road_distance_km'     => $roadKm,
            'estimated_minutes'    => $transitMinutes,
            'formatted_distance'   => $formattedDist,
            'formatted_time'       => $formattedTime,
        ];
    }

    /**
     * Resolve coordinate array [lat, lng] for a booking or fallback to barangay center.
     */
    public function resolveBookingCoordinates(Booking $booking): array
    {
        if ($booking->latitude && $booking->longitude) {
            return [(float) $booking->latitude, (float) $booking->longitude];
        }

        if ($booking->barangay && TuyBarangays::isValidBarangay($booking->barangay)) {
            return TuyBarangays::pointForStatic($booking->barangay);
        }

        if ($booking->client && $booking->client->latitude && $booking->client->longitude) {
            return [(float) $booking->client->latitude, (float) $booking->client->longitude];
        }

        return TuyBarangays::pointForStatic('Luna');
    }

    /**
     * Evaluate travel feasibility between an existing booking and a prospective new booking.
     */
    public function evaluateScheduleFeasibility(
        Booking $existingBooking,
        Carbon $newStartTime,
        float $newLat,
        float $newLng,
        int $estJobDurationMinutes = 120
    ): array {
        [$existLat, $existLng] = $this->resolveBookingCoordinates($existingBooking);
        $travel = $this->calculateTravel($existLat, $existLng, $newLat, $newLng);

        $existStart = Carbon::parse($existingBooking->scheduled_at);
        $existEnd = $existStart->copy()->addMinutes($estJobDurationMinutes);
        $newEnd = $newStartTime->copy()->addMinutes($estJobDurationMinutes);

        // Case 1: New job is after existing job
        if ($newStartTime >= $existStart) {
            $availableGapMinutes = (int) $existEnd->diffInMinutes($newStartTime, false);

            if ($availableGapMinutes < 0) {
                return [
                    'status'             => 'conflict',
                    'available_gap'      => $availableGapMinutes,
                    'required_transit'   => $travel['estimated_minutes'],
                    'distance_km'        => $travel['road_distance_km'],
                    'recommended_time'   => $existEnd->copy()->addMinutes($travel['estimated_minutes'] + 10)->format('g:i A'),
                    'message'            => "Direct schedule overlap with Job #{$existingBooking->id} ({$existingBooking->service_category}).",
                ];
            }

            if ($availableGapMinutes < $travel['estimated_minutes']) {
                $recommendedTime = $existEnd->copy()->addMinutes($travel['estimated_minutes'] + 10)->format('g:i A');
                return [
                    'status'             => 'conflict',
                    'available_gap'      => $availableGapMinutes,
                    'required_transit'   => $travel['estimated_minutes'],
                    'distance_km'        => $travel['road_distance_km'],
                    'recommended_time'   => $recommendedTime,
                    'message'            => "Insufficient travel time from {$existingBooking->barangay} to destination ({$travel['road_distance_km']} km, ~{$travel['estimated_minutes']} mins needed). Recommended start: {$recommendedTime}.",
                ];
            }

            if ($availableGapMinutes < ($travel['estimated_minutes'] + 15)) {
                return [
                    'status'             => 'tight',
                    'available_gap'      => $availableGapMinutes,
                    'required_transit'   => $travel['estimated_minutes'],
                    'distance_km'        => $travel['road_distance_km'],
                    'recommended_time'   => $newStartTime->format('g:i A'),
                    'message'            => "Tight buffer: ~{$travel['estimated_minutes']} mins transit for {$travel['road_distance_km']} km with only {$availableGapMinutes} mins between jobs.",
                ];
            }

            return [
                'status'             => 'feasible',
                'available_gap'      => $availableGapMinutes,
                'required_transit'   => $travel['estimated_minutes'],
                'distance_km'        => $travel['road_distance_km'],
                'recommended_time'   => $newStartTime->format('g:i A'),
                'message'            => "Comfortable schedule: {$availableGapMinutes} mins buffer for ~{$travel['estimated_minutes']} mins transit.",
            ];
        }

        // Case 2: New job is before existing job
        $availableGapMinutes = (int) $newEnd->diffInMinutes($existStart, false);

        if ($availableGapMinutes < $travel['estimated_minutes']) {
            $latestNewStart = $existStart->copy()->subMinutes($estJobDurationMinutes + $travel['estimated_minutes'] + 10)->format('g:i A');
            return [
                'status'             => 'conflict',
                'available_gap'      => $availableGapMinutes,
                'required_transit'   => $travel['estimated_minutes'],
                'distance_km'        => $travel['road_distance_km'],
                'recommended_time'   => $latestNewStart,
                'message'            => "Insufficient travel time before Job #{$existingBooking->id} in {$existingBooking->barangay} (~{$travel['estimated_minutes']} mins needed). Latest start: {$latestNewStart}.",
            ];
        }

        if ($availableGapMinutes < ($travel['estimated_minutes'] + 15)) {
            return [
                'status'             => 'tight',
                'available_gap'      => $availableGapMinutes,
                'required_transit'   => $travel['estimated_minutes'],
                'distance_km'        => $travel['road_distance_km'],
                'recommended_time'   => $newStartTime->format('g:i A'),
                'message'            => "Tight buffer before existing job: ~{$travel['estimated_minutes']} mins transit for {$travel['road_distance_km']} km.",
            ];
        }

        return [
            'status'             => 'feasible',
            'available_gap'      => $availableGapMinutes,
            'required_transit'   => $travel['estimated_minutes'],
            'distance_km'        => $travel['road_distance_km'],
            'recommended_time'   => $newStartTime->format('g:i A'),
            'message'            => "Feasible schedule buffer.",
        ];
    }

    /**
     * Build an ordered daily route itinerary for a worker with inter-job travel metrics.
     */
    public function buildDailyRoute(Collection $bookings, ?User $worker = null): array
    {
        $sorted = $bookings->sortBy('scheduled_at')->values();

        $legs = [];
        $waypoints = [];
        $totalDistanceKm = 0.0;
        $totalTransitMinutes = 0;

        // Base location: worker profile coordinates or barangay center
        $baseLat = null;
        $baseLng = null;
        $baseName = 'Home Base';

        if ($worker) {
            if ($worker->latitude && $worker->longitude) {
                $baseLat = (float) $worker->latitude;
                $baseLng = (float) $worker->longitude;
            } elseif ($worker->barangay && TuyBarangays::isValidBarangay($worker->barangay)) {
                [$baseLat, $baseLng] = TuyBarangays::pointForStatic($worker->barangay);
            }
            $baseName = $worker->barangay ? "Home ({$worker->barangay})" : 'Home Base';
        }

        if ($baseLat && $baseLng) {
            $waypoints[] = [
                'type'       => 'base',
                'step'       => 0,
                'label'      => $baseName,
                'latitude'   => $baseLat,
                'longitude'  => $baseLng,
                'time'       => 'Origin',
                'service'    => 'Worker Base',
                'status'     => 'base',
            ];
        }

        $prevLat = $baseLat;
        $prevLng = $baseLng;
        $prevJob = null;

        foreach ($sorted as $index => $booking) {
            [$currLat, $currLng] = $this->resolveBookingCoordinates($booking);
            $stepNum = $index + 1;

            $travelFromPrev = null;
            $feasibility = 'feasible';

            if ($prevLat !== null && $prevLng !== null) {
                $roadRoute = $this->fetchRoadRoute($prevLat, $prevLng, $currLat, $currLng);
                $travelFromPrev = $roadRoute;
                $totalDistanceKm += $roadRoute['road_distance_km'];
                $totalTransitMinutes += $roadRoute['estimated_minutes'];

                if ($prevJob instanceof Booking) {
                    $feasibilityRes = $this->evaluateScheduleFeasibility(
                        $prevJob,
                        Carbon::parse($booking->scheduled_at),
                        $currLat,
                        $currLng
                    );
                    $feasibility = $feasibilityRes['status'];
                }

                $legs[] = [
                    'from_title'         => $prevJob ? ($prevJob->service_category . ' (' . ($prevJob->barangay ?? 'Tuy') . ')') : $baseName,
                    'to_title'           => $booking->service_category . ' (' . ($booking->barangay ?? 'Tuy') . ')',
                    'distance_km'        => $roadRoute['road_distance_km'],
                    'formatted_distance' => $roadRoute['formatted_distance'],
                    'transit_minutes'    => $roadRoute['estimated_minutes'],
                    'formatted_time'     => $roadRoute['formatted_time'],
                    'feasibility'        => $feasibility,
                    'nav_url'            => "https://www.google.com/maps/dir/?api=1&origin={$prevLat},{$prevLng}&destination={$currLat},{$currLng}",
                    'geometry'           => $roadRoute['geometry'],
                ];
            }

            $waypoints[] = [
                'type'           => 'job',
                'id'             => $booking->id,
                'step'           => $stepNum,
                'label'          => "Stop #{$stepNum}: {$booking->service_category}",
                'client'         => $booking->client->name ?? 'Client',
                'client_phone'   => $booking->client->phone ?? '',
                'address'        => $booking->address,
                'barangay'       => $booking->barangay ?? 'Tuy',
                'latitude'       => $currLat,
                'longitude'      => $currLng,
                'time'           => Carbon::parse($booking->scheduled_at)->format('g:i A'),
                'service'        => $booking->service_category,
                'price'          => $booking->price,
                'status'         => $booking->status,
                'feasibility'    => $feasibility,
                'travel_prev'    => $travelFromPrev,
                'nav_url'        => "https://www.google.com/maps/dir/?api=1&destination={$currLat},{$currLng}",
            ];

            $prevLat = $currLat;
            $prevLng = $currLng;
            $prevJob = $booking;
        }

        return [
            'total_jobs'             => $sorted->count(),
            'total_distance_km'      => round($totalDistanceKm, 2),
            'formatted_total_dist'   => round($totalDistanceKm, 1) . ' km',
            'total_transit_minutes'  => $totalTransitMinutes,
            'formatted_total_time'   => $totalTransitMinutes >= 60
                ? floor($totalTransitMinutes / 60) . 'h ' . ($totalTransitMinutes % 60) . 'm'
                : $totalTransitMinutes . ' mins',
            'waypoints'              => $waypoints,
            'legs'                   => $legs,
        ];
    }

    /**
     * Fetch road-snapped driving route geometry (array of [lat, lng]) and road travel metrics.
     * Uses OSRM driving service with TuyRoadNodes fallback and Redis/File caching.
     *
     * @return array{
     *   geometry: array<array{float, float}>,
     *   road_distance_km: float,
     *   estimated_minutes: int,
     *   formatted_distance: string,
     *   formatted_time: string,
     *   is_osrm: bool
     * }
     */
    public function fetchRoadRoute(float $lat1, float $lng1, float $lat2, float $lng2): array
    {
        $straightKm = TuyBarangays::distanceKm($lat1, $lng1, $lat2, $lng2);
        if ($straightKm < 0.05) {
            return [
                'geometry'           => [[$lat1, $lng1], [$lat2, $lng2]],
                'road_distance_km'   => 0.0,
                'estimated_minutes'  => 0,
                'formatted_distance' => 'Same location',
                'formatted_time'     => '0 mins',
                'is_osrm'            => false,
            ];
        }

        $cacheKey = sprintf('road_route_%.4f_%.4f_%.4f_%.4f', $lat1, $lng1, $lat2, $lng2);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($lat1, $lng1, $lat2, $lng2) {
            try {
                $url = sprintf(
                    'https://router.project-osrm.org/route/v1/driving/%.6f,%.6f;%.6f,%.6f?overview=full&geometries=geojson',
                    $lng1, $lat1, $lng2, $lat2
                );

                $response = Http::timeout(3)->get($url);

                if ($response->successful() && $response->json('code') === 'Ok') {
                    $route = $response->json('routes.0');
                    $rawCoords = $route['geometry']['coordinates'] ?? [];

                    // Convert GeoJSON [lng, lat] to Leaflet [lat, lng]
                    $geometry = array_map(fn ($pt) => [(float) $pt[1], (float) $pt[0]], $rawCoords);

                    $roadDistKm = round(($route['distance'] ?? 0) / 1000.0, 2);
                    $durationSecs = $route['duration'] ?? 0;
                    $transitMins = (int) ceil($durationSecs / 60.0) + self::BASE_TRANSIT_BUFFER_MINUTES;

                    $formattedDist = $roadDistKm < 1.0 ? round($roadDistKm * 1000) . ' m' : $roadDistKm . ' km';
                    $formattedTime = $transitMins >= 60
                        ? floor($transitMins / 60) . 'h ' . ($transitMins % 60) . 'm'
                        : $transitMins . ' mins';

                    if (!empty($geometry)) {
                        return [
                            'geometry'           => $geometry,
                            'road_distance_km'   => $roadDistKm,
                            'estimated_minutes'  => $transitMins,
                            'formatted_distance' => $formattedDist,
                            'formatted_time'     => $formattedTime,
                            'is_osrm'            => true,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('OSRM routing request failed, falling back to Tuy road network: ' . $e->getMessage());
            }

            // Fallback: Municipal road corridor + calculated road travel metrics
            $fallbackGeometry = TuyRoadNodes::buildFallbackCorridor($lat1, $lng1, $lat2, $lng2);
            $travel = $this->calculateTravel($lat1, $lng1, $lat2, $lng2);

            return [
                'geometry'           => $fallbackGeometry,
                'road_distance_km'   => $travel['road_distance_km'],
                'estimated_minutes'  => $travel['estimated_minutes'],
                'formatted_distance' => $travel['formatted_distance'],
                'formatted_time'     => $travel['formatted_time'],
                'is_osrm'            => false,
            ];
        });
    }

    /**
     * Compute an optimized visiting order for bookings on a day to minimize road travel.
     * Uses Nearest-Neighbor TSP heuristic starting from the worker's home base.
     */
    public function suggestOptimizedStopOrder(Collection $bookings, ?User $worker = null): array
    {
        if ($bookings->count() <= 1) {
            return [
                'has_optimization'     => false,
                'original_distance_km' => 0.0,
                'optimized_distance_km'=> 0.0,
                'saved_distance_km'    => 0.0,
                'saved_minutes'        => 0,
                'formatted_savings'    => 'Single booking — no reordering required',
                'ordered_bookings'     => $bookings->values(),
            ];
        }

        [$startLat, $startLng] = $this->resolveWorkerCoordinates($worker);

        // Compute original sequential distance
        $origDist = 0.0;
        $origMins = 0;
        $prevLat = $startLat;
        $prevLng = $startLng;
        $sortedOriginal = $bookings->sortBy('scheduled_at')->values();

        foreach ($sortedOriginal as $b) {
            [$bLat, $bLng] = $this->resolveBookingCoordinates($b);
            $leg = $this->calculateTravel($prevLat, $prevLng, $bLat, $bLng);
            $origDist += $leg['road_distance_km'];
            $origMins += $leg['estimated_minutes'];
            $prevLat = $bLat;
            $prevLng = $bLng;
        }

        // Nearest Neighbor heuristic
        $unvisited = $bookings->values()->all();
        $ordered = [];
        $currLat = $startLat;
        $currLng = $startLng;
        $optDist = 0.0;
        $optMins = 0;

        while (!empty($unvisited)) {
            $bestIndex = 0;
            $bestDist = PHP_FLOAT_MAX;
            $bestTravel = null;

            foreach ($unvisited as $idx => $b) {
                [$bLat, $bLng] = $this->resolveBookingCoordinates($b);
                $travel = $this->calculateTravel($currLat, $currLng, $bLat, $bLng);
                if ($travel['road_distance_km'] < $bestDist) {
                    $bestDist = $travel['road_distance_km'];
                    $bestTravel = $travel;
                    $bestIndex = $idx;
                }
            }

            $chosen = $unvisited[$bestIndex];
            [$currLat, $currLng] = $this->resolveBookingCoordinates($chosen);
            $optDist += $bestTravel['road_distance_km'];
            $optMins += $bestTravel['estimated_minutes'];
            $ordered[] = $chosen;
            array_splice($unvisited, $bestIndex, 1);
        }

        $savedKm = round(max(0, $origDist - $optDist), 2);
        $savedMins = max(0, $origMins - $optMins);
        $hasOptimization = $savedKm >= 0.5;

        return [
            'has_optimization'     => $hasOptimization,
            'original_distance_km' => round($origDist, 2),
            'optimized_distance_km'=> round($optDist, 2),
            'saved_distance_km'    => $savedKm,
            'saved_minutes'        => $savedMins,
            'formatted_savings'    => $savedKm > 0 ? "Save ~{$savedKm} km & ~{$savedMins} mins" : 'Current schedule order is already optimal',
            'ordered_bookings'     => collect($ordered)->map(function ($b, $idx) {
                [$lat, $lng] = $this->resolveBookingCoordinates($b);
                return [
                    'id'               => $b->id,
                    'order'            => $idx + 1,
                    'service'          => $b->service_category,
                    'client'           => $b->client->name ?? 'Client',
                    'barangay'         => $b->barangay,
                    'current_time'     => Carbon::parse($b->scheduled_at)->format('g:i A'),
                    'latitude'         => $lat,
                    'longitude'        => $lng,
                ];
            })->all(),
        ];
    }

    /**
     * Resolve coordinate array [lat, lng] for a worker (live/current, profile, user coordinates, or barangay).
     */
    public function resolveWorkerCoordinates(?User $worker): array
    {
        if (!$worker) {
            return TuyBarangays::pointForStatic('Luna');
        }

        if ($worker->workerProfile && $worker->workerProfile->current_latitude && $worker->workerProfile->current_longitude) {
            return [(float) $worker->workerProfile->current_latitude, (float) $worker->workerProfile->current_longitude];
        }

        if ($worker->latitude && $worker->longitude) {
            return [(float) $worker->latitude, (float) $worker->longitude];
        }

        if ($worker->barangay && TuyBarangays::isValidBarangay($worker->barangay)) {
            return TuyBarangays::pointForStatic($worker->barangay);
        }

        return TuyBarangays::pointForStatic('Luna');
    }
}


