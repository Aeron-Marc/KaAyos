<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatBotController extends Controller
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
            $service = app(ChatBotService::class);
            $result = $service->chat(
                $validated['message'],
                $validated['history'] ?? []
            );

            return response()->json([
                'success' => true,
                'reply' => $result['reply'],
                'suggestions' => $result['suggestions'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'reply' => 'I\'m sorry, something went wrong. Please try again or visit our Contact page for assistance.',
                'suggestions' => [
                    'How do I book a worker?',
                    'What areas do you serve?',
                    'Contact support',
                ],
            ], 500);
        }
    }
}
