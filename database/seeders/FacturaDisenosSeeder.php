<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FacturaDiseno;
use App\Models\Centros_Medico;

class FacturaDisenosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los centros médicos
        $centros = Centros_Medico::all();

        foreach ($centros as $centro) {
            // Verificar si ya tiene un diseño predeterminado
            $disenoExistente = FacturaDiseno::where('centro_id', $centro->id)
                ->where('es_predeterminado', true)
                ->first();

            if (!$disenoExistente) {
                FacturaDiseno::create([
                    'nombre' => 'Diseño Clásico - ' . $centro->nombre,
                    'descripcion' => 'Diseño predeterminado para ' . $centro->nombre,
                    'es_predeterminado' => true,
                    'activo' => true,
                    'template_archivo' => 'factura_basica',
                    'orientacion_papel' => 'portrait',
                    'tamaño_papel' => 'A4',
                    
                    // Colores institucionales
                    'color_primario' => '#1e40af',
                    'color_secundario' => '#64748b',
                    'color_acento' => '#059669',
                    'color_texto' => '#1f2937',
                    
                    // Tipografía
                    'fuente_titulo' => 'Arial Black',
                    'fuente_texto' => 'Arial',
                    'tamaño_titulo' => 18,
                    'tamaño_texto' => 12,
                    'tamaño_subtitulo' => 14,
                    
                    // Espaciado
                    'margenes' => ['top' => 20, 'right' => 15, 'bottom' => 20, 'left' => 15],
                    'espaciado_lineas' => 5,
                    'espaciado_secciones' => 15,
                    
                    // Logo
                    'mostrar_logo' => true,
                    'posicion_logo' => 'izquierda',
                    'tamaño_logo_ancho' => 120,
                    'tamaño_logo_alto' => 80,
                    
                    // Encabezado
                    'mostrar_titulo_factura' => true,
                    'texto_titulo_factura' => 'FACTURA',
                    'mostrar_numero_factura' => true,
                    'mostrar_fecha_emision' => true,
                    'mostrar_fecha_vencimiento' => false,
                    
                    // Centro médico
                    'mostrar_info_centro' => true,
                    'mostrar_direccion_centro' => true,
                    'mostrar_telefono_centro' => true,
                    'mostrar_email_centro' => true,
                    'mostrar_rtn_centro' => true,
                    
                    // CAI
                    'mostrar_cai' => true,
                    'mostrar_rango_cai' => true,
                    'mostrar_fecha_limite_cai' => true,
                    'posicion_cai' => 'superior',
                    
                    // Paciente
                    'mostrar_info_paciente' => true,
                    'mostrar_direccion_paciente' => true,
                    'mostrar_telefono_paciente' => true,
                    'mostrar_rtn_paciente' => true,
                    'etiqueta_cliente' => 'Facturar a:',
                    
                    // Tabla servicios
                    'mostrar_tabla_servicios' => true,
                    'mostrar_columna_cantidad' => true,
                    'mostrar_columna_descripcion' => true,
                    'mostrar_columna_precio_unitario' => true,
                    'mostrar_columna_total' => true,
                    'color_encabezado_tabla' => '#f3f4f6',
                    'alternar_color_filas' => true,
                    'color_fila_alterna' => '#f9fafb',
                    
                    // Totales
                    'mostrar_subtotal' => true,
                    'mostrar_descuentos' => true,
                    'mostrar_impuestos' => true,
                    'mostrar_total' => true,
                    'posicion_totales' => 'derecha',
                    'resaltar_total' => true,
                    
                    // Pie de página
                    'mostrar_pie_pagina' => true,
                    'texto_pie_pagina' => 'Gracias por confiar en nuestros servicios médicos.',
                    'mostrar_firma_medico' => false,
                    'mostrar_sello_centro' => false,
                    'mostrar_qr_pago' => false,
                    'posicion_qr' => 'derecha',
                    
                    // Marca de agua
                    'mostrar_watermark' => false,
                    'texto_watermark' => null,
                    'color_watermark' => '#e5e7eb',
                    'opacidad_watermark' => 10,
                    'posicion_watermark' => 'centro',
                    
                    // Relaciones
                    'centro_id' => $centro->id,
                    'created_by' => 1, // Usuario administrador
                ]);
            }
        }
    }
}
