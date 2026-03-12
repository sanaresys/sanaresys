<?php

namespace App\Filament\Resources\FacturaDetalles;

use App\Filament\Resources\FacturaDetalles\FacturaDetallesResource\Pages;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FacturaDetallesResource extends Resource
{
    protected static ?string $model = FacturaDetalle::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Gestion de Facturacion';
    protected static ?string $navigationLabel = 'Detalles de Facturas';
    protected static ?string $pluralModelLabel = 'Detalles de Facturas';
    protected static ?string $modelLabel = 'Detalle de Factura';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('factura_id')
                    ->label('Factura ID')
                    ->disabled(),
                Forms\Components\TextInput::make('servicio.nombre')
                    ->label('Servicio')
                    ->disabled(),
                Forms\Components\TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->disabled(),
                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->prefix('L.')
                    ->disabled(),
                Forms\Components\TextInput::make('impuesto_monto')
                    ->label('Impuesto')
                    ->prefix('L.')
                    ->disabled(),
                Forms\Components\TextInput::make('total_linea')
                    ->label('Total')
                    ->prefix('L.')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('factura.numero_factura')
                    ->label('Numero de Factura')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function (?string $state, FacturaDetalle $record): string {
                        if ($record->factura->usa_cai && $record->factura->caiCorrelativo) {
                            return $record->factura->caiCorrelativo->numero_factura;
                        }

                        return $record->factura->generarNumeroSinCAI();
                    })
                    ->badge()
                    ->color('primary'),
                TextColumn::make('factura.paciente.persona.nombre_completo')
                    ->label('Paciente')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('servicio.nombre')
                    ->label('Servicio')
                    ->searchable()
                    ->placeholder('Sin servicio')
                    ->weight('bold'),
                TextColumn::make('servicio.codigo')
                    ->label('Codigo')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('cantidad')
                    ->label('Cant.')
                    ->alignCenter(),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('HNL')
                    ->alignEnd(),
                TextColumn::make('impuesto_monto')
                    ->label('Impuesto')
                    ->money('HNL')
                    ->alignEnd()
                    ->color('orange'),
                TextColumn::make('total_linea')
                    ->label('Total')
                    ->money('HNL')
                    ->alignEnd()
                    ->weight('bold')
                    ->color('success'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('factura_id')
                    ->label('Factura')
                    ->options(fn (): array => Factura::query()->pluck('id', 'id')->all())
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacturaDetalles::route('/'),
            'view' => Pages\ViewFacturaDetalles::route('/{record}'),
        ];
    }
}
