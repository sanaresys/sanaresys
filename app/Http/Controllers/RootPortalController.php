<?php

namespace App\Http\Controllers;

use App\Models\Centros_Medico;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Billing\BillingAdminService;
use App\Services\Billing\BillingStateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class RootPortalController extends Controller
{
    public function __construct(
        protected BillingStateService $billingStateService,
        protected BillingAdminService $billingAdminService,
    ) {
    }

    public function index()
    {
        $this->assertRoot();

        $centros = Centros_Medico::query()
            ->where('tenancy_mode', 'domain')
            ->with(['tenant', 'billingTenantSubscription', 'billingInvoices' => fn ($query) => $query->latest('id')->limit(5)])
            ->orderBy('id')
            ->get();

        $centros->load([
            'billingSubscriptions' => fn ($query) => $query
                ->orderByDesc('last_synced_at')
                ->orderByDesc('id'),
        ]);

        $hasModuleTables = Schema::connection('mysql')->hasTable('billing_module_subscriptions')
            && Schema::connection('mysql')->hasTable('billing_modules');

        if ($hasModuleTables) {
            $centros->load([
                'billingModuleSubscriptions' => fn ($query) => $query
                    ->with('module')
                    ->orderByDesc('renews_at')
                    ->orderByDesc('id'),
            ]);
        } else {
            $centros->each(fn (Centros_Medico $centro) => $centro->setRelation('billingModuleSubscriptions', collect()));
        }

        return view('root-portal', compact('centros'));
    }

    public function enterTenant(Request $request, Centros_Medico $centro): RedirectResponse
    {
        $this->assertRoot();

        $mode = (string) ($centro->tenancy_mode ?? '');
        if ($mode !== 'domain') {
            throw ValidationException::withMessages([
                'centro' => 'El centro seleccionado no esta en modo domain.',
            ]);
        }

        $tenant = Tenant::where('centro_id', $centro->id)->first();
        if (! $tenant) {
            throw ValidationException::withMessages([
                'centro' => 'No existe tenant para el centro seleccionado.',
            ]);
        }

        $domain = $tenant->getPrimaryDomain();
        if (! $domain) {
            throw ValidationException::withMessages([
                'centro' => 'El tenant seleccionado no tiene un dominio primario configurado.',
            ]);
        }

        $targetUserId = null;

        try {
            tenancy()->initialize($tenant);

            $targetUser = User::role('administrador')->first()
                ?? User::query()->first();

            if (! $targetUser) {
                throw ValidationException::withMessages([
                    'tenant' => 'El tenant no tiene usuarios para impersonacion.',
                ]);
            }

            $targetUserId = $targetUser->id;
        } finally {
            tenancy()->end();
        }

        $token = tenancy()->impersonate(
            tenant: $tenant,
            userId: (string) $targetUserId,
            redirectUrl: '/admin',
            authGuard: 'web'
        );

        Log::info('Root genero token de impersonacion para entrar a tenant domain.', [
            'root_user_id' => auth()->id(),
            'centro_id' => $centro->id,
            'tenant_id' => $tenant->id,
            'target_user_id' => $targetUserId,
            'domain' => $domain,
        ]);

        $scheme = (string) config('tenancy.tenant_scheme', 'https');
        return redirect()->away("{$scheme}://{$domain}/tenant/impersonate/{$token->token}");
    }

    public function setBillingOverride(Request $request, Centros_Medico $centro): RedirectResponse
    {
        $this->assertRoot();

        $validated = $request->validate([
            'override' => ['required', 'in:force_active,force_inactive,none'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $newOverride = $validated['override'] === 'none'
            ? null
            : $validated['override'];

        $this->billingStateService->applyOverride(
            centro: $centro,
            newOverride: $newOverride,
            reason: (string) $validated['reason'],
            performedBy: auth()->user()
        );

        return redirect()
            ->route('portal.root')
            ->with('status', 'Estado de facturacion actualizado correctamente.');
    }

    public function markInvoicePaid(Request $request, Centros_Medico $centro): RedirectResponse
    {
        $this->assertRoot();

        $validated = $request->validate([
            'invoice_id' => ['nullable', 'integer'],
        ]);
        $invoiceId = (int) ($validated['invoice_id'] ?? 0);

        $invoice = $centro->billingInvoices()
            ->when($invoiceId > 0, fn ($query) => $query->whereKey($invoiceId))
            ->whereIn('status', ['open', 'past_due'])
            ->latest('id')
            ->firstOrFail();

        $this->billingAdminService->markInvoicePaid(
            invoice: $invoice,
            actor: auth()->user(),
            reason: 'Pago manual marcado desde root.',
        );

        return redirect()->route('portal.root')
            ->with('status', 'Factura marcada como pagada manualmente.');
    }

    public function extendBilling(Request $request, Centros_Medico $centro): RedirectResponse
    {
        $this->assertRoot();

        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $this->billingAdminService->extendTenantPeriod(
            centro: $centro,
            days: (int) $validated['days'],
            actor: auth()->user(),
            reason: (string) $validated['reason'],
        );

        return redirect()->route('portal.root')
            ->with('status', 'Vigencia extendida correctamente.');
    }

    public function setTenantStatus(Request $request, Centros_Medico $centro): RedirectResponse
    {
        $this->assertRoot();

        $validated = $request->validate([
            'status' => ['required', 'in:active,past_due,grace,suspended,canceled'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $this->billingAdminService->setTenantStatus(
            centro: $centro,
            status: (string) $validated['status'],
            actor: auth()->user(),
            reason: (string) $validated['reason'],
        );

        return redirect()->route('portal.root')
            ->with('status', 'Estado del tenant actualizado.');
    }

    public function toggleCancelAtPeriodEnd(Request $request, Centros_Medico $centro): RedirectResponse
    {
        $this->assertRoot();

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $this->billingAdminService->setCancelAtPeriodEnd(
            centro: $centro,
            enabled: (bool) $validated['enabled'],
            actor: auth()->user(),
            reason: (string) $validated['reason'],
        );

        return redirect()->route('portal.root')
            ->with('status', 'Politica de cancelacion actualizada.');
    }

    protected function assertRoot(): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasRole('root')) {
            abort(403);
        }
    }
}
