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
        $baseUrl = config('litellm.base_url');
        // Remove trailing /v1 from base URL if present (endpoints already include /v1)
        $this->baseUrl = rtrim($baseUrl, '/v1');
        $this->masterKey = config('litellm.master_key');
    }

    /**
     * Get logs from LiteLLM.
     */
    public function getLogs(?string $startDate = null, ?string $endDate = null, int $limit = 100, ?string $apiKey = null): array
    {
        $endpoints = [
            '/global/activity/logs',
            '/v1/logs',
            '/logs',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $params = [
                    'limit' => $limit,
                ];

                if ($startDate) {
                    $params['start_date'] = $startDate;
                }
                if ($endDate) {
                    $params['end_date'] = $endDate;
                }
                if ($apiKey) {
                    $params['api_key'] = $apiKey;
                }

                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->masterKey}",
                ])->timeout(30)->get("{$this->baseUrl}{$endpoint}", $params);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    Log::info("LiteLLM logs endpoint response", [
                        'endpoint' => $endpoint,
                        'status' => $response->status(),
                        'response_type' => gettype($data),
                        'response_keys' => is_array($data) ? array_keys($data) : 'not_array',
                        'response_sample' => is_array($data) && count($data) > 0 ? array_slice($data, 0, 1) : $data,
                    ]);
                    
                    // Handle different response formats
                    if (isset($data['data']) && is_array($data['data'])) {
                        // Response format: {data: [...], total: ...}
                        Log::info("LiteLLM logs: Using 'data' key", ['count' => count($data['data'])]);
                        return $data['data'];
                    } elseif (isset($data['logs']) && is_array($data['logs'])) {
                        // Response format: {logs: [...]}
                        Log::info("LiteLLM logs: Using 'logs' key", ['count' => count($data['logs'])]);
                        return $data['logs'];
                    } elseif (is_array($data) && isset($data[0])) {
                        // Direct array response
                        Log::info("LiteLLM logs: Direct array response", ['count' => count($data)]);
                        return $data;
                    }
                    
                    Log::warning("LiteLLM logs response format unexpected", [
                        'endpoint' => $endpoint,
                        'response_keys' => is_array($data) ? array_keys($data) : 'not_array',
                        'response' => $data,
                    ]);
                } else {
                    Log::warning("LiteLLM logs endpoint failed", [
                        'endpoint' => $endpoint,
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 500), // İlk 500 karakter
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning("LiteLLM logs endpoint failed: {$endpoint}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
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
            '/key/new',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->masterKey}",
                    'Content-Type' => 'application/json',
                ])->timeout(10)->post("{$this->baseUrl}{$endpoint}", $data);

                Log::info("LiteLLM createKey request", [
                    'endpoint' => "{$this->baseUrl}{$endpoint}",
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    Log::info("LiteLLM createKey success", ['response' => $json]);
                    return $json;
                } else {
                    Log::warning("LiteLLM createKey failed", [
                        'endpoint' => $endpoint,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("LiteLLM create key endpoint exception: {$endpoint}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                continue;
            }
        }

        Log::error("LiteLLM createKey: All endpoints failed", [
            'base_url' => $this->baseUrl,
            'endpoints' => $endpoints,
        ]);

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

