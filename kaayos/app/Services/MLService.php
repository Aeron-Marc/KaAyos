<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MLService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('kaayos.ml_service_url', 'http://127.0.0.1:8000');
    }

    public function health(): ?array
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/health");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('ML service health check failed: ' . $e->getMessage());
        }
        return null;
    }

    public function predict(array $workers): ?array
    {
        if (empty($workers)) {
            return null;
        }

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/predict", [
                'workers' => $workers,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('ML predict failed', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Exception $e) {
            Log::warning('ML predict exception: ' . $e->getMessage());
        }

        return null;
    }

    public function isAvailable(): bool
    {
        return $this->health() !== null;
    }
}
