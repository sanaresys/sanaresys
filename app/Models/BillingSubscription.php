<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingSubscription extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'centro_id',
        'clinic_registration_request_id',
        'provider',
        'paypal_subscription_id',
        'paypal_plan_id',
        'plan_code',
        'provider_status',
        'status',
        'currency',
        'amount',
        'starts_at',
        'current_period_start_at',
        'current_period_end_at',
        'renews_at',
        'canceled_at',
        'last_synced_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'current_period_start_at' => 'datetime',
            'current_period_end_at' => 'datetime',
            'renews_at' => 'datetime',
            'canceled_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistrationRequest::class, 'clinic_registration_request_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
