<?php

namespace App\Filament\Resources\Descuentos;

use App\Filament\Resources\Descuentos\DescuentosResource\Pages;
use App\Filament\Resources\Descuentos\DescuentosResource\RelationManagers;
use App\Models\Descuento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DescuentosResource extends Resource
{
    protected static ?string $model = Descuento::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Descuento')
                    ->schema([
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Descuento Adulto Mayor, Promoción Navidad'),
                            
                        Select::make('tipo')
                            ->required()
                            ->options([
                                'PORCENTAJE' => 'Porcentaje',
                                'MONTO' => 'Monto Fijo',
                            ])
                            ->live()
                            ->helperText('Tipo de descuento a aplicar'),
                            
                        TextInput::make('valor')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->suffix(fn (Forms\Get $get): string => 
                                $get('tipo') === 'PORCENTAJE' ? '%' : 'L.'
                            )
                            ->placeholder(fn (Forms\Get $get): string => 
                                $get('tipo') === 'PORCENTAJE' ? 'Ej: 15.00' : 'Ej: 100.00'
                            ),
                            
                        Select::make('activo')
                            ->required()
                            ->options([
                                'SI' => 'Activo',
                                'NO' => 'Inactivo',
                            ])
                            ->default('SI'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Vigencia del Descuento')
                    ->schema([
                        DatePicker::make('aplica_desde')
                            ->required()
                            ->default(now())
                            ->helperText('Fecha desde la cual aplica el descuento'),
                            
                        DatePicker::make('aplica_hasta')
                            ->nullable()
                            ->helperText('Dejar vacío si no tiene fecha de vencimiento'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PORCENTAJE' => 'info',
                        'MONTO' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'PORCENTAJE' => 'Porcentaje',
                        'MONTO' => 'Monto Fijo',
                    }),
                    
                TextColumn::make('valor')
                    ->formatStateUsing(function (Descuento $record): string {
                        if ($record->tipo === 'PORCENTAJE') {
                            return $record->valor . '%';
                        }
                        return 'L. ' . number_format($record->valor, 2);
                    })
                    ->sortable()
                    ->alignEnd(),
                    
                TextColumn::make('aplica_desde')
                    ->date()
                    ->sortable(),
                    
                TextColumn::make('aplica_hasta')
                    ->date()
                    ->sortable()
                    ->placeholder('Sin vencimiento'),
                    
                TextColumn::make('activo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SI' => 'success',
                        'NO' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'SI' => 'Activo',
                        'NO' => 'Inactivo',
                    }),
                    
                TextColumn::make('estado_vigencia')
                    ->badge()
                    ->getStateUsing(function (Descuento $record): string {
                        if ($record->activo === 'NO') {
                            return 'inactivo';
                        }
                        
                        $hoy = now()->toDateString();
                        if ($record->aplica_desde > $hoy) {
                            return 'pendiente';
                        }
                        if ($record->aplica_hasta && $record->aplica_hasta < $hoy) {
                            return 'vencido';
                        }
                        return 'vigente';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'vigente' => 'success',
                        'pendiente' => 'warning',
                        'vencido' => 'danger',
                        'inactivo' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'vigente' => 'Vigente',
                        'pendiente' => 'Pendiente',
                        'vencido' => 'Vencido',
                        'inactivo' => 'Inactivo',
                    }),
                    
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->options([
                        'PORCENTAJE' => 'Porcentaje',
                        'MONTO' => 'Monto Fijo',
                    ]),
                    
                SelectFilter::make('activo')
                    ->options([
                        'SI' => 'Activo',
                        'NO' => 'Inactivo',
                    ]),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListDescuentos::route('/'),
            'create' => Pages\CreateDescuentos::route('/create'),
            'edit' => Pages\EditDescuentos::route('/{record}/edit'),
        ];
    }
}
