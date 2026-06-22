<?php

namespace App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Carbon\Carbon;

class ViewContratoMedico extends ViewRecord
{
    protected static string $resource = ContratoMedicoResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información del Médico')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('medico.persona.nombre_completo')
                                    ->label('Nombre del Médico')
                                    ->getStateUsing(fn ($record) => 
                                        $record->medico->persona->primer_nombre . ' ' . 
                                        ($record->medico->persona->segundo_nombre ? $record->medico->persona->segundo_nombre . ' ' : '') .
                                        $record->medico->persona->primer_apellido . ' ' . 
                                        ($record->medico->persona->segundo_apellido ?? '')
                                    )
                                    ->weight(FontWeight::Bold)
                                    ->size('lg')
                                    ->color('primary'),

                                TextEntry::make('centro.nombre_centro')
                                    ->label('Centro Médico')
                                    ->icon('heroicon-o-building-office-2')
                                    ->weight(FontWeight::Medium),

                                TextEntry::make('medico.numero_colegiacion')
                                    ->label('Número de Colegiación')
                                    ->icon('heroicon-o-identification'),

                                TextEntry::make('medico.especialidades_principales')
                                    ->label('Especialidades')
                                    ->getStateUsing(fn ($record) => 
                                        $record->medico->especialidades->pluck('especialidad')->join(', ')
                                    )
                                    ->badge()
                                    ->color('success')
                                    ->separator(', '),
                            ]),
                    ])
                    ->icon('heroicon-o-user-circle')
                    ->collapsible(),

                Section::make('Información del Contrato')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('fecha_inicio')
                                    ->label('Fecha de Inicio')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar-days')
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('fecha_fin')
                                    ->label('Fecha de Finalización')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar')
                                    ->badge()
                                    ->color('warning'),

                                TextEntry::make('duracion_contrato')
                                    ->label('Duración del Contrato')
                                    ->getStateUsing(function ($record) {
                                        if (!$record->fecha_fin) {
                                            return 'Indefinido';
                                        }
                                        $inicio = Carbon::parse($record->fecha_inicio);
                                        $fin = Carbon::parse($record->fecha_fin);
                                        $meses = $inicio->diffInMonths($fin);
                                        return $meses . ' meses';
                                    })
                                    ->icon('heroicon-o-clock')
                                    ->badge()
                                    ->color('info'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('salario_quincenal')
                                    ->label('Salario Quincenal')
                                    ->prefix('L. ')
                                    ->numeric(2, '.', ',')
                                    ->icon('heroicon-o-banknotes')
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('salario_mensual')
                                    ->label('Salario Mensual')
                                    ->prefix('L. ')
                                    ->numeric(2, '.', ',')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->badge()
                                    ->color('success')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('porcentaje_servicio')
                                    ->label('Porcentaje por Servicio')
                                    ->suffix('%')
                                    ->icon('heroicon-o-calculator')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('activo')
                                    ->label('Estado del Contrato')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Activo' : 'Inactivo')
                                    ->icon('heroicon-o-check-circle')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                            ]),
                    ])
                    ->icon('heroicon-o-document-check')
                    ->collapsible(),

                Section::make('Información del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Fecha de Registro')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-clock')
                                    ->since(),

                                TextEntry::make('updated_at')
                                    ->label('Última Actualización')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-arrow-path')
                                    ->since(),
                            ]),
                    ])
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // Botón de volver al listado siempre visible para todos
        $actions[] = Action::make('back')
            ->label('Volver al Listado')
            ->icon('heroicon-o-arrow-left')
            ->color('gray')
            ->url(static::$resource::getUrl('index'));

        // Mostrar botones de editar y ver perfil solo para administradores y root
        if ($user->hasRole('administrador') || $user->hasRole('root')) {
            $actions[] = Action::make('edit')
                ->label('Editar Contrato')
                ->icon('heroicon-o-pencil')
                ->color('warning')
                ->url(fn () => static::$resource::getUrl('edit', ['record' => $this->record]));

            $actions[] = Action::make('ver_medico')
                ->label('Ver Perfil del Médico')
                ->icon('heroicon-o-user')
                ->color('primary')
                ->url(fn ($record) => route('filament.admin.resources.medico.medicos.view', [
                    'record' => $record->medico
                ]));
        }

        return $actions;
    }

    public function getTitle(): string
    {
        return 'Contrato de ' . $this->record->medico->persona->primer_nombre . ' ' . $this->record->medico->persona->primer_apellido;
    }
}
