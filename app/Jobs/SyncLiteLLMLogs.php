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
                    ->pluck('litellm_key_id')
                    ->toArray();

                foreach ($apiKeys as $apiKeyId) {
                    // Fetch logs from LiteLLM
                    $logs = $litellmClient->getLogs($startDate, $endDate, 1000, $apiKeyId);

                    foreach ($logs as $log) {
                        // Check if log already exists
                        $existingLog = UsageLog::where('litellm_log_id', $log['id'] ?? null)->first();
                        
                        if ($existingLog) {
                            continue;
                        }

                        // Find API key
                        $apiKey = ApiKey::where('litellm_key_id', $log['user_api_key'] ?? null)->first();
                        
                        if (!$apiKey) {
                            continue;
                        }

                        // Create usage log
                        UsageLog::create([
                            'tenant_id' => $tenant->id,
                            'api_key_id' => $apiKey->id,
                            'litellm_log_id' => $log['id'] ?? null,
                            'endpoint' => $log['path'] ?? '/unknown',
                            'method' => 'POST',
                            'status_code' => $log['status_code'] ?? null,
                            'response_time' => isset($log['response_time']) ? (int)($log['response_time'] * 1000) : null,
                            'tokens_used' => $log['total_tokens'] ?? 0,
                            'cost' => $log['spend'] ?? 0,
                            'metadata' => [
                                'model' => $log['model'] ?? null,
                            ],
                            'created_at' => isset($log['created_at']) ? Carbon::parse($log['created_at']) : now(),
                            'synced_at' => now(),
                        ]);

                        $totalSynced++;
                        $lastSyncedId = $log['id'] ?? null;
                    }
                }
            }

            $syncLog->markCompleted($totalSynced, $lastSyncedId);
        } catch (\Exception $e) {
            $syncLog->markFailed($e->getMessage());
            throw $e;
        }
    }
}
