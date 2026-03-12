<?php

namespace App\Http\Controllers;

use App\Models\Centros_Medico;
use App\Services\Billing\BillingPlanService;
use App\Services\Billing\BillingSubscriptionService;
use App\Services\Billing\PayPalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class TenantBillingController extends Controller
{
    public function __construct(
        protected BillingPlanService $billingPlanService,
        protected PayPalService $payPalService,
        protected BillingSubscriptionService $billingSubscriptionService,
    ) {
    }

    public function inactive()
    {
        $tenant = tenancy()->tenant;
        abort_unless($tenant && $tenant->centro_id, 404);

        $centro = Centros_Medico::on('mysql')->findOrFail($tenant->centro_id);
        if ($centro->isBillingActive()) {
            return redirect('/admin');
        }

        return view('tenant-billing-inactive', [
            'centro' => $centro,
            'plans' => $this->billingPlanService->all(),
            'selectedPlanCode' => $centro->billing_plan_code ?: $this->billingPlanService->defaultPlanCode(),
        ]);
    }

    public function startReactivation(Request $request): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        $tenant = tenancy()->tenant;
        abort_unless($tenant && $tenant->centro_id, 404);

        $centro = Centros_Medico::on('mysql')->findOrFail($tenant->centro_id);
        $validated = $request->validate([
            'plan_code' => ['required', 'string', 'max:32'],
        ]);

        $planCode = (string) $validated['plan_code'];
        $paypalPlanId = $this->billingPlanService->getPayPalPlanIdOrFail($planCode);

        try {
            $result = $this->payPalService->createSubscription(
                paypalPlanId: $paypalPlanId,
                customId: "reactivate:{$tenant->id}:" . auth()->id() . ':' . now()->timestamp,
                returnUrl: route('tenant.billing.reactivate.return'),
                cancelUrl: route('tenant.billing.reactivate.cancel'),
            );

            $this->billingSubscriptionService->syncFromPayPalSubscription(
                paypalSubscription: (array) $result['raw'],
                centroId: $centro->id
            );

            session()->put('tenant_reactivation_subscription_id', (string) $result['id']);
            session()->put('tenant_reactivation_plan_code', $planCode);

            return redirect()->away((string) $result['approve_url']);
        } catch (Throwable $e) {
            Log::error('Error iniciando reactivacion PayPal para tenant inactivo.', [
                'tenant_id' => $tenant->id,
                'centro_id' => $centro->id,
                'plan_code' => $planCode,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.billing.inactive')
                ->with('error', 'No se pudo iniciar el checkout de PayPal.');
        }
    }

    public function returnFromPayPal(Request $request): RedirectResponse
    {
        $tenant = tenancy()->tenant;
        abort_unless($tenant && $tenant->centro_id, 404);

        $centro = Centros_Medico::on('mysql')->findOrFail($tenant->centro_id);
        $subscriptionId = (string) ($request->query('subscription_id')
            ?? $request->query('token')
            ?? session('tenant_reactivation_subscription_id', ''));

        if ($subscriptionId === '') {
            return redirect()->route('tenant.billing.inactive')
                ->with('error', 'PayPal no devolvio una suscripcion valida.');
        }

        try {
            $subscriptionData = $this->payPalService->getSubscription($subscriptionId);
            $subscription = $this->billingSubscriptionService->syncFromPayPalSubscription(
                paypalSubscription: $subscriptionData,
                centroId: $centro->id
            );

            if ($subscription->isActive()) {
                return redirect('/admin')->with('status', 'Suscripcion activa. Acceso restaurado.');
            }

            return redirect()->route('tenant.billing.inactive')
                ->with('status', 'Pago aprobado. Estamos esperando activacion final de PayPal.');
        } catch (Throwable $e) {
            Log::error('Error confirmando retorno de reactivacion PayPal.', [
                'tenant_id' => $tenant->id,
                'centro_id' => $centro->id,
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.billing.inactive')
                ->with('error', 'No se pudo confirmar la reactivacion.');
        }
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('tenant.billing.inactive')
            ->with('error', 'Cancelaste el checkout de reactivacion.');
    }
}
