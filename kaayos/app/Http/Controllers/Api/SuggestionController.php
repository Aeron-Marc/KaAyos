<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatBotService;
use App\Services\MLService;
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

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'reply' => 'Please log in to get personalized suggestions.',
                'suggestions' => [],
                'workers' => [],
            ], 401);
        }

        try {
            $clientLocation = [];
            try {
                $clientLocation = ['client' => $user->locationContext()];
            } catch (\Throwable $e) {
                Log::warning('Location context failed', ['error' => $e->getMessage()]);
            }

            $chat = app(ChatBotService::class);
            $result = $chat->chat(
                $validated['message'],
                $validated['history'] ?? [],
                $user,
                $clientLocation
            );

            $workers = [];
            $intent = $this->extractIntent($validated['message']);
            if ($intent['intent'] === 'service_request' && !empty($intent['category'])) {
                $workers = $this->fetchWorkers($intent['category'], $validated['message']);
            }

            $reply = $result['reply'];
            if (!empty($workers)) {
                $intro = $this->contextualIntro($workers, $validated['message']);
                if ($intro) {
                    $reply = $intro;
                } else {
                    $reply = $this->briefReply($reply);
                }
            }

            return response()->json([
                'success' => true,
                'reply' => $reply,
                'suggestions' => $result['suggestions'],
                'workers' => $workers,
            ]);
        } catch (\Exception $e) {
            Log::error('Suggestion error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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

    protected function contextualIntro(array $workers, string $userMessage): ?string
    {
        try {
            $chat = app(ChatBotService::class);

            $category = $workers[0]['category'] ?? 'workers';
            $area = config('kaayos.default_location', 'Tuy, Batangas');

            $prompt = <<<PROMPT
The user said: "{$userMessage}"

We found {$category} workers in {$area}. Explain in 1-2 short sentences — natural and conversational, like a real assistant talking to a neighbor. Refer to what they asked for but keep it varied and human. No lists, no details.

Reply with just the explanation.
PROMPT;

            $result = $chat->chat($prompt);
            $text = trim($result['reply'] ?? '');
            return !empty($text) ? $text : null;
        } catch (\Exception $e) {
            Log::warning('Contextual intro failed: ' . $e->getMessage());
            return null;
        }
    }

    protected function briefReply(string $reply): string
    {
        $lines = explode("\n", $reply);
        $brief = [];
        foreach ($lines as $line) {
            if (preg_match('/^\d+\.\s/', $line)) {
                break;
            }
            $brief[] = $line;
        }
        $result = trim(implode("\n", $brief));
        return !empty($result) ? $result : $reply;
    }

    protected function fetchWorkers(string $category, string $userMessage = ''): array
    {
        $query = User::where('role', 'worker')
            ->with('workerProfile')
            ->withCount([
                'bookingsAsWorker as completed_jobs_count' => fn($q) => $q->where('status', 'completed'),
                'bookingsAsWorker as total_jobs_count',
            ])
            ->active()
            ->where('service_category', $category);

        $rawWorkers = $query->get()->map(function ($u) use ($userMessage) {
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
            $totalJobs = $u->total_jobs_count ?? 0;
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
                'years_experience' => $yearsExp,
                'jobs_completed' => $completedJobs,
                'total_jobs' => $totalJobs,
                'latitude'  => $lat,
                'longitude' => $lng,
                'location_approximate' => (bool) ($profile?->location_is_approximate ?? true),
                'match_percent' => $matchPercent,
            ];
        })->values()->toArray();

        if (empty($rawWorkers)) {
            return $rawWorkers;
        }

        $workers = $this->rankWithML($rawWorkers);

        usort($workers, fn($a, $b) => $b['match_percent'] <=> $a['match_percent']);

        return $workers;
    }

    protected function rankWithML(array $workers): array
    {
        try {
            $mlWorkers = array_map(function ($w) {
                $totalJobs = $w['total_jobs'];
                $completionRate = $totalJobs > 0 ? round(($w['jobs_completed'] / $totalJobs) * 100) : 50;
                return [
                    'worker_id' => $w['id'],
                    'service_category' => $w['category'],
                    'distance_km' => 1.0,
                    'worker_avg_rating' => $w['rating'],
                    'worker_completion_rate' => $completionRate,
                    'jobs_completed_in_category' => $w['jobs_completed'],
                    'is_new_worker' => $w['jobs_completed'] < 3 ? 1 : 0,
                ];
            }, $workers);

            $ml = app(MLService::class);
            $result = $ml->predict($mlWorkers);

            if ($result && isset($result['rankings'])) {
                $predMap = [];
                foreach ($result['rankings'] as $pred) {
                    if (isset($pred['worker_id'], $pred['probability'])) {
                        $predMap[$pred['worker_id']] = min(100, max(0, (int) round($pred['probability'] * 100)));
                    }
                }
                foreach ($workers as &$w) {
                    $w['match_percent'] = $predMap[$w['id']] ?? $w['match_percent'];
                }
                unset($w);
            }
        } catch (\Exception $e) {
            Log::warning('ML ranking failed, falling back to heuristic: ' . $e->getMessage());
        }

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