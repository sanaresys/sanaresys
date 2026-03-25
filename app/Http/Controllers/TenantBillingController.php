<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Models\BillingModule;
use App\Models\Centros_Medico;
use App\Services\Billing\BillingAdminService;
use App\Services\Billing\BillingInvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TenantBillingController extends Controller
{
    public function __construct(
        protected BillingInvoiceService $billingInvoiceService,
        protected BillingAdminService $billingAdminService,
    ) {
    }

    public function index()
    {
        $centro = $this->currentCentro();
        $tenantSubscription = $this->billingInvoiceService->ensureTenantSubscriptionForCentro($centro);
        $openInvoice = $this->billingInvoiceService->openInvoiceForCentro($centro);

        if (! $openInvoice && $centro->isBillingBlocked()) {
            $openInvoice = $this->billingInvoiceService->createReactivationInvoice($centro);
        }

        $invoices = BillingInvoice::query()
            ->with('items')
            ->where('centro_id', $centro->id)
            ->latest('id')
            ->limit(12)
            ->get();

        $modules = BillingModule::query()
            ->where('is_active', true)
            ->with([
                'subscriptions' => fn ($query) => $query->where('centro_id', $centro->id),
            ])
            ->orderBy('name')
            ->get();

        return view('tenant-billing', [
            'centro' => $centro,
            'tenantSubscription' => $tenantSubscription,
            'openInvoice' => $openInvoice,
            'invoices' => $invoices,
            'modules' => $modules,
            'paypalClientId' => (string) config('services.paypal.client_id', ''),
            'paypalCurrency' => (string) config('billing.currency', 'USD'),
        ]);
    }

    public function inactive()
    {
        return redirect()->route('tenant.billing.index');
    }

    public function startReactivation(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('billing.manage') || auth()->user()?->hasRole('administrador'), 403);

        $centro = $this->currentCentro();
        $planCode = $request->validate([
            'plan_code' => ['nullable', 'string', 'max:32'],
        ])['plan_code'] ?? null;

        $this->billingInvoiceService->createReactivationInvoice($centro, $planCode ? (string) $planCode : null);

        return redirect()->route('tenant.billing.index')
            ->with('status', 'Se preparo la factura de reactivacion. Completa el pago para recuperar acceso.');
    }

    public function createOrder(Request $request, BillingInvoice $invoice): JsonResponse
    {
        abort_unless(
            auth()->user()?->can('billing.invoice.pay')
            || auth()->user()?->can('billing.manage')
            || auth()->user()?->hasRole('administrador'),
            403
        );

        $centro = $this->currentCentro();
        abort_unless((int) $invoice->centro_id === (int) $centro->id, 404);

        $attempt = $this->billingInvoiceService->createOrReuseAttempt(
            invoice: $invoice,
            context: 'tenant_invoice',
            requestedBy: auth()->user(),
            returnUrl: route('tenant.billing.reactivate.return'),
            cancelUrl: route('tenant.billing.reactivate.cancel'),
        );

        return response()->json([
            'orderId' => $attempt->paypal_order_id,
        ]);
    }

    public function capture(Request $request, BillingInvoice $invoice): JsonResponse
    {
        abort_unless(
            auth()->user()?->can('billing.invoice.pay')
            || auth()->user()?->can('billing.manage')
            || auth()->user()?->hasRole('administrador'),
            403
        );

        $centro = $this->currentCentro();
        abort_unless((int) $invoice->centro_id === (int) $centro->id, 404);

        $validated = $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        $this->billingInvoiceService->captureAttemptFromReturn(
            paypalOrderId: (string) $validated['order_id'],
            centro: $centro,
        );

        return response()->json([
            'redirect_url' => $centro->fresh()->isBillingActive() ? '/admin' : route('tenant.billing.index'),
        ]);
    }

    public function returnFromPayPal(Request $request): RedirectResponse
    {
        $centro = $this->currentCentro();
        $orderId = (string) ($request->query('token') ?? $request->query('orderId'));

        if ($orderId === '') {
            return redirect()->route('tenant.billing.index')
                ->with('error', 'PayPal no devolvio una orden valida.');
        }

        try {
            $this->billingInvoiceService->captureAttemptFromReturn(
                paypalOrderId: $orderId,
                centro: $centro,
            );

            return redirect($centro->fresh()->isBillingActive() ? '/admin' : route('tenant.billing.index'))
                ->with('status', 'Pago confirmado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error confirmando pago tenant desde retorno PayPal.', [
                'centro_id' => $centro->id,
                'paypal_order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.billing.index')
                ->with('error', 'No se pudo confirmar el pago.');
        }
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('tenant.billing.index')
            ->with('error', 'Cancelaste el checkout de PayPal.');
    }

    public function cancelAtPeriodEnd(): RedirectResponse
    {
        $centro = $this->currentCentro();
        abort_unless(auth()->user()?->can('billing.cancellation.manage') || auth()->user()?->hasRole('administrador'), 403);

        $this->billingAdminService->setCancelAtPeriodEnd(
            centro: $centro,
            enabled: true,
            actor: auth()->user(),
            reason: 'Cancelacion programada desde portal tenant.',
        );

        return redirect()->route('tenant.billing.index')
            ->with('status', 'La cancelacion al final del periodo fue programada.');
    }

    public function resumeRenewal(): RedirectResponse
    {
        $centro = $this->currentCentro();
        abort_unless(auth()->user()?->can('billing.cancellation.manage') || auth()->user()?->hasRole('administrador'), 403);

        $this->billingAdminService->setCancelAtPeriodEnd(
            centro: $centro,
            enabled: false,
            actor: auth()->user(),
            reason: 'Cancelacion programada revertida desde portal tenant.',
        );

        return redirect()->route('tenant.billing.index')
            ->with('status', 'La renovacion vuelve a quedar habilitada.');
    }

    protected function currentCentro(): Centros_Medico
    {
        $tenant = tenancy()->tenant;
        abort_unless($tenant && $tenant->centro_id, 404);

        return Centros_Medico::on('mysql')->findOrFail($tenant->centro_id);
    }
}
