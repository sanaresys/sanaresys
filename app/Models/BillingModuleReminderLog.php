<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingModuleReminderLog extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'billing_module_subscription_id',
        'centro_id',
        'billing_module_id',
        'days_before_expiry',
        'channel',
        'recipient',
        'scheduled_for_date',
        'sent_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'days_before_expiry' => 'integer',
            'scheduled_for_date' => 'date',
            'sent_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(BillingModuleSubscription::class, 'billing_module_subscription_id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(BillingModule::class, 'billing_module_id');
    }
}

