<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingModule extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'code',
        'name',
        'description',
        'price_monthly',
        'price_annual',
        'currency',
        'is_active',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_annual' => 'decimal:2',
            'is_active' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(BillingModuleSubscription::class, 'billing_module_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(BillingModuleOrder::class, 'billing_module_id');
    }
}
