<?php

namespace App\Jobs;

use App\Models\ApiKey;
use App\Models\LiteLLMSyncLog;
use App\Models\Tenant;
use App\Services\LiteLLM\LiteLLMClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class SyncLiteLLMCosts implements ShouldQueue
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
            'sync_type' => 'costs',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $litellmClient = app(LiteLLMClient::class);
            
            // Get last sync time
            $lastSync = LiteLLMSyncLog::where('sync_type', 'costs')
                ->where('status', 'success')
                ->latest('completed_at')
                ->first();

            $startDate = $lastSync?->completed_at 
                ? $lastSync->completed_at->subHour()->format('Y-m-d') 
                : Carbon::now()->subDays(30)->format('Y-m-d');
            
            $endDate = Carbon::now()->format('Y-m-d');

            // Get all active tenants
            $tenants = Tenant::where('is_active', true)->get();
            
            $totalSynced = 0;

            foreach ($tenants as $tenant) {
                // Get API keys for this tenant
                $apiKeys = ApiKey::where('tenant_id', $tenant->id)
                    ->where('is_active', true)
                    ->pluck('litellm_key_id')
                    ->toArray();

                foreach ($apiKeys as $apiKeyId) {
                    // Fetch costs from LiteLLM
                    $spend = $litellmClient->getSpend($startDate, $endDate, $apiKeyId);
                    
                    if (!empty($spend)) {
                        // Cost data is already aggregated, we can store it in cache or update tenant stats
                        // For now, we just mark as synced
                        $totalSynced++;
                    }
                }
            }

            $syncLog->markCompleted($totalSynced);
        } catch (\Exception $e) {
            $syncLog->markFailed($e->getMessage());
            throw $e;
        }
    }
}
