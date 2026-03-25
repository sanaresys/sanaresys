<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingNotificationLog extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'centro_id',
        'billing_invoice_id',
        'billing_tenant_subscription_id',
        'billing_module_subscription_id',
        'event_key',
        'channel',
        'recipient',
        'scheduled_for_date',
        'sent_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for_date' => 'date',
            'sent_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }
}
