<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatBotService
{
    protected string $provider;
    protected string $apiKey;
    protected string $model;
    protected AiTools $tools;
    protected array $history = [];
    protected ?User $user = null;

    public function __construct()
    {
        $this->provider = config('kaayos.chatbot_provider', 'openai');
        $this->apiKey = config('kaayos.chatbot_api_key', '');
        $this->model = config('kaayos.chatbot_model', 'gpt-4o-mini');
        $this->tools = app(AiTools::class);
    }

    public function chat(string $message, array $history = [], ?User $user = null): array
    {
        $this->history = $this->normalizeHistory($history);
        $this->user = $user;
        $this->tools->setUser($user);

        $intent = $this->detectIntent($message);

        if ($intent !== 'default') {
            return match ($intent) {
                'greeting'   => $this->greeting(),
                'services'   => $this->askServices(),
                'workers'    => $this->askWorkers($message),
                'booking'    => $this->askBooking(),
                'areas'      => $this->askAreas(),
                'verify'     => $this->askVerification(),
                'pricing'    => $this->askPricing(),
                'review'     => $this->askReview(),
                'cancel'     => $this->askCancel(),
                'contact'    => $this->askContact(),
            };
        }

        if ($this->isHarmfulQuery($message)) {
            return [
                'reply' => 'I\'m sorry, but I can only answer questions about KaAyos home services. Please ask me about booking workers, available services, or anything related to home service needs.',
                'suggestions' => [
                    'What services are available?',
                    'How do I book a worker?',
                    'What areas do you serve?',
                ],
            ];
        }

        if (!empty($this->apiKey)) {
            return match ($this->provider) {
                'openrouter' => $this->askWithTools($message),
                'gemini'     => $this->askGemini($message),
                default      => $this->askOpenAI($message),
            };
        }

        return $this->askHelp($message);
    }

    protected function systemPrompt(): string
    {
        $catCount = ServiceCategory::where('is_active', true)->count();
        $workerCount = User::where('role', 'worker')->count();

        $userLocationBlock = '';
        if ($this->user !== null && $this->user->barangay) {
            $city = $this->user->city ?? 'Tuy, Batangas';
            $userLocationBlock = "\nUser location: The current user is logged in and located in Brgy. {$this->user->barangay}, {$city}. When they ask for workers \"near me\", \"nearby\", \"close to me\", or any proximity-based request, use search_workers with their barangay to find and rank workers by distance. Mention their barangay in your reply so they know the search was personalized.\n";
        } else {
            $userLocationBlock = "\nUser location: The user is not logged in (guest). If they ask about nearby workers, politely ask which barangay they are in (e.g., \"What barangay are you located in so I can find the closest workers?\") and then call search_workers with their barangay. Do NOT guess their location.\n";
        }

        return <<<PROMPT
You are KaAyos AI Assistant — a helpful support chatbot for KaAyos, a home service marketplace in Tuy, Batangas, Philippines.
        Your role is to assist clients (homeowners) with finding, evaluating, and booking skilled workers ("workers").

Key facts about KaAyos:
- Has {$catCount} service categories and {$workerCount} registered workers
- Workers are verified with government ID and barangay clearance before going live
- The system matches workers by distance, rating, skill match, and completion rate
- KaAyos currently serves all 22 barangays of Tuy, Batangas
- Clients can browse workers, read reviews, chat with workers, and book directly
- Bookings go through statuses: new → accepted → en_route → in_progress → completed
- Workers can be cancelled only when status is "new" or "accepted"
- Pricing is agreed between client and worker (hourly or fixed)
- A 10% platform fee applies to completed jobs
- Clients must be logged in to book a worker
- Users can register as both client and worker with one account
{$userLocationBlock}
How to behave:
- Be friendly, concise, and helpful. Respond in clear, professional English only.
- Keep responses under 3 paragraphs unless listing workers/services.
- You MAY recommend, rank, compare, and give opinions about workers, services, pricing, and booking options. This is your core job — please do it confidently.
- When a user asks you to recommend, find, rank, or compare workers or services, CALL the appropriate tool first (get_categories, search_workers, get_worker_detail, check_worker_bookings) and base your answer on the returned data.
- When recommending workers, briefly explain why based on real tool data (rating, reviews, verified status, experience, skills, distance).
- If a follow-up question refers to "him", "the first one", "that worker", or similar pronouns, use the conversation history to resolve who the user means and use get_worker_detail if you need more info.
- Always suggest 3 relevant follow-up questions at the end.

When to refuse (only these cases):
- Requests that are sexually explicit, lewd, or romantic in nature
- Requests that promote violence, self-harm, or illegal activity
- Requests to bypass security, impersonate someone, or hack
- Requests for a specific user's personal information (phone, address, ID numbers, password)
- Questions that are CLEARLY unrelated to KaAyos home services (e.g., coding help, medical advice, homework, news, politics). Politely explain you can only help with KaAyos home services and suggest a relevant KaAyos topic instead.

Casual small talk, greetings, and brief personal remarks are fine and do NOT require refusal — just steer them back to KaAyos topics naturally.
Do NOT share any user's personal information.
You have tools available to query categories, services, workers, and bookings. When a user asks for information or recommendations, use the appropriate tool instead of making up data.
PROMPT;
    }

    protected function askWithTools(string $message): array
    {
        $messages = [['role' => 'system', 'content' => $this->systemPrompt()]];
        foreach ($this->history as $entry) {
            $messages[] = ['role' => $entry['role'], 'content' => $entry['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $tools = $this->tools->getDefinitions();

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer'  => config('kaayos.openrouter_site', ''),
                'X-Title'       => config('kaayos.openrouter_name', 'KaAyos'),
            ])->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model'       => $this->model,
                'messages'    => $messages,
                'tools'       => $tools,
                'temperature' => 0.7,
                'max_tokens'  => 1000,
            ]);

            if ($response->failed()) {
                Log::error('OpenRouter API error', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->fallbackResponse();
            }

            $data = $response->json();
            $choice = $data['choices'][0]['message'] ?? [];
            $reply = $choice['content'] ?? '';

            if (!empty($choice['tool_calls'])) {
                $messages[] = $choice;

                foreach ($choice['tool_calls'] as $toolCall) {
                    $functionName = $toolCall['function']['name'] ?? '';
                    $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true);
                    $result = $this->tools->execute($functionName, $arguments ?? []);

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'content' => $result,
                    ];
                }

                $response2 = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'HTTP-Referer'  => config('kaayos.openrouter_site', ''),
                    'X-Title'       => config('kaayos.openrouter_name', 'KaAyos'),
                ])->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'temperature' => 0.7,
                    'max_tokens'  => 1000,
                ]);

                if ($response2->failed()) {
                    Log::error('OpenRouter tool response error', ['status' => $response2->status()]);
                    return $this->fallbackResponse();
                }

                $data2 = $response2->json();
                $reply = $data2['choices'][0]['message']['content'] ?? '';
            }

            return [
                'reply'       => $reply,
                'suggestions' => $this->getSuggestions($reply),
            ];

        } catch (\Exception $e) {
            Log::error('OpenRouter exception: ' . $e->getMessage());
            return $this->fallbackResponse();
        }
    }

    protected function askOpenAI(string $message): array
    {
        $messages = [['role' => 'system', 'content' => $this->systemPrompt()]];
        foreach ($this->history as $entry) {
            $messages[] = ['role' => $entry['role'], 'content' => $entry['content']];
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

    protected function askGemini(string $message): array
    {
        $contents = [];
        foreach ($this->history as $entry) {
            $role = $entry['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = ['role' => $role, 'parts' => [['text' => $entry['content']]]];
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
        if (str_contains($lower, 'verif') || str_contains($lower, 'document')) {
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

    protected function normalizeHistory(array $history): array
    {
        $normalized = [];
        $count = 0;
        foreach ($history as $entry) {
            if ($count >= 20) {
                break;
            }
            $role = $entry['role'] ?? '';
            $content = $entry['content'] ?? '';

            if ($role === 'bot' || $role === 'assistant') {
                $role = 'assistant';
            } elseif ($role === 'user') {
                $role = 'user';
            } else {
                continue;
            }

            if (!is_string($content) || trim($content) === '') {
                continue;
            }

            $normalized[] = [
                'role'    => $role,
                'content' => mb_substr($content, 0, 1000),
            ];
            $count++;
        }

        return $normalized;
    }

    protected function containsWord(string $haystack, string $needle): bool
    {
        $pattern = '/(?:^|[\s,.!?])' . preg_quote($needle, '/') . '(?:$|[\s,.!?])/i';
        return preg_match($pattern, $haystack) === 1;
    }

    protected function detectIntent(string $message): string
    {
        $lower = strtolower(trim($message));

        $phrasePatterns = [
            'services' => ['ano-ano', 'anong serbisyo', 'ano ang serbisyo', 'what services', 'service categories', 'are available'],
            'greeting' => ['good morning', 'good afternoon', 'good evening', 'magandang umaga', 'magandang hapon'],
            'booking'  => ['how do i book', 'how to book', 'how to hire', 'how does booking', 'paano mag-book'],
            'areas'    => ['what areas', 'where do you', 'saan kayo', 'what barangays', 'list of barangays', '22 barangays'],
            'verify'   => ['how are workers verified', 'how do you verify', 'document requirements', 'requirements to', 'paano mag-verify'],
            'contact'  => ['contact support', 'customer support', 'technical support'],
        ];
        foreach ($phrasePatterns as $intent => $phrases) {
            foreach ($phrases as $phrase) {
                if (str_contains($lower, $phrase)) {
                    return $intent;
                }
            }
        }

        $keywordPatterns = [
            'booking'  => ['paano', 'mag-book', 'appointment', 'schedule'],
            'areas'    => ['tuy', 'batangas'],
            'verify'   => ['verify', 'verified', 'verification', 'document', 'clearance', 'badge', 'background'],
            'pricing'  => ['magkano', 'presyo', 'bayad', 'fee', 'payment', 'price', 'cost'],
            'review'   => ['review', 'rating', 'feedback', 'testimonial'],
            'cancel'   => ['cancel', 'refund', 'reschedule', 'modify'],
            'greeting' => ['hello', 'kamusta'],
            'contact'  => ['contact', 'email', 'support', 'complain', 'report'],
            'services' => ['category', 'categories'],
        ];
        foreach ($keywordPatterns as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if ($this->containsWord($lower, $keyword)) {
                    return $intent;
                }
            }
        }

        return 'default';
    }

    protected function isHarmfulQuery(string $message): bool
    {
        $lower = strtolower(trim($message));

        $harmful = [
            'sex', 'sexy', 'porn', 'nude', 'naked', 'fuck', 'shit', 'dick', 'vagina',
            'suck my', 'blowjob', 'hookup', 'escort', 'prostitute', 'dating',
            'kill', 'murder', 'suicide', 'self-harm', 'die', 'death',
            'drugs', 'cocaine', 'weed', 'marijuana', 'gambling', 'casino', 'bet',
            'hack', 'crack', 'exploit', 'steal', 'scam', 'cheat', 'fraud',
            'terrorist', 'bomb', 'weapon', 'gun',
            'fortune telling', 'quack doctor', 'love potion',
            'password', 'login', 'credentials', 'admin',
        ];

        foreach ($harmful as $word) {
            if (str_contains($lower, $word)) {
                return true;
            }
        }

        return false;
    }

    protected function greeting(): array
    {
        $workerCount = User::where('role', 'worker')->count();
        $categoryCount = ServiceCategory::where('is_active', true)->count();

        return [
            'reply' => "👋 Welcome to <strong>KaAyos</strong> — your trusted home service platform in Tuy, Batangas.\n\nWe have <strong>{$workerCount} verified workers</strong> across <strong>{$categoryCount} service categories</strong>. Whether you need a plumber, electrician, carpenter, or cleaner, we can help you find the right worker.\n\nHow can I assist you today?",
            'suggestions' => [
                'What services are available?',
                'How do I book a worker?',
                'Find a plumber near me',
            ],
        ];
    }

    protected function askServices(): array
    {
        $categories = ServiceCategory::withCount('services')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($categories->isEmpty()) {
            return $this->askHelp('');
        }

        $lines = $categories->map(fn($c) => "• <strong>{$c->name}</strong> — {$c->services_count} services available")->join("\n");
        $totalServices = $categories->sum('services_count');

        return [
            'reply' => "Here are the service categories we offer:\n\n{$lines}\n\nWe have a total of <strong>{$totalServices} services</strong> across all categories. Would you like to know more about a specific category?",
            'suggestions' => $categories->take(4)->map(fn($c) => "Tell me about {$c->name} services")->values()->toArray(),
        ];
    }

    protected function askWorkers(string $message): array
    {
        $category = null;
        $lower = strtolower($message);

        $catMap = [
            'plumber' => 'Plumbing', 'plumbing' => 'Plumbing', 'tubig' => 'Plumbing', 'pipe' => 'Plumbing',
            'electrician' => 'Electrical', 'electrical' => 'Electrical', 'kuryente' => 'Electrical', 'wir' => 'Electrical', 'circuit' => 'Electrical', 'bolt' => 'Electrical',
            'carpenter' => 'Carpentry', 'carpentry' => 'Carpentry', 'karpintero' => 'Carpentry', 'wood' => 'Carpentry', 'furniture' => 'Carpentry',
            'painter' => 'Painting', 'paint' => 'Painting', 'color' => 'Painting', 'pintor' => 'Painting',
            'clean' => 'Cleaning', 'cleaning' => 'Cleaning', 'linis' => 'Cleaning', 'house' => 'Cleaning',
            'garden' => 'Gardening', 'gardening' => 'Gardening', 'lawn' => 'Gardening', 'halaman' => 'Gardening',
            'welder' => 'Welding', 'welding' => 'Welding',
            'mason' => 'Masonry', 'masonry' => 'Masonry', 'construction' => 'Masonry',
        ];

        foreach ($catMap as $key => $cat) {
            if (str_contains($lower, $key)) {
                $category = $cat;
                break;
            }
        }

        if (!$category) {
            $dbCategories = ServiceCategory::where('is_active', true)->pluck('name');
            foreach ($dbCategories as $dbCat) {
                if (str_contains($lower, strtolower($dbCat))) {
                    $category = $dbCat;
                    break;
                }
            }
        }

        $query = User::where('role', 'worker')
            ->whereHas('workerProfile')
            ->with('workerProfile');

        if ($category) {
            $query->where('service_category', $category);
            $workers = $query->inRandomOrder()->take(5)->get();
        } else {
            $workers = $query->inRandomOrder()->take(5)->get();
        }

        if ($workers->isEmpty()) {
            $msg = $category
                ? "There are currently <strong>no workers registered</strong> under the <strong>{$category}</strong> category. This category might be new or workers haven't signed up yet. Try browsing other available categories."
                : "I couldn't find any workers matching your search. Try browsing our <a href=\"/#services\">service categories</a> to see available workers.";
            return [
                'reply' => $msg,
                'suggestions' => $category
                    ? ['What services are available?', 'How do I book a worker?', 'Tell me about Plumbing services']
                    : ['What services are available?', 'How do I book a worker?', 'Browse all workers'],
            ];
        }

        $intro = $category
            ? "Here are some <strong>{$category}</strong> workers available in Tuy, Batangas:"
            : "Here are some of our verified workers in Tuy, Batangas:";

        $lines = $workers->map(function ($w) {
            $rate = $w->workerProfile?->hourly_rate ? '₱' . number_format($w->workerProfile->hourly_rate) . '/hr' : 'Negotiable';
            $rating = $w->workerProfile?->average_rating ? number_format($w->workerProfile->average_rating, 1) . '★' : 'New';
            return "• <strong>{$w->name}</strong> — {$rating} — {$rate}";
        })->join("\n");

        $totalWorkers = User::where('role', 'worker')->whereHas('workerProfile')->count();
        $catCount = $category
            ? User::where('role', 'worker')->where('service_category', $category)->whereHas('workerProfile')->count()
            : $totalWorkers;

        return [
            'reply' => "{$intro}\n\n{$lines}\n\nWe have <strong>{$catCount} workers</strong> available" . ($category ? " in {$category}" : '') . ". Visit their profiles to see reviews and book them directly!",
            'suggestions' => [
                'How do I book a worker?',
                'Tell me about Plumbing services',
                'What areas do you serve?',
            ],
        ];
    }

    protected function askBooking(): array
    {
        return [
            'reply' => "Booking a worker on KaAyos is easy! Here's how:\n\n<strong>1. Browse</strong> — Find workers by category on our <a href=\"/\">homepage</a>.\n<strong>2. View Profile</strong> — Check their skills, reviews, and rates.\n<strong>3. Book</strong> — Click \"Book Now\" on their profile (you'll need to <a href=\"/login\">sign in</a> first).\n<strong>4. Chat & Confirm</strong> — Message the worker to agree on schedule and pricing.\n<strong>5. Done!</strong> — After the job, leave a review to help the community.\n\nBookings go through these statuses: <em>New → Accepted → En Route → In Progress → Completed</em>.\n\nA 10% platform fee applies to completed jobs to keep the platform running.",
            'suggestions' => [
                'How are workers verified?',
                'Can I cancel a booking?',
                'How does pricing work?',
            ],
        ];
    }

    protected function askAreas(): array
    {
        return [
            'reply' => "KaAyos currently serves <strong>all 22 barangays of Tuy, Batangas</strong>. We're proudly partnered with <strong>PESO Tuy, Batangas</strong> (Public Employment Service Office) to bring verified workers to your doorstep.\n\nOur 22 barangays include: <em>Lumbangan, Tuy Proper, Bilan, Bolboc, Dalima, Dao, Guinhawa, Lumbangan East, Lumbangan West, Mabini, Palincaro, Putol, Sabang, San Jose, San Juan, San Pedro, Saral, Tuy East, Tuy West, and more.</em>\n\nWe plan to expand to neighboring municipalities in the future!",
            'suggestions' => [
                'What services are available?',
                'How do I book a worker?',
                'Find workers near me',
            ],
        ];
    }

    protected function askVerification(): array
    {
        $verifiedCount = User::where('role', 'worker')
            ->whereHas('workerDocuments', fn($q) => $q->where('status', 'verified'))
            ->count();

        return [
            'reply' => "Safety and trust are our priority! ✅\n\nEvery worker on KaAyos must submit:\n• A valid <strong>government-issued ID</strong>\n• <strong>Barangay clearance</strong>\n\nThese documents are reviewed and verified by our team before the worker's profile goes live. Workers who pass display a <strong>\"Verified\"</strong> badge and a <strong>\"PESO Accredited\"</strong> seal.\n\nWe currently have <strong>{$verifiedCount} fully verified workers</strong> on the platform. You can also read reviews from previous clients to help you choose the right worker.",
            'suggestions' => [
                'How do I book a verified worker?',
                'What if a worker has no reviews?',
                'How do I leave a review?',
            ],
        ];
    }

    protected function askPricing(): array
    {
        return [
            'reply' => "Here's how pricing works on KaAyos:\n\n<strong>For Clients:</strong>\n• Browse and book workers for free — no upfront payment needed\n• Pricing is agreed between you and the worker (hourly rate or fixed price)\n• A <strong>10% platform fee</strong> is added to completed jobs to support KaAyos\n\n<strong>For Workers:</strong>\n• Set your own hourly rate in your profile\n• You receive your full rate; the 10% fee is charged to the client\n\nYou can discuss and negotiate pricing directly with the worker through our in-app chat before confirming the booking.",
            'suggestions' => [
                'How do I book a worker?',
                'Are there any hidden fees?',
                'Can I negotiate the price?',
            ],
        ];
    }

    protected function askReview(): array
    {
        $avgRating = Review::avg('rating');
        $reviewCount = Review::count();

        return [
            'reply' => "Reviews help our community! Here's how they work:\n\n• After a job is completed, you can rate the worker from 1–5 stars\n• Leave a comment to help others know what to expect\n• Workers build their reputation through honest feedback\n\nOur platform has <strong>{$reviewCount} reviews</strong> with an average rating of <strong>" . number_format($avgRating, 1) . "★</strong>.\n\nYou can read reviews on each worker's profile before booking to make an informed decision.",
            'suggestions' => [
                'How do I find the right worker?',
                'How are workers verified?',
                'Can I edit my review?',
            ],
        ];
    }

    protected function askCancel(): array
    {
        return [
            'reply' => "Need to cancel a booking? Here's what you should know:\n\n• You can cancel a booking when its status is <strong>\"New\"</strong> or <strong>\"Accepted\"</strong>\n• Once the worker is en route or already working, cancellation is no longer available\n• If you need to reschedule, you can message the worker through in-app chat\n\nTo cancel, go to your <strong>Bookings</strong> page and use the cancel button if the status allows it. If you have any issues, <a href=\"/contact\">contact our support team</a>.",
            'suggestions' => [
                'What is the refund policy?',
                'How do I contact the worker?',
                'Can I reschedule?',
            ],
        ];
    }

    protected function askContact(): array
    {
        return [
            'reply' => "Need help? Here's how to reach us:\n\n• <strong>Contact Page:</strong> <a href=\"/contact\">kaayos.com/contact</a>\n• <strong>Email:</strong> hello@kaayos.com\n• <strong>Facebook:</strong> facebook.com/kaayos\n\nYou can also browse our <a href=\"/#faq\">FAQ section</a> for quick answers to common questions. If you're logged in, you can report issues directly from your dashboard.",
            'suggestions' => [
                'How do I book a worker?',
                'How are workers verified?',
                'What areas do you serve?',
            ],
        ];
    }

    protected function askHelp(string $message): array
    {
        return [
            'reply' => "I'm not sure I understood that. Let me help you with what I know!\n\nI can answer questions about:\n• <strong>Services</strong> — What categories and workers are available\n• <strong>Booking</strong> — How to hire a worker step-by-step\n• <strong>Areas</strong> — Where we operate (Tuy, Batangas)\n• <strong>Verification</strong> — How workers are vetted\n• <strong>Pricing</strong> — Costs, fees, and payments\n• <strong>Cancellation</strong> — Modifying or cancelling bookings\n\nOr you can browse our <a href=\"/#faq\">FAQ</a> for more information.\n\nHow can I help you?",
            'suggestions' => [
                'What services are available?',
                'How do I book a worker?',
                'What areas do you serve?',
            ],
        ];
    }
}
