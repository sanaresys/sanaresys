<?php

namespace App\Filament\Resources\ContabilidadMedica\DetalleNominaResource\Pages;

use App\Filament\Resources\ContabilidadMedica\DetalleNominaResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ViewDetalleNomina extends ViewRecord
{
    protected static string $resource = DetalleNominaResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información de la Nómina')
                    ->icon('heroicon-o-clipboard-document')
                    ->schema([
                        TextEntry::make('nomina.empresa')
                            ->label('Empresa'),

                        TextEntry::make('nomina.mes')
                            ->label('Mes')
                            ->formatStateUsing(function ($state) {
                                $meses = [
                                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                                ];
                                return $meses[(int)$state] ?? $state;
                            }),

                        TextEntry::make('nomina.año')
                            ->label('Año'),

                        TextEntry::make('nomina.tipo_pago')
                            ->label('Tipo de Pago')
                            ->formatStateUsing(fn ($state) => ucfirst($state)),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Datos del Médico')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextEntry::make('medico_nombre')
                            ->label('Nombre del Médico'),

                        TextEntry::make('medico.persona.telefono')
                            ->label('Teléfono')
                            ->default('N/A'),

                        TextEntry::make('medico.persona.email')
                            ->label('Email')
                            ->default('N/A'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Detalle de Pagos')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        TextEntry::make('salario_base')
                            ->label('Salario Base')
                            ->money('HNL'),

                        TextEntry::make('percepciones')
                            ->label('Percepciones')
                            ->money('HNL')
                            ->color('success'),

                        TextEntry::make('deducciones')
                            ->label('Deducciones')
                            ->money('HNL')
                            ->color('danger'),

                        TextEntry::make('total_pagar')
                            ->label('Total a Pagar')
                            ->money('HNL')
                            ->color('success')
                            ->weight('bold'),

                        TextEntry::make('percepciones_detalle')
                            ->label('Detalle de Percepciones')
                            ->default('N/A')
                            ->columnSpanFull(),

                        TextEntry::make('deducciones_detalle')
                            ->label('Detalle de Deducciones')
                            ->default('N/A')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
