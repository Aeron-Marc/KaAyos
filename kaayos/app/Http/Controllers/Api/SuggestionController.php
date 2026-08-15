<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatBotService;
use App\Services\MLService;
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
            $result = $chat->chat($validated['message'], $validated['history'] ?? []);

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
        try {
            $chat = app(ChatBotService::class);

            $prompt = <<<PROMPT
Classify this user message. Reply ONLY with valid JSON, no other text.

User message: "{$message}"

Possible intent types:
- "greeting": user is just saying hi, hello, good morning, etc. with no service request
- "inquiry": user is asking a general question about the platform (what services, how it works, pricing, etc.)
- "service_request": user wants a specific service (plumber, electrician, cleaning, etc.)

Respond with:
{
  "intent": "greeting|inquiry|service_request",
  "category": "only if service_request, pick the best category. Otherwise empty string.",
  "description": "brief description of what they need or are asking"
}
PROMPT;

            $result = $chat->chat($prompt);
            $text = $result['reply'] ?? '';
            $text = trim($text);
            $text = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $text);

            $decoded = json_decode($text, true);
            if (is_array($decoded)) {
                return [
                    'intent' => $decoded['intent'] ?? 'service_request',
                    'category' => $decoded['category'] ?? '',
                    'description' => $decoded['description'] ?? $message,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('AI intent extraction failed: ' . $e->getMessage());
        }

        return [
            'intent' => 'service_request',
            'category' => '',
            'description' => $message,
        ];
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
            ->active()
            ->where('service_category', $category);

        $rawWorkers = $query->get()->map(function ($u) {
            $profile = $u->workerProfile;
            $completedJobs = $u->bookingsAsWorker()->where('status', 'completed')->count();
            $totalJobs = $u->bookingsAsWorker()->count();
            $name = $u->name ?? '';
            $parts = explode(' ', $name, 2);

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
                'rating' => (float) ($profile?->average_rating ?? 0),
                'price' => (float) ($profile?->hourly_rate ?? 0),
                'verified' => (bool) ($profile?->government_id_verified ?? false),
                'skills' => $profile?->skills ?? [],
                'years_experience' => $profile?->years_of_experience ?? 0,
                'jobs_completed' => $completedJobs,
                'total_jobs' => $totalJobs,
                'latitude' => $profile?->current_latitude,
                'longitude' => $profile?->current_longitude,
            ];
        })->values()->toArray();

        if (empty($rawWorkers)) {
            return $rawWorkers;
        }

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
        }, $rawWorkers);

        $ml = app(MLService::class);
        $result = $ml->predict($mlWorkers);

        $workers = $rawWorkers;
        if ($result && isset($result['rankings'])) {
            $predMap = [];
            foreach ($result['rankings'] as $pred) {
                if (isset($pred['worker_id'], $pred['probability'])) {
                    $predMap[$pred['worker_id']] = min(100, max(0, (int) round($pred['probability'] * 100)));
                }
            }
            foreach ($workers as &$w) {
                $w['match_percent'] = $predMap[$w['id']] ?? 50;
            }
        } else {
            foreach ($workers as &$w) {
                $score = ($w['rating'] / 5) * 40
                       + min($w['jobs_completed'], 50) / 50 * 25
                       + min($w['years_experience'], 10) / 10 * 25
                       + ($w['verified'] ? 10 : 0);
                $w['match_percent'] = min(100, max(0, (int) round($score)));
            }
        }

        usort($workers, fn($a, $b) => $b['match_percent'] - $a['match_percent']);

        return $workers;
    }
}
