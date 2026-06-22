<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingInvoiceItem extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'billing_invoice_id',
        'billing_module_id',
        'item_type',
        'description',
        'billing_interval',
        'quantity',
        'unit_amount',
        'amount',
        'period_starts_at',
        'period_ends_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_amount' => 'decimal:2',
            'amount' => 'decimal:2',
            'period_starts_at' => 'datetime',
            'period_ends_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'billing_invoice_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(BillingModule::class, 'billing_module_id');
    }
}
