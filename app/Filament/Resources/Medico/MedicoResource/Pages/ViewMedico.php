<?php

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Split;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;
use Filament\Infolists\Components\Actions\Action;

class ViewMedico extends ViewRecord
{
    protected static string $resource = MedicoResource::class;

    protected function resolveRecord(int | string $key): \App\Models\Medico
    {
        // Forzar la carga de la relación persona sin scopes globales
        return \App\Models\Medico::withoutGlobalScopes()
            ->with(['persona', 'especialidades', 'contratoActivo'])
            ->findOrFail($key);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Split::make([
                            Grid::make(1)
                                ->schema([
                                    ImageEntry::make('persona.fotografia')
                                        ->label('Fotografía')
                                        ->circular()
                                        ->size(180)
                                        ->getStateUsing(function ($record) {
                                            if ($record->persona->fotografia) {
                                                return asset('storage/' . $record->persona->fotografia);
                                            }
                                            return 'https://ui-avatars.com/api/?name=' . urlencode($record->persona->primer_nombre . ' ' . $record->persona->primer_apellido);
                                        }),
                                ])
                                ->columnSpan(1),
                            
                            Grid::make(1)
                                ->schema([
                                    TextEntry::make('persona.nombre_completo')
                                        ->label('')
                                        ->getStateUsing(fn ($record) => 
                                            $record->persona->primer_nombre . ' ' . 
                                            ($record->persona->segundo_nombre ? $record->persona->segundo_nombre . ' ' : '') .
                                            $record->persona->primer_apellido . ' ' . 
                                            ($record->persona->segundo_apellido ?? '')
                                        )
                                        ->weight(FontWeight::Bold)
                                        ->size('xl')
                                        ->color('primary'),
                                    
                                    TextEntry::make('especialidades')
                                        ->label('Especialidades Médicas')
                                        ->getStateUsing(fn ($record) => 
                                            $record->especialidades->pluck('especialidad')->join(', ')
                                        )
                                        ->badge()
                                        ->color('success')
                                        ->separator(', '),
                                    
                                    TextEntry::make('numero_colegiacion')
                                        ->label('N° de Colegiación')
                                        ->weight(FontWeight::SemiBold)
                                        ->badge()
                                        ->color('success'),
                                ])
                                ->columnSpan(2),
                        ])
                        ->from('md')
                    ])
                    ->columnSpanFull(),

                Section::make('Información Personal')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('persona.dni')
                                    ->label('DNI')
                                    ->icon('heroicon-o-identification')
                                    ->copyable()
                                    ->copyMessage('DNI copiado'),
                                
                                TextEntry::make('persona.telefono')
                                    ->label('Teléfono')
                                    ->icon('heroicon-o-phone')
                                    ->copyable()
                                    ->copyMessage('Teléfono copiado'),
                                
                                TextEntry::make('persona.sexo')
                                    ->label('Sexo')
                                    ->icon('heroicon-o-user')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'M' => 'Masculino',
                                        'F' => 'Femenino',
                                        default => $state,
                                    }),
                                
                                TextEntry::make('persona.fecha_nacimiento')
                                    ->label('Fecha de Nacimiento')
                                    ->icon('heroicon-o-cake')
                                    ->date('d/m/Y')
                                    ->suffix(fn ($record) => 
                                        ' (' . \Carbon\Carbon::parse($record->persona->fecha_nacimiento)->age . ' años)'
                                    ),
                                
                                TextEntry::make('persona.nacionalidad.nacionalidad')
                                    ->label('Nacionalidad')
                                    ->icon('heroicon-o-flag'),
                                
                                TextEntry::make('persona.direccion')
                                    ->label('Dirección')
                                    ->icon('heroicon-o-map-pin')
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->icon('heroicon-o-user-circle'),

                Section::make('Información Profesional')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('horario_entrada')
                                    ->label('Horario de Entrada')
                                    ->icon('heroicon-o-clock')
                                    ->time('g:i A')
                                    ->badge()
                                    ->color('success'),
                                
                                TextEntry::make('horario_salida')
                                    ->label('Horario de Salida')
                                    ->icon('heroicon-o-clock')
                                    ->time('g:i A')
                                    ->badge()
                                    ->color('danger'),
                                
                                TextEntry::make('duracion_jornada')
                                    ->label('Duración de Jornada')
                                    ->icon('heroicon-o-clock')
                                    ->getStateUsing(function ($record) {
                                        $entrada = \Carbon\Carbon::parse($record->horario_entrada);
                                        $salida = \Carbon\Carbon::parse($record->horario_salida);
                                        $duracion = $entrada->diffInHours($salida);
                                        return $duracion . ' horas';
                                    })
                                    ->badge()
                                    ->color('info'),
                            ])
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->icon('heroicon-o-briefcase'),

                Section::make('Información Contractual')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('contratoActivo.fecha_inicio')
                                    ->label('Fecha de Inicio')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar')
                                    ->badge()
                                    ->color('success'),
                                
                                TextEntry::make('contratoActivo.fecha_fin')
                                    ->label('Fecha de Finalización')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar')
                                    ->badge()
                                    ->color('warning'),
                                
                                TextEntry::make('contratoActivo.salario_quincenal')
                                    ->label('Salario Quincenal')
                                    ->money('HNL')
                                    ->icon('heroicon-o-banknotes')
                                    ->badge()
                                    ->color('success'),
                                
                                TextEntry::make('contratoActivo.salario_mensual')
                                    ->label('Salario Mensual')
                                    ->money('HNL')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->badge()
                                    ->color('success')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('contratoActivo.porcentaje_servicio')
                                    ->label('Porcentaje por Servicio')
                                    ->icon('heroicon-o-calculator')
                                    ->suffix('%')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('contratoActivo.activo')
                                    ->label('Estado del Contrato')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Activo' : 'Inactivo')
                                    ->icon('heroicon-o-check-circle')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                            ]),
                    ])
                    ->icon('heroicon-o-document-check')
                    ->visible(fn ($record) => $record->contratoActivo !== null)
                    ->collapsible()
                    ->collapsed()
                    ->headerActions([
                        Action::make('ver_contrato')
                            ->label('Ver Detalles Completos')
                            ->icon('heroicon-o-document-magnifying-glass')
                            ->color('primary')
                            ->url(fn ($record) => route('filament.admin.resources.contabilidad-medica.contrato-medicos.view', [
                                'record' => $record->contratoActivo->id
                            ]))
                            ->visible(fn ($record) => $record->contratoActivo !== null),
                        Action::make('ver_historial_contratos')
                            ->label('Ver Historial de Contratos')
                            ->icon('heroicon-o-clock')
                            ->color('gray')
                            ->url(fn ($record) => route('filament.admin.resources.contabilidad-medica.contrato-medicos.index', [
                                'tableFilters[medico_id][value]' => $record->id
                            ]))
                            ->visible(fn ($record) => $record->contratos()->count() > 1),
                    ]),

                Section::make('Información del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Fecha de Registro')
                                    ->icon('heroicon-o-calendar-days')
                                    ->dateTime('d/m/Y H:i')
                                    ->since(),
                                
                                TextEntry::make('updated_at')
                                    ->label('Última Actualización')
                                    ->icon('heroicon-o-arrow-path')
                                    ->dateTime('d/m/Y H:i')
                                    ->since(),
                            ])
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->icon('heroicon-o-cog-6-tooth'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
