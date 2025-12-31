<?php

namespace App\Jobs;

use App\Models\ApiKey;
use App\Models\LiteLLMSyncLog;
use App\Models\Tenant;
use App\Models\UsageLog;
use App\Services\LiteLLM\LiteLLMClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class SyncLiteLLMLogs implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $syncLog = LiteLLMSyncLog::create([
            'sync_type' => 'logs',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $litellmClient = app(LiteLLMClient::class);
            
            // Get last sync time
            $lastSync = LiteLLMSyncLog::where('sync_type', 'logs')
                ->where('status', 'success')
                ->latest('completed_at')
                ->first();

            $startDate = $lastSync?->completed_at 
                ? $lastSync->completed_at->subMinutes(5)->format('Y-m-d') 
                : Carbon::now()->subDays(30)->format('Y-m-d');
            
            $endDate = Carbon::now()->format('Y-m-d');

            // Get all active tenants
            $tenants = Tenant::where('is_active', true)->get();
            
            $totalSynced = 0;
            $lastSyncedId = null;

            foreach ($tenants as $tenant) {
                // Get API keys for this tenant
                $apiKeys = ApiKey::where('tenant_id', $tenant->id)
                    ->where('is_active', true)
                    ->get();

                // Fetch all logs from LiteLLM (without filtering by API key first)
                \Log::info('Fetching logs from LiteLLM', [
                    'tenant_id' => $tenant->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'base_url' => config('litellm.base_url'),
                ]);
                
                $logs = $litellmClient->getLogs($startDate, $endDate, 1000, null);

                \Log::info('Fetched logs from LiteLLM', [
                    'count' => count($logs),
                    'tenant_id' => $tenant->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'sample_log' => count($logs) > 0 ? $logs[0] : null,
                    'all_log_keys' => count($logs) > 0 ? array_keys($logs[0]) : [],
                ]);

                foreach ($logs as $log) {
                    // Check if log already exists
                    $existingLog = UsageLog::withoutGlobalScopes()
                        ->where('litellm_log_id', $log['id'] ?? $log['request_id'] ?? null)
                        ->first();
                    
                    if ($existingLog) {
                        continue;
                    }

                    // Find API key by key hash or user_api_key
                    $apiKey = null;
                    $keyHash = $log['key_hash'] ?? $log['user_api_key'] ?? $log['api_key'] ?? null;
                    
                    if ($keyHash) {
                        // Try to find by matching key hash with stored API keys
                        // LiteLLM stores key_hash as SHA256 hash of the API key
                        foreach ($apiKeys as $key) {
                            // Get the actual API key (we need to check the hashed key in database)
                            // Since key is hashed in database, we need to check litellm_key_id
                            // or try to match the hash
                            
                            // First, try direct match with litellm_key_id
                            if ($key->litellm_key_id === $keyHash) {
                                $apiKey = $key;
                                break;
                            }
                            
                            // Try matching with hash of litellm_key_id (if it's the actual key)
                            $hashedKeyId = hash('sha256', $key->litellm_key_id);
                            if ($hashedKeyId === $keyHash || substr($hashedKeyId, 0, 16) === substr($keyHash, 0, 16)) {
                                $apiKey = $key;
                                break;
                            }
                            
                            // If litellm_key_id is the actual API key (starts with 'sk-'), hash it
                            if (str_starts_with($key->litellm_key_id, 'sk-')) {
                                $hashedActualKey = hash('sha256', $key->litellm_key_id);
                                if ($hashedActualKey === $keyHash || substr($hashedActualKey, 0, 16) === substr($keyHash, 0, 16)) {
                                    $apiKey = $key;
                                    break;
                                }
                            }
                        }
                    }
                    
                    if (!$apiKey) {
                        \Log::debug('API key not found for log', [
                            'key_hash' => $keyHash,
                            'log_id' => $log['id'] ?? $log['request_id'] ?? null,
                            'available_keys' => $apiKeys->pluck('litellm_key_id')->toArray(),
                            'log_keys' => array_keys($log),
                        ]);
                        continue;
                    }

                    // Extract tokens from log
                    $tokensUsed = 0;
                    if (isset($log['total_tokens'])) {
                        $tokensUsed = $log['total_tokens'];
                    } elseif (isset($log['usage']['total_tokens'])) {
                        $tokensUsed = $log['usage']['total_tokens'];
                    } elseif (isset($log['tokens'])) {
                        $tokensUsed = $log['tokens'];
                    }

                    // Extract cost
                    $cost = $log['spend'] ?? $log['cost'] ?? $log['_response_cost'] ?? 0;

                    // Extract response time (convert to milliseconds)
                    $responseTime = null;
                    if (isset($log['response_time'])) {
                        $responseTime = is_numeric($log['response_time']) 
                            ? (int)($log['response_time'] * 1000) 
                            : (int)$log['response_time'];
                    } elseif (isset($log['duration'])) {
                        $responseTime = (int)($log['duration'] * 1000);
                    }

                    // Create usage log
                    UsageLog::withoutGlobalScopes()->create([
                        'tenant_id' => $tenant->id,
                        'api_key_id' => $apiKey->id,
                        'litellm_log_id' => $log['id'] ?? $log['request_id'] ?? null,
                        'endpoint' => $log['path'] ?? $log['endpoint'] ?? '/unknown',
                        'method' => $log['method'] ?? 'POST',
                        'status_code' => $log['status_code'] ?? ($log['status'] === 'Success' ? 200 : 500),
                        'response_time' => $responseTime,
                        'tokens_used' => $tokensUsed,
                        'cost' => $cost,
                        'metadata' => [
                            'model' => $log['model'] ?? null,
                            'request_id' => $log['request_id'] ?? null,
                            'session_id' => $log['session_id'] ?? null,
                        ],
                        'created_at' => isset($log['created_at']) 
                            ? Carbon::parse($log['created_at']) 
                            : (isset($log['timestamp']) ? Carbon::parse($log['timestamp']) : now()),
                        'synced_at' => now(),
                    ]);

                    $totalSynced++;
                    $lastSyncedId = $log['id'] ?? $log['request_id'] ?? null;
                }
            }

            $syncLog->markCompleted($totalSynced, $lastSyncedId);
        } catch (\Exception $e) {
            $syncLog->markFailed($e->getMessage());
            throw $e;
        }
    }
}
