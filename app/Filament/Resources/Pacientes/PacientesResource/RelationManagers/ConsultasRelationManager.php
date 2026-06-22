<?php

namespace App\Filament\Resources\Pacientes\PacientesResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Medico;

class ConsultasRelationManager extends RelationManager
{
    protected static string $relationship = 'consultas';

    protected static ?string $title = 'Historial de Consultas';

    protected static ?string $modelLabel = 'Consulta';

    protected static ?string $pluralModelLabel = 'Consultas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('info')
                    ->content('Para editar consultas, utilice el módulo de Consultas.')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Consulta')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('medico.persona.nombre_completo')
                    ->label('Médico')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('diagnostico')
                    ->label('Diagnóstico')
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->diagnostico;
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('tratamiento')
                    ->label('Tratamiento')
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->tratamiento;
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->observaciones;
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('medico_id')
                    ->label('Médico')
                    ->options(function () {
                        return Medico::with('persona')->get()->filter(function ($m) {
                            return $m->persona !== null;
                        })->mapWithKeys(function ($m) {
                            return [$m->id => $m->persona->nombre_completo];
                        })->toArray();
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                // No permitir crear desde aquí
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver Detalles'),
            ])
            ->bulkActions([
                // No permitir acciones en lote
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Sin consultas registradas')
            ->emptyStateDescription('Este paciente aún no tiene consultas médicas registradas.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información General')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha de Consulta')
                                    ->dateTime(),

                                Infolists\Components\TextEntry::make('medico.persona.nombre_completo')
                                    ->label('Médico'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Detalles de Consulta')
                    ->schema([
                        Infolists\Components\Section::make('Diagnóstico')
                            ->schema([
                                Infolists\Components\TextEntry::make('diagnostico')
                                    ->hiddenLabel()
                                    ->placeholder('Sin diagnóstico registrado')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin diagnóstico registrado')
                                    ->copyable()
                                    ->extraAttributes([
                                        'style' => 'white-space: pre-line; text-align: left; word-wrap: break-word; max-height: 200px; overflow-y: auto; padding: 12px; border-radius: 6px; border: 1px solid; line-height: 1.6;',
                                        'class' => 'bg-gray-50 border-gray-200 text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100'
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(false),

                        Infolists\Components\Section::make('Tratamiento')
                            ->schema([
                                Infolists\Components\TextEntry::make('tratamiento')
                                    ->hiddenLabel()
                                    ->placeholder('Sin tratamiento registrado')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin tratamiento registrado')
                                    ->copyable()
                                    ->extraAttributes([
                                        'style' => 'white-space: pre-line; text-align: left; word-wrap: break-word; max-height: 200px; overflow-y: auto; padding: 12px; border-radius: 6px; border: 1px solid; line-height: 1.6;',
                                        'class' => 'bg-gray-50 border-gray-200 text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100'
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(false),

                        Infolists\Components\Section::make('Observaciones')
                            ->schema([
                                Infolists\Components\TextEntry::make('observaciones')
                                    ->hiddenLabel()
                                    ->placeholder('Sin observaciones registradas')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin observaciones registradas')
                                    ->copyable()
                                    ->extraAttributes([
                                        'style' => 'white-space: pre-line; text-align: left; word-wrap: break-word; max-height: 200px; overflow-y: auto; padding: 12px; border-radius: 6px; border: 1px solid; line-height: 1.6;',
                                        'class' => 'bg-gray-50 border-gray-200 text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100'
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(false),
                    ]),
            ]);
    }
}
