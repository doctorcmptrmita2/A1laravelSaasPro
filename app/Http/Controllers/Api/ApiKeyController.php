<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Services\LiteLLM\LiteLLMClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    protected LiteLLMClient $litellmClient;

    public function __construct(LiteLLMClient $litellmClient)
    {
        $this->litellmClient = $litellmClient;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->tenant_id) {
            return response()->json(['error' => 'No tenant associated'], 403);
        }

        $apiKeys = ApiKey::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->get()
            ->map(function ($key) {
                return [
                    'id' => $key->id,
                    'name' => $key->name,
                    'litellm_key_id' => $key->litellm_key_id,
                    'last_used_at' => $key->last_used_at,
                    'created_at' => $key->created_at,
                ];
            });

        return response()->json($apiKeys);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('API Key creation request received', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        
        if (!$user) {
            \Log::warning('Unauthenticated user attempted to create API key');
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        if (!$user->tenant_id) {
            \Log::warning('User without tenant attempted to create API key', [
                'user_id' => $user->id,
            ]);
            return response()->json(['error' => 'No tenant associated'], 403);
        }

        // Create API key in LiteLLM (with fallback to local generation)
        $litellmKey = null;
        $litellmKeyId = null;
        
        try {
            $litellmResponse = $this->litellmClient->createKey([
                'metadata' => [
                    'tenant_id' => $user->tenant_id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ],
            ]);

            if ($litellmResponse) {
                // LiteLLM response format: {'key': 'sk-...', 'key_id': '...'} veya {'key': 'sk-...'}
                $litellmKey = $litellmResponse['key'] ?? $litellmResponse['api_key'] ?? null;
                $litellmKeyId = $litellmResponse['key_id'] ?? $litellmResponse['id'] ?? $litellmKey;
            }
        } catch (\Exception $e) {
            \Log::warning('Exception creating API key in LiteLLM, using fallback', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
            ]);
        }

        // Fallback: Generate API key locally if LiteLLM fails
        if (!$litellmKey) {
            \Log::info('LiteLLM unavailable, generating API key locally', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
            ]);
            
            // Generate a secure API key locally
            $litellmKey = 'sk-' . Str::random(48);
            $litellmKeyId = 'local-' . Str::random(16);
        }

        // Store in database
        $apiKey = ApiKey::create([
            'tenant_id' => $user->tenant_id,
            'litellm_key_id' => $litellmKeyId,
            'name' => $request->name,
            'key' => $litellmKey, // Will be hashed by model
            'is_active' => true,
        ]);

        return response()->json([
            'id' => $apiKey->id,
            'name' => $apiKey->name,
            'key' => $litellmKey, // Return plain key only once
            'litellm_key_id' => $apiKey->litellm_key_id,
            'created_at' => $apiKey->created_at,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        
        $apiKey = ApiKey::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->firstOrFail();

        // Get usage stats from LiteLLM
        $keyInfo = $this->litellmClient->getKeyInfo($apiKey->litellm_key_id);

        return response()->json([
            'id' => $apiKey->id,
            'name' => $apiKey->name,
            'litellm_key_id' => $apiKey->litellm_key_id,
            'last_used_at' => $apiKey->last_used_at,
            'is_active' => $apiKey->is_active,
            'created_at' => $apiKey->created_at,
            'litellm_info' => $keyInfo,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        
        $apiKey = ApiKey::where('tenant_id', $user->tenant_id)
            ->where('id', $id)
            ->firstOrFail();

        // Delete from LiteLLM
        $deleted = $this->litellmClient->deleteKey($apiKey->litellm_key_id);

        if (!$deleted) {
            // Log warning but continue with local deletion
            \Log::warning("Failed to delete API key from LiteLLM: {$apiKey->litellm_key_id}");
        }

        // Deactivate in database (soft delete)
        $apiKey->update(['is_active' => false]);

        return response()->json(['message' => 'API key deleted successfully']);
    }
}
