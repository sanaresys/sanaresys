<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicRegistrationRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING_VERIFICATION = 'pending_verification';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_PROVISIONED = 'provisioned';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';

    protected $connection = 'mysql';

    protected $fillable = [
        'public_id',
        'status',
        'nombre_centro',
        'slug',
        'direccion',
        'telefono',
        'rtn',
        'owner_name',
        'owner_email',
        'password_encrypted',
        'verification_sent_at',
        'verification_expires_at',
        'verified_at',
        'provisioned_at',
        'failed_at',
        'resend_count',
        'centro_id',
        'tenant_id',
        'primary_domain',
        'onboarding_redirect_url',
        'failure_code',
        'failure_message',
    ];

    protected function casts(): array
    {
        return [
            'verification_sent_at' => 'datetime',
            'verification_expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'provisioned_at' => 'datetime',
            'failed_at' => 'datetime',
            'resend_count' => 'integer',
            'centro_id' => 'integer',
        ];
    }

    public function isPendingVerification(): bool
    {
        return $this->status === self::STATUS_PENDING_VERIFICATION;
    }

    public function isProvisioned(): bool
    {
        return $this->status === self::STATUS_PROVISIONED;
    }

    public function isExpired(): bool
    {
        return $this->verification_expires_at !== null
            && now()->greaterThan($this->verification_expires_at);
    }
}

