<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatBotService
{
    protected string $provider;
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->provider = config('kaayos.chatbot_provider', 'openai');
        $this->apiKey = config('kaayos.chatbot_api_key', '');
        $this->model = config('kaayos.chatbot_model', 'gpt-4o-mini');
    }

    protected function systemPrompt(): string
    {
        return <<<PROMPT
You are KaAyos AI Assistant — a helpful support chatbot for KaAyos, a home service marketplace in Tuy, Batangas, Philippines.
Your role is to assist clients (homeowners) with finding and booking skilled workers ("trabahador").

Key facts about KaAyos:
- Service categories: Plumbing, Electrical, Carpentry, Cleaning, Painting, Roofing, Aircon Servicing, Hauling
- Workers are verified with government ID and barangay clearance before going live
- The system uses AI-assisted matching ranking workers by distance, rating, skill match, and completion rate
- KaAyos currently serves all 42 barangays of Tuy, Batangas
- Clients can browse workers, read reviews, chat with workers, and book directly
- Bookings go through statuses: new → accepted → en_route → in_progress → completed
- Workers can be cancelled only when status is "new" or "accepted"
- Pricing is agreed between client and worker (hourly or fixed)
- A 10% platform fee applies to completed jobs
- Clients must be logged in to book a worker
- Users can register as both client and worker with one account

Guidelines:
- Be friendly, concise, and helpful. Use conversational Filipino-English if appropriate.
- If you don't know something, direct the user to contact support via the Contact page.
- Do NOT make up pricing or availability — tell users to check worker profiles for current rates.
- Do NOT share any user's personal information.
- Keep responses under 3 paragraphs.
- Suggest relevant follow-up questions when appropriate.
PROMPT;
    }

    public function chat(string $message, array $history = []): array
    {
        if (empty($this->apiKey)) {
            return $this->fallbackResponse();
        }

        return match ($this->provider) {
            'gemini' => $this->askGemini($message, $history),
            default  => $this->askOpenAI($message, $history),
        };
    }

    protected function askOpenAI(string $message, array $history): array
    {
        $messages = [['role' => 'system', 'content' => $this->systemPrompt()]];

        foreach (array_slice($history, -10) as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'temperature' => 0.7,
                    'max_tokens'  => 500,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->fallbackResponse();
            }

            $data = $response->json();
            $reply = $data['choices'][0]['message']['content'] ?? '';

            return [
                'reply'       => $reply,
                'suggestions' => $this->getSuggestions($reply),
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI exception: ' . $e->getMessage());
            return $this->fallbackResponse();
        }
    }

    protected function askGemini(string $message, array $history): array
    {
        $contents = [];

        foreach (array_slice($history, -10) as $msg) {
            $role = $msg['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = ['role' => $role, 'parts' => [['text' => $msg['content']]]];
        }

        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(30)->post($url, [
                'systemInstruction' => ['parts' => [['text' => $this->systemPrompt()]]],
                'contents'          => $contents,
                'generationConfig'  => [
                    'temperature'     => 0.7,
                    'maxOutputTokens' => 500,
                ],
            ]);

            if ($response->failed()) {
                Log::error('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->fallbackResponse();
            }

            $data = $response->json();
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            return [
                'reply'       => $reply,
                'suggestions' => $this->getSuggestions($reply),
            ];
        } catch (\Exception $e) {
            Log::error('Gemini exception: ' . $e->getMessage());
            return $this->fallbackResponse();
        }
    }

    protected function getSuggestions(string $reply): array
    {
        $all = [
            'How do I book a worker?',
            'What areas do you serve?',
            'How are workers verified?',
            'Can I cancel a booking?',
            'How does pricing work?',
            'How do I leave a review?',
        ];

        if (empty($reply)) return $all;

        $lower = strtolower($reply);

        if (str_contains($lower, 'book')) {
            return ['How do I find the right worker?', 'Can I message a worker first?', 'What if the worker doesn\'t show up?'];
        }
        if (str_contains($lower, 'area') || str_contains($lower, 'service') || str_contains($lower, 'tuy')) {
            return ['What services are available?', 'Do you cover nearby towns?', 'How do I book?'];
        }
        if (str_contains($lower, 'verif') || str_contains($lower, 'document') || str_contains($lower, 'id')) {
            return ['Why do workers need verification?', 'How do I find verified workers?', 'What if a worker has no reviews?'];
        }
        if (str_contains($lower, 'cancel')) {
            return ['Can I reschedule?', 'How do I contact the worker?', 'What is the refund policy?'];
        }
        if (str_contains($lower, 'price') || str_contains($lower, 'cost') || str_contains($lower, 'fee') || str_contains($lower, 'pay')) {
            return ['Are there any hidden fees?', 'How do I pay the worker?', 'Can I negotiate the price?'];
        }
        if (str_contains($lower, 'review') || str_contains($lower, 'rate') || str_contains($lower, 'feedback')) {
            return ['Can I edit my review?', 'How do ratings work?', 'Can I see reviews before booking?'];
        }

        return $all;
    }

    protected function fallbackResponse(): array
    {
        return [
            'reply' => 'I\'m sorry, I\'m having trouble connecting right now. Please try again later or visit our <a href="/contact">Contact page</a> for help.',
            'suggestions' => [
                'How do I book a worker?',
                'What areas do you serve?',
                'How are workers verified?',
            ],
        ];
    }
}
