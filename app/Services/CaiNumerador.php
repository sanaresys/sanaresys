<?php

namespace App\Services;

use App\Models\CAIAutorizaciones;
use App\Models\CAI_Correlativos;
use App\Models\Factura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CaiNumerador
{
    /**
     * Genera un nuevo correlativo CAI para una factura
     */
    public static function generar(int $caiId, int $usuarioId, int $centroId, ?int $facturaId = null): CAI_Correlativos
    {
        return DB::transaction(function () use ($caiId, $usuarioId, $centroId, $facturaId) {
            // Obtener CAI con bloqueo para evitar concurrencia
            $cai = CAIAutorizaciones::where('id', $caiId)
                ->where('estado', 'ACTIVA')
                ->lockForUpdate()
                ->first();

            if (!$cai) {
                throw new \Exception('CAI no disponible o inactivo');
            }

            // Verificar que no esté vencido
            if ($cai->fecha_limite < now()->toDateString()) {
                $cai->update(['estado' => 'VENCIDA']);
                throw new \Exception('CAI vencido desde el ' . $cai->fecha_limite->format('d/m/Y'));
            }

            // Inicializar numero_actual si es null
            if (is_null($cai->numero_actual)) {
                $cai->numero_actual = $cai->rango_inicial;
                $cai->save();
            }

            // Verificar que no esté agotado DESPUÉS de inicializar
            if ($cai->numero_actual > $cai->rango_final) {
                $cai->update(['estado' => 'AGOTADA']);
                throw new \Exception('CAI agotado - no quedan números disponibles');
            }

            // Obtener el siguiente número
            $numeroCorrelativo = $cai->numero_actual;
            
            // Validación adicional de seguridad
            if (is_null($numeroCorrelativo)) {
                throw new \Exception('Error interno: número correlativo no inicializado');
            }
            
            // Generar el número de factura formateado
            $numeroFactura = self::formatearNumeroFactura($cai->rtn, $numeroCorrelativo);

            // Crear el correlativo
            $correlativo = CAI_Correlativos::create([
                'autorizacion_id' => $cai->id,
                'numero_correlativo' => $numeroCorrelativo,
                'numero_factura' => $numeroFactura,
                'fecha_emision' => now(),
                'factura_id' => $facturaId,
                'usuario_id' => $usuarioId,
                'centro_id' => $centroId,
                'created_by' => $usuarioId,
            ]);

            // Incrementar el número actual del CAI
            $cai->increment('numero_actual');

            // Verificar si se agotó después del incremento
            if ($cai->numero_actual > $cai->rango_final) {
                $cai->update(['estado' => 'AGOTADA']);
                Log::info("CAI agotado: {$cai->cai_codigo}");
            }

            return $correlativo;
        });
    }

    /**
     * Genera correlativo automáticamente para una factura
     */
    public static function generarParaFactura(Factura $factura): ?CAI_Correlativos
    {
        $cai = self::obtenerCAIDisponible($factura->centro_id);
        
        if (!$cai) {
            Log::warning("No hay CAI disponible para centro {$factura->centro_id}");
            return null;
        }

        try {
            return self::generar(
                $cai->id, 
                $factura->created_by ?? Auth::id() ?? 1,
                $factura->centro_id,
                $factura->id
            );
        } catch (\Exception $e) {
            Log::error("Error generando CAI para factura {$factura->id}: " . $e->getMessage());
            return null;
        }
    }

    private static function formatearNumeroFactura(string $rtn, int $numeroCorrelativo): string
    {
        // Formato: 001-001-01-00000001
        // Formato estándar para Honduras SAR
        $establecimiento = '001';  // Código de establecimiento 
        $puntoEmision = '001';     // Punto de emisión
        $tipoDocumento = '01';     // Tipo de documento (01 = Factura)
        
        // La cuarta parte es el correlativo que incrementa (8 dígitos)
        $correlativo = str_pad($numeroCorrelativo, 8, '0', STR_PAD_LEFT);
        
        return "{$establecimiento}-{$puntoEmision}-{$tipoDocumento}-{$correlativo}";
    }

    /**
     * Obtener CAI disponible para un centro
     */
    public static function obtenerCAIDisponible($centroId)
    {
        $cai = CAIAutorizaciones::where('centro_id', $centroId)
            ->where('fecha_limite', '>', now()) // No vencido
            ->where(function($query) {
                $query->where('estado', 'ACTIVA')
                    ->orWhere(function($subQuery) {
                        // Re-evaluar CAIs marcados como agotados por error
                        $subQuery->where('estado', 'AGOTADA')
                                ->whereRaw('numero_actual < rango_final');
                    });
            })
            ->orderBy('fecha_limite', 'asc')
            ->first();
        
        // Si encontramos un CAI que estaba marcado como agotado incorrectamente
        if ($cai && $cai->estado === 'AGOTADA' && $cai->numero_actual < $cai->rango_final) {
            $cai->update(['estado' => 'ACTIVA']);
            
            Log::info('🔧 CAI reactivado automáticamente', [
                'cai_id' => $cai->id,
                'numero_actual' => $cai->numero_actual,
                'rango_final' => $cai->rango_final
            ]);
        }
        
        return $cai;
    }

    /**
     * Asignar número de factura con CAI
     */
    public function asignarNumeroFactura($centroId, $facturaId)
    {
        try {
            Log::info('🏷️ Iniciando asignación de número CAI', [
                'centro_id' => $centroId,
                'factura_id' => $facturaId
            ]);

            // ✅ VERIFICAR SI YA EXISTE UN CORRELATIVO PARA ESTA FACTURA
            $existeCorrelativo = CAI_Correlativos::where('factura_id', $facturaId)->first();
            if ($existeCorrelativo) {
                Log::warning('⚠️ Ya existe correlativo para esta factura', [
                    'factura_id' => $facturaId,
                    'correlativo_id' => $existeCorrelativo->id,
                    'numero_factura' => $existeCorrelativo->numero_factura
                ]);
                return $existeCorrelativo;
            }

            // Buscar CAI disponible
            $cai = self::obtenerCAIDisponible($centroId);
            
            if (!$cai) {
                Log::warning('⚠️ No hay CAI disponible', [
                    'centro_id' => $centroId
                ]);
                return null;
            }

            // Usar transacción para evitar problemas de concurrencia
            return DB::transaction(function () use ($cai, $facturaId, $centroId) {
                // Bloquear el registro CAI para evitar duplicados
                $caiLocked = CAIAutorizaciones::where('id', $cai->id)
                    ->lockForUpdate()
                    ->first();

                if (!$caiLocked) {
                    throw new \Exception('No se pudo bloquear el CAI');
                }

                // ✅ MEJORADO: Verificar disponibilidad real
                if ($caiLocked->numero_actual >= $caiLocked->rango_final) {
                    // Marcar como agotada
                    $caiLocked->update(['estado' => 'AGOTADA']);
                    throw new \Exception('CAI agotado');
                }

                // ✅ MEJORADO: Calcular próximo número disponible
                $proximoNumero = $caiLocked->numero_actual + 1;
                
                // ✅ VERIFICAR QUE NO EXCEDA EL RANGO
                if ($proximoNumero > $caiLocked->rango_final) {
                    $caiLocked->update(['estado' => 'AGOTADA']);
                    throw new \Exception('CAI agotado - número excede rango final');
                }
                
                // Generar número de factura formateado
                $numeroFactura = $this->generarNumeroFactura($caiLocked, $proximoNumero);

                // Crear el correlativo
                $correlativo = CAI_Correlativos::create([
                    'autorizacion_id' => $caiLocked->id,
                    'factura_id' => $facturaId,
                    'numero_correlativo' => $proximoNumero,
                    'numero_factura' => $numeroFactura,
                    'fecha_emision' => now(),
                    'usuario_id' => auth()->id(),
                    'centro_id' => $centroId,
                ]);

                // Actualizar el número actual en la autorización
                $caiLocked->update([
                    'numero_actual' => $proximoNumero
                ]);

                // Verificar si se agotó
                if ($proximoNumero >= $caiLocked->rango_final) {
                    $caiLocked->update(['estado' => 'AGOTADA']);
                    Log::info('🏁 CAI agotado', [
                        'cai_id' => $caiLocked->id,
                        'ultimo_numero' => $proximoNumero
                    ]);
                }

                Log::info('✅ Número CAI asignado exitosamente', [
                    'correlativo_id' => $correlativo->id,
                    'numero_factura' => $numeroFactura,
                    'numero_correlativo' => $proximoNumero,
                    'numeros_disponibles' => $caiLocked->rango_final - $proximoNumero
                ]);

                return $correlativo;
            });

        } catch (\Exception $e) {
            Log::error('❌ Error al asignar número CAI', [
                'centro_id' => $centroId,
                'factura_id' => $facturaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Generar número de factura formateado
     */
    private function generarNumeroFactura(CAIAutorizaciones $cai, int $numeroCorrelativo): string
    {
        // Formato típico: 001-001-01-00000001
        $establecimiento = str_pad(1, 3, '0', STR_PAD_LEFT);
        $puntoEmision = str_pad(1, 3, '0', STR_PAD_LEFT);
        $tipoDocumento = '01';
        $correlativo = str_pad($numeroCorrelativo, 8, '0', STR_PAD_LEFT);
        
        return "{$establecimiento}-{$puntoEmision}-{$tipoDocumento}-{$correlativo}";
    }

    /**
     * Verificar si un CAI está por vencerse
     */
    public static function verificarVencimiento($caiId): array
    {
        $cai = CAIAutorizaciones::find($caiId);
        
        if (!$cai) {
            return ['status' => 'not_found'];
        }

        $diasRestantes = now()->diffInDays($cai->fecha_limite, false);
        $numerosRestantes = $cai->rango_final - $cai->numero_actual;
        
        return [
            'status' => 'active',
            'dias_restantes' => $diasRestantes,
            'numeros_restantes' => $numerosRestantes,
            'porcentaje_utilizado' => $cai->porcentajeUtilizado(),
            'requiere_atencion' => $diasRestantes <= 30 || $numerosRestantes <= 100
        ];
    }

    /**
     * Obtener estadísticas de uso de CAI
     */
    public static function obtenerEstadisticas($centroId): array
    {
        $cais = CAIAutorizaciones::where('centro_id', $centroId)->get();
        
        $estadisticas = [
            'total_cais' => $cais->count(),
            'activos' => $cais->where('estado', 'ACTIVA')->count(),
            'vencidos' => $cais->where('estado', 'VENCIDA')->count(),
            'agotados' => $cais->where('estado', 'AGOTADA')->count(),
            'por_vencer' => 0,
            'facturas_emitidas' => 0
        ];

        foreach ($cais as $cai) {
            // Contar CAIs por vencer (próximos 30 días)
            if ($cai->estado === 'ACTIVA' && $cai->fecha_limite <= now()->addDays(30)) {
                $estadisticas['por_vencer']++;
            }
            
            // Contar facturas emitidas
            $estadisticas['facturas_emitidas'] += $cai->numero_actual - $cai->rango_inicial;
        }

        return $estadisticas;
    }
}