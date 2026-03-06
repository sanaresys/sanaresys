<?php

namespace App\Filament\Resources\Facturas\FacturasResource\Pages;

use App\Filament\Resources\Facturas\FacturasResource;
use App\Filament\Resources\Consultas\ConsultasResource;
use App\Models\Consulta;
use App\Models\FacturaDetalle;
use App\Models\Descuento;
use App\Models\PagosFactura;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CreateFacturas extends CreateRecord
{
    protected static string $resource = FacturasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('regresar')
                ->label('Regresar a Servicios')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(function () {
                    $consultaId = request()->get('consulta_id');
                    if ($consultaId) {
                        return ConsultasResource::getUrl('manage-servicios', ['record' => $consultaId]);
                    }
                    return ConsultasResource::getUrl('index');
                })
                ->button(),
        ];
    }

    public function mount(): void
    {
        parent::mount();
        
        $consultaId = request()->get('consulta_id');
        
        if ($consultaId) {
            $consulta = Consulta::with(['paciente', 'medico', 'servicios'])->find($consultaId);
            
            if ($consulta) {
                // Obtener totales actualizados desde la página de servicios si están disponibles
                $serviciosActualizados = FacturaDetalle::where('consulta_id', $consultaId)
                    ->whereNull('factura_id')
                    ->get();
                
                if ($serviciosActualizados->isNotEmpty()) {
                    // Usar totales de servicios actualizados
                    $subtotal = $serviciosActualizados->sum('subtotal');
                    $impuestoTotal = $serviciosActualizados->sum('impuesto_monto');
                    
                    Log::info('📊 Usando servicios actualizados de FacturaDetalle', [
                        'cantidad_servicios' => $serviciosActualizados->count(),
                        'subtotal_calculado' => $subtotal,
                        'impuesto_calculado' => $impuestoTotal,
                    ]);
                } else {
                    // Usar totales de servicios originales de la consulta
                    $subtotal = $consulta->servicios->sum('precio');
                    $impuestoTotal = $subtotal * 0.15;
                    
                    Log::info('📊 Usando servicios originales de Consulta', [
                        'cantidad_servicios' => $consulta->servicios->count(),
                        'subtotal_calculado' => $subtotal,
                        'impuesto_calculado' => $impuestoTotal,
                    ]);
                }
                
                // Obtener descuento si viene en la URL
                $descuentoTotal = (float) (request()->get('descuento_total') ?? 0);
                
                // Obtener usa_cai desde URL
                $usaCai = request()->get('usa_cai') === '1' || request()->get('usa_cai') === 'true';
                
                // Calcular total final
                $total = $subtotal + $impuestoTotal - $descuentoTotal;
                
                // Pre-llenar el formulario
                $this->form->fill([
                    'consulta_id' => $consultaId,
                    'paciente_id' => $consulta->paciente_id,
                    'medico_id' => $consulta->medico_id,
                    'cita_id' => $consulta->cita_id,
                    'subtotal' => $subtotal,
                    'descuento_total' => $descuentoTotal,
                    'impuesto_total' => $impuestoTotal,
                    'total' => $total,
                    'saldo_pendiente' => $total,
                    'fecha_emision' => now()->format('Y-m-d'),
                    'usa_cai' => $usaCai,
                ]);
                
                Log::info('✅ Formulario pre-llenado', [
                    'usa_cai' => $usaCai,
                    'usa_cai_url' => request()->get('usa_cai'),
                    'total' => $total
                ]);
                
                // Restaurar datos de pagos desde sessionStorage si existen
                $this->restorePaymentData($consultaId);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('📥 Estado completo del formulario recibido:', $data);

        Log::info('🚀 mutateFormDataBeforeCreate ejecutado', ['data' => $data]);
        
        // Obtener consulta desde URL si no está en los datos
        if (empty($data['consulta_id'])) {
            $data['consulta_id'] = request()->get('consulta_id');
        }

        // Asegurar que usa_cai se preserve desde URL si no está en data
        if (!isset($data['usa_cai'])) {
            $usaCaiUrl = request()->get('usa_cai');
            $data['usa_cai'] = $usaCaiUrl === '1' || $usaCaiUrl === 'true';
        }

        // Obtener información de la consulta
        if (!empty($data['consulta_id'])) {
            $consulta = Consulta::with(['paciente', 'medico', 'servicios'])->find($data['consulta_id']);
            
            if ($consulta) {
                $data['paciente_id'] = $consulta->paciente_id;
                $data['medico_id'] = $consulta->medico_id;
                $data['cita_id'] = $consulta->cita_id;
                
                // Obtener totales desde FacturaDetalle (servicios actualizados)
                $serviciosActualizados = FacturaDetalle::where('consulta_id', $data['consulta_id'])
                    ->whereNull('factura_id')
                    ->get();
                
                if ($serviciosActualizados->isNotEmpty()) {
                    // Usar servicios actualizados de FacturaDetalle
                    $subtotal = $serviciosActualizados->sum('subtotal');
                    $impuestoTotalServicio = $serviciosActualizados->sum('impuesto_monto');
                } else {
                    // Fallback a servicios originales si no hay actualizados
                    $subtotal = $consulta->servicios->sum('precio');
                    $impuestoTotalServicio = $subtotal * 0.15;
                }
                
                // Calcular descuento si aplica
                $descuentoTotal = 0;
                if (!empty($data['descuento_id'])) {
                    $descuento = Descuento::find($data['descuento_id']);
                    if ($descuento) {
                        if ($descuento->tipo === 'PORCENTAJE') {
                            $descuentoTotal = ($subtotal * $descuento->valor) / 100;
                        } else {
                            $descuentoTotal = $descuento->valor;
                        }
                    }
                } elseif (!empty($data['descuento_total'])) {
                    // Si viene el descuento total desde URL
                    $descuentoTotal = (float) $data['descuento_total'];
                }
                
                // Calcular total final
                $total = $subtotal + $impuestoTotalServicio - $descuentoTotal;
                
                $data['subtotal'] = $subtotal;
                $data['descuento_total'] = $descuentoTotal;
                $data['impuesto_total'] = $impuestoTotalServicio;
                $data['total'] = $total;
                $data['fecha_emision'] = now();
                $data['usuario_id'] = Auth::id();
                $data['created_by'] = Auth::id();
                // Multi-tenant: centro_id no es necesario
                $data['estado'] = $data['estado'] ?? 'PENDIENTE';
                
                // Asegurar usa_cai con múltiples fuentes
                if (!isset($data['usa_cai'])) {
                    $usaCaiUrl = request()->get('usa_cai');
                    $data['usa_cai'] = $usaCaiUrl === '1' || $usaCaiUrl === 'true';
                }
                
                // Agregar campos opcionales si no están presentes
                if (!isset($data['observaciones'])) {
                    $data['observaciones'] = null;
                }
                
                Log::info('✅ Datos calculados en mutateFormDataBeforeCreate', [
                    'subtotal' => $subtotal,
                    'descuento_total' => $descuentoTotal,
                    'impuesto_total' => $impuestoTotalServicio,
                    'total' => $total,
                    'usa_cai' => $data['usa_cai'],
                    'usa_cai_url' => request()->get('usa_cai'),
                    'servicios_count' => $serviciosActualizados->count(),
                ]);

                // Limpiar pagos: solo incluir los válidos (monto > 0 y tipo de pago presente)
                if (!empty($data['pagos']) && is_array($data['pagos'])) {
                    $data['pagos'] = collect($data['pagos'])->filter(function ($pago) {
                        $monto = isset($pago['monto_recibido']) ? floatval(str_replace(',', '', $pago['monto_recibido'])) : 0;
                        return !empty($pago['tipo_pago_id']) && $monto > 0;
                    })->values()->toArray();
                }

            }
        }

        return $data;
    }
    
    protected function afterCreate(): void
    {
        Log::info('📥 Pagos recibidos en afterCreate()', [
            'formState' => $this->form->getState()
        ]);

        // ============= SECCIÓN CRÍTICA: PROCESAMIENTO DE PAGOS =============
        
        // Obtener los datos del formulario
        $formData = $this->form->getState();
        $pagos = $formData['pagos'] ?? [];
        
        Log::info('💰 INICIANDO PROCESAMIENTO DE PAGOS', [
            'factura_id' => $this->record->id,
            'total_factura' => $this->record->total,
            'cantidad_pagos_recibidos' => count($pagos),
            'pagos_raw' => $pagos
        ]);

        // Variables para tracking
        $pagosCreados = [];
        $pagosFiltrados = [];
        $totalPagado = 0;

        // ===== FILTRAR Y VALIDAR PAGOS =====
        foreach ($pagos as $index => $pagoData) {
            // Convertir el monto a float para validación consistente
            $montoRecibido = null;
            
            // Manejar diferentes formatos de entrada
            if (isset($pagoData['monto_recibido'])) {
                $montoRecibido = $pagoData['monto_recibido'];
                
                // Limpiar el valor (remover espacios, comas si las hay)
                if (is_string($montoRecibido)) {
                    $montoRecibido = str_replace(',', '', trim($montoRecibido));
                }
                
                // Convertir a float
                $montoRecibido = (float) $montoRecibido;
            }
            
            Log::info("📝 Evaluando pago #{$index}", [
                'tipo_pago_id' => $pagoData['tipo_pago_id'] ?? null,
                'monto_original' => $pagoData['monto_recibido'] ?? null,
                'monto_procesado' => $montoRecibido,
                'es_valido' => ($montoRecibido > 0)
            ]);
            
            // SOLO procesar si el monto es mayor a 0 Y tiene tipo de pago
            if ($montoRecibido > 0 && !empty($pagoData['tipo_pago_id'])) {
                $pagosFiltrados[] = [
                    'tipo_pago_id' => $pagoData['tipo_pago_id'],
                    'monto_recibido' => $montoRecibido
                ];
                
                Log::info("✅ Pago #{$index} ACEPTADO para procesamiento", [
                    'monto' => $montoRecibido
                ]);
            } else {
                Log::info("⏭️ Pago #{$index} OMITIDO", [
                    'razon' => $montoRecibido <= 0 ? 'Monto inválido o cero' : 'Sin tipo de pago'
                ]);
            }
        }

        Log::info('🎯 RESUMEN DE FILTRADO', [
            'pagos_originales' => count($pagos),
            'pagos_validos' => count($pagosFiltrados),
            'pagos_filtrados' => $pagosFiltrados
        ]);

        // ===== CREAR PAGOS VÁLIDOS EN LA BASE DE DATOS =====
        DB::beginTransaction();
        
        try {
            foreach ($pagosFiltrados as $pagoValido) {
                try {
                    $nuevoPago = PagosFactura::create([
                        'factura_id' => $this->record->id,
                        'tipo_pago_id' => $pagoValido['tipo_pago_id'],
                        'monto_recibido' => $pagoValido['monto_recibido'],
                        'paciente_id' => $this->record->paciente_id,
                        'centro_id' => $this->record->centro_id,
                        'fecha_pago' => now(),
                        'created_by' => Auth::id(),
                        'monto_devolucion' => 0,
                    ]);
                    
                    $pagosCreados[] = $nuevoPago;
                    $totalPagado += $pagoValido['monto_recibido'];
                    
                    Log::info('✅ PAGO CREADO EXITOSAMENTE', [
                        'pago_id' => $nuevoPago->id,
                        'factura_id' => $this->record->id,
                        'tipo_pago_id' => $pagoValido['tipo_pago_id'],
                        'monto' => $pagoValido['monto_recibido']
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('❌ ERROR AL CREAR PAGO INDIVIDUAL', [
                        'factura_id' => $this->record->id,
                        'error' => $e->getMessage(),
                        'data' => $pagoValido
                    ]);
                    throw $e; // Re-lanzar para hacer rollback
                }
            }
            
            DB::commit();
            
            Log::info('💰 TODOS LOS PAGOS CREADOS EXITOSAMENTE', [
                'factura_id' => $this->record->id,
                'cantidad_pagos_creados' => count($pagosCreados),
                'total_pagado' => $totalPagado,
                'ids_pagos' => collect($pagosCreados)->pluck('id')->toArray()
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('❌ ERROR CRÍTICO - ROLLBACK DE PAGOS', [
                'factura_id' => $this->record->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        // ===== ACTUALIZAR ESTADO DE LA FACTURA =====
        if ($totalPagado > 0) {
            $totalFactura = $this->record->total;
            $nuevoEstado = 'PENDIENTE';
            
            if ($totalPagado >= $totalFactura) {
                $nuevoEstado = 'PAGADA';
            } elseif ($totalPagado > 0) {
                $nuevoEstado = 'PARCIAL';
            }
            
            $this->record->update([
                'estado' => $nuevoEstado,
                'saldo_pendiente' => max(0, $totalFactura - $totalPagado)
            ]);
            
            Log::info('📊 ESTADO DE FACTURA ACTUALIZADO', [
                'factura_id' => $this->record->id,
                'estado' => $nuevoEstado,
                'total_factura' => $totalFactura,
                'total_pagado' => $totalPagado,
                'saldo_pendiente' => max(0, $totalFactura - $totalPagado)
            ]);
        } else {
            Log::info('📄 FACTURA SIN PAGOS', [
                'factura_id' => $this->record->id,
                'estado' => 'PENDIENTE'
            ]);
        }

        // ============= FIN SECCIÓN DE PAGOS =============

        // Procesar detalles de factura
        $record = $this->record;
        $consultaId = $record->consulta_id;

        if ($consultaId) {
            // Actualizar los FacturaDetalle con el ID de la factura creada
            $detallesActualizados = FacturaDetalle::where('consulta_id', $consultaId)
                ->whereNull('factura_id')
                ->update(['factura_id' => $record->id]);
                
            Log::info('📋 Detalles de factura actualizados', [
                'factura_id' => $record->id,
                'detalles_actualizados' => $detallesActualizados
            ]);

            // Procesar CAI si es necesario
            if ($record->usa_cai && !$record->cai_correlativo_id) {
                Log::info('🏷️ Procesando CAI para factura', ['factura_id' => $record->id]);
                
                try {
                    // Obtener el servicio CAI
                    $caiService = app(\App\Services\CaiNumerador::class);
                    
                    // Asignar número de CAI a la factura
                    $correlativo = $caiService->asignarNumeroFactura($record->centro_id, $record->id);
                    
                    if ($correlativo) {
                        // Actualizar la factura con la información del CAI
                        $record->update([
                            'cai_correlativo_id' => $correlativo->id,
                            'codigo_cai' => $correlativo->autorizacion->cai_codigo,
                            'numero_factura_cai' => $correlativo->numero_factura,
                        ]);
                        
                        Log::info('✅ CAI asignado exitosamente', [
                            'factura_id' => $record->id,
                            'correlativo_id' => $correlativo->id,
                            'numero_factura' => $correlativo->numero_factura
                        ]);
                    } else {
                        Log::warning('⚠️ No se pudo asignar CAI - sin autorizaciones disponibles', [
                            'factura_id' => $record->id,
                            'centro_id' => $record->centro_id
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('❌ Error al procesar CAI', [
                        'factura_id' => $record->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                Log::info('📄 Factura creada sin CAI o CAI ya asignado', [
                    'factura_id' => $record->id,
                    'usa_cai' => $record->usa_cai,
                    'cai_correlativo_id' => $record->cai_correlativo_id
                ]);
            }
            
            // Limpiar datos de borrador después de crear exitosamente
            $this->clearDraftData($consultaId);
        }
        
        // Log final del proceso
        Log::info('🎉 PROCESO afterCreate COMPLETADO', [
            'factura_id' => $this->record->id,
            'pagos_creados' => count($pagosCreados),
            'total_pagado' => $totalPagado,
            'estado_final' => $this->record->estado
        ]);
    }

    
    protected function beforeCreate(): void
    {
        Log::info('🔍 beforeCreate ejecutado', ['data_keys' => array_keys($this->data)]);
        
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            Log::info('💾 handleRecordCreation iniciado', ['data' => $data]);
            $record = parent::handleRecordCreation($data);
            Log::info('✅ Factura creada exitosamente', ['factura_id' => $record->id]);
            return $record;
        } catch (\Exception $e) {
            Log::error('❌ Error al crear factura', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Restaurar datos de pagos desde sessionStorage
     */
    private function restorePaymentData($consultaId)
    {
        // Los datos se restaurarán mediante JavaScript en el frontend
        // Este método puede ser extendido para manejar datos desde el servidor si es necesario
        Log::info('🔄 Preparando restauración de datos de pago para consulta', ['consulta_id' => $consultaId]);
    }

    /**
     * Limpiar datos de borrador después de crear la factura exitosamente
     */
    private function clearDraftData($consultaId)
    {
        Log::info('🗑️ Limpiando datos de borrador para consulta', ['consulta_id' => $consultaId]);
        
        // JavaScript se encargará de limpiar localStorage y sessionStorage
        // Este método registra que la factura fue creada exitosamente
        
        // Inyectar script para limpiar datos del cliente
        $this->js("
            if (typeof window.formPersistence !== 'undefined') {
                const persistence = window.formPersistence();
                persistence.consultaId = '$consultaId';
                persistence.storageKey = 'factura_pagos_v2_$consultaId';
                persistence.sessionKey = 'session_factura_pagos_v2_$consultaId';
                persistence.clearStoredData();
            } else {
                // Fallback directo
                localStorage.removeItem('factura_pagos_v2_$consultaId');
                sessionStorage.removeItem('session_factura_pagos_v2_$consultaId');
                console.log('🗑️ Datos de borrador eliminados (fallback)');
            }
        ");
    }
}