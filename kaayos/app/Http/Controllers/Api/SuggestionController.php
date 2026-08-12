<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatBotService;
use App\Support\TuyBarangays;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuggestionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'sometimes|array',
            'history.*.role' => 'required|in:user,assistant',
            'history.*.content' => 'required|string',
        ]);

        try {
            $chat = app(ChatBotService::class);
            $result = $chat->chat(
                $validated['message'],
                $validated['history'] ?? [],
                auth()->user(),
                ['client' => auth()->user()->locationContext()]
            );

            $workers = [];
            $intent = $this->extractIntent($validated['message']);
            if ($intent['intent'] === 'service_request' && !empty($intent['category'])) {
                $workers = $this->fetchWorkers($intent['category'], $validated['message']);
            }

            return response()->json([
                'success' => true,
                'reply' => $result['reply'],
                'suggestions' => $result['suggestions'],
                'workers' => $workers,
            ]);
        } catch (\Exception $e) {
            Log::error('Suggestion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'reply' => 'I\'m sorry, something went wrong. Please try again.',
                'suggestions' => ['Looking for a plumber', 'Need an electrician', 'Best rated workers'],
                'workers' => [],
            ], 500);
        }
    }

    protected function extractIntent(string $message): array
    {
        $lower = strtolower(trim($message));

        $catMap = [
            'plumber' => 'Plumbing', 'plumbing' => 'Plumbing', 'tubig' => 'Plumbing', 'pipe' => 'Plumbing',
            'electrician' => 'Electrical', 'electrical' => 'Electrical', 'kuryente' => 'Electrical',
            'carpenter' => 'Carpentry', 'carpentry' => 'Carpentry', 'karpintero' => 'Carpentry',
            'painter' => 'Painting', 'painting' => 'Painting', 'pintor' => 'Painting',
            'clean' => 'Cleaning', 'cleaning' => 'Cleaning', 'linis' => 'Cleaning',
            'garden' => 'Gardening', 'gardening' => 'Gardening', 'halaman' => 'Gardening',
            'welder' => 'Welding', 'welding' => 'Welding',
            'mason' => 'Masonry', 'masonry' => 'Masonry',
        ];

        foreach ($catMap as $key => $cat) {
            if (str_contains($lower, $key)) {
                return ['intent' => 'service_request', 'category' => $cat, 'description' => $message];
            }
        }

        $greetings = ['hello', 'hi', 'hey', 'kamusta', 'good morning', 'good afternoon', 'good evening'];
        foreach ($greetings as $g) {
            if (str_contains($lower, $g)) {
                return ['intent' => 'greeting', 'category' => '', 'description' => $message];
            }
        }

        $inquiry = ['what', 'how', 'where', 'when', 'why', 'do you', 'can i', 'is there', 'are there'];
        foreach ($inquiry as $q) {
            if (str_starts_with($lower, $q) || str_contains($lower, ' ' . $q)) {
                return ['intent' => 'inquiry', 'category' => '', 'description' => $message];
            }
        }

        return ['intent' => 'service_request', 'category' => '', 'description' => $message];
    }

    protected function fetchWorkers(string $category, string $userMessage = ''): array
    {
        $query = User::where('role', 'worker')
            ->with('workerProfile')
            ->withCount(['bookingsAsWorker as completed_jobs_count' => fn($q) => $q->where('status', 'completed')])
            ->active()
            ->where('service_category', $category);

        $workers = $query->get()->map(function ($u) use ($userMessage) {
            $profile = $u->workerProfile;
            $name = $u->name ?? '';
            $parts = explode(' ', $name, 2);

            $existingLat = $profile?->current_latitude;
            $existingLng = $profile?->current_longitude;

            if ($existingLat !== null && $existingLng !== null) {
                $lat = (float) $existingLat;
                $lng = (float) $existingLng;
            } else {
                $barangay = $u->barangay
                    ?? TuyBarangays::residenceFor($u->id);
                [$lat, $lng] = TuyBarangays::pointFor($barangay, $u->id);

                $profile?->update([
                    'current_latitude'  => $lat,
                    'current_longitude' => $lng,
                    'service_zone'     => ['barangay' => $barangay],
                    'location_is_approximate' => true,
                ]);
            }

            $rating = (float) ($profile?->average_rating ?? 0);
            $completedJobs = $u->completed_jobs_count ?? 0;
            $verified = (bool) ($profile?->government_id_verified ?? false);
            $yearsExp = (int) ($profile?->years_of_experience ?? 0);
            $skills = $profile?->skills ?? [];

            $matchPercent = $this->computeMatchPercent(
                $rating, $completedJobs, $verified, $yearsExp, $skills, $userMessage
            );

            return [
                'id' => $u->id,
                'name' => $name,
                'first_name' => $parts[0] ?? $name,
                'last_name' => $parts[1] ?? '',
                'category' => $u->service_category ?? '',
                'avatar' => $u->avatar ? \Storage::url($u->avatar) : null,
                'initials' => strtoupper(
                    substr($parts[0] ?? $name, 0, 1) .
                    substr($parts[1] ?? '', 0, 1)
                ),
                'rating' => $rating,
                'price' => (float) ($profile?->hourly_rate ?? 0),
                'distance' => $u->residence,
                'verified' => $verified,
                'skills' => $skills,
                'jobs_completed' => $completedJobs,
                'latitude'  => $lat,
                'longitude' => $lng,
                'location_approximate' => (bool) ($profile?->location_is_approximate ?? true),
                'match_percent' => $matchPercent,
            ];
        })->values()->toArray();

        usort($workers, fn($a, $b) => $b['match_percent'] <=> $a['match_percent']);

        return $workers;
    }

    protected function computeMatchPercent(
        float $rating,
        int $completedJobs,
        bool $verified,
        int $yearsExperience,
        array $skills,
        string $userMessage
    ): int {
        $score = 0;

        $score += ($rating / 5.0) * 35;

        $score += (min($completedJobs, 10) / 10.0) * 25;

        $score += $verified ? 20.0 : 10.0;

        $score += (min($yearsExperience, 15) / 15.0) * 20;

        if ($userMessage !== '') {
            $keywords = preg_split('/\s+/', strtolower($userMessage));
            $skillsLower = array_map('strtolower', $skills);
            foreach ($keywords as $keyword) {
                $keyword = preg_replace('/[^a-z0-9]/', '', $keyword);
                if (strlen($keyword) < 3) {
                    continue;
                }
                foreach ($skillsLower as $skill) {
                    if (str_contains($skill, $keyword)) {
                        $score += 5;
                        break 2;
                    }
                }
            }
        }

        return min(100, max(0, (int) round($score)));
    }
}
