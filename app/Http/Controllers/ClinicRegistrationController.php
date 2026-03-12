<?php

namespace App\Http\Controllers;

use App\Mail\ClinicRegistrationVerificationMail;
use App\Models\ClinicRegistrationRequest;
use App\Services\Billing\BillingPlanService;
use App\Services\Billing\BillingSubscriptionService;
use App\Services\Billing\PayPalService;
use App\Services\Billing\RegistrationProvisioningService;
use App\Services\TenantIdentityService;
use App\Services\TenantProvisioningService;
use App\Support\CentralUrl;
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
        protected BillingPlanService $billingPlanService,
        protected PayPalService $payPalService,
        protected BillingSubscriptionService $billingSubscriptionService,
        protected RegistrationProvisioningService $registrationProvisioningService,
    ) {
    }

    public function create(Request $request)
    {
        $plans = $this->billingPlanService->all();
        $selectedPlanCode = (string) $request->query('plan', old('plan_code', $this->billingPlanService->defaultPlanCode()));

        if (! isset($plans[$selectedPlanCode])) {
            $selectedPlanCode = $this->billingPlanService->defaultPlanCode();
        }

        return view('registro-clinica', [
            'plans' => $plans,
            'selectedPlanCode' => $selectedPlanCode,
            'selectedPlan' => $plans[$selectedPlanCode] ?? null,
        ]);
    }

    public function success(Request $request)
    {
        $domain = $request->query('domain');
        $clinic = $request->query('clinic');
        $redirect = $request->query('redirect');

        if (! $domain || ! $redirect) {
            return redirect()->away(CentralUrl::route('clinica.registro'));
        }

        return view('registro-clinica-exito', compact('domain', 'clinic', 'redirect'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_code' => ['required', 'string', 'max:32'],
            'nombre_centro' => ['required', 'string', 'max:255'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:50'],
            'rtn' => ['required', 'string', 'max:100', 'unique:centros_medicos,rtn'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $plan = $this->billingPlanService->get($validated['plan_code']);
        $planCode = (string) ($plan['code'] ?? $validated['plan_code']);
        $paypalPlanId = $this->billingPlanService->getPayPalPlanIdOrFail($planCode);

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
            'payment_status' => 'pending',
            'plan_code' => $planCode,
            'paypal_plan_id' => $paypalPlanId,
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
            'plan_code' => $registration->plan_code,
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

        $canStartPayment = in_array($registration->status, [
            ClinicRegistrationRequest::STATUS_VERIFIED,
            ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
        ], true);

        return view('registro-clinica-waiting', compact('registration', 'canResend', 'canStartPayment'));
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

        if ($registration->isPendingPayment()) {
            return $this->startPayment($registration->public_id);
        }

        if (! in_array($registration->status, [
            ClinicRegistrationRequest::STATUS_PENDING_VERIFICATION,
            ClinicRegistrationRequest::STATUS_EXPIRED,
            ClinicRegistrationRequest::STATUS_VERIFIED,
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

        return $this->startPayment($registration->public_id);
    }

    public function startPayment(string $publicId): RedirectResponse
    {
        $registration = $this->findRegistrationOrFail($publicId);

        if ($registration->isProvisioned() && $registration->onboarding_redirect_url) {
            return redirect()->away($registration->onboarding_redirect_url);
        }

        if (! in_array($registration->status, [
            ClinicRegistrationRequest::STATUS_VERIFIED,
            ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
        ], true)) {
            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('error', 'La solicitud aun no esta lista para iniciar pago.');
        }

        if ($registration->paypal_subscription_id) {
            try {
                $subscriptionData = $this->payPalService->getSubscription($registration->paypal_subscription_id);
                $this->syncRegistrationFromPayPal($registration, $subscriptionData);
                $registration->refresh();

                if ($registration->payment_status === 'active') {
                    $target = $this->registrationProvisioningService->provisionFromPaidRegistration($registration);
                    if ($target) {
                        return redirect()->away($target);
                    }
                }
            } catch (Throwable $e) {
                Log::warning('No se pudo sincronizar suscripcion previa al iniciar pago.', [
                    'registration_public_id' => $registration->public_id,
                    'subscription_id' => $registration->paypal_subscription_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        try {
            return $this->startPaymentCheckout($registration);
        } catch (Throwable $e) {
            Log::error('Error iniciando checkout PayPal para registro de clinica.', [
                'registration_public_id' => $registration->public_id,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            $this->markRegistrationFailed(
                $registration,
                'payment_checkout_failed',
                'No se pudo iniciar el pago en PayPal. Intenta nuevamente.'
            );

            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('error', 'No se pudo iniciar el checkout de PayPal.');
        }
    }

    public function paymentReturn(Request $request, string $publicId): RedirectResponse
    {
        $registration = $this->findRegistrationOrFail($publicId);
        $subscriptionId = (string) ($request->query('subscription_id')
            ?? $request->query('token')
            ?? $registration->paypal_subscription_id);

        if ($subscriptionId === '') {
            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('error', 'PayPal no devolvio una suscripcion valida.');
        }

        try {
            $subscriptionData = $this->payPalService->getSubscription($subscriptionId);
            $this->syncRegistrationFromPayPal($registration, $subscriptionData);
            $registration->refresh();

            if ($registration->payment_status === 'active') {
                $target = $this->registrationProvisioningService->provisionFromPaidRegistration($registration);

                if ($target) {
                    return redirect()->away($target);
                }
            }

            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('status', 'Recibimos tu aprobacion de pago. Estamos esperando activacion final de PayPal.');
        } catch (Throwable $e) {
            Log::error('Error procesando retorno PayPal para registro de clinica.', [
                'registration_public_id' => $registration->public_id,
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            $registration->refresh();
            if ($registration->isProvisioned() && $registration->onboarding_redirect_url) {
                return redirect()->away($registration->onboarding_redirect_url);
            }

            if ($registration->payment_status === 'active') {
                return redirect()
                    ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                    ->with('status', 'Pago activo detectado. Completaremos la activacion en segundos.');
            }

            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('error', 'No se pudo confirmar el pago en PayPal. Reintenta desde esta pagina.');
        }
    }

    public function paymentCancel(string $publicId): RedirectResponse
    {
        $registration = $this->findRegistrationOrFail($publicId);

        $registration->forceFill([
            'status' => ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
            'payment_status' => 'pending',
        ])->save();

        return redirect()
            ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
            ->with('error', 'Cancelaste el checkout de PayPal. Puedes intentar nuevamente.');
    }

    protected function startPaymentCheckout(ClinicRegistrationRequest $registration): RedirectResponse
    {
        $planCode = $registration->plan_code ?: $this->billingPlanService->defaultPlanCode();
        $paypalPlanId = $this->billingPlanService->getPayPalPlanIdOrFail($planCode);

        $result = $this->payPalService->createSubscription(
            paypalPlanId: $paypalPlanId,
            customId: "registration:{$registration->public_id}",
            returnUrl: route('clinica.registro.payment.return', ['publicId' => $registration->public_id]),
            cancelUrl: route('clinica.registro.payment.cancel', ['publicId' => $registration->public_id]),
        );

        $registration->forceFill([
            'status' => ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
            'payment_status' => 'pending',
            'paypal_subscription_id' => $result['id'],
            'paypal_plan_id' => $paypalPlanId,
            'failure_code' => null,
            'failure_message' => null,
            'failed_at' => null,
        ])->save();

        $this->billingSubscriptionService->syncFromPayPalSubscription(
            paypalSubscription: (array) $result['raw'],
            registration: $registration
        );

        return redirect()->away((string) $result['approve_url']);
    }

    protected function syncRegistrationFromPayPal(
        ClinicRegistrationRequest $registration,
        array $paypalSubscription
    ): void {
        $this->billingSubscriptionService->syncFromPayPalSubscription(
            paypalSubscription: $paypalSubscription,
            registration: $registration,
            centroId: $registration->centro_id,
        );

        $providerStatus = (string) ($paypalSubscription['status'] ?? '');
        $normalizedStatus = $this->payPalService->normalizeStatus($providerStatus);
        $subscriptionId = (string) ($paypalSubscription['id'] ?? $registration->paypal_subscription_id);
        $paypalPlanId = (string) ($paypalSubscription['plan_id'] ?? $registration->paypal_plan_id);

        $registration->forceFill([
            'status' => ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
            'payment_status' => $normalizedStatus === 'active' ? 'active' : 'pending',
            'paypal_subscription_id' => $subscriptionId ?: null,
            'paypal_plan_id' => $paypalPlanId ?: $registration->paypal_plan_id,
            'payment_approved_at' => $normalizedStatus === 'active'
                ? ($registration->payment_approved_at ?? now())
                : $registration->payment_approved_at,
        ])->save();
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

    protected function markRegistrationFailed(
        ClinicRegistrationRequest $registration,
        string $code,
        string $message
    ): void {
        $registration->forceFill([
            'status' => ClinicRegistrationRequest::STATUS_FAILED,
            'payment_status' => 'failed',
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
}
