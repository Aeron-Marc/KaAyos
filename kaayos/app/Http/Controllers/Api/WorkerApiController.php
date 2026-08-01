<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkerApiController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $weekStart = now()->startOfWeek();

        $weeklyEarnings = (float) ($user->bookingsAsWorker()->completed()
            ->where('completed_at', '>=', $weekStart)
            ->sum('price') ?? 0);

        $activeJobs = $user->bookingsAsWorker()
            ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_EN_ROUTE, Booking::STATUS_IN_PROGRESS])
            ->count();

        $pendingJobs = $user->bookingsAsWorker()
            ->where('status', Booking::STATUS_NEW)
            ->count();

        $totalCompleted = $user->bookingsAsWorker()->completed()->count();

        $unreadMessages = \App\Models\Message::forUser($user->id)->unread()->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'weekly_earnings' => $weeklyEarnings,
                'active_jobs'     => $activeJobs,
                'pending_jobs'    => $pendingJobs,
                'rating'          => (float) ($user->workerProfile?->average_rating ?? 0),
                'completed_jobs'  => $totalCompleted,
                'unread_messages' => $unreadMessages,
            ],
        ]);
    }

    public function earnings(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = now();

        $completed = $user->bookingsAsWorker()->completed()->take(50)->get();

        $total = (int) ($completed->sum('price') ?? 0);
        $thisMonth = (int) ($completed->filter(function ($b) use ($now) {
            return $b->completed_at
                && $b->completed_at->month === $now->month
                && $b->completed_at->year === $now->year;
        })->sum('price') ?? 0);

        $pendingPayout = (int) ($user->bookingsAsWorker()
            ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_EN_ROUTE, Booking::STATUS_IN_PROGRESS])
            ->sum('price') ?? 0);

        $count = $completed->count();
        $avgPerJob = $count > 0 ? round($total / $count) : 0;

        $payouts = $completed->sortByDesc('completed_at')->values()
            ->map(function ($booking) {
                return [
                    'id'     => $booking->id,
                    'date'   => $booking->completed_at?->format('M d, Y') ?? 'N/A',
                    'client' => $booking->client->name ?? 'Unknown',
                    'job'    => $booking->service_category,
                    'amount' => (float) ($booking->price ?? 0),
                    'status' => 'Completed',
                ];
            });

        return response()->json([
            'success' => true,
            'earnings' => [
                'total'          => $total,
                'this_month'     => $thisMonth,
                'pending_payout' => $pendingPayout,
                'avg_per_job'    => $avgPerJob,
                'payouts'        => $payouts,
            ],
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        $profile = $user->workerProfile ?? WorkerProfile::create(['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'profile' => [
                'first_name'          => $user->first_name,
                'last_name'           => $user->last_name,
                'name'                => $user->name,
                'email'               => $user->email,
                'phone'               => $user->phone,
                'city'                => $user->city,
                'language'            => $user->language,
                'service_category'    => $user->service_category,
                'avatar_url'          => $user->avatar ? \Storage::url($user->avatar) : null,
                'bio'                 => $profile->bio ?? '',
                'skills'              => $profile->skills ?? [],
                'spoken_languages'    => $profile->spoken_languages ?? [],
                'hourly_rate'         => (float) ($profile->hourly_rate ?? 0),
                'available_days'      => $profile->available_days,
                'preferred_hours'     => $profile->preferred_hours,
                'service_areas'       => $profile->service_areas ?? [],
                'years_of_experience' => (int) ($profile->years_of_experience ?? 0),
                'service_radius'      => (int) ($profile->service_radius ?? 0),
                'service_zone'        => $profile->service_zone ?? [],
                'average_rating'      => (float) ($profile->average_rating ?? 0),
                'verified'            => (bool) ($profile->government_id_verified ?? false),
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name'          => ['required', 'string', 'max:100'],
            'last_name'           => ['required', 'string', 'max:100'],
            'phone'               => ['nullable', 'string', 'max:20', 'regex:/^(?:\+63|0)[0-9]{10}$/'],
            'city'                => ['nullable', 'string', 'max:255'],
            'language'            => ['required', 'string', Rule::in(['English', 'Filipino'])],
            'service_category'    => ['nullable', 'string', 'max:255'],
            'bio'                 => ['nullable', 'string', 'max:2000'],
            'skills'              => ['nullable', 'array'],
            'skills.*'            => ['string', 'max:100'],
            'spoken_languages'    => ['nullable', 'array'],
            'spoken_languages.*'  => ['string', 'max:100'],
            'hourly_rate'         => ['nullable', 'numeric', 'min:0'],
            'available_days'      => ['nullable', 'string', 'max:255'],
            'preferred_hours'     => ['nullable', 'string', 'max:255'],
            'service_areas'       => ['nullable', 'array'],
            'service_areas.*'     => ['string', 'max:255'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:100'],
            'service_radius'      => ['nullable', 'integer', 'min:0', 'max:500'],
            'service_zone'        => ['nullable', 'array'],
            'service_zone.*'      => ['string', 'max:255'],
        ]);

        $user->update([
            'first_name'       => $data['first_name'],
            'last_name'        => $data['last_name'],
            'name'             => $data['first_name'] . ' ' . $data['last_name'],
            'phone'            => $data['phone'] ?? null,
            'city'             => $data['city'] ?? null,
            'language'         => $data['language'],
            'service_category' => $data['service_category'] ?? null,
        ]);

        $profile = $user->workerProfile ?? new WorkerProfile(['user_id' => $user->id]);

        $profile->fill([
            'bio'                 => $data['bio'] ?? null,
            'skills'              => $data['skills'] ?? null,
            'spoken_languages'    => $data['spoken_languages'] ?? null,
            'hourly_rate'         => $data['hourly_rate'] ?? null,
            'available_days'      => $data['available_days'] ?? null,
            'preferred_hours'     => $data['preferred_hours'] ?? null,
            'service_areas'       => $data['service_areas'] ?? null,
            'years_of_experience' => $data['years_of_experience'] ?? null,
            'service_radius'      => $data['service_radius'] ?? null,
            'service_zone'        => $data['service_zone'] ?? null,
        ]);

        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile saved successfully.',
        ]);
    }
}
