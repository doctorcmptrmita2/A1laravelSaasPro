<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\UsageLog;
use App\Services\LiteLLM\LiteLLMClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProxyController extends Controller
{
    protected LiteLLMClient $litellmClient;

    public function __construct(LiteLLMClient $litellmClient)
    {
        $this->litellmClient = $litellmClient;
    }

    /**
     * Proxy chat completions request to LiteLLM.
     */
    public function chatCompletions(Request $request)
    {
        return $this->proxyRequest($request, '/v1/chat/completions');
    }

    /**
     * Proxy completions request to LiteLLM.
     */
    public function completions(Request $request)
    {
        return $this->proxyRequest($request, '/v1/completions');
    }

    /**
     * Proxy embeddings request to LiteLLM.
     */
    public function embeddings(Request $request)
    {
        return $this->proxyRequest($request, '/v1/embeddings');
    }

    /**
     * Generic proxy method.
     */
    protected function proxyRequest(Request $request, string $endpoint)
    {
        // Get API key from Authorization header
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !Str::startsWith($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Missing or invalid Authorization header'], 401);
        }

        $apiKey = Str::after($authHeader, 'Bearer ');
        
        // Find API key in database
        $apiKeyModel = ApiKey::where('is_active', true)
            ->get()
            ->first(function ($key) use ($apiKey) {
                return $key->verifyKey($apiKey);
            });

        if (!$apiKeyModel) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        // Check tenant and subscription
        $tenant = $apiKeyModel->tenant;
        
        if (!$tenant || !$tenant->is_active) {
            return response()->json(['error' => 'Tenant is not active'], 403);
        }

        // Check subscription (if not in trial)
        if (!$tenant->trial_ends_at?->isFuture()) {
            $subscription = $tenant->activeSubscription;
            
            if (!$subscription) {
                return response()->json(['error' => 'No active subscription'], 403);
            }
        }

        // Update last used timestamp
        $apiKeyModel->update(['last_used_at' => now()]);

        // Proxy request to LiteLLM
        $startTime = microtime(true);
        $response = $this->litellmClient->proxyRequest($endpoint, $request->all(), $apiKey);
        $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

        // Log usage
        if ($response['success']) {
            $this->logUsage($apiKeyModel, $endpoint, $request->method(), $response, $responseTime);
        }

        // Return response
        $statusCode = $response['status'] ?? 200;
        return response()->json($response['data'], $statusCode);
    }

    /**
     * Log usage to database.
     */
    protected function logUsage(ApiKey $apiKey, string $endpoint, string $method, array $response, float $responseTime): void
    {
        $data = $response['data'] ?? [];
        
        // Extract token and cost information from response
        $tokensUsed = 0;
        $cost = 0;
        $model = null;

        if (isset($data['usage'])) {
            $tokensUsed = $data['usage']['total_tokens'] ?? 0;
        }

        if (isset($data['model'])) {
            $model = $data['model'];
        }

        if (isset($data['_response_cost'])) {
            $cost = $data['_response_cost'];
        }

        UsageLog::create([
            'tenant_id' => $apiKey->tenant_id,
            'api_key_id' => $apiKey->id,
            'endpoint' => $endpoint,
            'method' => $method,
            'status_code' => $response['status'] ?? 200,
            'response_time' => (int) $responseTime,
            'tokens_used' => $tokensUsed,
            'cost' => $cost,
            'metadata' => [
                'model' => $model,
            ],
            'created_at' => now(),
            'synced_at' => now(),
        ]);
    }
}
