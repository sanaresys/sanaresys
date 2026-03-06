<?php

namespace App\Filament\Resources\PacientesResource\Pages;

use App\Filament\Resources\PacientesResource;
use App\Filament\Resources\Consultas\ConsultasResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Actions;
use Carbon\Carbon;

class ViewPacientes extends ViewRecord
{
    protected static string $resource = PacientesResource::class;

    public function getRelationManagers(): array
    {
        return [
            \App\Filament\Resources\Pacientes\PacientesResource\RelationManagers\ConsultasRelationManager::class,
        ];
    }

    public function getTitle(): string
    {
        return "Información del Paciente - {$this->record->persona->nombre_completo}";
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $paciente = $this->record;
        $totalConsultas = \App\Models\Consulta::where('paciente_id', $paciente->id)->count();
        $ultimaConsulta = \App\Models\Consulta::where('paciente_id', $paciente->id)->latest()->first();
        $medicosAtendieron = \App\Models\Consulta::where('paciente_id', $paciente->id)->distinct('medico_id')->count();

        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\ImageEntry::make('persona.fotografia')
                                    ->label('')
                                    ->circular()
                                    ->size(180)
                                    ->getStateUsing(function ($record) {
                                        if ($record->persona->foto ?? $record->persona->fotografia) {
                                            return asset('storage/' . ($record->persona->foto ?? $record->persona->fotografia));
                                        }
                                        return PacientesResource::generateAvatar(
                                            $record->persona->primer_nombre,
                                            $record->persona->primer_apellido
                                        );
                                    }),

                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('nombre_completo')
                                            ->label('Nombre Completo')
                                            ->getStateUsing(function ($record) {
                                                return trim("{$record->persona->primer_nombre} {$record->persona->segundo_nombre} {$record->persona->primer_apellido} {$record->persona->segundo_apellido}");
                                            })
                                            ->weight(FontWeight::Bold)
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                            ->color('primary'),

                                        Infolists\Components\TextEntry::make('persona.dni')
                                            ->label('DNI/Cédula')
                                            ->icon('heroicon-m-identification')
                                            ->iconPosition(IconPosition::Before)
                                            ->copyable()
                                            ->copyMessage('DNI copiado')
                                            ->weight(FontWeight::Medium),

                                        Infolists\Components\TextEntry::make('grupo_sanguineo')
                                            ->label('Grupo Sanguíneo')
                                            ->icon('heroicon-m-heart')
                                            ->iconPosition(IconPosition::Before)
                                            ->badge()
                                            ->color('danger')
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Medium),

                                        Infolists\Components\TextEntry::make('edad')
                                            ->label('Edad')
                                            ->icon('heroicon-m-calendar-days')
                                            ->iconPosition(IconPosition::Before)
                                            ->getStateUsing(function ($record) {
                                                if ($record->persona->fecha_nacimiento) {
                                                    $edad = Carbon::parse($record->persona->fecha_nacimiento)->age;
                                                    return $edad . ' años';
                                                }
                                                return 'No especificada';
                                            }),

                                        Infolists\Components\TextEntry::make('persona.sexo')
                                            ->label('Sexo')
                                            ->icon('heroicon-m-user')
                                            ->iconPosition(IconPosition::Before)
                                            ->formatStateUsing(fn ($state) => $state === 'M' ? 'Masculino' : 'Femenino')
                                            ->badge()
                                            ->color(fn ($state) => $state === 'M' ? 'info' : 'warning'),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->compact(),

                // Datos Personales
                Infolists\Components\Section::make('Datos Personales')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('persona.primer_nombre')
                                    ->label('Primer Nombre')
                                    ->icon('heroicon-m-user')
                                    ->iconPosition(IconPosition::Before),

                                Infolists\Components\TextEntry::make('persona.primer_apellido')
                                    ->label('Primer Apellido')
                                    ->icon('heroicon-m-user')
                                    ->iconPosition(IconPosition::Before),

                                Infolists\Components\TextEntry::make('persona.fecha_nacimiento')
                                    ->label('Fecha de Nacimiento')
                                    ->icon('heroicon-m-calendar')
                                    ->iconPosition(IconPosition::Before)
                                    ->date('d/m/Y')
                                    ->weight(FontWeight::Medium),

