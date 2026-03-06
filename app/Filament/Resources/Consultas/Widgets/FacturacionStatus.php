<?php

namespace App\Filament\Resources\Consultas\Widgets;

use Filament\Widgets\Widget;

class FacturacionStatus extends Widget
{
    protected static string $view = 'filament.resources.consultas.widgets.facturacion-status';

    public $record;

    protected function getViewData(): array
    {
        $tieneFactura = $this->record->facturas()->exists();
        $factura = $tieneFactura ? $this->record->facturas()->first() : null;
        
        return [
            'consulta' => $this->record,
            'factura' => $factura,
            'tieneFactura' => $tieneFactura,
        ];
    }
}