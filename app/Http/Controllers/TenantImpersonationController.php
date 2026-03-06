<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Database\Models\ImpersonationToken;
use Stancl\Tenancy\Features\UserImpersonation;

class TenantImpersonationController extends Controller
{
    public function __invoke(string $token)
    {
        if (! tenancy()->initialized) {
            abort(404);
        }

        $impersonationToken = ImpersonationToken::find($token);

        if (! $impersonationToken) {
            Log::warning('Token de impersonacion invalido o ya consumido.', [
                'tenant_id' => tenancy()->tenant?->id,
            ]);

            abort(403);
        }

        Log::info('Intento de consumo de token de impersonacion.', [
            'tenant_id' => tenancy()->tenant?->id,
            'token_tenant_id' => $impersonationToken->tenant_id,
            'target_user_id' => $impersonationToken->user_id,
        ]);

        return UserImpersonation::makeResponse($impersonationToken, 120);
    }
}
