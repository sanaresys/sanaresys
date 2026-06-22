<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingAudit extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'centro_id',
        'billing_invoice_id',
        'billing_tenant_subscription_id',
        'billing_module_subscription_id',
        'actor_user_id',
        'actor_type',
        'event_type',
        'reason',
        'old_values',
        'new_values',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'meta' => 'array',
        ];
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
