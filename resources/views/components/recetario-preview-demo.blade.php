@php
    // Para la vista previa, usamos los valores del formulario
    $logo = $config['logo'] ?? null;
    
    // Si es array (viene de FileUpload), tomar el primer elemento
    if (is_array($logo) && !empty($logo)){
        $logo = reset($logo);
    }
    
    $showLogo = $config['mostrar_logo'] ?? false;
    $headerColor = $config['color_primario'] ?? '#1e40af';
    $secondaryColor = $config['color_secundario'] ?? '#64748b';
    $fontFamily = $config['fuente_familia'] ?? 'Arial, sans-serif';
    $fontSize = ($config['fuente_tamano'] ?? 14) . 'px';
    $encabezadoTexto = $config['encabezado_texto'] ?? 'RECETA MÉDICA';
    $piePagina = $config['pie_pagina'] ?? 'Consulte a su médico antes de usar cualquier medicamento';
    $textoAdicional = $config['texto_adicional'] ?? '';

    // Debug - mostrar valores recibidos
    \Log::info('Vista previa recibió:', [
        'color_primario_recibido' => $config['color_primario'] ?? 'NULL',
        'color_secundario_recibido' => $config['color_secundario'] ?? 'NULL',
        'headerColor_procesado' => $headerColor,
        'secondaryColor_procesado' => $secondaryColor
    ]);

    // Función mejorada para manejar rutas de logo (igual que en la vista de impresión)
    $logoPath = null;
    $logoExists = false;
    
    if ($logo) {
        // Caso 1: Archivo temporal de Livewire (recién subido, sin guardar)
        if (str_starts_with($logo, 'livewire-tmp/')) {
            try {
                $logoPath = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($logo)->temporaryUrl();
                $logoExists = true;
            } catch (Exception $e) {
                $logoExists = false;
            }
        }
        // Caso 2: Archivo en storage/app/public
        elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($logo)) {
            $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->url($logo);
            $logoExists = true;
        }
        // Caso 3: Ruta relativa - intentar con storage/
        elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists(str_replace('storage/', '', $logo))) {
            $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->url(str_replace('storage/', '', $logo));
            $logoExists = true;
        }
        // Caso 4: Archivo en public/storage (enlace simbólico)
        elseif (file_exists(public_path('storage/' . str_replace('storage/', '', $logo)))) {
            $logoPath = asset('storage/' . str_replace('storage/', '', $logo));
            $logoExists = true;
        }
        // Caso 5: URL completa
        elseif (filter_var($logo, FILTER_VALIDATE_URL)) {
            $logoPath = $logo;
            $logoExists = true;
        }
        // Caso 6: Ruta directa en public
        elseif (file_exists(public_path($logo))) {
            $logoPath = asset($logo);
            $logoExists = true;
        }
    }
@endphp

