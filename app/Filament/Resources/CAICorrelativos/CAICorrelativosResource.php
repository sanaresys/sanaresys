<?php

namespace App\Filament\Resources\CAICorrelativos;

use App\Filament\Resources\CAICorrelativos\CAICorrelativosResource\Pages;
use App\Models\CAI_Correlativos;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class CAICorrelativosResource extends Resource
{
    protected static ?string $model = CAI_Correlativos::class;

    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Gestión de Facturación';
    protected static ?string $navigationLabel = 'Correlativos CAI';

    /* -----------------------------------------------------------------------
     * Solo lectura – no requerimos formulario de edición
     * -------------------------------------------------------------------- */
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([]);      // vacío
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_factura')
                    ->label('Nº Factura')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('numero_correlativo')
                    ->label('Correlativo')
                    ->formatStateUsing(fn (int $state): string => str_pad($state, 9, '0', STR_PAD_LEFT))
                    ->badge()
                    ->color('success'),

                TextColumn::make('autorizacion.cai_codigo')
                    ->label('CAI')
                    ->searchable()
                    ->limit(18)
                    ->tooltip(fn ($record) => $record->autorizacion?->cai_codigo ?? 'N/A'),

                TextColumn::make('fecha_emision')
                    ->dateTime('d/m/Y H:i')
                    ->label('Emitido')
                    ->sortable(),

                TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('centro.nombre_centro')
                    ->label('Centro')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('autorizacion_id')
                    ->label('CAI')
                    ->relationship('autorizacion', 'cai_codigo')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('fecha_emision', 'desc')
            ->emptyStateHeading('Sin correlativos aún')
            ->emptyStateDescription('Crea tu primera factura con CAI para que aparezca aquí.')
            ->emptyStateIcon('heroicon-o-document-plus');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCAICorrelativos::route('/'),
            'view'  => Pages\ViewCAICorrelativos::route('/{record}'),
            'create' => Pages\CreateCAICorrelativos::route('/create'),
            'edit'   => Pages\EditCAICorrelativos::route('/{record}/edit'),
        ];
    }
}
