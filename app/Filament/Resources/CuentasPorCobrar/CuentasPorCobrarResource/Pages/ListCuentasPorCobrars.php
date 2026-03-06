<?php

namespace App\Filament\Resources\CuentasPorCobrar\CuentasPorCobrarResource\Pages;

use App\Filament\Resources\CuentasPorCobrar\CuentasPorCobrarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCuentasPorCobrars extends ListRecords
{
    protected static string $resource = CuentasPorCobrarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pagar_cuenta')
                ->label('Procesar Pago')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->url(static::getResource()::getUrl('pagar'))
                ->tooltip('Buscar factura y procesar pago'),
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\CuentasPorCobrarStatsWidget::class,
        ];
    }
}
