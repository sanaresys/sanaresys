<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use App\Filament\Resources\Facturas\FacturasResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Consultas\Widgets\FacturacionStatus;
use App\Models\Consulta;
use App\Models\Receta;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewConsultas extends ViewRecord
{
    protected static string $resource = ConsultasResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // En ViewConsultas.php agregar botón:
            Actions\Action::make('agregar_servicios')
                ->label('Generar Cobro')
                ->icon('heroicon-o-plus-circle')
                ->color('info')
                ->url(fn () => "/admin/consultas/consultas/{$this->record->id}/servicios")
                ->visible(fn () => !$this->record->facturas()->exists()),



            // ---- BOTÓN VER FACTURA ---------------------------------
            Actions\Action::make('ver_factura')
                ->label('Ver Factura')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->url(fn ($record) =>
                    FacturasResource::getUrl('view', [
                        'record' => $record->facturas()->first()?->id,
                    ])
                )
                ->visible(fn ($record) => $record->facturas()->exists()),

            // Botón principal para crear nuevo examen
            Actions\Action::make('crear_examen')
                ->label('Nuevo Examen')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->size('lg')
                ->url(function (Consulta $record) {
                    return \App\Filament\Resources\Examenes\ExamenesResource::getUrl('create') .
                           '?paciente_id=' . $record->paciente_id .
                           '&consulta_id=' . $record->id .
                           '&medico_id=' . $record->medico_id;
                })
                ->openUrlInNewTab(false),

            // Botón principal para crear nueva receta
            Actions\Action::make('crear_receta')
                ->label('Nueva Receta')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->size('lg')
                ->url(function (Consulta $record) {
                    return \App\Filament\Resources\Receta\RecetaResource::getUrl('create-simple') .
                           '?paciente_id=' . $record->paciente_id .
                           '&consulta_id=' . $record->id .
                           '&medico_id=' . $record->medico_id;
                })
                ->openUrlInNewTab(false),

            // Separator
            Actions\Action::make('separator')
                ->label('')
                ->disabled()
                ->hidden(),

            // Botón para editar la consulta
            Actions\EditAction::make()
                ->color('warning'),

            // Botón para volver al listado
            Actions\Action::make('back')
                ->label('Volver')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Aquí podrías agregar widgets si necesitas mostrar información adicional
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Widget personalizado para mostrar el estado de facturación
            \App\Filament\Resources\Consultas\Widgets\FacturacionStatus::class,

        ];
    }

    private function getServiciosSubtotal($record): float
    {
        return \App\Models\FacturaDetalle::where('consulta_id', $record->id)
            ->whereNull('factura_id')
            ->sum('subtotal');
    }

    private function getServiciosImpuesto($record): float
    {
        return \App\Models\FacturaDetalle::where('consulta_id', $record->id)
            ->whereNull('factura_id')
            ->sum('impuesto_monto');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // 🎯 LAYOUT COMPLETO: INFORMACIÓN GENERAL + DETALLES DE CONSULTA
                Infolists\Components\TextEntry::make('informacion_completa_consulta')
                    ->label('')
                    ->state(function (Consulta $record): string {
                        // Fecha de consulta
                        $fecha = \Carbon\Carbon::parse($record->created_at)->format('d/m/Y H:i');

                        // Información del paciente
                        $pacienteInfo = 'No disponible';
                        if ($record->paciente && $record->paciente->persona) {
                            $persona = $record->paciente->persona;
                            $nombre = $persona->nombre_completo;
                            $dni = $persona->dni ? "DNI: {$persona->dni}" : '';
                            $telefono = $persona->telefono ? "Tel: {$persona->telefono}" : '';
                            $pacienteInfo = $nombre . "<br>" . $dni . "<br>" . $telefono;
                        }

                        // Información del médico
                        $medicoInfo = 'No disponible';
                        if ($record->medico && $record->medico->persona) {
                            $persona = $record->medico->persona;
                            $nombre = $persona->nombre_completo;
                            $dni = $persona->dni ? "DNI: {$persona->dni}" : '';
                            $colegiacion = $record->medico->numero_colegiacion ? "Col: {$record->medico->numero_colegiacion}" : '';
                            $medicoInfo = $nombre . "<br>" . $dni . "<br>" . $colegiacion;
                        } elseif ($record->medico_id) {
                            $medico = \App\Models\Medico::withoutGlobalScopes()->with('persona')->find($record->medico_id);
                            if ($medico && $medico->persona) {
                                $persona = $medico->persona;
                                $nombre = $persona->nombre_completo;
                                $dni = $persona->dni ? "DNI: {$persona->dni}" : '';
                                $colegiacion = $medico->numero_colegiacion ? "Col: {$medico->numero_colegiacion}" : '';
                                $medicoInfo = $nombre . "<br>" . $dni . "<br>" . $colegiacion;
                            }
                        }

                        // Diagnóstico, Tratamiento y Observaciones
                        $diagnostico = $record->diagnostico ?: 'Sin diagnóstico registrado';
                        $tratamiento = $record->tratamiento ?: 'Sin tratamiento registrado';
                        $observaciones = $record->observaciones ?: 'Sin observaciones registradas';

                        return '
                        <div style="width: 100%;">
                            <!-- PRIMERA FILA: Información General -->
                            <!-- TÍTULO: Información del Paciente -->
                            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; margin-top: 0.5rem; color: #374151;">Información del Paciente</h3>

                            <div style="display: flex !important; flex-direction: row !important; gap: 1rem; width: 100%; margin-bottom: 1rem;">
                                <!-- Tarjeta Fecha -->
                                <div style="flex: 1; background: white; border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 1.25rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); min-height: 120px;">
                                    <div style="font-weight: 600; color: #374151; margin-bottom: 0.75rem; font-size: 0.9rem;">
                                        📅 Fecha de Consulta
                                    </div>
                                    <div style="font-size: 1.1rem; font-weight: 600; color: #059669;">
                                        ' . $fecha . '
                                    </div>
                                </div>

                                <!-- Tarjeta Paciente -->
                                <div style="flex: 1; background: white; border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 1.25rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); min-height: 120px;">
                                    <div style="font-weight: 600; color: #374151; margin-bottom: 0.75rem; font-size: 0.9rem;">
                                        👤 Paciente
                                    </div>
                                    <div style="color: #059669; line-height: 1.4;">
                                        ' . $pacienteInfo . '
                                    </div>
                                </div>

                                <!-- Tarjeta Médico -->
                                <div style="flex: 1; background: white; border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 1.25rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); min-height: 120px;">
                                    <div style="font-weight: 600; color: #374151; margin-bottom: 0.75rem; font-size: 0.9rem;">
                                        👨‍⚕️ Médico
                                    </div>
                                    <div style="color: #059669; line-height: 1.4;">
                                        ' . $medicoInfo . '
                                    </div>
                                </div>
                            </div>

                            <!-- SEGUNDA FILA: Detalles de Consulta -->
                                                        </div>

                            <!-- TÍTULO: Detalles de Consulta -->
                            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; margin-top: 0.5rem; color: #374151;">Detalles de Consulta</h3>

                            <!-- SEGUNDA FILA: Detalles de Consulta -->
                            <div style="display: flex !important; flex-direction: row !important; gap: 1rem; width: 100%; margin-bottom: 2rem;">
                                <!-- Tarjeta Diagnóstico -->
                                <div style="flex: 1; background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 0.5rem; padding: 1rem; min-height: 120px;">
                                    <div style="font-weight: 600; color: #374151; margin-bottom: 0.75rem; font-size: 0.9rem;">
                                        🔍 Diagnóstico
                                    </div>
                                    <div style="color: #1e40af; line-height: 1.4; word-wrap: break-word; max-height: 80px; overflow-y: auto;">
                                        ' . nl2br(htmlspecialchars($diagnostico)) . '
                                    </div>
                                </div>

                                <!-- Tarjeta Tratamiento -->
                                <div style="flex: 1; background: #f0fdf4; border: 1px solid #22c55e; border-radius: 0.5rem; padding: 1rem; min-height: 120px;">
                                    <div style="font-weight: 600; color: #374151; margin-bottom: 0.75rem; font-size: 0.9rem;">
                                        💊 Tratamiento
                                    </div>
                                    <div style="color: #166534; line-height: 1.4; word-wrap: break-word; max-height: 80px; overflow-y: auto;">
                                        ' . nl2br(htmlspecialchars($tratamiento)) . '
                                    </div>
                                </div>

                                <!-- Tarjeta Observaciones -->
                                <div style="flex: 1; background: #fffbeb; border: 1px solid #f59e0b; border-radius: 0.5rem; padding: 1rem; min-height: 120px;">
                                    <div style="font-weight: 600; color: #374151; margin-bottom: 0.75rem; font-size: 0.9rem;">
                                        📝 Observaciones
                                    </div>
                                    <div style="color: #92400e; line-height: 1.4; word-wrap: break-word; max-height: 80px; overflow-y: auto;">
                                        ' . nl2br(htmlspecialchars($observaciones)) . '
                                    </div>
                                </div>
                            </div>
                        </div>';
                    })
                    ->html()
                    ->extraAttributes([
                        'style' => 'margin: 0; padding: 0;'
                    ]),                // Sección de recetas asociadas
                Infolists\Components\Section::make('Recetas Médicas')
                    ->schema([
                        Infolists\Components\TextEntry::make('recetas')
                            ->label('')
                            ->state(function (Consulta $record): string {
                                if (!$record->recetas()->exists()) {
                                    return '<div class="flex items-center justify-center p-6 space-y-4">
                                        <div class="text-center">
                                            <div class="text-4xl mb-4">📝</div>
                                            <p class="text-gray-500 dark:text-gray-400">No hay recetas médicas asociadas a esta consulta</p>
                                        </div>
                                    </div>';
                                }
                                $html = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-800">
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Medicamentos</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Indicaciones</th>
                                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">';
                                foreach ($record->recetas as $index => $receta) {
                                    $fecha = \Carbon\Carbon::parse($receta->fecha_receta)->format('d/m/Y');
                                    $recetaNum = $index + 1;
                                    $html .= '<tr class="hover:bg-blue-100 dark:hover:bg-blue-900">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">' . $recetaNum . '</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' . $fecha . '</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">' . nl2br(e($receta->medicamentos)) . '</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">' . nl2br(e($receta->indicaciones)) . '</td>
                                        <td class="px-4 py-3 text-center text-sm font-medium">
                                            <div class="flex justify-center space-x-2">
                                                <a href="' . route('recetas.imprimir', $receta) . '" target="_blank" class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors" title="Imprimir">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                    Imprimir
                                                </a>
                                                <a href="' . \App\Filament\Resources\Receta\RecetaResource::getUrl('edit', ['record' => $receta->id]) . '" class="inline-flex items-center px-2 py-1 bg-yellow-400 hover:bg-yellow-500 text-gray-900 rounded transition-colors" title="Editar">
                                                    ✏️ Editar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>';
                                }
                                $html .= '</tbody></table></div>';
                                return $html;
                            })
                            ->html()
                            ->columnSpanFull(),

                        // Mensaje cuando no hay recetas
                        Infolists\Components\TextEntry::make('no_recetas')
                            ->label('')
                            ->state(' No hay recetas médicas asociadas a esta consulta')
                            ->color('gray')
                            ->weight('medium')
                            ->extraAttributes([
                                'style' => 'text-align: center; padding: 40px; border: 2px dashed; border-radius: 12px; background: linear-gradient(135deg, rgba(156, 163, 175, 0.1), rgba(209, 213, 219, 0.1));',
                                'class' => 'border-gray-300 dark:border-gray-600'
                            ])
                            ->visible(function (Consulta $record): bool {
                                return !$record->recetas()->exists();
                            }),

                        // Botones de acción generales para las recetas
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('gestionar_recetas')
                                ->label('Ver todas las recetas')
                                ->icon('heroicon-o-document-text')
                                ->color('info')
                                ->url(function (Consulta $record) {
                                    return \App\Filament\Resources\Receta\RecetaResource::getUrl('index');
                                })
                                ->openUrlInNewTab(false)
                                ->visible(function (Consulta $record) {
                                    return $record->recetas()->count() > 0;
                                }),

                            Infolists\Components\Actions\Action::make('imprimir_todas_recetas')
                                ->label('Imprimir Todas las Recetas')
                                ->icon('heroicon-o-printer')
                                ->color('success')
                                ->action(function (Consulta $record) {
                                    // Redirigir a una vista de impresión con todas las recetas de la consulta
                                    return redirect()->route('recetas.imprimir.consulta', ['consulta' => $record->id]);
                                })
                                ->visible(function (Consulta $record) {
                                    return $record->recetas()->count() > 1; // Solo mostrar si hay más de 1 receta
                                }),
                        ])
                        ->columnSpanFull()
                        ->visible(function (Consulta $record) {
                            return $record->recetas()->count() > 0;
                        }),
                    ])
                    ->description('Lista organizada de todas las recetas médicas emitidas durante esta consulta')
                    ->collapsible()
                    ->collapsed(false)
                    ->icon('heroicon-o-clipboard-document-list'),

                // 🔬 NUEVA SECCIÓN DE EXÁMENES MÉDICOS
                Infolists\Components\Section::make('🔬 Exámenes Médicos')
                    ->schema([
                        Infolists\Components\TextEntry::make('examenes')
                            ->label('')
                            ->state(function (Consulta $record): string {
                                if (!$record->examenes()->exists()) {
                                    return '<div class="flex items-center justify-center p-8 space-y-4">
                                        <div class="text-center">
                                            <div class="text-5xl mb-4">🔬</div>
                                            <p class="text-gray-500 dark:text-gray-400 text-lg">No hay exámenes médicos asociados a esta consulta</p>
                                            <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Use el botón "Nuevo Examen" para solicitar exámenes médicos</p>
                                        </div>
                                    </div>';
                                }

                                $html = '<div class="overflow-x-auto shadow-sm rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo de Examen</th>
                                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha Solicitado</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Observaciones</th>
                                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';

                                foreach ($record->examenes as $index => $examen) {
                                    $examenNum = $index + 1;
                                    $fechaCreado = $examen->created_at ? 
                                        \Carbon\Carbon::parse($examen->created_at)->format('d/m/Y H:i') : 'N/A';
                                    
                                    // Estado con colores
                                    $estadoBadge = match($examen->estado) {
                                        'Solicitado' => '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">⏳ Solicitado</span>',
                                        'Completado' => '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">✅ Completado</span>',
                                        'No presentado' => '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">❌ No presentado</span>',
                                        default => '<span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">📝 ' . htmlspecialchars($examen->estado) . '</span>'
                                    };

                                    $observaciones = $examen->observaciones ? htmlspecialchars($examen->observaciones) : '<span class="text-gray-400 italic">Sin observaciones</span>';

                                    $html .= '<tr class="hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">' . $examenNum . '</td>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <div class="flex items-center">
                                                <span class="text-lg mr-2">🔬</span>
                                                ' . htmlspecialchars($examen->tipo_examen) . '
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-center">' . $estadoBadge . '</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">' . $fechaCreado . '</td>
                                        <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate" title="' . htmlspecialchars($examen->observaciones ?: '') . '">' . $observaciones . '</td>
                                        <td class="px-4 py-4 text-center text-sm font-medium">
                                            <div class="flex justify-center space-x-2">
                                                <a href="' . route('examenes.imprimir', $examen) . '" target="_blank" 
                                                   class="inline-flex items-center px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded-md transition-colors text-xs font-medium" 
                                                   title="Imprimir orden de examen">
                                                    🖨️ Imprimir
                                                </a>
                                                <a href="' . \App\Filament\Resources\Examenes\ExamenesResource::getUrl('edit', ['record' => $examen->id]) . '" 
                                                   class="inline-flex items-center px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md transition-colors text-xs font-medium" 
                                                   title="Editar examen">
                                                    ✏️ Editar
                                                </a>
                                                <a href="' . \App\Filament\Resources\Examenes\ExamenesResource::getUrl('view', ['record' => $examen->id]) . '" 
                                                   class="inline-flex items-center px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-md transition-colors text-xs font-medium" 
                                                   title="Ver detalles">
                                                    👁️ Ver
                                                </a>
                                            </div>
                                        </td>
                                    </tr>';
                                }
                                $html .= '</tbody></table></div>';

                                return $html;
                            })
                            ->html()
                            ->columnSpanFull(),

                        // Mensaje cuando no hay exámenes
                        Infolists\Components\TextEntry::make('no_examenes')
                            ->label('')
                            ->state('🔬 No hay exámenes médicos asociados a esta consulta')
                            ->color('gray')
                            ->weight('medium')
                            ->extraAttributes([
                                'style' => 'text-align: center; padding: 40px; border: 2px dashed; border-radius: 12px; background: linear-gradient(135deg, rgba(156, 163, 175, 0.1), rgba(209, 213, 219, 0.1));',
                                'class' => 'border-gray-300 dark:border-gray-600'
                            ])
                            ->visible(function (Consulta $record): bool {
                                return !$record->examenes()->exists();
                            }),

                        // Botones de acción para gestionar exámenes
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('ver_todos_examenes')
                                ->label('Ver todos los exámenes')
                                ->icon('heroicon-o-clipboard-document-check')
                                ->color('info')
                                ->url(function (Consulta $record) {
                                    return \App\Filament\Resources\Examenes\ExamenesResource::getUrl('index') . 
                                           '?tableFilters[consulta_id][value]=' . $record->id;
                                })
                                ->openUrlInNewTab(false)
                                ->visible(function (Consulta $record) {
                                    return $record->examenes()->count() > 0;
                                }),

                            Infolists\Components\Actions\Action::make('imprimir_examenes')
                                ->label('Imprimir Lista de Exámenes')
                                ->icon('heroicon-o-printer')
                                ->color('success')
                                ->url(function (Consulta $record) {
                                    return route('examenes.imprimir.consulta', ['consulta' => $record->id]);
                                })
                                ->openUrlInNewTab(true)
                                ->visible(function (Consulta $record) {
                                    return $record->examenes()->count() > 0;
                                }),
                        ])
                        ->columnSpanFull()
                        ->visible(function (Consulta $record) {
                            return $record->examenes()->count() > 0;
                        }),
                    ])
                    ->description('Lista de todos los exámenes médicos solicitados durante esta consulta')
                    ->collapsible()
                    ->collapsed(false)
                    ->icon('heroicon-o-clipboard-document-check'),
            ]);
    }
}
