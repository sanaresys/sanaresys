<?php

namespace App\Filament\Resources\PagosFacturas\PagosFacturasResource\Pages;

use App\Filament\Resources\PagosFacturas\PagosFacturasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Services\FacturaPagoService;
use App\Models\Factura;
use Filament\Notifications\Notification;

class CreatePagosFacturas extends CreateRecord
{
    protected static string $resource = PagosFacturasResource::class;

    protected array $facturaPagoData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Solo procesamos con el servicio, no creamos el registro aquí
        $this->facturaPagoData = $data;
        
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Usar el servicio para registrar el pago en lugar del proceso normal
        $factura = Factura::findOrFail($data['factura_id']);
        
        $pago = FacturaPagoService::registrarPago(
            factura: $factura,
            montoRecibido: $data['monto_recibido'],
            tipoPagoId: $data['tipo_pago_id'],
            usuarioId: auth()->id(),
        );

        return $pago;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Pago registrado correctamente')
            ->body('El estado de la factura ha sido actualizado.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}