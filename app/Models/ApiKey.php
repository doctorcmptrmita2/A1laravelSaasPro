<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

#[ScopedBy([TenantScope::class])]

class ApiKey extends Model
{
    protected $fillable = [
        'tenant_id',
        'litellm_key_id',
        'name',
        'key',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'key',
    ];

    /**
     * Get the tenant that owns the API key.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the usage logs for the API key.
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(UsageLog::class);
    }

    /**
     * Set the key attribute (hash it).
     */
    public function setKeyAttribute($value): void
    {
        $this->attributes['key'] = Hash::make($value);
    }

    /**
     * Verify the API key.
     */
    public function verifyKey(string $key): bool
    {
        return Hash::check($key, $this->key);
    }
}
