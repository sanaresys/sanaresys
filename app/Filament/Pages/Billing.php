<?php

namespace App\Filament\Pages;

use App\Models\BillingInvoice;
use App\Models\BillingInvoiceItem;
use App\Models\BillingModule;
use App\Models\BillingModuleSubscription;
use App\Models\BillingTenantSubscription;
use App\Models\Centros_Medico;
use App\Services\Billing\BillingInvoiceService;
use App\Services\Billing\BillingPeriodService;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Billing extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Billing';

    protected static ?string $navigationGroup = 'Gestión de Facturación';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Billing';

    protected static ?string $slug = 'billing';

    protected static string $view = 'filament.pages.billing';

    public array $billing = [];

    public function mount(
        BillingInvoiceService $billingInvoiceService,
        BillingPeriodService $periodService,
    ): void {
        $centro = $this->currentCentro();
        $tenantSubscription = $billingInvoiceService->ensureTenantSubscriptionForCentro($centro);
        $openInvoice = $billingInvoiceService->openInvoiceForCentro($centro);

        if (! $openInvoice && $centro->isBillingBlocked()) {
            $openInvoice = $billingInvoiceService->createReactivationInvoice($centro);
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
                'subscriptions' => fn ($query) => $query
                    ->where('centro_id', $centro->id)
                    ->with('lastInvoice'),
            ])
            ->orderBy('name')
            ->get();

        $this->billing = $this->buildBillingPayload(
            centro: $centro,
            tenantSubscription: $tenantSubscription,
            openInvoice: $openInvoice,
            invoices: $invoices,
            modules: $modules,
            periodService: $periodService,
        );
    }

    public function getSubheading(): ?string
    {
        return 'Plan base, módulos y pagos pendientes en un solo lugar.';
    }

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    public static function canAccess(): bool
    {
        return (bool) Auth::user();
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->can('billing.manage')
            || $user->can('billing.invoice.pay')
            || $user->can('billing.cancellation.manage')
            || $user->hasRole('administrador')
            || $user->hasRole('root');
    }

    protected function buildBillingPayload(
        Centros_Medico $centro,
        BillingTenantSubscription $tenantSubscription,
        ?BillingInvoice $openInvoice,
        Collection $invoices,
        Collection $modules,
        BillingPeriodService $periodService,
    ): array {
        $openInvoiceModuleId = $this->extractInvoiceModuleId($openInvoice);
        $initialPanel = $openInvoice ? 'invoice' : 'plan';

        return [
            'centro' => [
                'id' => $centro->id,
                'name' => $centro->nombre_centro,
                'is_blocked' => $centro->isBillingBlocked(),
            ],
            'tenant' => [
                'status' => $tenantSubscription->status,
                'status_label' => $this->tenantStatusLabel($tenantSubscription->status),
                'status_help' => $this->tenantStatusHelp($tenantSubscription->status),
                'status_tone' => $this->toneForState($tenantSubscription->status),
                'plan_code' => strtoupper((string) $tenantSubscription->plan_code),
                'plan_label' => $this->planLabel($tenantSubscription->billing_interval),
                'interval' => $tenantSubscription->billing_interval,
                'interval_label' => $this->intervalLabel($tenantSubscription->billing_interval),
                'next_charge_at' => $this->formatDate($tenantSubscription->next_charge_at),
                'grace_until' => $this->formatDate($tenantSubscription->grace_until, true) ?: 'No aplica',
                'cancel_at_period_end' => (bool) $tenantSubscription->cancel_at_period_end,
                'cancel_help' => $tenantSubscription->cancel_at_period_end
                    ? 'La renovación está detenida al final de este período.'
                    : 'La renovación sigue activa para el próximo ciclo.',
                'cancel_url' => route('tenant.billing.cancel-at-period-end'),
                'resume_url' => route('tenant.billing.resume-renewal'),
            ],
            'open_invoice' => $openInvoice ? $this->invoicePayload($openInvoice) : null,
            'open_invoice_module_id' => $openInvoiceModuleId,
            'initial_panel' => $initialPanel,
            'history' => $invoices->map(fn (BillingInvoice $invoice) => [
                'id' => $invoice->id,
                'public_id' => $invoice->public_id,
                'kind' => $invoice->kind,
                'kind_label' => $this->invoiceKindLabel($invoice->kind),
                'status' => $invoice->status,
                'status_label' => $this->invoiceStatusLabel($invoice->status),
                'status_tone' => $this->toneForState($invoice->status),
                'total' => $this->money((float) $invoice->total),
                'cover_range' => $this->formatRange($invoice->billing_starts_at, $invoice->billing_ends_at),
            ])->all(),
            'modules' => $modules->map(function (BillingModule $module) use ($tenantSubscription, $periodService, $openInvoiceModuleId, $openInvoice) {
                $subscription = $module->subscriptions->first();
                $status = $subscription?->status ?? 'available';
                $activationOptions = $this->activationOptions($module, $tenantSubscription, $periodService);
                $isCurrentOpenInvoice = $openInvoiceModuleId === $module->id;

                return [
                    'id' => $module->id,
                    'code' => $module->code,
                    'name' => $module->name,
                    'description' => $module->description ?: 'Servicio adicional para ampliar lo que puede hacer tu clínica.',
                    'status' => $status,
                    'status_label' => $this->moduleStatusLabel($status),
                    'status_help' => $this->moduleStatusHelp($status, $subscription),
                    'status_tone' => $this->toneForState($status),
                    'row_summary' => $this->moduleRowSummary($subscription),
                    'price_monthly' => $this->money((float) $module->price_monthly),
                    'price_annual' => $this->money((float) ($module->price_annual ?: ((float) $module->price_monthly * 12))),
                    'is_active' => (bool) ($subscription && in_array($subscription->status, ['active', 'past_due', 'grace'], true)),
                    'can_activate' => ! $subscription || in_array($subscription->status, ['available', 'suspended', 'canceled', 'pending'], true),
                    'can_cancel' => (bool) ($subscription && in_array($subscription->status, ['active', 'past_due', 'grace'], true) && ! $subscription->cancel_at_period_end),
                    'cancel_at_period_end' => (bool) ($subscription?->cancel_at_period_end),
                    'current_plan_label' => $subscription ? $this->planLabel($subscription->billing_interval) : 'Aún no activado',
                    'next_charge_at' => $this->formatDate($subscription?->next_charge_at) ?: 'Se calcula cuando lo actives',
                    'grace_until' => $this->formatDate($subscription?->grace_until, true) ?: 'No aplica',
                    'cancel_url' => route('tenant.billing.modules.cancel-at-period-end', $module->id),
                    'subscribe_url' => route('tenant.billing.modules.subscribe', $module->id),
                    'activation_options' => $activationOptions,
                    'default_interval' => $activationOptions[0]['code'] ?? 'monthly',
                    'has_blocking_open_invoice' => (bool) $openInvoice,
                    'is_current_open_invoice' => $isCurrentOpenInvoice,
                    'blocking_invoice_help' => $openInvoice
                        ? ($isCurrentOpenInvoice
                            ? 'Este módulo ya tiene un pago pendiente por completar.'
                            : 'Ya tienes un pago pendiente por completar antes de activar otro módulo.')
                        : null,
                ];
            })->all(),
            'paypal' => [
                'client_id' => (string) config('services.paypal.client_id', ''),
                'currency' => (string) config('billing.currency', 'USD'),
            ],
            'capabilities' => [
                'can_manage' => $this->userCanManageBilling(),
                'can_pay' => $this->userCanPayBilling(),
                'can_manage_cancellation' => $this->userCanManageCancellation(),
            ],
            'admin_url' => '/admin',
        ];
    }

    protected function invoicePayload(BillingInvoice $invoice): array
    {
        $invoice->loadMissing('items.module');
        $moduleId = $this->extractInvoiceModuleId($invoice);

        return [
            'id' => $invoice->id,
            'public_id' => $invoice->public_id,
            'kind' => $invoice->kind,
            'kind_label' => $this->invoiceKindLabel($invoice->kind),
            'status' => $invoice->status,
            'status_label' => $this->invoiceStatusLabel($invoice->status),
            'status_tone' => $this->toneForState($invoice->status),
            'total' => $this->money((float) $invoice->total),
            'due_at' => $this->formatDate($invoice->due_at, true) ?: 'Inmediato',
            'cover_range' => $this->formatRange($invoice->billing_starts_at, $invoice->billing_ends_at),
            'help' => $this->invoiceHelp($invoice),
            'module_id' => $moduleId,
            'module_name' => $invoice->items
                ->first(fn (BillingInvoiceItem $item) => (int) $item->billing_module_id > 0)
                ?->module?->name,
            'order_url' => route('tenant.billing.invoices.order', $invoice->id),
            'capture_url' => route('tenant.billing.invoices.capture', $invoice->id),
            'items' => $invoice->items->map(fn (BillingInvoiceItem $item) => [
                'description' => $item->description,
                'kind_label' => $this->invoiceKindLabel($item->item_type),
                'amount' => $this->money((float) $item->amount),
                'period_range' => $this->formatRange($item->period_starts_at, $item->period_ends_at),
            ])->all(),
        ];
    }

    protected function activationOptions(
        BillingModule $module,
        BillingTenantSubscription $tenantSubscription,
        BillingPeriodService $periodService,
    ): array {
        $anchor = $tenantSubscription->anchor_at
            ?: $tenantSubscription->current_period_starts_at
            ?: $periodService->now();

        return collect(['monthly', 'annual'])
            ->map(function (string $interval) use ($module, $anchor, $periodService) {
                $cycle = $periodService->currentCycleForAnchor($anchor->copy(), $interval, $periodService->now());
                $fullAmount = $periodService->modulePrice($module, $interval);
                $todayAmount = $periodService->proratedAmount(
                    $fullAmount,
                    $cycle['starts_at'],
                    $cycle['ends_at'],
                    $periodService->now(),
                );

                return [
                    'code' => $interval,
                    'label' => $interval === 'annual' ? 'Anual' : 'Mensual',
                    'teaser' => $interval === 'annual'
                        ? 'Ahorras tiempo y mantienes el módulo por todo el año.'
                        : 'Pagas mes a mes y se alinea con el ciclo actual.',
                    'full_amount' => $this->money($fullAmount),
                    'today_amount' => $this->money($todayAmount),
                    'cycle_ends_at' => $this->formatDate($cycle['ends_at']),
                ];
            })
            ->all();
    }

    protected function currentCentro(): Centros_Medico
    {
        $tenant = tenancy()->tenant;
        abort_unless($tenant && $tenant->centro_id, 404);

        return Centros_Medico::on('mysql')->findOrFail($tenant->centro_id);
    }

    protected function extractInvoiceModuleId(?BillingInvoice $invoice): ?int
    {
        if (! $invoice) {
            return null;
        }

        $item = $invoice->items
            ->first(fn (BillingInvoiceItem $item) => (int) $item->billing_module_id > 0);

        return $item ? (int) $item->billing_module_id : null;
    }

    protected function tenantStatusLabel(?string $status): string
    {
        return match ($status) {
            'active' => 'Al día',
            'past_due' => 'Pago pendiente',
            'grace' => 'En gracia',
            'suspended' => 'Suspendida',
            'canceled' => 'Cancelada',
            default => 'Pendiente',
        };
    }

    protected function tenantStatusHelp(?string $status): string
    {
        return match ($status) {
            'active' => 'Tu clínica está al día y puede seguir trabajando normalmente.',
            'past_due' => 'Hay un cobro pendiente, pero todavía conservas acceso.',
            'grace' => 'Tu clínica sigue funcionando mientras resuelves el pago dentro del período de gracia.',
            'suspended' => 'Necesitas completar el pago pendiente para recuperar el acceso completo.',
            'canceled' => 'La renovación quedó detenida y la clínica salió del ciclo normal de cobro.',
            default => 'Revisa esta sección para confirmar si tienes alguna acción pendiente.',
        };
    }

    protected function moduleStatusLabel(?string $status): string
    {
        return match ($status) {
            'active' => 'Activo',
            'pending' => 'Pendiente',
            'past_due' => 'Pago pendiente',
            'grace' => 'En gracia',
            'suspended' => 'Suspendido',
            'canceled' => 'Cancelado',
            default => 'Disponible',
        };
    }

    protected function moduleStatusHelp(?string $status, ?BillingModuleSubscription $subscription): string
    {
        return match ($status) {
            'active' => 'Este módulo ya está funcionando con normalidad.',
            'pending' => 'Este módulo tiene una activación pendiente de pago.',
            'past_due' => 'Este módulo sigue visible, pero ya tiene un pago pendiente.',
            'grace' => 'Este módulo está dentro de gracia y puede requerir pago pronto.',
            'suspended' => 'Este módulo está detenido hasta regularizar el cobro.',
            'canceled' => 'Este módulo ya no seguirá renovándose.',
            default => 'Puedes activarlo cuando lo necesites.',
        };
    }

    protected function moduleRowSummary(?BillingModuleSubscription $subscription): string
    {
        if (! $subscription) {
            return 'Listo para activar';
        }

        $parts = [$this->planLabel($subscription->billing_interval)];

        if ($subscription->cancel_at_period_end) {
            $parts[] = 'Termina al final del período';
        } elseif ($subscription->next_charge_at) {
            $parts[] = 'Próximo ' . $this->formatDate($subscription->next_charge_at);
        }

        return implode(' • ', $parts);
    }

    protected function invoiceKindLabel(?string $kind): string
    {
        return match ($kind) {
            'onboarding' => 'Pago inicial de la clínica',
            'renewal' => 'Renovación del plan',
            'reactivation' => 'Reactivación de la clínica',
            'module_proration' => 'Cobro proporcional de módulo',
            'refund_replacement' => 'Saldo pendiente por reverso',
            'base_plan' => 'Plan principal',
            'module_renewal' => 'Renovación de módulo',
            default => ucfirst(str_replace('_', ' ', (string) $kind)),
        };
    }

    protected function invoiceStatusLabel(?string $status): string
    {
        return match ($status) {
            'open' => 'Pendiente',
            'past_due' => 'Atrasada',
            'paid' => 'Pagada',
            'refunded' => 'Reembolsada',
            'voided' => 'Cancelada',
            default => ucfirst((string) $status),
        };
    }

    protected function invoiceHelp(BillingInvoice $invoice): string
    {
        return match ($invoice->kind) {
            'module_proration' => 'Hoy pagas solo la parte correspondiente al tiempo que falta de este período. En el siguiente ciclo entrará el valor completo.',
            'reactivation' => 'Este pago reabre la cuenta y devuelve el acceso al panel.',
            'renewal' => 'Este es el cobro que mantiene activo tu plan base en el siguiente ciclo.',
            default => 'Completa este pago para mantener tu facturación al día.',
        };
    }

    protected function toneForState(?string $state): string
    {
        return match ($state) {
            'active', 'paid' => 'success',
            'past_due', 'grace', 'warning', 'pending' => 'warning',
            'suspended', 'canceled', 'failed', 'voided' => 'danger',
            default => 'neutral',
        };
    }

    protected function intervalLabel(?string $interval): string
    {
        return $interval === 'annual' ? 'Anual' : 'Mensual';
    }

    protected function planLabel(?string $interval): string
    {
        return $interval === 'annual' ? 'Plan anual' : 'Plan mensual';
    }

    protected function money(float $amount): string
    {
        return 'USD ' . number_format($amount, 2);
    }

    protected function formatDate(Carbon|string|null $value, bool $withTime = false): ?string
    {
        if (! $value) {
            return null;
        }

        $date = $value instanceof Carbon ? $value : Carbon::parse($value);

        return $date->format($withTime ? 'd/m/Y H:i' : 'd/m/Y');
    }

    protected function formatRange(Carbon|string|null $startsAt, Carbon|string|null $endsAt): string
    {
        $start = $this->formatDate($startsAt);
        $end = $this->formatDate($endsAt);

        if (! $start && ! $end) {
            return 'Se calculará al confirmar el pago';
        }

        return trim(($start ?: 'Inicio') . ' - ' . ($end ?: 'Fin'));
    }

    protected function userCanManageBilling(): bool
    {
        $user = Auth::user();

        return (bool) ($user && (
            $user->can('billing.manage')
            || $user->hasRole('administrador')
            || $user->hasRole('root')
        ));
    }

    protected function userCanPayBilling(): bool
    {
        $user = Auth::user();

        return (bool) ($user && (
            $user->can('billing.invoice.pay')
            || $user->can('billing.manage')
            || $user->hasRole('administrador')
            || $user->hasRole('root')
        ));
    }

    protected function userCanManageCancellation(): bool
    {
        $user = Auth::user();

        return (bool) ($user && (
            $user->can('billing.cancellation.manage')
            || $user->can('billing.manage')
            || $user->hasRole('administrador')
            || $user->hasRole('root')
        ));
    }
}
