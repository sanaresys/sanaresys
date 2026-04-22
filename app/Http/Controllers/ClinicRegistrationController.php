<?php

namespace App\Http\Controllers;

use App\Mail\ClinicRegistrationVerificationMail;
use App\Models\BillingInvoice;
use App\Models\Centros_Medico;
use App\Models\ClinicRegistrationRequest;
use App\Services\Billing\BillingInvoiceService;
use App\Services\Billing\BillingPlanService;
use App\Services\Billing\RegistrationProvisioningService;
use App\Services\TenantIdentityService;
use App\Services\TenantProvisioningService;
use App\Support\CentralUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClinicRegistrationController extends Controller
{
    public function __construct(
        protected TenantIdentityService $identityService,
        protected TenantProvisioningService $provisioningService,
        protected BillingPlanService $billingPlanService,
        protected BillingInvoiceService $billingInvoiceService,
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
            'rtn' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('centros_medicos', 'rtn')->whereNotNull('rtn'),
            ],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $rtn = $this->normalizeNullableString($validated['rtn'] ?? null);

        $plan = $this->billingPlanService->get($validated['plan_code']);
        $planCode = (string) ($plan['code'] ?? $validated['plan_code']);

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
            'nombre_centro' => $validated['nombre_centro'],
            'slug' => $slug,
            'direccion' => $validated['direccion'],
            'telefono' => $validated['telefono'],
            'rtn' => $rtn,
            'owner_name' => $validated['owner_name'],
            'owner_email' => strtolower($validated['owner_email']),
            'password_encrypted' => Crypt::encryptString($validated['password']),
            'verification_sent_at' => now(),
            'verification_expires_at' => now()->addDay(),
        ]);

        $this->sendVerificationEmail($registration);

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

        $canStartPayment = $registration->isProvisioned()
            && $this->resolveOpenBasePlanInvoiceForRegistration($registration) !== null;

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

        if ($registration->isProvisioned()) {
            return $this->redirectProvisionedRegistration($registration);
        }

        if (! in_array($registration->status, [
            ClinicRegistrationRequest::STATUS_PENDING_VERIFICATION,
            ClinicRegistrationRequest::STATUS_EXPIRED,
            ClinicRegistrationRequest::STATUS_VERIFIED,
            ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
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

            if (! $locked->isProvisioned()) {
                $locked->forceFill([
                    'status' => ClinicRegistrationRequest::STATUS_VERIFIED,
                    'verified_at' => $locked->verified_at ?? now(),
                    'failure_code' => null,
                    'failure_message' => null,
                    'failed_at' => null,
                ])->save();
            }

            $registration = $locked;
        });

        if ($registration->status === ClinicRegistrationRequest::STATUS_EXPIRED) {
            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('error', 'El enlace expiro. Solicita un reenvio.');
        }

        $this->billingInvoiceService->activateOnboardingTrial($registration->fresh());
        $registration->refresh();

        if ($registration->isProvisioned()) {
            return $this->redirectProvisionedRegistration($registration);
        }

        return redirect()
            ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
            ->with('status', 'Tu periodo gratis se activo. Estamos finalizando la configuracion.');
    }

    public function startPayment(string $publicId): RedirectResponse
    {
        return redirect()->route('clinica.registro.billing', ['publicId' => $publicId]);
    }

    public function billing(string $publicId)
    {
        $registration = $this->findRegistrationOrFail($publicId);
        $context = $this->resolveBillingContext($registration);

        if (! $context) {
            if ($registration->isProvisioned()) {
                return $this->redirectProvisionedRegistration($registration);
            }

            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('error', 'Primero debes verificar el correo para continuar al pago.');
        }

        $invoice = $context['invoice'];
        $billingMode = $context['mode'];

        return view('registro-clinica-billing', [
            'registration' => $registration->fresh(),
            'invoice' => $invoice,
            'billingMode' => $billingMode,
            'requiresConsent' => $billingMode === 'onboarding',
            'freeTrialDays' => (int) config('billing.onboarding.free_trial_days', 30),
            'paypalClientId' => (string) config('services.paypal.client_id', ''),
            'paypalCurrency' => (string) config('billing.currency', 'USD'),
            'consentTextVersion' => (string) config('billing.engine.consent_text_version', 'v1'),
        ]);
    }

    public function createBillingOrder(Request $request, string $publicId): JsonResponse
    {
        $registration = $this->findRegistrationOrFail($publicId);
        $context = $this->resolveBillingContext($registration);

        if (! $context) {
            return response()->json([
                'message' => 'La solicitud no esta lista para pago.',
            ], 422);
        }

        $request->validate([
            'consent' => ['accepted'],
        ]);

        $updates = [
            'consent_at' => now(),
            'consent_text_version' => (string) config('billing.engine.consent_text_version', 'v1'),
            'consent_ip' => (string) $request->ip(),
        ];

        if ($context['mode'] === 'onboarding') {
            $updates['status'] = ClinicRegistrationRequest::STATUS_PENDING_PAYMENT;
            $updates['payment_status'] = 'pending';
        }

        $registration->forceFill($updates)->save();

        $invoice = $context['invoice'];
        $attempt = $this->billingInvoiceService->createOrReuseAttempt(
            invoice: $invoice,
            context: $context['mode'] === 'onboarding' ? 'registration_onboarding' : 'registration_renewal',
            requestedBy: null,
            returnUrl: route('clinica.registro.payment.return', ['publicId' => $registration->public_id]),
            cancelUrl: route('clinica.registro.payment.cancel', ['publicId' => $registration->public_id]),
        );

        return response()->json([
            'orderId' => $attempt->paypal_order_id,
        ]);
    }

    public function captureBillingOrder(Request $request, string $publicId): JsonResponse
    {
        $registration = $this->findRegistrationOrFail($publicId);
        $validated = $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        if ($registration->isProvisioned() && $registration->centro_id) {
            $centro = Centros_Medico::on('mysql')->find($registration->centro_id);

            if ($centro) {
                $this->billingInvoiceService->captureAttemptFromReturn(
                    paypalOrderId: (string) $validated['order_id'],
                    centro: $centro,
                );
            } else {
                $this->billingInvoiceService->captureAttemptFromReturn(
                    paypalOrderId: (string) $validated['order_id'],
                    registration: $registration,
                );
            }
        } else {
            $this->billingInvoiceService->captureAttemptFromReturn(
                paypalOrderId: (string) $validated['order_id'],
                registration: $registration,
            );
        }

        $registration->refresh();

        return response()->json([
            'redirect_url' => $registration->isProvisioned()
                ? ($this->registrationProvisioningService->issueTenantAccessUrl($registration)
                    ?: route('clinica.registro.waiting', ['publicId' => $registration->public_id]))
                : route('clinica.registro.waiting', ['publicId' => $registration->public_id]),
        ]);
    }

    public function paymentReturn(Request $request, string $publicId): RedirectResponse
    {
        $registration = $this->findRegistrationOrFail($publicId);
        $orderId = (string) ($request->query('token') ?? $request->query('orderId'));

        if ($orderId === '') {
            return redirect()
                ->route('clinica.registro.billing', ['publicId' => $registration->public_id])
                ->with('error', 'PayPal no devolvio una orden valida.');
        }

        try {
            if ($registration->isProvisioned() && $registration->centro_id) {
                $centro = Centros_Medico::on('mysql')->find($registration->centro_id);

                if ($centro) {
                    $this->billingInvoiceService->captureAttemptFromReturn(
                        paypalOrderId: $orderId,
                        centro: $centro,
                    );
                } else {
                    $this->billingInvoiceService->captureAttemptFromReturn(
                        paypalOrderId: $orderId,
                        registration: $registration,
                    );
                }
            } else {
                $this->billingInvoiceService->captureAttemptFromReturn(
                    paypalOrderId: $orderId,
                    registration: $registration,
                );
            }

            $registration->refresh();

            if ($registration->isProvisioned()) {
                return $this->redirectProvisionedRegistration($registration);
            }

            return redirect()
                ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
                ->with('status', 'Pago recibido. Estamos terminando la activacion.');
        } catch (\Throwable $e) {
            Log::error('Error capturando pago de onboarding.', [
                'registration_public_id' => $registration->public_id,
                'paypal_order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('clinica.registro.billing', ['publicId' => $registration->public_id])
                ->with('error', 'No se pudo confirmar el pago. Intenta nuevamente.');
        }
    }

    public function paymentCancel(string $publicId): RedirectResponse
    {
        $registration = $this->findRegistrationOrFail($publicId);

        return redirect()
            ->route('clinica.registro.billing', ['publicId' => $registration->public_id])
            ->with('error', 'Cancelaste el checkout de PayPal. Puedes intentar nuevamente.');
    }

    public function enterTenant(string $publicId): RedirectResponse
    {
        $registration = $this->findRegistrationOrFail($publicId);

        if (! $registration->isProvisioned()) {
            return redirect()
                ->route('clinica.registro.billing', ['publicId' => $registration->public_id])
                ->with('status', 'Tu clinica aun no esta activada. Completa el pago para continuar.');
        }

        return $this->redirectProvisionedRegistration($registration);
    }

    protected function sendVerificationEmail(ClinicRegistrationRequest $registration): void
    {
        $verificationPath = URL::temporarySignedRoute(
            'clinica.registro.verify',
            $registration->verification_expires_at ?? now()->addDay(),
            ['publicId' => $registration->public_id],
            false,
        );
        $verificationUrl = rtrim(CentralUrl::origin(), '/') . $verificationPath;

        Mail::to($registration->owner_email)->send(
            new ClinicRegistrationVerificationMail($registration, $verificationUrl)
        );
    }

    protected function findRegistrationOrFail(string $publicId): ClinicRegistrationRequest
    {
        return ClinicRegistrationRequest::query()
            ->where('public_id', $publicId)
            ->firstOrFail();
    }

    protected function redirectProvisionedRegistration(ClinicRegistrationRequest $registration): RedirectResponse
    {
        $target = $this->registrationProvisioningService->issueTenantAccessUrl($registration->fresh());

        if ($target) {
            return redirect()->away($target);
        }

        return redirect()
            ->route('clinica.registro.waiting', ['publicId' => $registration->public_id])
            ->with('error', 'No se pudo generar un acceso nuevo al tenant. Intenta nuevamente.');
    }

    /**
     * @return array{mode: string, invoice: BillingInvoice}|null
     */
    protected function resolveBillingContext(ClinicRegistrationRequest $registration): ?array
    {
        if ($registration->isProvisioned()) {
            $invoice = $this->resolveOpenBasePlanInvoiceForRegistration($registration);

            if (! $invoice) {
                return null;
            }

            return [
                'mode' => 'renewal',
                'invoice' => $invoice,
            ];
        }

        if (! in_array($registration->status, [
            ClinicRegistrationRequest::STATUS_VERIFIED,
            ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
        ], true)) {
            return null;
        }

        return [
            'mode' => 'onboarding',
            'invoice' => $this->billingInvoiceService->createOnboardingInvoice($registration),
        ];
    }

    protected function resolveOpenBasePlanInvoiceForRegistration(ClinicRegistrationRequest $registration): ?BillingInvoice
    {
        if (! $registration->centro_id) {
            return null;
        }

        $centro = Centros_Medico::on('mysql')->find($registration->centro_id);
        if (! $centro) {
            return null;
        }

        return $this->billingInvoiceService->openBasePlanInvoiceForCentro($centro);
    }

    protected function normalizeNullableString(null|string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
