<?php

namespace App\Filament\Resources\Facturas\FacturasResource\Pages;

use App\Filament\Resources\Facturas\FacturasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class EditFacturas extends EditRecord
{
    protected static string $resource = FacturasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (Model $record) => $record->estado !== 'PAGADA'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // En modo edición este método no se ejecuta
        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar los pagos existentes para mostrarlos en el formulario
        $factura = $this->record;
        
        if ($factura) {
            // Cargar pagos desde el modelo pagos_factura
            $pagosExistentes = \App\Models\PagosFactura::where('factura_id', $factura->id)
                ->with('tipoPago')
                ->get();
            
            if ($pagosExistentes->count() > 0) {
                $data['pagos'] = $pagosExistentes->map(function ($pago) {
                    return [
                        'id' => $pago->id,
                        'tipo_pago_id' => $pago->tipo_pago_id,
                        'monto_recibido' => $pago->monto_recibido,
                        'existia_previamente' => true // Flag para identificar pagos existentes
                    ];
                })->toArray();
                
                $data['total_pagado'] = $pagosExistentes->sum('monto_recibido');
                $data['cambio'] = $pagosExistentes->sum('monto_devolucion') ?? 0;
            }
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // No permitir edición de facturas completamente pagadas
        if ($this->record->estado === 'PAGADA') {
            Notification::make()
                ->warning()
                ->title('No se puede editar')
                ->body('Las facturas completamente pagadas no pueden ser modificadas.')
                ->send();
                
            // Devolver los datos originales sin cambios
            return $this->record->toArray();
        }
        
        // Para facturas parcialmente pagadas o pendientes, preservar los pagos existentes
    $pagosExistentes = \App\Models\PagosFactura::where('factura_id', $this->record->id)->get();
        
        if ($pagosExistentes->count() > 0) {
            // Preservar pagos existentes en el formulario
            $pagosPreservados = $pagosExistentes->map(function ($pago) {
                return [
                    'id' => $pago->id,
                    'tipo_pago_id' => $pago->tipo_pago_id,
                    'monto_recibido' => $pago->monto_recibido,
                    'existia_previamente' => true
                ];
            })->toArray();
            
            // Si hay nuevos pagos en el formulario, agregarlos
            if (isset($data['pagos']) && is_array($data['pagos'])) {
                $pagosNuevos = collect($data['pagos'])
                    ->filter(function($pago) {
                        return !isset($pago['existia_previamente']) || !$pago['existia_previamente'];
                    })
                    ->values()
                    ->toArray();
                
                $data['pagos'] = array_merge($pagosPreservados, $pagosNuevos);
            } else {
                $data['pagos'] = $pagosPreservados;
            }
        }
        
        return $data;
    }

    protected function beforeSave(): void
    {
        // Verificar que no se está intentando editar una factura pagada
        if ($this->record->estado === 'PAGADA') {
            Notification::make()
                ->warning()
                ->title('Edición no permitida')
                ->body('No se pueden modificar facturas que ya están completamente pagadas.')
                ->send();
                
            $this->halt();
        }
    }

    protected function afterSave(): void
    {
        // Obtener datos del formulario
        $formData = $this->form->getState();
        
        // Solo procesar pagos si existen en el formulario y la factura no está PAGADA
        if (isset($formData['pagos']) && is_array($formData['pagos']) && $this->record->estado !== 'PAGADA') {
            
            foreach ($formData['pagos'] as $pago) {
                if (!empty($pago['tipo_pago_id']) && !empty($pago['monto_recibido'])) {
                    
                    // Si el pago tiene ID, verificar si existe y actualizar
                    if (isset($pago['id'])) {
                        $pagoExistente = \App\Models\PagosFactura::find($pago['id']);
                        if ($pagoExistente && $pagoExistente->factura_id == $this->record->id) {
                            // Solo actualizar si no está marcado como preservado
                            if (!isset($pago['existia_previamente']) || !$pago['existia_previamente']) {
                                $pagoExistente->update([
                                    'tipo_pago_id' => $pago['tipo_pago_id'],
                                    'monto_recibido' => $pago['monto_recibido'],
                                ]);
                            }
                        }
                    } else {
                        // Es un pago nuevo, crear
                        \App\Models\PagosFactura::create([
                            'factura_id' => $this->record->id,
                            'paciente_id' => $this->record->paciente_id,
                            'centro_id' => $this->record->centro_id,
                            'tipo_pago_id' => $pago['tipo_pago_id'],
                            'monto_recibido' => $pago['monto_recibido'],
                            'monto_devolucion' => 0, // Campo requerido
                            'fecha_pago' => now(),
                            'created_by' => \Illuminate\Support\Facades\Auth::id(),
                        ]);
                    }
                }
            }
        }
        
        // Actualizar estado de pago de la factura
        $this->record->actualizarEstadoPago();
        
        // Notificar al usuario sobre la preservación de datos de pago
        if ($this->record->pagos && $this->record->pagos->count() > 0) {
            Notification::make()
                ->success()
                ->title('Datos de pago preservados')
                ->body('Los datos de pago existentes se han mantenido correctamente.')
                ->send();
        }
    }
}
