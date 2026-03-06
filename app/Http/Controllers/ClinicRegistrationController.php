<?php

namespace App\Http\Controllers;

use App\Mail\ClinicRegistrationVerificationMail;
use App\Models\ClinicRegistrationRequest;
use App\Models\Centros_Medico;
use App\Services\TenantIdentityService;
use App\Services\TenantProvisioningService;
use App\Support\CentralUrl;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ClinicRegistrationController extends Controller
{
    public function __construct(
        protected TenantIdentityService $identityService,
        protected TenantProvisioningService $provisioningService,
    ) {
    }

    public function create()
    {
        return view('registro-clinica');
    }

    public function success(Request $request)
    {
        $domain   = $request->query('domain');
        $clinic   = $request->query('clinic');
        $redirect = $request->query('redirect');

        if (!$domain || !$redirect) {
            return redirect()->away(CentralUrl::route('clinica.registro'));
        }

        return view('registro-clinica-exito', compact('domain', 'clinic', 'redirect'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_centro' => ['required', 'string', 'max:255'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:50'],
            'rtn' => ['required', 'string', 'max:100', 'unique:centros_medicos,rtn'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($this->provisioningService->emailExistsInAnyTenant($validated['owner_email'])) {
            throw ValidationException::withMessages([
                'owner_email' => 'El correo ya esta en uso en otro tenant.',
            ]);
        }

        $slug = $this->identityService->generateSlug($validated['nombre_centro']);
        $this->identityService->validateSlugAvailable($slug);

        $registration = ClinicRegistrationRequest::query()->create([
            'public_id' => (string) Str::uuid(),
            'status' => ClinicRegistrationRequest::STATUS_PENDING_VERIFICATION,
            'nombre_centro' => $validated['nombre_centro'],
            'slug' => $slug,
            'direccion' => $validated['direccion'],
            'telefono' => $validated['telefono'],
            'rtn' => $validated['rtn'],
            'owner_name' => $validated['owner_name'],
            'owner_email' => strtolower($validated['owner_email']),
            'password_encrypted' => Crypt::encryptString($validated['password']),
            'verification_sent_at' => now(),
            'verification_expires_at' => now()->addDay(),
        ]);

        $this->sendVerificationEmail($registration);

        Log::info('Solicitud de registro de clinica pendiente de verificacion.', [
            'registration_public_id' => $registration->public_id,
            'email' => $registration->owner_email,
            'slug' => $registration->slug,
        ]);

        return redirect()
            ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
            ->with('status', 'Te enviamos un correo de verificacion. Revisa tu bandeja de entrada.');
    }

    public function waitVerification(string $publicId)
    {
        $registration = $this->findRegistrationOrFail($publicId);

        if ($registration->isPendingVerification() && $registration->isExpired()) {
            $registration->forceFill([
                'status' => ClinicRegistrationRequest::STATUS_EXPIRED,
                'failed_at' => now(),
                'failure_code' => 'verification_expired',
                'failure_message' => 'El enlace de verificacion expiro.',
            ])->save();
        }

        $canResend = in_array($registration->status, [
            ClinicRegistrationRequest::STATUS_PENDING_VERIFICATION,
            ClinicRegistrationRequest::STATUS_EXPIRED,
        ], true) && $registration->resend_count < 5;

        return view('registro-clinica-waiting', compact('registration', 'canResend'));
    }

    public function resendVerification(string $publicId): RedirectResponse
    {
        $registration = $this->findRegistrationOrFail($publicId);

        if ($registration->isProvisioned()) {
            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('status', 'Esta solicitud ya fue verificada. Puedes continuar.');
        }

        if ($registration->status === ClinicRegistrationRequest::STATUS_FAILED) {
            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('error', 'Esta solicitud fallo. Vuelve a llenar el formulario.');
        }

        if ($registration->resend_count >= 5) {
            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('error', 'Limite de reenvios alcanzado. Inicia una nueva solicitud.');
        }

        $registration->forceFill([
            'status' => ClinicRegistrationRequest::STATUS_PENDING_VERIFICATION,
            'verification_sent_at' => now(),
            'verification_expires_at' => now()->addDay(),
            'failure_code' => null,
            'failure_message' => null,
            'failed_at' => null,
            'resend_count' => $registration->resend_count + 1,
        ])->save();

        $this->sendVerificationEmail($registration);

        return redirect()
            ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
            ->with('status', 'Te enviamos un nuevo correo de verificacion.');
    }

    public function verify(string $publicId): RedirectResponse
    {
        $registration = $this->findRegistrationOrFail($publicId);

        if ($registration->isProvisioned() && $registration->onboarding_redirect_url) {
            return redirect()->away($registration->onboarding_redirect_url);
        }

        if (! in_array($registration->status, [
            ClinicRegistrationRequest::STATUS_PENDING_VERIFICATION,
            ClinicRegistrationRequest::STATUS_EXPIRED,
        ], true)) {
            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('error', 'Esta solicitud no puede verificarse en su estado actual.');
        }

        DB::connection('mysql')->transaction(function () use (&$registration): void {
            $locked = ClinicRegistrationRequest::query()
                ->whereKey($registration->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->isProvisioned()) {
                $registration = $locked;
                return;
            }

            if ($locked->isExpired()) {
                $locked->forceFill([
                    'status' => ClinicRegistrationRequest::STATUS_EXPIRED,
                    'failed_at' => now(),
                    'failure_code' => 'verification_expired',
                    'failure_message' => 'El enlace de verificacion expiro.',
                ])->save();

                $registration = $locked;
                return;
            }

            $locked->forceFill([
                'status' => ClinicRegistrationRequest::STATUS_VERIFIED,
                'verified_at' => $locked->verified_at ?? now(),
                'failure_code' => null,
                'failure_message' => null,
                'failed_at' => null,
            ])->save();

            $registration = $locked;
        });

        if ($registration->isProvisioned() && $registration->onboarding_redirect_url) {
            return redirect()->away($registration->onboarding_redirect_url);
        }

        if ($registration->status === ClinicRegistrationRequest::STATUS_EXPIRED) {
            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('error', 'El enlace expiro. Solicita un reenvio.');
        }

        if ($registration->status !== ClinicRegistrationRequest::STATUS_VERIFIED) {
            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('error', 'No se pudo iniciar la provision de esta solicitud.');
        }

        $centro = null;

        try {
            $password = Crypt::decryptString((string) $registration->password_encrypted);
        } catch (DecryptException $e) {
            $this->failRegistration(
                $registration,
                'password_decrypt_failed',
                'No se pudo validar la solicitud. Registra la clinica nuevamente.'
            );

            return redirect()
                ->to(CentralUrl::route('clinica.registro'))
                ->withErrors(['nombre_centro' => 'La solicitud ya no es valida. Registra la clinica nuevamente.'])
                ->withInput($this->buildInputPayload($registration));
        }

        try {
            if (Centros_Medico::on('mysql')->where('rtn', $registration->rtn)->exists()) {
                throw ValidationException::withMessages([
                    'rtn' => 'El RTN ya esta en uso por otra clinica.',
                ]);
            }

            if ($this->provisioningService->emailExistsInAnyTenant($registration->owner_email)) {
                throw ValidationException::withMessages([
                    'owner_email' => 'El correo ya esta en uso en otro tenant.',
                ]);
            }

            $this->identityService->validateSlugAvailable($registration->slug);

            $centro = Centros_Medico::create([
                'nombre_centro' => $registration->nombre_centro,
                'direccion' => $registration->direccion,
                'telefono' => $registration->telefono,
                'rtn' => $registration->rtn,
                'slug' => $registration->slug,
                'tenancy_mode' => 'domain',
                'onboarding_current_step' => 0,
                'onboarding_skipped_cai' => false,
                'onboarding_completed_at' => null,
            ]);

            $result = $this->provisioningService->provisionNewCenter($centro, [
                'name' => $registration->owner_name,
                'email' => $registration->owner_email,
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

            $registration->forceFill([
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

            Log::info('Clinica provisionada luego de verificar correo.', [
                'registration_public_id' => $registration->public_id,
                'centro_id' => $centro->id,
                'tenant_id' => $result->tenant->id,
                'domain' => $result->primaryDomain,
            ]);

            return redirect()->away($target);
        } catch (ValidationException $e) {
            $this->failRegistration(
                $registration,
                'validation_conflict',
                collect($e->errors())->flatten()->first() ?? 'Conflicto de datos durante la verificacion.'
            );

            return redirect()
                ->to(CentralUrl::route('clinica.registro'))
                ->withErrors($e->errors())
                ->withInput($this->buildInputPayload($registration));
        } catch (Throwable $e) {
            Log::error('Error en provisioning posterior a verificacion de correo.', [
                'registration_public_id' => $registration->public_id,
                'centro_id' => $centro?->id,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            if ($centro) {
                try {
                    $centro->delete();
                } catch (Throwable $cleanupError) {
                    Log::error('Error limpiando centro tras fallo de provisioning.', [
                        'registration_public_id' => $registration->public_id,
                        'centro_id' => $centro->id,
                        'error' => $cleanupError->getMessage(),
                    ]);
                }
            }

            $this->failRegistration(
                $registration,
                'provisioning_failed',
                'No se pudo completar la creacion de la clinica. Intenta nuevamente.'
            );

            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('error', 'No se pudo completar la creacion. Vuelve a llenar el formulario.');
        }
    }

    protected function sendVerificationEmail(ClinicRegistrationRequest $registration): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'clinica.registro.verify',
            $registration->verification_expires_at ?? now()->addDay(),
            ['publicId' => $registration->public_id],
        );

        Mail::to($registration->owner_email)->send(
            new ClinicRegistrationVerificationMail($registration, $verificationUrl)
        );
    }

    protected function failRegistration(
        ClinicRegistrationRequest $registration,
        string $code,
        string $message
    ): void {
        $registration->forceFill([
            'status' => ClinicRegistrationRequest::STATUS_FAILED,
            'failed_at' => now(),
            'failure_code' => $code,
            'failure_message' => $message,
        ])->save();
    }

    protected function findRegistrationOrFail(string $publicId): ClinicRegistrationRequest
    {
        return ClinicRegistrationRequest::query()
            ->where('public_id', $publicId)
            ->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    protected function buildInputPayload(ClinicRegistrationRequest $registration): array
    {
        return [
            'nombre_centro' => $registration->nombre_centro,
            'direccion' => $registration->direccion,
            'telefono' => $registration->telefono,
            'rtn' => $registration->rtn,
            'owner_name' => $registration->owner_name,
            'owner_email' => $registration->owner_email,
        ];
    }
}
