<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingWebhookEvent extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'provider',
        'event_id',
        'event_type',
        'resource_type',
        'status',
        'payload',
        'processed_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
