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

    protected function fetchWorkers(string $category): array
    {
        $query = User::where('role', 'worker')
            ->with('workerProfile')
            ->withCount(['bookingsAsWorker as completed_jobs_count' => fn($q) => $q->where('status', 'completed')])
            ->active()
            ->where('service_category', $category);

        return $query->get()->map(function ($u) {
            $profile = $u->workerProfile;
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
                'jobs_completed' => $u->completed_jobs_count,
            ];
        })->values()->toArray();
    }
}
