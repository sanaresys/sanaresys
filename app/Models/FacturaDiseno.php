<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacturaDiseno extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'factura_disenos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'es_predeterminado',
        'activo',
        'template_archivo',
        'orientacion_papel',
        'tamaño_papel',
        'color_primario',
        'color_secundario',
        'color_acento',
        'color_texto',
        'fuente_titulo',
        'fuente_texto',
        'tamaño_titulo',
        'tamaño_texto',
        'tamaño_subtitulo',
        'margenes',
        'espaciado_lineas',
        'espaciado_secciones',
        'mostrar_logo',
        'posicion_logo',
        'tamaño_logo_ancho',
        'tamaño_logo_alto',
        'mostrar_titulo_factura',
        'texto_titulo_factura',
        'mostrar_numero_factura',
        'mostrar_fecha_emision',
        'mostrar_fecha_vencimiento',
        'mostrar_info_centro',
        'mostrar_direccion_centro',
        'mostrar_telefono_centro',
        'mostrar_email_centro',
        'mostrar_rtn_centro',
        'mostrar_cai',
        'mostrar_rango_cai',
        'mostrar_fecha_limite_cai',
        'posicion_cai',
        'mostrar_info_paciente',
        'mostrar_direccion_paciente',
        'mostrar_telefono_paciente',
        'mostrar_rtn_paciente',
        'etiqueta_cliente',
        'mostrar_tabla_servicios',
        'mostrar_columna_cantidad',
        'mostrar_columna_descripcion',
        'mostrar_columna_precio_unitario',
        'mostrar_columna_total',
        'color_encabezado_tabla',
        'alternar_color_filas',
        'color_fila_alterna',
        'mostrar_subtotal',
        'mostrar_descuentos',
        'mostrar_impuestos',
        'mostrar_total',
        'posicion_totales',
        'resaltar_total',
        'mostrar_pie_pagina',
        'texto_pie_pagina',
        'mostrar_firma_medico',
        'mostrar_sello_centro',
        'mostrar_qr_pago',
        'posicion_qr',
        'mostrar_watermark',
        'texto_watermark',
        'color_watermark',
        'opacidad_watermark',
        'posicion_watermark',
        'configuracion_adicional',
        'css_personalizado',
        'centro_id',
        'factura_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'es_predeterminado' => 'boolean',
        'activo' => 'boolean',
        'margenes' => 'array',
        'mostrar_logo' => 'boolean',
        'mostrar_titulo_factura' => 'boolean',
        'mostrar_numero_factura' => 'boolean',
        'mostrar_fecha_emision' => 'boolean',
        'mostrar_fecha_vencimiento' => 'boolean',
        'mostrar_info_centro' => 'boolean',
        'mostrar_direccion_centro' => 'boolean',
        'mostrar_telefono_centro' => 'boolean',
        'mostrar_email_centro' => 'boolean',
        'mostrar_rtn_centro' => 'boolean',
        'mostrar_cai' => 'boolean',
        'mostrar_rango_cai' => 'boolean',
        'mostrar_fecha_limite_cai' => 'boolean',
        'mostrar_info_paciente' => 'boolean',
        'mostrar_direccion_paciente' => 'boolean',
        'mostrar_telefono_paciente' => 'boolean',
        'mostrar_rtn_paciente' => 'boolean',
        'mostrar_tabla_servicios' => 'boolean',
        'mostrar_columna_cantidad' => 'boolean',
        'mostrar_columna_descripcion' => 'boolean',
        'mostrar_columna_precio_unitario' => 'boolean',
        'mostrar_columna_total' => 'boolean',
        'alternar_color_filas' => 'boolean',
        'mostrar_subtotal' => 'boolean',
        'mostrar_descuentos' => 'boolean',
        'mostrar_impuestos' => 'boolean',
        'mostrar_total' => 'boolean',
        'resaltar_total' => 'boolean',
        'mostrar_pie_pagina' => 'boolean',
        'mostrar_firma_medico' => 'boolean',
        'mostrar_sello_centro' => 'boolean',
        'mostrar_qr_pago' => 'boolean',
        'mostrar_watermark' => 'boolean',
        'configuracion_adicional' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación con centro médico
     */
    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    /**
     * Relación con facturas que usan este diseño
     */
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'factura_diseno_id');
    }

    /**
     * Scope para obtener diseños activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener el diseño predeterminado
     */
    public function scopePredeterminado($query)
    {
        return $query->where('es_predeterminado', true)->where('activo', true);
    }

    /**
     * Scope para obtener diseños por centro
     */
    public function scopePorCentro($query, $centroId)
    {
        return $query->where('centro_id', $centroId);
    }

    /**
     * Obtiene las configuraciones de márgenes con valores por defecto
     */
    public function getMargenesAttribute($value)
    {
        $margenes = json_decode($value, true) ?? [];
        
        return array_merge([
            'top' => 20,
            'right' => 15,
            'bottom' => 20,
            'left' => 15
        ], $margenes);
    }

    /**
     * Obtiene la configuración adicional con valores por defecto
     */
    public function getConfiguracionAdicionalAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    /**
     * Método helper para aplicar configuración de colores
     */
    public function getConfiguracionColores(): array
    {
        return [
            'primario' => $this->color_primario,
            'secundario' => $this->color_secundario,
            'acento' => $this->color_acento,
            'texto' => $this->color_texto,
            'encabezado_tabla' => $this->color_encabezado_tabla,
            'fila_alterna' => $this->color_fila_alterna,
            'watermark' => $this->color_watermark,
        ];
    }

    /**
     * Método helper para obtener configuración de tipografía
     */
    public function getConfiguracionTipografia(): array
    {
        return [
            'fuente_titulo' => $this->fuente_titulo,
            'fuente_texto' => $this->fuente_texto,
            'tamaño_titulo' => $this->tamaño_titulo,
            'tamaño_texto' => $this->tamaño_texto,
            'tamaño_subtitulo' => $this->tamaño_subtitulo,
        ];
    }

    /**
     * Método para establecer un diseño como predeterminado
     */
    public function establecerComoPredeterminado(): void
    {
        // Remover predeterminado de otros diseños del mismo centro
        static::where('centro_id', $this->centro_id)
              ->where('id', '!=', $this->id)
              ->update(['es_predeterminado' => false]);
        
        // Establecer este como predeterminado
        $this->update(['es_predeterminado' => true, 'activo' => true]);
    }
}
