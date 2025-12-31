<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([TenantScope::class])]

class BillingRecord extends Model
{
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'period_start',
        'period_end',
        'total_requests',
        'total_tokens',
        'total_cost',
        'stripe_invoice_id',
        'status',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_requests' => 'integer',
        'total_tokens' => 'integer',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the billing record.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the subscription that owns the billing record.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
