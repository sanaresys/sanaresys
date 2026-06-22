<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingTenantSubscription extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'centro_id',
        'clinic_registration_request_id',
        'status',
        'plan_code',
        'billing_interval',
        'anchor_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'next_charge_at',
        'grace_until',
        'cancel_at_period_end',
        'canceled_at',
        'dunning_attempts',
        'last_successful_charge_at',
        'last_failed_charge_at',
        'last_invoice_id',
        'consent_at',
        'consent_text_version',
        'consent_ip',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'anchor_at' => 'datetime',
            'current_period_starts_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'next_charge_at' => 'datetime',
            'grace_until' => 'datetime',
            'cancel_at_period_end' => 'boolean',
            'canceled_at' => 'datetime',
            'dunning_attempts' => 'integer',
            'last_successful_charge_at' => 'datetime',
            'last_failed_charge_at' => 'datetime',
            'consent_at' => 'datetime',
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

    public function lastInvoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'last_invoice_id');
    }

    public function allowsPanelAccess(): bool
    {
        return in_array($this->status, ['active', 'past_due', 'grace'], true);
    }
}
