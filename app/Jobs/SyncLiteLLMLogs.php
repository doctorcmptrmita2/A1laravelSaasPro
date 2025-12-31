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
                $logs = $litellmClient->getLogs($startDate, $endDate, 1000, null);

                \Log::info('Fetched logs from LiteLLM', [
                    'count' => count($logs),
                    'tenant_id' => $tenant->id,
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
                    $keyHash = $log['key_hash'] ?? $log['user_api_key'] ?? null;
                    
                    if ($keyHash) {
                        // Try to find by litellm_key_id (which might be the key hash)
                        $apiKey = $apiKeys->first(function ($key) use ($keyHash) {
                            return $key->litellm_key_id === $keyHash || 
                                   hash('sha256', $key->litellm_key_id) === $keyHash ||
                                   substr(hash('sha256', $key->litellm_key_id), 0, 16) === substr($keyHash, 0, 16);
                        });
                    }
                    
                    // If still not found, try to match by checking all API keys
                    if (!$apiKey) {
                        foreach ($apiKeys as $key) {
                            // Check if the log's key hash matches this API key
                            $keyToCheck = $key->litellm_key_id ?? $key->key;
                            if ($keyHash && (
                                $keyHash === $keyToCheck ||
                                $keyHash === hash('sha256', $keyToCheck) ||
                                substr($keyHash, 0, 16) === substr(hash('sha256', $keyToCheck), 0, 16)
                            )) {
                                $apiKey = $key;
                                break;
                            }
                        }
                    }
                    
                    if (!$apiKey) {
                        \Log::debug('API key not found for log', [
                            'key_hash' => $keyHash,
                            'log_id' => $log['id'] ?? $log['request_id'] ?? null,
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
