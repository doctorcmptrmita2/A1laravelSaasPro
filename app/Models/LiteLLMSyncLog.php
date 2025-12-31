<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiteLLMSyncLog extends Model
{
    protected $table = 'litellm_sync_logs';

    protected $fillable = [
        'sync_type',
        'status',
        'records_synced',
        'last_synced_id',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'records_synced' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Mark sync as completed.
     */
    public function markCompleted(int $recordsSynced, ?string $lastSyncedId = null): void
    {
        $this->update([
            'status' => 'success',
            'records_synced' => $recordsSynced,
            'last_synced_id' => $lastSyncedId,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark sync as failed.
     */
    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }
}
