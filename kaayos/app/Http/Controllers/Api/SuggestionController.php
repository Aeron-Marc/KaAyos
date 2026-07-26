<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatBotService;
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
                $workers = $this->fetchWorkers($intent['category']);
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

    protected function fetchWorkers(string $category): array
    {
        $query = User::where('role', 'worker')
            ->with('workerProfile')
            ->active()
            ->where('service_category', $category);

        return $query->get()->map(function ($u) {
            $profile = $u->workerProfile;
            $completedJobs = $u->bookingsAsWorker()->where('status', 'completed')->count();
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
                'jobs_completed' => $completedJobs,
            ];
        })->values()->toArray();
    }
}
