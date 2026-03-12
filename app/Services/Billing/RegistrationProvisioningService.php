<?php

namespace App\Services\Billing;

use App\Models\Centros_Medico;
use App\Models\ClinicRegistrationRequest;
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

                if ($locked->isProvisioned() && $locked->onboarding_redirect_url) {
                    return $locked->onboarding_redirect_url;
                }

                if ($locked->payment_status !== 'active') {
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

                $centro = Centros_Medico::query()->create([
                    'nombre_centro' => $locked->nombre_centro,
                    'direccion' => $locked->direccion,
                    'telefono' => $locked->telefono,
                    'rtn' => $locked->rtn,
                    'slug' => $locked->slug,
                    'tenancy_mode' => 'domain',
                    'billing_status' => 'active',
                    'billing_plan_code' => $locked->plan_code,
                    'billing_renews_at' => null,
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

                $token = tenancy()->impersonate(
                    tenant: $result->tenant,
                    userId: (string) $result->adminUserId,
                    redirectUrl: '/admin',
                    authGuard: 'web'
                );

                $scheme = (string) config('tenancy.tenant_scheme', 'https');
                $target = "{$scheme}://{$result->primaryDomain}/tenant/impersonate/{$token->token}";

                $locked->forceFill([
                    'status' => ClinicRegistrationRequest::STATUS_PROVISIONED,
                    'centro_id' => $centro->id,
                    'tenant_id' => $result->tenant->id,
                    'primary_domain' => $result->primaryDomain,
                    'onboarding_redirect_url' => $target,
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

                return $target;
            });
        } catch (\Throwable $e) {
            // Recovery path for central transaction state lost during tenant bootstrapping.
            if (str_contains(strtolower($e->getMessage()), 'no active transaction')) {
                $fresh = ClinicRegistrationRequest::query()
                    ->whereKey($registration->id)
                    ->first();

                if ($fresh?->isProvisioned() && $fresh->onboarding_redirect_url) {
                    Log::warning('Provision termino correctamente pero la transaccion central se cerro antes de commit.', [
                        'registration_public_id' => $fresh->public_id,
                    ]);

                    return $fresh->onboarding_redirect_url;
                }
            }

            throw $e;
        }
    }
}
