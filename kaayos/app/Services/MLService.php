<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MLService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('kaayos.ml_service_url', 'http://127.0.0.1:8001');
        $this->apiKey = config('kaayos.ml_api_key', '');
    }

    protected function headers(): array
    {
        $headers = ['Accept' => 'application/json'];
        if ($this->apiKey) {
            $headers['X-API-Key'] = $this->apiKey;
        }
        return $headers;
    }

    public function health(): ?array
    {
        try {
            $response = Http::timeout(3)
                ->withHeaders($this->headers())
                ->get("{$this->baseUrl}/health");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::debug('ML service health check failed: ' . $e->getMessage());
        }
        return null;
    }

    public function predict(array $workers): ?array
    {
        if (empty($workers)) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->headers())
                ->post("{$this->baseUrl}/predict", [
                    'workers' => $workers,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::debug('ML predict failed', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Exception $e) {
            Log::debug('ML predict exception: ' . $e->getMessage());
        }

        return null;
    }

    public function isAvailable(): bool
    {
        return $this->health() !== null;
    }
}
