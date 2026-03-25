<?php

namespace App\Http\Controllers;

use App\Models\BillingModule;
use App\Models\BillingModuleSubscription;
use App\Models\Centros_Medico;
use App\Services\Billing\BillingAuditService;
use App\Services\Billing\BillingInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TenantModuleBillingController extends Controller
{
    public function __construct(
        protected BillingInvoiceService $billingInvoiceService,
        protected BillingAuditService $billingAuditService,
    ) {
    }

    public function index(): RedirectResponse
    {
        return redirect()->route('tenant.billing.index');
    }

    public function startCheckout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'module_code' => ['required', 'string', 'max:64'],
            'billing_interval' => ['nullable', 'in:monthly,annual'],
        ]);

        $module = BillingModule::query()
            ->where('code', $validated['module_code'])
            ->where('is_active', true)
            ->firstOrFail();

        return $this->subscribe($request, $module);
    }

    public function subscribe(Request $request, BillingModule $module): RedirectResponse
    {
        abort_unless(auth()->user()?->can('billing.manage') || auth()->user()?->hasRole('administrador'), 403);

        $interval = (string) ($request->input('billing_interval') ?: config('billing.module_billing.default_interval', 'monthly'));
        if (! in_array($interval, ['monthly', 'annual'], true)) {
            throw ValidationException::withMessages([
                'billing_interval' => 'Intervalo no valido para el modulo.',
            ]);
        }

        $centro = $this->currentCentro();
        $invoice = $this->billingInvoiceService->createModuleProrationInvoice($centro, $module, $interval);

        $this->billingAuditService->log(
            eventType: 'billing.module.invoice_created',
            centro: $centro,
            invoice: $invoice,
            actor: auth()->user(),
            actorType: 'user',
            reason: 'Prorrateo de modulo solicitado desde portal tenant.',
            meta: [
                'module_code' => $module->code,
                'billing_interval' => $interval,
            ],
        );

        return redirect()->route('tenant.billing.index')
            ->with('status', 'Se preparo la factura del modulo. Completa el pago desde billing.');
    }

    public function returnFromPayPal(): RedirectResponse
    {
        return redirect()->route('tenant.billing.index');
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('tenant.billing.index')
            ->with('error', 'Cancelaste el checkout del modulo.');
    }

    public function cancelAtPeriodEnd(BillingModule $module): RedirectResponse
    {
        abort_unless(auth()->user()?->can('billing.cancellation.manage') || auth()->user()?->hasRole('administrador'), 403);

        $centro = $this->currentCentro();
        $subscription = BillingModuleSubscription::query()
            ->where('centro_id', $centro->id)
            ->where('billing_module_id', $module->id)
            ->firstOrFail();

        $oldValue = (bool) $subscription->cancel_at_period_end;
        $subscription->cancel_at_period_end = true;
        $subscription->save();

        $this->billingAuditService->log(
            eventType: 'billing.module.cancel_scheduled',
            centro: $centro,
            moduleSubscription: $subscription,
            actor: auth()->user(),
            actorType: 'user',
            reason: 'Cancelacion programada de modulo desde portal tenant.',
            oldValues: ['cancel_at_period_end' => $oldValue],
            newValues: ['cancel_at_period_end' => true],
        );

        return redirect()->route('tenant.billing.index')
            ->with('status', 'El modulo se cancelara al final del periodo actual.');
    }

    protected function currentCentro(): Centros_Medico
    {
        $tenant = tenancy()->tenant;
        abort_unless($tenant && $tenant->centro_id, 404);

        return Centros_Medico::on('mysql')->findOrFail($tenant->centro_id);
    }
}