                                Infolists\Components\TextEntry::make('persona.nacionalidad.nacionalidad')
                                    ->label('Nacionalidad')
                                    ->icon('heroicon-m-flag')
                                    ->iconPosition(IconPosition::Before)
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('persona.telefono')
                                    ->label('Teléfono')
                                    ->icon('heroicon-m-phone')
                                    ->iconPosition(IconPosition::Before)
                                    ->copyable()
                                    ->copyMessage('Teléfono copiado'),

                                Infolists\Components\TextEntry::make('persona.direccion')
                                    ->label('Dirección')
                                    ->icon('heroicon-m-map-pin')
                                    ->iconPosition(IconPosition::Before),
                            ]),
                    ])
                    ->icon('heroicon-m-user-circle')
                    ->collapsible()
                    ->collapsed(false),

                // Datos del Paciente / Información Médica
                Infolists\Components\Section::make('Información Médica')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('grupo_sanguineo')
                                    ->label('Grupo Sanguíneo')
                                    ->icon('heroicon-m-heart')
                                    ->iconPosition(IconPosition::Before)
                                    ->badge()
                                    ->color('danger'),

                                Infolists\Components\TextEntry::make('contacto_emergencia')
                                    ->label('Contacto de Emergencia')
                                    ->icon('heroicon-m-phone-arrow-up-right')
                                    ->iconPosition(IconPosition::Before)
                                    ->copyable()
                                    ->copyMessage('Contacto de emergencia copiado'),

                                Infolists\Components\TextEntry::make('total_enfermedades')
                                    ->label('Total de Enfermedades')
                                    ->icon('heroicon-m-clipboard-document-list')
                                    ->iconPosition(IconPosition::Before)
                                    ->getStateUsing(function ($record) {
                                        $total = $record->enfermedades->count();
                                        return $total . ' ' . ($total === 1 ? 'enfermedad' : 'enfermedades');
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        $total = $record->enfermedades->count();
                                        if ($total === 0) return 'success';
                                        if ($total <= 2) return 'warning';
                                        return 'danger';
                                    }),
                            ]),
                    ])
                    ->icon('heroicon-m-heart')
                    ->collapsible()
                    ->collapsed(false),

                // Historial de Enfermedades (Versión mejorada)
                Infolists\Components\Section::make('Historial de Enfermedades')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('enfermedades')
                            ->schema([
                                Infolists\Components\Grid::make(1)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('enfermedades')
                                            ->label('Enfermedad')
                                            ->weight(FontWeight::Bold)
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                            ->icon('heroicon-m-exclamation-triangle')
                                            ->iconPosition(IconPosition::Before)
                                            ->color('danger')
                                            ->columnSpanFull()
                                            ->getStateUsing(fn ($record) => $record->nombre ?? $record->enfermedades ?? 'Enfermedad no especificada'),

                                        Infolists\Components\TextEntry::make('pivot.fecha_diagnostico')
                                            ->label('Fecha de Diagnóstico')
                                            ->formatStateUsing(function ($state) {
                                                if ($state) {
                                                    return date('d/m/Y', strtotime($state));
                                                }
                                                return 'No especificada';
                                            })
                                            ->badge()
                                            ->color('info')
                                            ->icon('heroicon-m-calendar')
                                            ->iconPosition(IconPosition::Before),

                                        Infolists\Components\TextEntry::make('descripcion')
                                            ->label('Descripción')
                                            ->icon('heroicon-m-document-text')
                                            ->iconPosition(IconPosition::Before)
                                            ->formatStateUsing(fn ($state) => $state ?: 'Sin descripción')
                                            ->limit(150)
                                            ->columnSpanFull(),

                                        Infolists\Components\TextEntry::make('pivot.tratamiento')
                                            ->label('Tratamiento')
                                            ->icon('heroicon-m-clipboard-document')
                                            ->iconPosition(IconPosition::Before)
                                            ->formatStateUsing(fn ($state) => $state ?: 'No especificado')
                                            ->columnSpanFull()
                                            ->prose(),
                                    ]),
                            ])
                            ->contained(true),
                    ])
                    ->icon('heroicon-m-clipboard-document-list')
                    ->collapsible()
                    ->collapsed(true)
                    ->hidden(fn ($record) => $record->enfermedades->isEmpty()),

                // Mensaje cuando no hay enfermedades
                Infolists\Components\Section::make('Historial de Enfermedades')
                    ->schema([
                        Infolists\Components\TextEntry::make('sin_enfermedades')
                            ->label('')
                            ->getStateUsing(fn () => 'Este paciente no tiene enfermedades registradas.')
                            ->icon('heroicon-m-check-circle')
                            ->iconPosition(IconPosition::Before)
                            ->color('success')
                            ->weight(FontWeight::Medium),
                    ])
                    ->icon('heroicon-m-heart')
                    ->collapsible()
                    ->visible(fn ($record) => $record->enfermedades->isEmpty()),

                // Estadísticas Médicas
                Infolists\Components\Section::make('Estadísticas Médicas')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_consultas')
                                    ->label('Total de Consultas')
                                    ->state($totalConsultas)
                                    ->icon('heroicon-m-clipboard-document-list')
                                    ->iconPosition(IconPosition::Before)
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('ultima_consulta')
                                    ->label('Última Consulta')
                                    ->state($ultimaConsulta ? $ultimaConsulta->created_at->format('d/m/Y') : 'Sin consultas')
                                    ->icon('heroicon-m-calendar-days')
                                    ->iconPosition(IconPosition::Before)
                                    ->badge()
                                    ->color('success'),

                                Infolists\Components\TextEntry::make('medicos_atendieron')
                                    ->label('Médicos que lo han atendido')
                                    ->state($medicosAtendieron)
                                    ->icon('heroicon-m-user-group')
                                    ->iconPosition(IconPosition::Before)
                                    ->badge()
                                    ->color('warning'),
                            ]),
                    ])
                    ->icon('heroicon-m-chart-bar')
                    ->collapsible()
                    ->collapsed(false),

                // Información del Sistema
                Infolists\Components\Section::make('Información del Sistema')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha de Registro')
                                    ->icon('heroicon-m-plus-circle')
                                    ->iconPosition(IconPosition::Before)
                                    ->dateTime('d/m/Y H:i')
                                    ->weight(FontWeight::Medium),

                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Última Actualización')
                                    ->icon('heroicon-m-pencil-square')
                                    ->iconPosition(IconPosition::Before)
                                    ->dateTime('d/m/Y H:i')
                                    ->weight(FontWeight::Medium),
                            ]),
                    ])
                    ->icon('heroicon-m-cog-6-tooth')
                    ->collapsible()
                    ->collapsed(true),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('crear_consulta')
                ->label('Crear Consulta')
                ->icon('heroicon-m-clipboard-document-list')
                ->color('success')
                ->url(function () {
                    return \App\Filament\Resources\Consultas\ConsultasResource::getUrl('create', [
                        'paciente_id' => $this->record->id
                    ]);
                }),

            Actions\EditAction::make()
                ->label('Editar Paciente')
                ->icon('heroicon-m-pencil')
                ->color('primary'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $persona = $this->record->persona;

        if ($persona) {
            $data['primer_nombre'] = $persona->primer_nombre;
            $data['segundo_nombre'] = $persona->segundo_nombre;
            $data['primer_apellido'] = $persona->primer_apellido;
            $data['segundo_apellido'] = $persona->segundo_apellido;
            $data['dni'] = $persona->dni;
            $data['telefono'] = $persona->telefono;
            $data['direccion'] = $persona->direccion;
            $data['sexo'] = $persona->sexo;
            $data['fecha_nacimiento'] = $persona->fecha_nacimiento;
            $data['nacionalidad_id'] = $persona->nacionalidad_id;
            // Soporte para ambos nombres de campo
            $data['foto'] = $persona->foto ?? $persona->fotografia;
            $data['fotografia'] = $persona->fotografia ?? $persona->foto;
        }

        // Obtener TODAS las enfermedades del paciente
        $enfermedadesData = [];
        if ($this->record->enfermedades->isNotEmpty()) {
            foreach ($this->record->enfermedades as $enfermedad) {
                $pivot = $enfermedad->pivot;

                $enfermedadesData[] = [
                    'enfermedad_id' => $enfermedad->id,
                    'fecha_diagnostico' => $pivot->fecha_diagnostico,
                    'tratamiento' => $pivot->tratamiento,
                    'ano_diagnostico' => $pivot->fecha_diagnostico ?
                        date('Y', strtotime($pivot->fecha_diagnostico)) :
                        date('Y'),
                ];
            }

            // Para compatibilidad con el código original (primera enfermedad)
            $primeraEnfermedad = $this->record->enfermedades->first();
            $pivot = $primeraEnfermedad->pivot;

            $data['enfermedad_id'] = $primeraEnfermedad->id;
            $data['fecha_diagnostico'] = $pivot->fecha_diagnostico;
            $data['tratamiento'] = $pivot->tratamiento;
        }

        $data['enfermedades_data'] = $enfermedadesData;

        return $data;
    }
}