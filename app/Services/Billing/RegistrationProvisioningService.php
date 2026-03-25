<?php

namespace App\Services\Billing;

use App\Models\Centros_Medico;
use App\Models\ClinicRegistrationRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantIdentityService;
use App\Services\TenantProvisioningService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RegistrationProvisioningService
{
    public function __construct(
        protected TenantIdentityService $identityService,
        protected TenantProvisioningService $provisioningService,
        protected BillingSubscriptionService $billingSubscriptionService,
    ) {
    }

    public function provisionFromPaidRegistration(ClinicRegistrationRequest $registration): ?string
    {
        try {
            return DB::connection('mysql')->transaction(function () use ($registration): ?string {
                $locked = ClinicRegistrationRequest::query()
                    ->whereKey($registration->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($locked->isProvisioned()) {
                    return $this->issueTenantAccessUrl($locked) ?: $locked->onboarding_redirect_url;
                }

                if (! in_array($locked->payment_status, ['paid', 'active'], true)) {
                    return null;
                }

                if (! in_array($locked->status, [
                    ClinicRegistrationRequest::STATUS_VERIFIED,
                    ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
                ], true)) {
                    return null;
                }

                try {
                    $password = Crypt::decryptString((string) $locked->password_encrypted);
                } catch (DecryptException) {
                    throw ValidationException::withMessages([
                        'password' => 'No se pudo validar la solicitud. Registra la clinica nuevamente.',
                    ]);
                }

                if (Centros_Medico::on('mysql')->where('rtn', $locked->rtn)->exists()) {
                    throw ValidationException::withMessages([
                        'rtn' => 'El RTN ya esta en uso por otra clinica.',
                    ]);
                }

                if ($this->provisioningService->emailExistsInAnyTenant($locked->owner_email)) {
                    throw ValidationException::withMessages([
                        'owner_email' => 'El correo ya esta en uso en otro tenant.',
                    ]);
                }

                $this->identityService->validateSlugAvailable($locked->slug);

                $billingInvoice = $locked->billingInvoice()->first();

                $centro = Centros_Medico::query()->create([
                    'nombre_centro' => $locked->nombre_centro,
                    'direccion' => $locked->direccion,
                    'telefono' => $locked->telefono,
                    'email' => $locked->owner_email,
                    'rtn' => $locked->rtn,
                    'slug' => $locked->slug,
                    'tenancy_mode' => 'domain',
                    'billing_status' => 'active',
                    'billing_plan_code' => $locked->plan_code,
                    'billing_renews_at' => $billingInvoice?->billing_renews_at,
                    'billing_last_sync_at' => now(),
                    'billing_override' => null,
                    'onboarding_current_step' => 0,
                    'onboarding_skipped_cai' => false,
                    'onboarding_completed_at' => null,
                ]);

                $result = $this->provisioningService->provisionNewCenter($centro, [
                    'name' => $locked->owner_name,
                    'email' => $locked->owner_email,
                    'password' => $password,
                ]);

                $locked->forceFill([
                    'status' => ClinicRegistrationRequest::STATUS_PROVISIONED,
                    'centro_id' => $centro->id,
                    'tenant_id' => $result->tenant->id,
                    'primary_domain' => $result->primaryDomain,
                    'onboarding_redirect_url' => null,
                    'provisioned_at' => now(),
                    'password_encrypted' => null,
                    'failure_code' => null,
                    'failure_message' => null,
                    'failed_at' => null,
                ])->save();

                if ($locked->paypal_subscription_id) {
                    $this->billingSubscriptionService->linkSubscriptionToCentro(
                        $locked->paypal_subscription_id,
                        $centro->id
                    );
                }

                Log::info('Clinica provisionada luego de pago activo.', [
                    'registration_public_id' => $locked->public_id,
                    'centro_id' => $centro->id,
                    'tenant_id' => $result->tenant->id,
                    'domain' => $result->primaryDomain,
                ]);

                return $this->issueTenantAccessUrl($locked->fresh());
            });
        } catch (\Throwable $e) {
            // Recovery path for central transaction state lost during tenant bootstrapping.
            if (str_contains(strtolower($e->getMessage()), 'no active transaction')) {
                $fresh = ClinicRegistrationRequest::query()
                    ->whereKey($registration->id)
                    ->first();

                if ($fresh?->isProvisioned()) {
                    Log::warning('Provision termino correctamente pero la transaccion central se cerro antes de commit.', [
                        'registration_public_id' => $fresh->public_id,
                    ]);

                    return $this->issueTenantAccessUrl($fresh) ?: $fresh->onboarding_redirect_url;
                }
            }

            throw $e;
        }
    }

    public function issueTenantAccessUrl(ClinicRegistrationRequest $registration, string $redirectUrl = '/admin'): ?string
    {
        if (! $registration->isProvisioned()) {
            return null;
        }

        $tenant = null;

        if ($registration->tenant_id) {
            $tenant = Tenant::query()->find($registration->tenant_id);
        }

        if (! $tenant && $registration->centro_id) {
            $tenant = Tenant::query()->where('centro_id', $registration->centro_id)->first();
        }

        if (! $tenant) {
            return null;
        }

        $domain = $registration->primary_domain ?: $tenant->getPrimaryDomain();
        if (! $domain) {
            return null;
        }

        $targetUserId = null;

        try {
            tenancy()->initialize($tenant);

            $targetUser = User::query()
                ->where('email', $registration->owner_email)
                ->first()
                ?? User::role('administrador')->first()
                ?? User::query()->first();

            if (! $targetUser) {
                return null;
            }

            $targetUserId = $targetUser->id;
        } finally {
            tenancy()->end();
        }

        $token = tenancy()->impersonate(
            tenant: $tenant,
            userId: (string) $targetUserId,
            redirectUrl: $redirectUrl,
            authGuard: 'web'
        );

        $scheme = (string) config('tenancy.tenant_scheme', 'https');
        $target = "{$scheme}://{$domain}/tenant/impersonate/{$token->token}";

        $registration->forceFill([
            'tenant_id' => $tenant->id,
            'primary_domain' => $domain,
            'onboarding_redirect_url' => $target,
        ])->save();

        return $target;
    }
}
