<?php

namespace App\Services;

use App\Models\Tenant;

class ProvisionResult
{
    public function __construct(
        public Tenant $tenant,
        public string $primaryDomain,
        public string $databaseName,
        public int $adminUserId,
    ) {
    }
}

