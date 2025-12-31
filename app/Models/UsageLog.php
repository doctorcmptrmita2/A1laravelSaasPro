<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([TenantScope::class])]

class UsageLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'api_key_id',
        'litellm_log_id',
        'endpoint',
        'method',
        'status_code',
        'response_time',
        'tokens_used',
        'cost',
        'metadata',
        'created_at',
        'synced_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'response_time' => 'integer',
        'tokens_used' => 'integer',
        'cost' => 'decimal:6',
        'created_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Get the tenant that owns the usage log.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the API key that owns the usage log.
     */
    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
