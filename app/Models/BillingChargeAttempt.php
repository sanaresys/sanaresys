<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingChargeAttempt extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'billing_invoice_id',
        'centro_id',
        'clinic_registration_request_id',
        'requested_by_user_id',
        'context',
        'provider',
        'attempt_number',
        'status',
        'currency',
        'amount',
        'paypal_order_id',
        'paypal_capture_id',
        'approve_url',
        'approved_at',
        'captured_at',
        'failed_at',
        'refunded_at',
        'failure_code',
        'failure_message',
        'payload',
        'capture_payload',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'captured_at' => 'datetime',
            'failed_at' => 'datetime',
            'refunded_at' => 'datetime',
            'payload' => 'array',
            'capture_payload' => 'array',
            'meta' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'billing_invoice_id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ClinicRegistrationRequest::class, 'clinic_registration_request_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
