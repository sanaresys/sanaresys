<?php

namespace App\Services\Billing;

use App\Models\BillingChargeAttempt;
use App\Models\BillingInvoice;
use App\Models\BillingInvoiceItem;
use App\Models\BillingModule;
use App\Models\BillingModuleSubscription;
use App\Models\BillingSubscription;
use App\Models\BillingTenantSubscription;
use App\Models\Centros_Medico;
use App\Models\ClinicRegistrationRequest;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BillingInvoiceService
{
    public function __construct(
        protected PayPalService $payPalService,
        protected BillingPlanService $planService,
        protected BillingPeriodService $periodService,
        protected BillingStateService $billingStateService,
        protected BillingAuditService $auditService,
        protected BillingNotificationService $notificationService,
        protected RegistrationProvisioningService $registrationProvisioningService,
    ) {
    }

    public function createOnboardingInvoice(ClinicRegistrationRequest $registration): BillingInvoice
    {
        $existing = BillingInvoice::query()
            ->with('items')
            ->when($registration->billing_invoice_id, fn ($query) => $query->whereKey($registration->billing_invoice_id))
            ->where('clinic_registration_request_id', $registration->id)
            ->whereIn('status', ['open', 'past_due'])
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $planCode = $registration->plan_code ?: $this->planService->defaultPlanCode();
        $interval = $this->periodService->planInterval($planCode);
        $startsAt = $this->periodService->now();
        $endsAt = $this->periodService->addInterval($startsAt, $interval);

        $invoice = BillingInvoice::query()->create([
            'public_id' => (string) Str::uuid(),
            'clinic_registration_request_id' => $registration->id,
            'kind' => 'onboarding',
            'status' => 'open',
            'currency' => (string) config('billing.currency', 'USD'),
            'due_at' => $startsAt,
            'grace_until' => $this->periodService->graceUntil($startsAt),
            'billing_starts_at' => $startsAt,
            'billing_ends_at' => $endsAt,
            'billing_renews_at' => $endsAt,
            'meta' => [
                'plan_code' => $planCode,
                'billing_interval' => $interval,
            ],
        ]);

        BillingInvoiceItem::query()->create([
            'billing_invoice_id' => $invoice->id,
            'item_type' => 'base_plan',
            'description' => sprintf('Plan base %s', strtoupper($planCode)),
            'billing_interval' => $interval,
            'quantity' => 1,
            'unit_amount' => $this->periodService->planPrice($planCode),
            'amount' => $this->periodService->planPrice($planCode),
            'period_starts_at' => $startsAt,
            'period_ends_at' => $endsAt,
            'meta' => [
                'plan_code' => $planCode,
            ],
        ]);

        $this->syncInvoiceTotals($invoice);

        $registration->forceFill([
            'status' => ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
            'payment_status' => 'pending',
            'billing_invoice_id' => $invoice->id,
        ])->save();

        return $invoice->fresh('items');
    }

    public function activateOnboardingTrial(ClinicRegistrationRequest $registration): void
    {
        if ($registration->isProvisioned()) {
            return;
        }

        $trialDays = $this->onboardingFreeTrialDays();

        $invoice = DB::connection('mysql')->transaction(function () use ($registration, $trialDays): BillingInvoice {
            $locked = ClinicRegistrationRequest::query()
                ->whereKey($registration->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->isProvisioned()) {
                return BillingInvoice::query()
                    ->where('clinic_registration_request_id', $locked->id)
                    ->where('kind', 'onboarding')
                    ->where('status', 'paid')
                    ->latest('id')
                    ->firstOrFail();
            }

            if (! in_array($locked->status, [
                ClinicRegistrationRequest::STATUS_VERIFIED,
                ClinicRegistrationRequest::STATUS_PENDING_PAYMENT,
            ], true)) {
                throw ValidationException::withMessages([
                    'registration' => 'La solicitud no esta lista para activar el periodo gratis.',
                ]);
            }

            $paidInvoice = BillingInvoice::query()
                ->with('items')
                ->where('clinic_registration_request_id', $locked->id)
                ->where('kind', 'onboarding')
                ->where('status', 'paid')
                ->latest('id')
                ->first();

            if ($paidInvoice) {
                $locked->forceFill([
                    'billing_invoice_id' => $paidInvoice->id,
                    'payment_status' => 'paid',
                    'payment_approved_at' => $locked->payment_approved_at ?: $this->periodService->now(),
                ])->save();

                return $paidInvoice;
            }

            $planCode = $locked->plan_code ?: $this->planService->defaultPlanCode();
            $interval = $this->periodService->planInterval($planCode);
            $startsAt = $this->periodService->now();
            $endsAt = $startsAt->copy()->addDays($trialDays);

            BillingInvoice::query()
                ->where('clinic_registration_request_id', $locked->id)
                ->where('kind', 'onboarding')
                ->whereIn('status', ['open', 'past_due'])
                ->update([
                    'status' => 'voided',
                    'voided_at' => $startsAt,
                    'notes' => 'Factura reemplazada por activacion de periodo gratis.',
                ]);

            $invoice = BillingInvoice::query()->create([
                'public_id' => (string) Str::uuid(),
                'clinic_registration_request_id' => $locked->id,
                'kind' => 'onboarding',
                'status' => 'open',
                'currency' => (string) config('billing.currency', 'USD'),
                'due_at' => $startsAt,
                'grace_until' => null,
                'billing_starts_at' => $startsAt,
                'billing_ends_at' => $endsAt,
                'billing_renews_at' => $endsAt,
                'meta' => [
                    'plan_code' => $planCode,
                    'billing_interval' => $interval,
                    'origin' => 'onboarding_trial',
                    'free_trial_days' => $trialDays,
                ],
            ]);

            BillingInvoiceItem::query()->create([
                'billing_invoice_id' => $invoice->id,
                'item_type' => 'base_plan',
                'description' => sprintf('Periodo gratis (%d dias) plan %s', $trialDays, strtoupper($planCode)),
                'billing_interval' => $interval,
                'quantity' => 1,
                'unit_amount' => 0,
                'amount' => 0,
                'period_starts_at' => $startsAt,
                'period_ends_at' => $endsAt,
                'meta' => [
                    'plan_code' => $planCode,
                    'origin' => 'onboarding_trial',
                    'full_price_reference' => $this->periodService->planPrice($planCode),
                ],
            ]);

            $this->syncInvoiceTotals($invoice);

            $locked->forceFill([
                'billing_invoice_id' => $invoice->id,
                'payment_status' => 'pending',
            ])->save();

            return $invoice->fresh('items');
        });

        if ($invoice->status !== 'paid') {
            $this->recordOfflinePayment(
                invoice: $invoice,
                actor: null,
                reason: sprintf('Periodo gratis de onboarding activado (%d dias).', $trialDays),
            );
        }

        $registration->refresh();

        if (! $registration->isProvisioned() && in_array((string) $registration->payment_status, ['paid', 'active'], true)) {
            $this->registrationProvisioningService->provisionFromPaidRegistration($registration);
        }
    }

    public function createReactivationInvoice(Centros_Medico $centro, ?string $planCode = null): BillingInvoice
    {
        $tenantSubscription = $this->ensureTenantSubscription($centro, $planCode);

        $existing = BillingInvoice::query()
            ->with('items')
            ->where('centro_id', $centro->id)
            ->whereIn('status', ['open', 'past_due'])
            ->whereIn('kind', ['renewal', 'reactivation', 'refund_replacement'])
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->createRenewalInvoice($tenantSubscription, collect(), 'reactivation');
    }

    public function createRenewalInvoice(
        BillingTenantSubscription $tenantSubscription,
        Collection $moduleSubscriptions,
        string $kind = 'renewal',
        bool $includeBasePlan = true,
        ?Carbon $dueAt = null,
    ): BillingInvoice {
        $tenantSubscription->loadMissing('centro');
        $centro = $tenantSubscription->centro;
        $dueAt ??= $tenantSubscription->next_charge_at ?: $this->periodService->now();

        $existing = BillingInvoice::query()
            ->with('items')
            ->where('centro_id', $centro?->id)
            ->whereIn('status', ['open', 'past_due'])
            ->where('kind', $kind)
            ->whereDate('due_at', $dueAt->toDateString())
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $invoice = BillingInvoice::query()->create([
            'public_id' => (string) Str::uuid(),
            'centro_id' => $centro?->id,
            'kind' => $kind,
            'status' => 'open',
            'currency' => (string) config('billing.currency', 'USD'),
            'due_at' => $dueAt,
            'grace_until' => $this->periodService->graceUntil($dueAt),
            'billing_starts_at' => $tenantSubscription->current_period_ends_at ?: $dueAt,
            'billing_ends_at' => $this->periodService->addInterval(
                $tenantSubscription->current_period_ends_at ?: $dueAt,
                $tenantSubscription->billing_interval
            ),
            'billing_renews_at' => $this->periodService->addInterval(
                $tenantSubscription->current_period_ends_at ?: $dueAt,
                $tenantSubscription->billing_interval
            ),
            'meta' => [
                'plan_code' => $tenantSubscription->plan_code,
                'billing_interval' => $tenantSubscription->billing_interval,
            ],
        ]);

        if ($includeBasePlan && ! $tenantSubscription->cancel_at_period_end) {
            BillingInvoiceItem::query()->create([
                'billing_invoice_id' => $invoice->id,
                'item_type' => 'base_plan',
                'description' => sprintf('Renovacion plan %s', strtoupper($tenantSubscription->plan_code)),
                'billing_interval' => $tenantSubscription->billing_interval,
                'quantity' => 1,
                'unit_amount' => $this->periodService->planPrice($tenantSubscription->plan_code),
                'amount' => $this->periodService->planPrice($tenantSubscription->plan_code),
                'period_starts_at' => $tenantSubscription->current_period_ends_at ?: $dueAt,
                'period_ends_at' => $invoice->billing_ends_at,
                'meta' => [
                    'plan_code' => $tenantSubscription->plan_code,
                ],
            ]);
        }

        $moduleSubscriptions
            ->filter(function (BillingModuleSubscription $subscription): bool {
                return ! $subscription->cancel_at_period_end
                    && in_array($subscription->status, ['active', 'past_due', 'grace'], true);
            })
            ->each(function (BillingModuleSubscription $subscription) use ($invoice): void {
                $subscription->loadMissing('module');
                $startsAt = $subscription->current_period_ends_at ?: $subscription->next_charge_at ?: $invoice->due_at;
                $endsAt = $this->periodService->addInterval($startsAt, $subscription->billing_interval);
                $fullAmount = $subscription->module
                    ? $this->periodService->modulePrice($subscription->module, $subscription->billing_interval)
                    : (float) $subscription->amount;

                BillingInvoiceItem::query()->create([
                    'billing_invoice_id' => $invoice->id,
                    'billing_module_id' => $subscription->billing_module_id,
                    'item_type' => 'module_renewal',
                    'description' => sprintf(
                        'Renovacion modulo %s',
                        $subscription->module?->name ?? 'adicional'
                    ),
                    'billing_interval' => $subscription->billing_interval,
                    'quantity' => 1,
                    'unit_amount' => $fullAmount,
                    'amount' => $fullAmount,
                    'period_starts_at' => $startsAt,
                    'period_ends_at' => $endsAt,
                    'meta' => [
                        'module_subscription_id' => $subscription->id,
                        'full_amount' => $fullAmount,
                    ],
                ]);
            });

        $this->syncInvoiceTotals($invoice);

        return $invoice->fresh('items');
    }

    public function createModuleProrationInvoice(
        Centros_Medico $centro,
        BillingModule $module,
        string $interval,
    ): BillingInvoice {
        $tenantSubscription = $this->ensureTenantSubscription($centro);
        $existingOpenInvoice = $this->findOpenModuleInvoice($centro, $module);

        if ($existingOpenInvoice) {
            $matchesRequestedInterval = $existingOpenInvoice->items->contains(function (BillingInvoiceItem $item) use ($module, $interval): bool {
                return (int) $item->billing_module_id === (int) $module->id
                    && $item->item_type === 'module_proration'
                    && $item->billing_interval === $interval;
            });

            if ($matchesRequestedInterval) {
                return $existingOpenInvoice;
            }

            throw ValidationException::withMessages([
                'module' => 'Este modulo ya tiene un cobro pendiente. Resuelve ese pago antes de cambiarlo a otro periodo.',
            ]);
        }

        $moduleSubscription = BillingModuleSubscription::query()
            ->firstOrNew([
                'centro_id' => $centro->id,
                'billing_module_id' => $module->id,
            ]);

        if ($moduleSubscription->exists
            && in_array($moduleSubscription->status, ['active', 'pending', 'past_due', 'grace'], true)
            && ! $moduleSubscription->cancel_at_period_end) {
            throw ValidationException::withMessages([
                'module' => $moduleSubscription->status === 'pending'
                    ? 'Este modulo ya tiene una activacion pendiente. Completa ese pago antes de intentarlo otra vez.'
                    : 'Este modulo ya esta activo para esta clinica.',
            ]);
        }

        $anchorAt = $tenantSubscription->anchor_at
            ?: $tenantSubscription->current_period_starts_at
            ?: $this->periodService->now();
        $cycle = $this->periodService->currentCycleForAnchor($anchorAt, $interval);
        $fullAmount = $this->periodService->modulePrice($module, $interval);
        $proratedAmount = $this->periodService->proratedAmount(
            $fullAmount,
            $cycle['starts_at'],
            $cycle['ends_at'],
            $this->periodService->now(),
        );

        $existing = BillingInvoice::query()
            ->with('items')
            ->where('centro_id', $centro->id)
            ->where('kind', 'module_proration')
            ->whereIn('status', ['open', 'past_due'])
            ->whereHas('items', function ($query) use ($module, $interval): void {
                $query->where('billing_module_id', $module->id)
                    ->where('billing_interval', $interval)
                    ->where('item_type', 'module_proration');
            })
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $moduleSubscription->fill([
            'status' => 'pending',
            'billing_interval' => $interval,
            'currency' => $module->currency ?: (string) config('billing.currency', 'USD'),
            'amount' => $fullAmount,
            'anchor_at' => $anchorAt,
            'current_period_starts_at' => $cycle['starts_at'],
            'current_period_ends_at' => $cycle['ends_at'],
            'next_charge_at' => $cycle['ends_at'],
            'cancel_at_period_end' => false,
            'dunning_attempts' => 0,
            'meta' => array_merge((array) $moduleSubscription->meta, [
                'pending_activation' => true,
            ]),
        ]);
        $moduleSubscription->save();

        $invoice = BillingInvoice::query()->create([
            'public_id' => (string) Str::uuid(),
            'centro_id' => $centro->id,
            'kind' => 'module_proration',
            'status' => 'open',
            'currency' => $module->currency ?: (string) config('billing.currency', 'USD'),
            'due_at' => $this->periodService->now(),
            'grace_until' => $this->periodService->graceUntil($this->periodService->now()),
            'billing_starts_at' => $cycle['starts_at'],
            'billing_ends_at' => $cycle['ends_at'],
            'billing_renews_at' => $cycle['ends_at'],
            'meta' => [
                'module_subscription_id' => $moduleSubscription->id,
            ],
        ]);

        BillingInvoiceItem::query()->create([
            'billing_invoice_id' => $invoice->id,
            'billing_module_id' => $module->id,
            'item_type' => 'module_proration',
            'description' => sprintf('Prorrateo modulo %s', $module->name),
            'billing_interval' => $interval,
            'quantity' => 1,
            'unit_amount' => $fullAmount,
            'amount' => $proratedAmount,
            'period_starts_at' => $cycle['starts_at'],
            'period_ends_at' => $cycle['ends_at'],
            'meta' => [
                'module_subscription_id' => $moduleSubscription->id,
                'full_amount' => $fullAmount,
            ],
        ]);

        $this->syncInvoiceTotals($invoice);

        $moduleSubscription->forceFill([
            'last_invoice_id' => $invoice->id,
        ])->save();

        return $invoice->fresh('items');
    }

    public function createOrReuseAttempt(
        BillingInvoice $invoice,
        string $context,
        ?User $requestedBy,
        string $returnUrl,
        string $cancelUrl,
    ): BillingChargeAttempt {
        $invoice = $this->ensureInvoiceCanBePaid($invoice);

        if ($invoice->status === 'paid') {
            throw ValidationException::withMessages([
                'invoice' => 'Esta factura ya fue pagada.',
            ]);
        }

        $reusable = $this->findReusableAttempt($invoice);
        if ($reusable) {
            return $reusable;
        }

        $attemptNumber = ((int) $invoice->chargeAttempts()->max('attempt_number')) + 1;
        $description = $this->invoiceDescription($invoice);
        $customId = sprintf('invoice:%s:%d', $invoice->public_id, $attemptNumber);
        $order = $this->payPalService->createOrder(
            amount: (float) $invoice->total,
            currency: $invoice->currency,
            description: $description,
            customId: $customId,
            returnUrl: $returnUrl,
            cancelUrl: $cancelUrl,
        );

        return BillingChargeAttempt::query()->create([
            'billing_invoice_id' => $invoice->id,
            'centro_id' => $invoice->centro_id,
            'clinic_registration_request_id' => $invoice->clinic_registration_request_id,
            'requested_by_user_id' => $requestedBy?->id,
            'context' => $context,
            'provider' => 'paypal',
            'attempt_number' => $attemptNumber,
            'status' => $this->normalizeAttemptStatus((string) ($order['status'] ?? 'CREATED')),
            'currency' => $invoice->currency,
            'amount' => $invoice->total,
            'paypal_order_id' => (string) $order['id'],
            'approve_url' => (string) ($order['approve_url'] ?? ''),
            'payload' => (array) ($order['raw'] ?? []),
            'meta' => [
                'description' => $description,
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
        ]);
    }

    public function captureAttemptFromReturn(
        string $paypalOrderId,
        ?Centros_Medico $centro = null,
        ?ClinicRegistrationRequest $registration = null,
    ): BillingChargeAttempt {
        $attempt = BillingChargeAttempt::query()
            ->where('paypal_order_id', $paypalOrderId)
            ->firstOrFail();

        if ($centro && $attempt->centro_id && (int) $attempt->centro_id !== (int) $centro->id) {
            abort(404);
        }

        if ($registration && $attempt->clinic_registration_request_id && (int) $attempt->clinic_registration_request_id !== (int) $registration->id) {
            abort(404);
        }

        if ($attempt->status === 'captured' && $attempt->captured_at) {
            return $attempt->fresh('invoice.items');
        }

        $this->ensureInvoiceCanBePaid($attempt->invoice()->with('items')->firstOrFail());

        $capturePayload = $this->payPalService->captureOrder($paypalOrderId);

        return $this->applyCapturedAttemptPayload($attempt, $capturePayload);
    }

    public function handleCaptureCompleted(
        ?string $paypalOrderId,
        ?string $paypalCaptureId,
        array $payload,
    ): ?BillingChargeAttempt {
        $attempt = $this->findAttemptForCapture($paypalOrderId, $paypalCaptureId);
        if (! $attempt) {
            return null;
        }

        if ($attempt->status === 'captured' && $attempt->captured_at) {
            return $attempt;
        }

        if ($paypalOrderId) {
            try {
                $providerOrder = $this->payPalService->getOrder($paypalOrderId);

                return $this->applyCapturedAttemptPayload($attempt, $providerOrder);
            } catch (\Throwable) {
                // Se procesa con el payload recibido del webhook si la consulta remota falla.
            }
        }

        return $this->applyCapturedAttemptPayload($attempt, $payload);
    }

    public function handleRefund(string $paypalCaptureId, array $payload): ?BillingChargeAttempt
    {
        $attempt = BillingChargeAttempt::query()
            ->where('paypal_capture_id', $paypalCaptureId)
            ->first();

        if (! $attempt) {
            return null;
        }

        $invoice = $attempt->invoice()->with('items')->first();
        if (! $invoice) {
            return $attempt;
        }

        DB::connection('mysql')->transaction(function () use ($attempt, $invoice, $payload, $paypalCaptureId): void {
            $refundedAt = $this->parsePaypalTimestamp(
                Arr::get($payload, 'resource.update_time')
                    ?? Arr::get($payload, 'resource.create_time')
            ) ?? $this->periodService->now();

            $attempt->forceFill([
                'status' => 'refunded',
                'refunded_at' => $refundedAt,
                'capture_payload' => array_merge((array) $attempt->capture_payload, [
                    'refund_event' => $payload,
                ]),
            ])->save();

            $invoice->forceFill([
                'status' => 'refunded',
                'refunded_at' => $refundedAt,
                'meta' => array_merge((array) $invoice->meta, [
                    'refund_event' => $payload,
                ]),
            ])->save();

            $replacement = $this->createReplacementInvoice($invoice);
            $this->markSubscriptionsPastDue($invoice->centro, $replacement);

            $this->auditService->log(
                eventType: 'billing.invoice.refunded',
                centro: $invoice->centro,
                invoice: $invoice,
                reason: 'PayPal reporto un refund o reverso.',
                newValues: [
                    'replacement_invoice_id' => $replacement->id,
                ],
                meta: [
                    'paypal_capture_id' => $paypalCaptureId,
                ],
            );
        });

        return $attempt->fresh('invoice.items');
    }

    public function openInvoiceForCentro(Centros_Medico $centro): ?BillingInvoice
    {
        return BillingInvoice::query()
            ->with(['items', 'chargeAttempts'])
            ->where('centro_id', $centro->id)
            ->whereIn('status', ['open', 'past_due'])
            ->latest('id')
            ->first();
    }

    public function openBasePlanInvoiceForCentro(Centros_Medico $centro): ?BillingInvoice
    {
        return BillingInvoice::query()
            ->with(['items', 'chargeAttempts'])
            ->where('centro_id', $centro->id)
            ->whereIn('status', ['open', 'past_due'])
            ->whereHas('items', function ($query): void {
                $query->where('item_type', 'base_plan');
            })
            ->latest('id')
            ->first();
    }

    public function ensureTenantSubscriptionForCentro(
        Centros_Medico $centro,
        ?string $planCode = null,
    ): BillingTenantSubscription {
        return $this->ensureTenantSubscription($centro, $planCode);
    }

    public function recordOfflinePayment(
        BillingInvoice $invoice,
        ?User $actor = null,
        ?string $reason = null,
    ): BillingChargeAttempt {
        if ($invoice->status === 'paid') {
            return $invoice->chargeAttempts()
                ->where('provider', 'manual')
                ->latest('id')
                ->firstOrFail();
        }

        $capturedAt = $this->periodService->now();
        $attempt = DB::connection('mysql')->transaction(function () use ($invoice, $actor, $capturedAt): BillingChargeAttempt {
            $attemptNumber = ((int) $invoice->chargeAttempts()->max('attempt_number')) + 1;

            $attempt = BillingChargeAttempt::query()->create([
                'billing_invoice_id' => $invoice->id,
                'centro_id' => $invoice->centro_id,
                'clinic_registration_request_id' => $invoice->clinic_registration_request_id,
                'requested_by_user_id' => $actor?->id,
                'context' => 'manual',
                'provider' => 'manual',
                'attempt_number' => $attemptNumber,
                'status' => 'captured',
                'currency' => $invoice->currency,
                'amount' => $invoice->total,
                'approved_at' => $capturedAt,
                'captured_at' => $capturedAt,
                'payload' => [
                    'source' => 'root_manual',
                ],
                'capture_payload' => [
                    'source' => 'root_manual',
                ],
            ]);

            $invoice->forceFill([
                'status' => 'paid',
                'paid_at' => $capturedAt,
            ])->save();

            if ($invoice->registration) {
                $invoice->registration->forceFill([
                    'payment_status' => 'paid',
                    'payment_approved_at' => $capturedAt,
                    'failure_code' => null,
                    'failure_message' => null,
                    'failed_at' => null,
                ])->save();
            }

            return $attempt;
        });

        $this->afterInvoicePaid($invoice->fresh(['items', 'registration', 'centro']), $attempt->fresh());

        $this->auditService->log(
            eventType: 'billing.invoice.marked_paid',
            centro: $invoice->centro,
            invoice: $invoice,
            actor: $actor,
            actorType: $actor ? 'user' : 'system',
            reason: $reason ?: 'Pago manual registrado desde root.',
        );

        return $attempt->fresh('invoice.items');
    }

    protected function applyCapturedAttemptPayload(BillingChargeAttempt $attempt, array $payload): BillingChargeAttempt
    {
        $invoice = $attempt->invoice()->with(['items', 'registration', 'centro'])->firstOrFail();

        DB::connection('mysql')->transaction(function () use ($attempt, $invoice, $payload): void {
            $capturedAt = $this->parsePaypalTimestamp($this->payPalService->extractOrderCaptureTime($payload))
                ?? $this->periodService->now();

            $attempt->forceFill([
                'status' => 'captured',
                'paypal_capture_id' => $this->payPalService->extractOrderCaptureId($payload) ?: $attempt->paypal_capture_id,
                'approved_at' => $attempt->approved_at ?: $capturedAt,
                'captured_at' => $capturedAt,
                'capture_payload' => $payload,
                'amount' => $this->payPalService->extractOrderAmount($payload) ?? $attempt->amount,
                'failure_code' => null,
                'failure_message' => null,
                'failed_at' => null,
            ])->save();

            $invoice->forceFill([
                'status' => 'paid',
                'paid_at' => $capturedAt,
                'centro_id' => $invoice->centro_id ?: $attempt->centro_id,
                'meta' => array_merge((array) $invoice->meta, [
                    'paypal_order_id' => $attempt->paypal_order_id,
                    'paypal_capture_id' => $attempt->paypal_capture_id,
                ]),
            ])->save();

            if ($invoice->registration) {
                $nextRegistrationStatus = $invoice->kind === 'onboarding'
                    ? ClinicRegistrationRequest::STATUS_PENDING_PAYMENT
                    : $invoice->registration->status;

                $invoice->registration->forceFill([
                    'status' => $nextRegistrationStatus,
                    'payment_status' => 'paid',
                    'payment_approved_at' => $capturedAt,
                    'billing_invoice_id' => $invoice->id,
                    'failure_code' => null,
                    'failure_message' => null,
                    'failed_at' => null,
                ])->save();
            }
        });

        $attempt = $attempt->fresh('invoice.items');
        $invoice = $attempt->invoice;

        $this->afterInvoicePaid($invoice, $attempt);

        return $attempt->fresh('invoice.items');
    }

    protected function afterInvoicePaid(BillingInvoice $invoice, BillingChargeAttempt $attempt): void
    {
        $invoice->loadMissing(['items', 'registration', 'centro']);

        if ($invoice->kind === 'onboarding' && $invoice->registration) {
            $this->completeOnboardingInvoice($invoice);
            $invoice->refresh()->loadMissing(['items', 'registration', 'centro']);
        }

        $centro = $invoice->centro;
        if ($invoice->registration?->centro_id && ! $centro) {
            $centro = Centros_Medico::query()->find($invoice->registration->centro_id);
            if ($centro && ! $invoice->centro_id) {
                $invoice->forceFill(['centro_id' => $centro->id])->save();
            }
        }

        if ($centro) {
            $this->applySuccessStateToCentro($centro, $invoice);
            $this->voidConflictingOpenModuleInvoices($centro, $invoice);
        }

        $this->auditService->log(
            eventType: 'billing.invoice.paid',
            centro: $centro,
            invoice: $invoice,
            tenantSubscription: $centro?->billingTenantSubscription,
            actorType: 'system',
            newValues: [
                'status' => 'paid',
                'paypal_order_id' => $attempt->paypal_order_id,
                'paypal_capture_id' => $attempt->paypal_capture_id,
            ],
        );

        if ($centro) {
            $this->notificationService->notifyTenantAdmins(
                centro: $centro,
                eventKey: 'billing.payment_succeeded',
                channels: ['database'],
                payload: [
                    'title' => 'Pago aplicado',
                    'body' => 'Tu pago fue acreditado y el estado de facturacion volvio a estar al dia.',
                    'level' => 'success',
                    'action_url' => '/billing',
                    'action_label' => 'Ver billing',
                ],
                invoice: $invoice,
                tenantSubscription: $centro->billingTenantSubscription,
            );
        }
    }

    protected function completeOnboardingInvoice(BillingInvoice $invoice): void
    {
        $registration = $invoice->registration;
        if (! $registration) {
            return;
        }

        $this->registrationProvisioningService->provisionFromPaidRegistration($registration);
        $registration->refresh();

        if (! $registration->centro_id) {
            return;
        }

        $centro = Centros_Medico::query()->find($registration->centro_id);
        if (! $centro) {
            return;
        }

        $invoice->forceFill([
            'centro_id' => $centro->id,
        ])->save();

        $planCode = $registration->plan_code ?: $this->planService->defaultPlanCode();
        $tenantSubscription = BillingTenantSubscription::query()
            ->firstOrNew(['centro_id' => $centro->id]);

        $tenantSubscription->fill([
            'clinic_registration_request_id' => $registration->id,
            'status' => 'active',
            'plan_code' => $planCode,
            'billing_interval' => $this->periodService->planInterval($planCode),
            'anchor_at' => $invoice->billing_starts_at,
            'current_period_starts_at' => $invoice->billing_starts_at,
            'current_period_ends_at' => $invoice->billing_ends_at,
            'next_charge_at' => $invoice->billing_ends_at,
            'grace_until' => null,
            'cancel_at_period_end' => false,
            'canceled_at' => null,
            'dunning_attempts' => 0,
            'last_successful_charge_at' => $invoice->paid_at,
            'last_failed_charge_at' => null,
            'last_invoice_id' => $invoice->id,
            'consent_at' => $registration->consent_at,
            'consent_text_version' => $registration->consent_text_version,
            'consent_ip' => $registration->consent_ip,
            'meta' => array_merge((array) $tenantSubscription->meta, [
                'origin' => 'onboarding',
            ]),
        ]);
        $tenantSubscription->save();

        $this->billingStateService->syncCentroSnapshotFromTenantSubscription($tenantSubscription);
    }

    protected function applySuccessStateToCentro(Centros_Medico $centro, BillingInvoice $invoice): void
    {
        $tenantSubscription = $this->ensureTenantSubscription($centro);

        if ($invoice->items->contains(fn (BillingInvoiceItem $item) => $item->item_type === 'base_plan')) {
            $planCode = (string) Arr::get($invoice->meta, 'plan_code', $tenantSubscription->plan_code);
            $tenantSubscription->fill([
                'status' => 'active',
                'plan_code' => $planCode,
                'billing_interval' => $this->periodService->planInterval($planCode),
                'anchor_at' => $tenantSubscription->anchor_at ?: ($invoice->billing_starts_at ?: $this->periodService->now()),
                'current_period_starts_at' => $invoice->billing_starts_at,
                'current_period_ends_at' => $invoice->billing_ends_at,
                'next_charge_at' => $invoice->billing_ends_at,
                'grace_until' => null,
                'cancel_at_period_end' => false,
                'canceled_at' => null,
                'dunning_attempts' => 0,
                'last_successful_charge_at' => $invoice->paid_at,
                'last_failed_charge_at' => null,
                'last_invoice_id' => $invoice->id,
            ]);
            $tenantSubscription->save();
        }

        $invoice->items
            ->filter(fn (BillingInvoiceItem $item) => in_array($item->item_type, ['module_proration', 'module_renewal'], true))
            ->each(function (BillingInvoiceItem $item) use ($centro, $invoice, $tenantSubscription): void {
                $subscriptionId = Arr::get($item->meta, 'module_subscription_id');
                $subscription = BillingModuleSubscription::query()
                    ->when($subscriptionId, fn ($query) => $query->whereKey($subscriptionId))
                    ->when(! $subscriptionId, function ($query) use ($centro, $item): void {
                        $query->where('centro_id', $centro->id)
                            ->where('billing_module_id', $item->billing_module_id);
                    })
                    ->firstOrNew([
                        'centro_id' => $centro->id,
                        'billing_module_id' => $item->billing_module_id,
                    ]);

                $subscription->fill([
                    'status' => 'active',
                    'billing_interval' => $item->billing_interval ?: $subscription->billing_interval ?: 'monthly',
                    'currency' => $invoice->currency,
                    'amount' => (float) Arr::get($item->meta, 'full_amount', $item->unit_amount),
                    'anchor_at' => $subscription->anchor_at ?: $tenantSubscription->anchor_at,
                    'current_period_starts_at' => $item->period_starts_at,
                    'current_period_ends_at' => $item->period_ends_at,
                    'next_charge_at' => $item->period_ends_at,
                    'grace_until' => null,
                    'cancel_at_period_end' => false,
                    'dunning_attempts' => 0,
                    'last_successful_charge_at' => $invoice->paid_at,
                    'last_failed_charge_at' => null,
                    'last_invoice_id' => $invoice->id,
                    'starts_at' => $subscription->starts_at ?: $invoice->paid_at,
                    'ends_at' => $item->period_ends_at,
                    'renews_at' => $item->period_ends_at,
                    'last_payment_at' => $invoice->paid_at,
                    'meta' => array_merge((array) $subscription->meta, [
                        'pending_activation' => false,
                    ]),
                ]);
                $subscription->save();
            });

        $this->billingStateService->syncCentroSnapshotFromTenantSubscription($tenantSubscription->fresh());
    }

    protected function createReplacementInvoice(BillingInvoice $invoice): BillingInvoice
    {
        $replacement = BillingInvoice::query()->create([
            'public_id' => (string) Str::uuid(),
            'centro_id' => $invoice->centro_id,
            'clinic_registration_request_id' => $invoice->clinic_registration_request_id,
            'kind' => 'refund_replacement',
            'status' => 'open',
            'currency' => $invoice->currency,
            'due_at' => $this->periodService->now(),
            'grace_until' => $this->periodService->graceUntil($this->periodService->now()),
            'billing_starts_at' => $invoice->billing_starts_at,
            'billing_ends_at' => $invoice->billing_ends_at,
            'billing_renews_at' => $invoice->billing_renews_at,
            'meta' => array_merge((array) $invoice->meta, [
                'replaces_invoice_id' => $invoice->id,
            ]),
        ]);

        foreach ($invoice->items as $item) {
            BillingInvoiceItem::query()->create([
                'billing_invoice_id' => $replacement->id,
                'billing_module_id' => $item->billing_module_id,
                'item_type' => $item->item_type,
                'description' => $item->description,
                'billing_interval' => $item->billing_interval,
                'quantity' => $item->quantity,
                'unit_amount' => $item->unit_amount,
                'amount' => $item->amount,
                'period_starts_at' => $item->period_starts_at,
                'period_ends_at' => $item->period_ends_at,
                'meta' => $item->meta,
            ]);
        }

        $this->syncInvoiceTotals($replacement);

        return $replacement->fresh('items');
    }

    protected function markSubscriptionsPastDue(?Centros_Medico $centro, BillingInvoice $replacementInvoice): void
    {
        if (! $centro) {
            return;
        }

        $tenantSubscription = $this->ensureTenantSubscription($centro);
        $tenantSubscription->forceFill([
            'status' => 'past_due',
            'grace_until' => $replacementInvoice->grace_until,
            'last_failed_charge_at' => $this->periodService->now(),
            'dunning_attempts' => max(1, (int) $tenantSubscription->dunning_attempts),
            'last_invoice_id' => $replacementInvoice->id,
        ])->save();

        $replacementInvoice->items
            ->filter(fn (BillingInvoiceItem $item) => in_array($item->item_type, ['module_proration', 'module_renewal'], true))
            ->each(function (BillingInvoiceItem $item) use ($centro, $replacementInvoice): void {
                BillingModuleSubscription::query()
                    ->where('centro_id', $centro->id)
                    ->where('billing_module_id', $item->billing_module_id)
                    ->update([
                        'status' => 'past_due',
                        'grace_until' => $replacementInvoice->grace_until,
                        'last_failed_charge_at' => $this->periodService->now(),
                        'dunning_attempts' => DB::raw('CASE WHEN dunning_attempts < 1 THEN 1 ELSE dunning_attempts END'),
                        'last_invoice_id' => $replacementInvoice->id,
                    ]);
            });

        $this->billingStateService->syncCentroSnapshotFromTenantSubscription($tenantSubscription->fresh());

        $this->notificationService->notifyTenantAdmins(
            centro: $centro,
            eventKey: 'billing.payment_reversed',
            channels: ['database', 'mail'],
            payload: [
                'title' => 'Pago revertido',
                'body' => 'Se detecto un refund o reverso. Tu cuenta quedo con un saldo pendiente y requiere accion.',
                'level' => 'warning',
                'action_url' => '/billing',
                'action_label' => 'Pagar ahora',
            ],
            invoice: $replacementInvoice,
            tenantSubscription: $tenantSubscription,
        );
    }

    protected function ensureTenantSubscription(
        Centros_Medico $centro,
        ?string $planCode = null,
    ): BillingTenantSubscription {
        $existing = BillingTenantSubscription::query()
            ->where('centro_id', $centro->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $legacy = BillingSubscription::query()
            ->where('centro_id', $centro->id)
            ->orderByDesc('last_synced_at')
            ->orderByDesc('id')
            ->first();

        $planCode = $planCode
            ?: $centro->billing_plan_code
            ?: $legacy?->plan_code
            ?: $this->planService->defaultPlanCode();
        $interval = $this->periodService->planInterval($planCode);
        $currentPeriodEndsAt = $centro->billing_renews_at
            ?: $legacy?->renews_at
            ?: $this->periodService->addInterval($this->periodService->now(), $interval);
        $currentPeriodStartsAt = $legacy?->current_period_start_at
            ?: $this->periodService->subtractInterval($currentPeriodEndsAt, $interval);
        $status = in_array($centro->billing_status, ['active', 'past_due', 'grace', 'canceled'], true)
            ? $centro->billing_status
            : 'suspended';

        return BillingTenantSubscription::query()->create([
            'centro_id' => $centro->id,
            'status' => $status,
            'plan_code' => $planCode,
            'billing_interval' => $interval,
            'anchor_at' => $legacy?->starts_at ?: $currentPeriodStartsAt,
            'current_period_starts_at' => $currentPeriodStartsAt,
            'current_period_ends_at' => $currentPeriodEndsAt,
            'next_charge_at' => $currentPeriodEndsAt,
            'grace_until' => null,
            'cancel_at_period_end' => false,
            'canceled_at' => null,
            'dunning_attempts' => 0,
            'last_successful_charge_at' => $legacy?->last_synced_at,
            'last_failed_charge_at' => null,
            'meta' => [
                'migrated_from_legacy' => $legacy?->id,
            ],
        ]);
    }

    protected function syncInvoiceTotals(BillingInvoice $invoice): void
    {
        $invoice->loadMissing('items');
        $subtotal = (float) $invoice->items->sum(fn (BillingInvoiceItem $item) => (float) $item->amount);

        $invoice->forceFill([
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal, 2),
        ])->save();
    }

    protected function ensureInvoiceCanBePaid(BillingInvoice $invoice): BillingInvoice
    {
        $invoice->loadMissing('items');

        if (! in_array($invoice->status, ['open', 'past_due', 'paid'], true)) {
            throw ValidationException::withMessages([
                'invoice' => 'Esta factura ya no esta disponible para cobro.',
            ]);
        }

        if ($invoice->status === 'paid') {
            return $invoice;
        }

        $supersedingInvoice = $this->findSupersedingPaidModuleInvoice($invoice);
        if (! $supersedingInvoice) {
            return $invoice;
        }

        $invoice->forceFill([
            'status' => 'voided',
            'voided_at' => $this->periodService->now(),
            'notes' => 'Factura anulada porque el modulo ya fue cubierto por otro pago.',
            'meta' => array_merge((array) $invoice->meta, [
                'superseded_by_invoice_id' => $supersedingInvoice->id,
            ]),
        ])->save();

        $this->auditService->log(
            eventType: 'billing.invoice.voided',
            centro: $invoice->centro,
            invoice: $invoice,
            reason: 'Se evito cobrar una factura vieja porque ya existia otra factura pagada del mismo modulo.',
            newValues: [
                'status' => 'voided',
                'superseded_by_invoice_id' => $supersedingInvoice->id,
            ],
        );

        throw ValidationException::withMessages([
            'invoice' => 'Ese cobro ya no corresponde porque el modulo ya fue contratado con otro pago.',
        ]);
    }

    protected function findSupersedingPaidModuleInvoice(BillingInvoice $invoice): ?BillingInvoice
    {
        $moduleIds = $invoice->items
            ->filter(fn (BillingInvoiceItem $item) => in_array($item->item_type, ['module_proration', 'module_renewal'], true))
            ->pluck('billing_module_id')
            ->filter()
            ->unique()
            ->values();

        if ($moduleIds->isEmpty()) {
            return null;
        }

        return BillingInvoice::query()
            ->where('centro_id', $invoice->centro_id)
            ->where('status', 'paid')
            ->whereKeyNot($invoice->id)
            ->where(function ($query) use ($invoice): void {
                if ($invoice->billing_ends_at) {
                    $query->where('billing_ends_at', '>=', $invoice->billing_ends_at);
                } else {
                    $query->where('id', '>', $invoice->id);
                }
            })
            ->whereHas('items', function ($query) use ($moduleIds): void {
                $query->whereIn('billing_module_id', $moduleIds->all())
                    ->whereIn('item_type', ['module_proration', 'module_renewal']);
            })
            ->orderByDesc('billing_ends_at')
            ->orderByDesc('id')
            ->first();
    }

    protected function findOpenModuleInvoice(Centros_Medico $centro, BillingModule $module): ?BillingInvoice
    {
        return BillingInvoice::query()
            ->with('items')
            ->where('centro_id', $centro->id)
            ->whereIn('status', ['open', 'past_due'])
            ->whereHas('items', function ($query) use ($module): void {
                $query->where('billing_module_id', $module->id)
                    ->where('item_type', 'module_proration');
            })
            ->latest('id')
            ->first();
    }

    protected function voidConflictingOpenModuleInvoices(Centros_Medico $centro, BillingInvoice $paidInvoice): void
    {
        $moduleIds = $paidInvoice->items
            ->filter(fn (BillingInvoiceItem $item) => in_array($item->item_type, ['module_proration', 'module_renewal'], true))
            ->pluck('billing_module_id')
            ->filter()
            ->unique()
            ->values();

        if ($moduleIds->isEmpty()) {
            return;
        }

        BillingInvoice::query()
            ->with('items')
            ->where('centro_id', $centro->id)
            ->whereKeyNot($paidInvoice->id)
            ->whereIn('status', ['open', 'past_due'])
            ->whereHas('items', function ($query) use ($moduleIds): void {
                $query->whereIn('billing_module_id', $moduleIds->all())
                    ->whereIn('item_type', ['module_proration', 'module_renewal']);
            })
            ->get()
            ->each(function (BillingInvoice $invoice) use ($paidInvoice, $centro): void {
                $invoice->forceFill([
                    'status' => 'voided',
                    'voided_at' => $this->periodService->now(),
                    'notes' => 'Factura anulada porque el modulo ya fue activado con otro cobro.',
                    'meta' => array_merge((array) $invoice->meta, [
                        'voided_by_invoice_id' => $paidInvoice->id,
                    ]),
                ])->save();

                $this->auditService->log(
                    eventType: 'billing.invoice.voided',
                    centro: $centro,
                    invoice: $invoice,
                    reason: 'Se anulo una factura abierta porque otra factura del mismo modulo ya fue pagada.',
                    newValues: [
                        'status' => 'voided',
                        'voided_by_invoice_id' => $paidInvoice->id,
                    ],
                );
            });
    }

    protected function findReusableAttempt(BillingInvoice $invoice): ?BillingChargeAttempt
    {
        $attempt = BillingChargeAttempt::query()
            ->where('billing_invoice_id', $invoice->id)
            ->whereIn('status', ['created', 'approved'])
            ->latest('id')
            ->first();

        if (! $attempt) {
            return null;
        }

        try {
            $providerOrder = $this->payPalService->getOrder($attempt->paypal_order_id);
            $status = $this->normalizeAttemptStatus((string) Arr::get($providerOrder, 'status', 'CREATED'));

            $attempt->forceFill([
                'status' => $status,
                'approve_url' => $this->payPalService->extractApproveUrl($providerOrder) ?: $attempt->approve_url,
                'payload' => array_merge((array) $attempt->payload, [
                    'latest_provider_order' => $providerOrder,
                ]),
            ])->save();
        } catch (\Throwable) {
            return $attempt;
        }

        return in_array($attempt->status, ['created', 'approved'], true)
            ? $attempt->fresh()
            : null;
    }

    protected function findAttemptForCapture(?string $paypalOrderId, ?string $paypalCaptureId): ?BillingChargeAttempt
    {
        if ($paypalOrderId) {
            $attempt = BillingChargeAttempt::query()
                ->where('paypal_order_id', $paypalOrderId)
                ->first();

            if ($attempt) {
                return $attempt;
            }
        }

        if ($paypalCaptureId) {
            return BillingChargeAttempt::query()
                ->where('paypal_capture_id', $paypalCaptureId)
                ->first();
        }

        return null;
    }

    protected function normalizeAttemptStatus(string $providerStatus): string
    {
        return match (strtoupper($providerStatus)) {
            'CREATED', 'PAYER_ACTION_REQUIRED' => 'created',
            'APPROVED' => 'approved',
            'COMPLETED' => 'captured',
            'VOIDED' => 'canceled',
            default => 'created',
        };
    }

    protected function invoiceDescription(BillingInvoice $invoice): string
    {
        return match ($invoice->kind) {
            'onboarding' => 'Activacion inicial de clinica',
            'module_proration' => 'Prorrateo de modulo',
            'reactivation' => 'Reactivacion de cuenta',
            'refund_replacement' => 'Reposicion de saldo revertido',
            default => 'Pago de facturacion',
        };
    }

    protected function parsePaypalTimestamp(?string $value): ?Carbon
    {
        if (! $value || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function onboardingFreeTrialDays(): int
    {
        return max(1, (int) config('billing.onboarding.free_trial_days', 30));
    }
}
