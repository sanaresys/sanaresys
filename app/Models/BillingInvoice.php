<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingInvoice extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'public_id',
        'centro_id',
        'clinic_registration_request_id',
        'kind',
        'status',
        'currency',
        'subtotal',
        'total',
        'due_at',
        'grace_until',
        'paid_at',
        'voided_at',
        'refunded_at',
        'billing_starts_at',
        'billing_ends_at',
        'billing_renews_at',
        'last_notified_at',
        'notes',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'due_at' => 'datetime',
            'grace_until' => 'datetime',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
            'refunded_at' => 'datetime',
            'billing_starts_at' => 'datetime',
            'billing_ends_at' => 'datetime',
            'billing_renews_at' => 'datetime',
            'last_notified_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(BillingInvoiceItem::class, 'billing_invoice_id');
    }

    public function chargeAttempts(): HasMany
    {
        return $this->hasMany(BillingChargeAttempt::class, 'billing_invoice_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(BillingAudit::class, 'billing_invoice_id');
    }
}
