<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingModuleSubscription extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'centro_id',
        'billing_module_id',
        'status',
        'billing_interval',
        'currency',
        'amount',
        'anchor_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'next_charge_at',
        'grace_until',
        'cancel_at_period_end',
        'dunning_attempts',
        'last_successful_charge_at',
        'last_failed_charge_at',
        'last_invoice_id',
        'starts_at',
        'ends_at',
        'renews_at',
        'last_payment_at',
        'last_refund_at',
        'last_paypal_order_id',
        'last_paypal_capture_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'anchor_at' => 'datetime',
            'current_period_starts_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'next_charge_at' => 'datetime',
            'grace_until' => 'datetime',
            'cancel_at_period_end' => 'boolean',
            'dunning_attempts' => 'integer',
            'last_successful_charge_at' => 'datetime',
            'last_failed_charge_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'renews_at' => 'datetime',
            'last_payment_at' => 'datetime',
            'last_refund_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(BillingModule::class, 'billing_module_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(BillingModuleOrder::class, 'billing_module_subscription_id');
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(BillingModuleReminderLog::class, 'billing_module_subscription_id');
    }

    public function lastInvoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'last_invoice_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'past_due', 'grace'], true);
    }
}
