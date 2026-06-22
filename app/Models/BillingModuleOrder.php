<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingModuleOrder extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'centro_id',
        'billing_module_id',
        'billing_module_subscription_id',
        'requested_by_user_id',
        'provider',
        'paypal_order_id',
        'paypal_capture_id',
        'custom_id',
        'status',
        'currency',
        'amount',
        'approve_url',
        'return_url',
        'cancel_url',
        'order_created_at',
        'order_approved_at',
        'captured_at',
        'canceled_at',
        'failed_at',
        'refunded_at',
        'payload',
        'capture_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'order_created_at' => 'datetime',
            'order_approved_at' => 'datetime',
            'captured_at' => 'datetime',
            'canceled_at' => 'datetime',
            'failed_at' => 'datetime',
            'refunded_at' => 'datetime',
            'payload' => 'array',
            'capture_payload' => 'array',
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

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(BillingModuleSubscription::class, 'billing_module_subscription_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}

