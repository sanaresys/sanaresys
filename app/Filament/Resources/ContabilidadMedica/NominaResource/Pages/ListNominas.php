<?php

namespace App\Filament\Resources\ContabilidadMedica\NominaResource\Pages;

use App\Filament\Resources\ContabilidadMedica\NominaResource;
use App\Services\Billing\TenantModuleAccessService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNominas extends ListRecords
{
    protected static string $resource = NominaResource::class;

    protected function getHeaderActions(): array
    {
        $moduleActive = app(TenantModuleAccessService::class)->isModuleActive('nomina');

        $actions = [];

        if (! $moduleActive && tenancy()->initialized) {
            $actions[] = Actions\Action::make('activar_modulo_nomina')
                ->label('Activar modulo')
                ->color('warning')
                ->url(route('tenant.billing.modules.index'));
        }

        if ($moduleActive) {
            $actions[] = Actions\CreateAction::make()
                ->label('Nueva nomina')
                ->icon('heroicon-o-plus');
        }

        return $actions;
    }
}

