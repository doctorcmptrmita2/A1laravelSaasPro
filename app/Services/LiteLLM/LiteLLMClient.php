<?php

namespace App\Services\LiteLLM;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LiteLLMClient
{
    protected string $baseUrl;
    protected string $masterKey;

    public function __construct()
    {
        $this->baseUrl = config('litellm.base_url');
        $this->masterKey = config('litellm.master_key');
    }

    /**
     * Get logs from LiteLLM.
     */
    public function getLogs(?string $startDate = null, ?string $endDate = null, int $limit = 100, ?string $apiKey = null): array
    {
        $endpoints = [
            '/v1/logs',
            '/global/activity/logs',
            '/logs',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->masterKey}",
                ])->get("{$this->baseUrl}{$endpoint}", [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'limit' => $limit,
                    'api_key' => $apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return is_array($data) ? $data : [];
                }
            } catch (\Exception $e) {
                Log::warning("LiteLLM logs endpoint failed: {$endpoint}", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return [];
    }

    /**
     * Get usage statistics from LiteLLM.
     */
    public function getUsage(?string $startDate = null, ?string $endDate = null, ?string $apiKey = null): array
    {
        $endpoints = [
            '/v1/usage/global',
            '/usage/global',
            '/global/activity/usage',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->masterKey}",
                ])->get("{$this->baseUrl}{$endpoint}", [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'api_key' => $apiKey,
                ]);

                if ($response->successful()) {
                    return $response->json() ?? [];
                }
            } catch (\Exception $e) {
                Log::warning("LiteLLM usage endpoint failed: {$endpoint}", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return [];
    }

    /**
     * Get spend/cost statistics from LiteLLM.
     */
    public function getSpend(?string $startDate = null, ?string $endDate = null, ?string $apiKey = null): array
    {
        $endpoints = [
            '/v1/usage/spend',
            '/usage/spend',
            '/global/activity/spend',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->masterKey}",
                ])->get("{$this->baseUrl}{$endpoint}", [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'api_key' => $apiKey,
                ]);

                if ($response->successful()) {
                    return $response->json() ?? [];
                }
            } catch (\Exception $e) {
                Log::warning("LiteLLM spend endpoint failed: {$endpoint}", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return [];
    }

    /**
     * Get list of API keys from LiteLLM.
     */
    public function getKeys(): array
    {
        $endpoints = [
            '/v1/key/list',
            '/key/list',
            '/global/activity/keys',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->masterKey}",
                ])->get("{$this->baseUrl}{$endpoint}");

                if ($response->successful()) {
                    $data = $response->json();
                    return is_array($data) ? $data : [];
                }
            } catch (\Exception $e) {
                Log::warning("LiteLLM keys endpoint failed: {$endpoint}", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return [];
    }

    /**
     * Get API key info from LiteLLM.
     */
    public function getKeyInfo(string $keyId): ?array
    {
        $endpoints = [
            "/v1/key/info?key_id={$keyId}",
            "/key/info?key_id={$keyId}",
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->masterKey}",
                ])->get("{$this->baseUrl}{$endpoint}");

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                Log::warning("LiteLLM key info endpoint failed: {$endpoint}", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return null;
    }

    /**
     * Create API key in LiteLLM.
     */
    public function createKey(array $data): ?array
    {
        $endpoints = [
            '/v1/key/generate',
            '/key/generate',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->masterKey}",
                ])->post("{$this->baseUrl}{$endpoint}", $data);

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                Log::warning("LiteLLM create key endpoint failed: {$endpoint}", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return null;
    }

    /**
     * Delete API key from LiteLLM.
     */
    public function deleteKey(string $keyId): bool
    {
        $endpoints = [
            "/v1/key/delete?key_id={$keyId}",
            "/key/delete?key_id={$keyId}",
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->masterKey}",
                ])->delete("{$this->baseUrl}{$endpoint}");

                if ($response->successful()) {
                    return true;
                }
            } catch (\Exception $e) {
                Log::warning("LiteLLM delete key endpoint failed: {$endpoint}", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return false;
    }

    /**
     * Proxy request to LiteLLM (for chat completions, etc.).
     */
    public function proxyRequest(string $endpoint, array $data, ?string $apiKey = null): array
    {
        try {
            $headers = [
                'Content-Type' => 'application/json',
            ];

            if ($apiKey) {
                $headers['Authorization'] = "Bearer {$apiKey}";
            }

            $response = Http::withHeaders($headers)
                ->post("{$this->baseUrl}{$endpoint}", $data);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json(),
                'headers' => $response->headers(),
            ];
        } catch (\Exception $e) {
            Log::error("LiteLLM proxy request failed: {$endpoint}", [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'data' => ['error' => $e->getMessage()],
            ];
        }
    }
}

