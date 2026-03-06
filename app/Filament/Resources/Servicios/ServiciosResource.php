<?php

namespace App\Filament\Resources\Servicios;

use App\Filament\Resources\Servicios\ServiciosResource\Pages;
use App\Filament\Resources\Servicios\ServiciosResource\RelationManagers;
use App\Models\Servicio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiciosResource extends Resource
{
    protected static ?string $model = Servicio::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        TextInput::make('codigo')
                            ->maxLength(50)
                            ->placeholder('Se genera automáticamente si se deja vacío')
                            ->helperText('Código único del servicio'),
                            
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Consulta General, Radiografía, Laboratorio'),
                            
                        Textarea::make('descripcion')
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Descripción detallada del servicio'),
                    ])->columns(1),
                    
                Forms\Components\Section::make('Configuración de Precios')
                    ->schema([
                        TextInput::make('precio_unitario')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->placeholder('0.00'),
                            
                        Select::make('es_exonerado')
                            ->required()
                            ->options([
                                'SI' => 'Sí, está exonerado',
                                'NO' => 'No, aplica impuestos',
                            ])
                            ->default('NO')
                            ->live()
                            ->helperText('¿Este servicio está exonerado de impuestos?'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Configuración de Impuestos')
                    ->schema([
                        Select::make('impuesto_id')
                            ->relationship('impuesto', 'nombre')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->hidden(fn (Forms\Get $get): bool => $get('es_exonerado') === 'SI')
                            ->helperText('Seleccionar impuesto a aplicar'),
                    ])->columns(1)
                    ->hidden(fn (Forms\Get $get): bool => $get('es_exonerado') === 'SI'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                    
                TextColumn::make('descripcion')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('precio_unitario')
                    ->money('HNL')
                    ->sortable()
                    ->alignEnd(),
                    
                TextColumn::make('impuesto.nombre')
                    ->placeholder('Sin impuesto')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(function (Servicio $record): string {
                        if ($record->impuesto) {
                            return $record->impuesto->nombre . ' (' . $record->impuesto->porcentaje . '%)';
                        }
                        return 'Sin impuesto';
                    }),
                    
                TextColumn::make('es_exonerado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SI' => 'warning',
                        'NO' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'SI' => 'Exonerado',
                        'NO' => 'Con Impuesto',
                    }),
                    
                TextColumn::make('precio_con_impuesto')
                    ->getStateUsing(function (Servicio $record): string {
                        $impuesto = $record->calcularImpuesto();
                        $total = $record->precio_unitario + $impuesto;
                        return 'L. ' . number_format($total, 2);
                    })
                    ->label('Precio Final')
                    ->alignEnd()
                    ->color('success'),
                    
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('es_exonerado')
                    ->options([
                        'SI' => 'Exonerados',
                        'NO' => 'Con Impuesto',
                    ]),
                    
                SelectFilter::make('impuesto_id')
                    ->relationship('impuesto', 'nombre')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('codigo', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServicios::route('/'),
            'create' => Pages\CreateServicios::route('/create'),
            'edit' => Pages\EditServicios::route('/{record}/edit'),
        ];
    }
}
