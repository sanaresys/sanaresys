<?php

namespace App\Services\Billing;

use App\Models\BillingModule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BillingModuleCatalogService
{
    /**
     * @return Collection<int, BillingModule>
     */
    public function activeModules(): Collection
    {
        if (! $this->catalogTableAvailable()) {
            return new Collection();
        }

        return BillingModule::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    public function getByCodeOrFail(string $code): BillingModule
    {
        if (! $this->catalogTableAvailable()) {
            throw ValidationException::withMessages([
                'module_code' => 'Facturacion modular no disponible. Ejecuta migraciones pendientes.',
            ]);
        }

        $module = BillingModule::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (! $module) {
            throw ValidationException::withMessages([
                'module_code' => 'El modulo seleccionado no esta disponible.',
            ]);
        }

        return $module;
    }

    protected function catalogTableAvailable(): bool
    {
        return Schema::connection('mysql')->hasTable('billing_modules');
    }
}
