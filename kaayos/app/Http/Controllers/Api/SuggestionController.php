<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Services\ChatBotService;
use App\Services\MLService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuggestionController extends Controller
{
    protected const CATEGORIES = [
        'Plumbing', 'Electrical', 'Carpentry', 'Cleaning',
        'Painting', 'Roofing', 'Aircon Servicing', 'Hauling',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'sometimes|array',
            'history.*.role' => 'required|in:user,assistant',
            'history.*.content' => 'required|string',
        ]);

        try {
            $intent = $this->extractIntent($validated['message']);
            $workers = $this->fetchWorkers($intent['category']);
            $ranked = $this->rankWorkers($workers, $intent);
            $reply = $this->buildReply($intent, $ranked);

            return response()->json([
                'success' => true,
                'reply' => $reply['text'],
                'suggestions' => $reply['suggestions'],
                'workers' => $ranked,
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
        $lower = strtolower($message);
        $category = 'General';
        $keywords = [];

        $map = [
            'Plumbing'       => ['plumber', 'plumbing', 'pipe', 'leak', 'faucet', 'toilet', 'drain', 'water'],
            'Electrical'     => ['electrician', 'electrical', 'wiring', 'circuit', 'outlet', 'light', 'switch', 'fuse'],
            'Carpentry'      => ['carpenter', 'carpentry', 'wood', 'furniture', 'cabinet', 'shelf', 'door', 'frame'],
            'Cleaning'       => ['clean', 'cleaning', 'maid', 'janitor', 'tidy', 'housekeeping', 'sanitize'],
            'Painting'       => ['paint', 'painting', 'painter', 'color', 'wall', 'roller', 'brush'],
            'Roofing'        => ['roof', 'roofing', 'roofer', 'shingle', 'gutter', 'ceiling'],
            'Aircon Servicing' => ['aircon', 'air conditioning', 'ac', 'hvac', 'cooling', 'refrigeration', 'aircon servicing'],
            'Hauling'        => ['haul', 'hauling', 'truck', 'deliver', 'move', 'transport', 'heavy', 'load'],
        ];

        foreach ($map as $cat => $catKeywords) {
            foreach ($catKeywords as $kw) {
                if (str_contains($lower, $kw)) {
                    $category = $cat;
                    $keywords[] = $kw;
                }
            }
        }

        $keywords = array_unique($keywords);

        if (empty($keywords)) {
            if (str_contains($lower, 'worker') || str_contains($lower, 'service') || str_contains($lower, 'recommend') || str_contains($lower, 'suggest') || str_contains($lower, 'find') || str_contains($lower, 'look')) {
                $category = 'General';
            }
        }

        try {
            $chat = app(ChatBotService::class);

            $prompt = <<<PROMPT
Extract the service need from this user message. Reply ONLY with valid JSON, no other text.

User message: "{$message}"

Respond with this exact format:
{
  "category": "the most relevant service category (Plumbing, Electrical, Carpentry, Cleaning, Painting, Roofing, Aircon Servicing, Hauling, or General)",
  "description": "brief description of what they need",
  "keywords": ["keyword1", "keyword2"]
}
PROMPT;

            $result = $chat->chat($prompt);
            $text = $result['reply'] ?? '';
            $text = trim($text);
            $text = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $text);

            $decoded = json_decode($text, true);
            if (is_array($decoded)) {
                return [
                    'category' => $decoded['category'] ?? $category,
                    'description' => $decoded['description'] ?? $message,
                    'keywords' => $decoded['keywords'] ?? $keywords,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('AI intent extraction failed, using PHP fallback: ' . $e->getMessage());
        }

        return [
            'category' => $category,
            'description' => $message,
            'keywords' => $keywords,
        ];
    }

    protected function fetchWorkers(string $category): array
    {
        $query = User::where('role', 'worker')
            ->with('workerProfile')
            ->active();

        if (!empty($category) && $category !== 'General') {
            $query->where('service_category', $category);
        }

        $userId = auth()->id();
        $pastCategories = [];
        if ($userId) {
            $pastCategories = User::find($userId)?->bookingsAsClient()
                ->whereIn('status', [Booking::STATUS_COMPLETED])
                ->pluck('service_category')
                ->unique()
                ->values()
                ->toArray() ?? [];
        }

        return $query->get()->map(function ($u) use ($pastCategories) {
            $profile = $u->workerProfile;

            $bookingQuery = $u->bookingsAsWorker();
            $totalJobs = (clone $bookingQuery)->count();
            $completedJobs = (clone $bookingQuery)->where('status', Booking::STATUS_COMPLETED)->count();

            $categoryJobs = (clone $bookingQuery)
                ->where('status', Booking::STATUS_COMPLETED)
                ->where('service_category', $u->service_category)
                ->count();

            $completionRate = $totalJobs > 0
                ? round(($completedJobs / $totalJobs) * 100, 1)
                : 0;

            $isNew = $completedJobs === 0 ? 1 : 0;

            $distance = 1.0;
            if ($profile?->current_latitude && $profile?->current_longitude) {
                $distance = $this->haversine(
                    13.8333, 120.7333,
                    (float) $profile->current_latitude,
                    (float) $profile->current_longitude
                );
            }

            $score = 0;
            $score += min(($profile?->average_rating ?? 0) * 10, 30);
            if ($profile?->government_id_verified) { $score += 20; }
            if (!empty($pastCategories) && in_array($u->service_category, $pastCategories)) { $score += 25; }
            $score += min($u->reviewsReceived()->count(), 15);
            if (!empty($profile?->skills)) { $score += 10; }

            $name = $u->name ?? '';
            $parts = explode(' ', $name, 2);

            return [
                'id' => $u->id,
                'name' => $name,
                'first_name' => $parts[0] ?? $name,
                'last_name' => $parts[1] ?? '',
                'category' => $u->service_category ?? 'General',
                'avatar' => $u->avatar ? \Storage::url($u->avatar) : null,
                'initials' => strtoupper(
                    substr($parts[0] ?? $name, 0, 1) .
                    substr($parts[1] ?? '', 0, 1)
                ),
                'rating' => (float) ($profile?->average_rating ?? 0),
                'reviews_count' => $u->reviewsReceived()->count(),
                'price' => (float) ($profile?->hourly_rate ?? 0),
                'verified' => (bool) ($profile?->government_id_verified ?? false),
                'skills' => $profile?->skills ?? [],
                'distance_km' => round($distance, 2),
                'completion_rate' => $completionRate,
                'jobs_completed' => $completedJobs,
                'jobs_in_category' => $categoryJobs,
                'is_new_worker' => $isNew,
                'score' => $score,
                'latitude' => (float) ($profile?->current_latitude ?? 13.8333),
                'longitude' => (float) ($profile?->current_longitude ?? 120.7333),
            ];
        })->values()->toArray();
    }

    protected function rankWorkers(array $workers, array $intent): array
    {
        if (empty($workers)) return [];

        $mlService = app(MLService::class);

        if ($mlService->isAvailable()) {
            try {
                $mlPayload = array_map(fn ($w) => [
                    'worker_id' => $w['id'],
                    'service_category' => $w['category'],
                    'distance_km' => $w['distance_km'],
                    'worker_avg_rating' => $w['rating'],
                    'worker_completion_rate' => $w['completion_rate'],
                    'jobs_completed_in_category' => $w['jobs_in_category'],
                    'is_new_worker' => $w['is_new_worker'],
                ], $workers);

                $mlResult = $mlService->predict($mlPayload);

                if ($mlResult && isset($mlResult['rankings'])) {
                    $probMap = collect($mlResult['rankings'])->pluck('probability', 'worker_id');

                    return collect($workers)->map(function ($w) use ($probMap) {
                        $w['match_percent'] = round(($probMap[$w['id']] ?? 0) * 100, 1);
                        return $w;
                    })->sortByDesc('match_percent')->values()->toArray();
                }
            } catch (\Exception $e) {
                Log::warning('ML ranking failed, using PHP fallback: ' . $e->getMessage());
            }
        }

        return collect($workers)->map(function ($w) {
            $w['match_percent'] = round(min($w['score'] / 100 * 100, 99), 1);
            return $w;
        })->sortByDesc('score')->values()->toArray();
    }

    protected function buildReply(array $intent, array $ranked): array
    {
        $chat = app(ChatBotService::class);
        return $this->buildAIReply($chat, $intent, $ranked);
    }

    protected function buildAIReply(ChatBotService $chat, array $intent, array $ranked): array
    {
        $description = $intent['description'] ?? '';
        $category = $intent['category'] ?? 'worker';
        $topWorkers = array_slice($ranked, 0, 5);
        $workerLines = [];

        foreach ($topWorkers as $i => $w) {
            $workerLines[] = ($i + 1) . ". {$w['name']} — {$w['category']}, ★ {$w['rating']} ({$w['match_percent']}% match)";
        }
        $workerSummary = empty($workerLines) ? 'No workers found for this category.' : implode("\n", $workerLines);

        $prompt = <<<PROMPT
The user wants: {$description}
Service category: {$category}

Workers found:
{$workerSummary}

Write a warm, conversational response (2-4 sentences). Sound like a helpful local assistant who's excited to help them find the right worker.

If workers were found: Acknowledge what they need, then recommend the top 1-2 by name with something specific (rating, experience, match score). End by inviting them to ask for more details.

If no workers were found: Apologize naturally and suggest alternatives like browsing all workers or trying a different category.

Keep it natural, like you're talking to a neighbor.

Then suggest 3 follow-up questions. Make them contextual based on the situation.

Format: respond with your message, then on a new line, put "---SUGGESTIONS---" and then a pipe-separated list.
PROMPT;

        $result = $chat->chat($prompt);
        $reply = $result['reply'] ?? '';

        $suggestions = [];
        if (str_contains($reply, '---SUGGESTIONS---')) {
            $parts = explode('---SUGGESTIONS---', $reply, 2);
            $reply = trim($parts[0]);
            if (!empty($parts[1])) {
                $suggestions = array_map('trim', explode('|', trim($parts[1])));
            }
        }

        return [
            'text' => $reply,
            'suggestions' => $suggestions,
        ];
    }

    protected function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}
