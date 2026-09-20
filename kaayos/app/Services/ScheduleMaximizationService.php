<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;

class ScheduleMaximizationService
{
    public function __construct(
        protected GeoTravelService $geoTravel
    ) {}

    /**
     * Find usable idle windows between bookings on a given day.
     */
    public function findAvailableGaps(User $worker, Carbon $date): array
    {
        $profile = $worker->workerProfile;
        if (!$profile || !is_array($profile->availability)) {
            return [];
        }

        $dayName = $date->format('l');
        $avail = collect($profile->availability)->firstWhere('day', $dayName);

        if (!$avail || !($avail['active'] ?? false) || empty($avail['start']) || empty($avail['end'])) {
            return [];
        }

        $dayStart = Carbon::parse($date->toDateString() . ' ' . $avail['start']);
        $dayEnd   = Carbon::parse($date->toDateString() . ' ' . $avail['end']);

        // Cross-midnight: if end is before or equal to start, push end to next calendar day
        if ($dayEnd->lessThanOrEqualTo($dayStart)) {
            $dayEnd->addDay();
        }

        $bookings = Booking::where('worker_id', $worker->id)
            ->whereDate('scheduled_at', $date->toDateString())
            ->whereNotIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED, Booking::STATUS_DECLINED])
            ->orderBy('scheduled_at')
            ->get();

        if ($bookings->isEmpty()) {
            return [
                [
                    'type' => 'full_day_open',
                    'start' => $dayStart->format('g:i A'),
                    'end' => $dayEnd->format('g:i A'),
                    'duration_hours' => round($dayStart->diffInMinutes($dayEnd) / 60, 1),
                    'barangay' => $worker->barangay ?? 'Base',
                ]
            ];
        }

        $gaps = [];
        $cursor = $dayStart->copy();
        $prevBarangay = $worker->barangay ?? 'Base';

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->scheduled_at);
            $jobDurationHours = (float) ($booking->estimated_duration_hours ?? 2.0);
            $bookingEnd = $bookingStart->copy()->addMinutes((int) ($jobDurationHours * 60));

            if ($bookingStart->greaterThan($cursor)) {
                $gapMinutes = $cursor->diffInMinutes($bookingStart);
                if ($gapMinutes >= 90) {
                    $gaps[] = [
                        'type' => 'express_gap_slot',
                        'start' => $cursor->format('g:i A'),
                        'end' => $bookingStart->format('g:i A'),
                        'usable_minutes' => $gapMinutes,
                        'duration_hours' => round($gapMinutes / 60, 1),
                        'near_barangay' => $prevBarangay,
                    ];
                }
            }

            if ($bookingEnd->greaterThan($cursor)) {
                $cursor = $bookingEnd->copy();
            }
            $prevBarangay = $booking->barangay ?? $prevBarangay;
        }

        // Check tail gap
        if ($dayEnd->greaterThan($cursor)) {
            $tailMinutes = $cursor->diffInMinutes($dayEnd);
            if ($tailMinutes >= 90) {
                $gaps[] = [
                    'type' => 'express_gap_slot',
                    'start' => $cursor->format('g:i A'),
                    'end' => $dayEnd->format('g:i A'),
                    'usable_minutes' => $tailMinutes,
                    'duration_hours' => round($tailMinutes / 60, 1),
                    'near_barangay' => $prevBarangay,
                ];
            }
        }

        return $gaps;
    }

    /**
     * Compute daily packing efficiency and schedule utilization.
     */
    public function computeDailyUtilization(User $worker, Carbon $date): array
    {
        $profile = $worker->workerProfile;
        $dayName = $date->format('l');
        $avail = $profile && is_array($profile->availability)
            ? collect($profile->availability)->firstWhere('day', $dayName)
            : null;

        if (!$avail || !($avail['active'] ?? false)) {
            return [
                'utilization_percent'  => 0,
                'booked_hours'         => 0,
                'total_capacity_hours' => 0,
                'total_jobs'           => 0,
                'status_label'         => 'Off Duty',
                'availability_window'  => null,
                'shift_window'         => null,
                'gaps'                 => [],
            ];
        }

        $dayStart = Carbon::parse($date->toDateString() . ' ' . $avail['start']);
        $dayEnd   = Carbon::parse($date->toDateString() . ' ' . $avail['end']);

        // Cross-midnight: if end is before or equal to start, push end to next calendar day
        $isCrossMidnight = $dayEnd->lessThanOrEqualTo($dayStart);
        if ($isCrossMidnight) {
            $dayEnd->addDay();
        }

        $startFormatted = Carbon::parse($date->toDateString() . ' ' . $avail['start'])->format('g:i A');
        $endFormatted   = Carbon::parse($date->toDateString() . ' ' . $avail['end'])->format('g:i A');
        $availabilityWindow = $startFormatted . ' – ' . $endFormatted . ($isCrossMidnight ? ' (+1 day)' : '');

        $totalWorkingMinutes = max(60, $dayStart->diffInMinutes($dayEnd));

        $bookings = Booking::where('worker_id', $worker->id)
            ->whereDate('scheduled_at', $date->toDateString())
            ->whereNotIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED, Booking::STATUS_DECLINED])
            ->get();

        $bookedMinutes = 0;
        foreach ($bookings as $b) {
            $bookedMinutes += (int) (($b->estimated_duration_hours ?? 2.0) * 60) + (int) ($b->estimated_transit_minutes ?? 15);
        }

        $utilizationPercent = min(100, (int) round(($bookedMinutes / $totalWorkingMinutes) * 100));
        $gaps = $this->findAvailableGaps($worker, $date);

        $statusLabel = match(true) {
            $utilizationPercent >= 80 => 'High Efficiency (Route Packed)',
            $utilizationPercent >= 50 => 'Moderate Efficiency',
            $utilizationPercent > 0   => 'Open Capacity Available',
            default                   => 'Fully Open Day',
        };

        return [
            'utilization_percent'  => $utilizationPercent,
            'booked_hours'         => round($bookedMinutes / 60, 1),
            'total_capacity_hours' => round($totalWorkingMinutes / 60, 1),
            'total_jobs'           => $bookings->count(),
            'status_label'         => $statusLabel,
            'availability_window'  => $availabilityWindow,
            'shift_window'         => $availabilityWindow,
            'gaps'                 => $gaps,
        ];
    }
}