<div class="recetario-preview-demo" style="font-family: {{ $fontFamily }}; font-size: {{ $fontSize }}; width: 100%; max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border: 1px solid #ddd;">
    <!-- Header Principal -->
    <div class="header-principal" style="display: flex; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid {{ $headerColor }};">
        @if($showLogo)
            <div class="logo-container" style="margin-right: 20px;">
                @if($logo)
                    @if($logoExists && $logoPath)
                        <img src="{{ $logoPath }}" 
                             alt="Logo" 
                             style="max-height: 80px; max-width: 120px; object-fit: contain; border: 1px solid #e5e7eb; border-radius: 4px;"
                             onerror="this.parentElement.innerHTML='<div style=\'max-height: 80px; max-width: 120px; background: #fee2e2; border: 1px dashed #ef4444; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #dc2626; text-align: center; padding: 10px;\'>Error<br>cargando<br>imagen</div>'">
                    @else
                        <div style="max-height: 80px; max-width: 120px; background: #fef3c7; border: 1px dashed #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #d97706; text-align: center; padding: 10px;">
                            Logo no<br>encontrado<br>
                            <small style="font-size: 9px; margin-top: 4px;">{{ Str::limit($logo, 15) }}</small>
                        </div>
                    @endif
                @else
                    <div style="max-height: 80px; max-width: 120px; background: #f3f4f6; border: 1px dashed #d1d5db; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #666; text-align: center; padding: 10px;">
                        Subir<br>Logo
                    </div>
                @endif
            </div>
        @endif
        
        <div class="info-header" style="flex: 1;">
            <h2 style="margin: 0; color: {{ $headerColor }}; font-size: 24px; font-weight: bold;">
                {{ ($config['titulo'] ?? $config['titulo_medico'] ?? 'Dr(a).') }} {{ $config['nombre_mostrar'] ?? $config['nombre_mostrar_medico'] ?? (auth()->user()->name ?? '[Su Nombre Completo]') }}
            </h2>
            <div style="margin-top: 8px; color: {{ $secondaryColor }};">
                <div style="font-weight: 600; color: #333; margin-bottom: 4px;">
                    Especialidades: [Sus Especialidades]
                </div>
                @if(!empty($config['telefono_mostrar']) || !empty($config['telefonos_medico']))
                    <div style="font-size: 13px; color: {{ $secondaryColor }}; margin-top: 2px;">
                        <span style="color: #666;">📞</span> {{ $config['telefono_mostrar'] ?? $config['telefonos_medico'] }}
                    </div>
                @endif
            </div>
        </div>
        
        <div class="centro-info" style="text-align: right; color: {{ $secondaryColor }};">
            <div style="font-weight: 600; color: #333; font-size: 16px;">{{ $config['nombre_centro'] ?? '[Nombre del Centro Médico]' }}</div>
            @if($config['mostrar_direccion'] ?? true)
                <div style="font-size: 12px; margin-top: 4px;">
                    <span style="color: #666;">📍</span> {{ $config['direccion'] ?? '[Dirección del Centro]' }}
                </div>
            @endif
            @if($config['mostrar_telefono'] ?? true)
                <div style="font-size: 12px; margin-top: 2px;">
                    <span style="color: #666;">📞</span> {{ $config['telefono'] ?? '[Teléfono del Centro]' }}
                </div>
            @endif
        </div>
    </div>

    <!-- Información del Paciente y Consulta -->
    <div class="patient-section" style="display: flex; gap: 30px; margin-bottom: 25px; padding: 15px; background-color: #f8fafc; border-radius: 8px;">
        <div class="patient-info" style="flex: 1;">
            <h4 style="margin: 0 0 10px 0; color: {{ $headerColor }}; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">Información del Paciente</h4>
            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 14px;">
                <div><strong>Nombre:</strong> Juan Pérez López</div>
                <div style="display: flex; gap: 20px;">
                    <span><strong>Edad:</strong> 35 años</span>
                    <span><strong>Sexo:</strong> M</span>
                </div>
            </div>
        </div>
        
        <div class="consult-info" style="flex: 1;">
            <h4 style="margin: 0 0 10px 0; color: {{ $headerColor }}; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">Información de la Consulta</h4>
            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 14px;">
                <div><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</div>
                <div><strong>Horario:</strong> [Su Horario de Atención]</div>
            </div>
        </div>
    </div>


    <!-- Recetas e Indicaciones en una sola tabla -->
    <div class="receta-cuadro-unico" style="margin-bottom: 30px; border: 2px solid #e5e7eb; border-radius: 10px; background: #fff; box-shadow: 0 2px 8px #0001; overflow: hidden;">
        <h4 style="margin: 0; padding: 18px 25px 10px 25px; color: {{ $headerColor }}; border-bottom: 2px solid {{ $headerColor }}; font-size: 18px;">
            ℞ {{ $encabezadoTexto }}
        </h4>
        <table style="width: 100%; border-collapse: collapse; font-size: 15px;">
            <thead>
                <tr style="background: {{ $headerColor }}; color: #fff;">
                    <th style="padding: 10px; border-right: 1px solid #e5e7eb; width: 40px;">N°</th>
                    <th style="padding: 10px; border-right: 1px solid #e5e7eb;">Medicamento</th>
                    <th style="padding: 10px;">Observaciones / Indicaciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Simular lista de recetas para la demo
                    $recetas = [
                        (object)[ 'medicamento' => 'Paracetamol 500mg', 'indicaciones' => '1 cada 8 horas' ],
                        (object)[ 'medicamento' => 'Amoxicilina 500mg', 'indicaciones' => '1 cada 12 horas' ],
                        (object)[ 'medicamento' => 'Ibuprofeno 400mg', 'indicaciones' => '1 al dia' ],
                    ];
                    // Agregar una fila con indicaciones generales si hay texto adicional
                    if ($textoAdicional) {
                        $recetas[] = (object)[ 'medicamento' => '-', 'indicaciones' => $textoAdicional ];
                    }
                @endphp
                @foreach($recetas as $i => $receta)
                    <tr style="background: {{ $i % 2 == 0 ? '#f9fafb' : '#fff' }}; color: #333;">
                        <td style="padding: 12px; border-right: 1px solid #e5e7eb; text-align: center;">{{ $i + 1 }}</td>
                        <td style="padding: 12px; border-right: 1px solid #e5e7eb; text-align: center;">{{ $receta->medicamento }}</td>
                        <td style="padding: 12px; text-align: center;">{{ $receta->indicaciones }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer con Firma -->
    <div class="footer-section" style="margin-top: 40px; display: flex; justify-content: space-between; align-items: end; border-top: 1px solid #e5e7eb; padding-top: 20px;">
        <div class="signature-area" style="text-align: center; min-width: 250px;">
            <div style="border-top: 1px solid #333; margin-bottom: 5px; width: 250px;"></div>
            <div style="font-weight: 600; font-size: 13px;">
                {{ ($config['titulo'] ?? $config['titulo_medico'] ?? 'Dr(a).') }} {{ $config['nombre_mostrar'] ?? $config['nombre_mostrar_medico'] ?? (auth()->user()->name ?? '[Su Nombre Completo]') }}
            </div>
            @if(!empty($config['telefono_mostrar']) || !empty($config['telefonos_medico']))
                <div style="font-size: 12px; color: {{ $secondaryColor }}; margin-top: 2px;">
                    <span style="color: #666;">📞</span> {{ $config['telefono_mostrar'] ?? $config['telefonos_medico'] }}
                </div>
            @endif
            <div style="font-size: 12px; color: {{ $secondaryColor }};">Reg. Médico: [Su Número de Colegiación]</div>
        </div>
        
        <div class="footer-info" style="text-align: right; font-size: 11px; color: {{ $secondaryColor }};">
            <div>Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</div>
            <div>{{ $piePagina }}</div>
        </div>
    </div>
</div>

<!-- Script para debug (opcional, puedes removerlo en producción) -->
<script>
    console.log('Config logo:', @json($logo ?? 'null'));
    console.log('Logo path:', @json($logoPath ?? 'null'));
    console.log('Logo exists:', @json($logoExists ?? false));
</script>