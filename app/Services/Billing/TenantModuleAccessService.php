<?php

namespace App\Services\Billing;

use App\Models\BillingModule;
use App\Models\BillingModuleSubscription;
use App\Models\Centros_Medico;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TenantModuleAccessService
{
    protected ?bool $hasModuleTables = null;

    public function isModuleActive(string $moduleCode): bool
    {
        if ($this->isRootUser()) {
            return true;
        }

        if (! $this->moduleTablesAvailable()) {
            return false;
        }

        $centro = $this->currentCentro();
        if (! $centro) {
            return false;
        }

        $subscription = $this->subscriptionForModule($centro->id, $moduleCode);
        if (! $subscription) {
            return false;
        }

        return in_array($subscription->status, ['active', 'past_due', 'grace'], true);
    }

    public function isPurchaseAllowed(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->hasRole('root')) {
            return true;
        }

        return $user->can('gestionar modulos billing');
    }

    public function statusForModule(string $moduleCode): string
    {
        if ($this->isRootUser()) {
            return 'active';
        }

        if (! $this->moduleTablesAvailable()) {
            return 'inactive';
        }

        $centro = $this->currentCentro();
        if (! $centro) {
            return 'inactive';
        }

        $subscription = $this->subscriptionForModule($centro->id, $moduleCode);
        if (! $subscription) {
            return 'inactive';
        }

        return (string) $subscription->status;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function expiringModulesForCurrentTenant(array $offsets = [7, 3, 1]): Collection
    {
        if (! $this->moduleTablesAvailable()) {
            return collect();
        }

        $centro = $this->currentCentro();
        if (! $centro) {
            return collect();
        }

        $offsetSet = array_values(array_unique(array_map('intval', $offsets)));
        if ($offsetSet === []) {
            return collect();
        }

        return BillingModuleSubscription::query()
            ->with('module')
            ->where('centro_id', $centro->id)
            ->where('status', 'active')
            ->whereNotNull('renews_at')
            ->get()
            ->map(function (BillingModuleSubscription $subscription) use ($offsetSet): ?array {
                $days = now()->startOfDay()->diffInDays($subscription->renews_at?->startOfDay(), false);

                if (! in_array((int) $days, $offsetSet, true)) {
                    return null;
                }

                return [
                    'module_code' => $subscription->module?->code,
                    'module_name' => $subscription->module?->name,
                    'renews_at' => $subscription->renews_at,
                    'days_before_expiry' => (int) $days,
                    'status' => $subscription->status,
                ];
            })
            ->filter()
            ->values();
    }

    public function currentCentro(): ?Centros_Medico
    {
        if (! tenancy()->initialized || ! tenancy()->tenant?->centro_id) {
            return null;
        }

        return Centros_Medico::on('mysql')
            ->find(tenancy()->tenant->centro_id);
    }

    protected function subscriptionForModule(int $centroId, string $moduleCode): ?BillingModuleSubscription
    {
        if (! $this->moduleTablesAvailable()) {
            return null;
        }

        $module = BillingModule::query()
            ->where('code', $moduleCode)
            ->first();

        if (! $module) {
            return null;
        }

        return BillingModuleSubscription::query()
            ->where('centro_id', $centroId)
            ->where('billing_module_id', $module->id)
            ->first();
    }

    protected function isRootUser(): bool
    {
        return (bool) auth()->user()?->hasRole('root');
    }

    public function moduleTablesAvailable(): bool
    {
        if ($this->hasModuleTables !== null) {
            return $this->hasModuleTables;
        }

        return $this->hasModuleTables = Schema::connection('mysql')->hasTable('billing_modules')
            && Schema::connection('mysql')->hasTable('billing_module_subscriptions');
    }
}
