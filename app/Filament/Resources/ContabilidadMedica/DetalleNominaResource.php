<?php

namespace App\Filament\Resources\ContabilidadMedica;

use App\Filament\Resources\ContabilidadMedica\DetalleNominaResource\Pages;
use App\Models\ContabilidadMedica\DetalleNomina;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class DetalleNominaResource extends Resource
{
    protected static ?string $model = DetalleNomina::class;

    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Historial de Pagos';
    protected static ?string $modelLabel = 'Historial de Pago';
    protected static ?string $pluralModelLabel = 'Historial de Pagos';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('nomina_id')
                    ->label('Nómina')
                    ->relationship('nomina', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "Nómina {$record->mes}/{$record->año} - {$record->empresa}")
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('medico_id')
                    ->label('Médico')
                    ->relationship('medico', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->persona->nombre_completo ?? 'Sin nombre')
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('medico_nombre')
                    ->label('Nombre del Médico')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('salario_base')
                    ->label('Salario Base')
                    ->numeric()
                    ->prefix('L.')
                    ->required(),

                Forms\Components\TextInput::make('deducciones')
                    ->label('Deducciones')
                    ->numeric()
                    ->prefix('L.')
                    ->default(0),

                Forms\Components\TextInput::make('percepciones')
                    ->label('Percepciones')
                    ->numeric()
                    ->prefix('L.')
                    ->default(0),

                Forms\Components\Textarea::make('deducciones_detalle')
                    ->label('Detalle de Deducciones')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('percepciones_detalle')
                    ->label('Detalle de Percepciones')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomina.empresa')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nomina.mes')
                    ->label('Mes')
                    ->formatStateUsing(function ($state) {
                        $meses = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                        ];
                        return $meses[(int)$state] ?? $state;
                    })
                    ->sortable(),

                TextColumn::make('nomina.año')
                    ->label('Año')
                    ->sortable(),

                TextColumn::make('medico_nombre')
                    ->label('Médico')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('salario_base')
                    ->label('Salario Base')
                    ->money('HNL')
                    ->color('sky')
                    ->sortable(),

                TextColumn::make('deducciones')
                    ->label('Deducciones')
                    ->money('HNL')
                    ->color('rose'),

                TextColumn::make('percepciones')
                    ->label('Percepciones')
                    ->money('HNL')
                    ->color('emerald'),

                TextColumn::make('total_pagar')
                    ->label('Total a Pagar')
                    ->money('HNL')
                    ->badge()
                    ->color('emerald')
                    ->weight('bold'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('nomina.año')
                    ->label('Año')
                    ->relationship('nomina', 'año')
                    ->options(function () {
                        $currentYear = date('Y');
                        $years = [];
                        for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),

                Tables\Filters\SelectFilter::make('nomina.mes')
                    ->label('Mes')
                    ->relationship('nomina', 'mes')
                    ->options([
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('sky'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['nomina', 'medico.persona']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetalleNominas::route('/'),
            'view' => Pages\ViewDetalleNomina::route('/{record}'),
        ];
    }

    // No permitir crear registros directamente
    public static function canCreate(): bool
    {
        return false;
    }
}
