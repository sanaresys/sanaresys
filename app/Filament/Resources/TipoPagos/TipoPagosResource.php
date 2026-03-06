<?php

namespace App\Filament\Resources\TipoPagos;

use App\Filament\Resources\TipoPagos\TipoPagosResource\Pages;
use App\Filament\Resources\TipoPagos\TipoPagosResource\RelationManagers;
use App\Models\TipoPago;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\TrashedFilter;

class TipoPagosResource extends Resource
{
    protected static ?string $model = TipoPago::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Gestión de Facturación';

    protected static ?string $modelLabel = 'Tipo Pago';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información básica')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\Textarea::make('descripcion')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Auditoría')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Creado')
                            ->content(fn (?TipoPago $record) => $record?->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Actualizado')
                            ->content(fn (?TipoPago $record) => $record?->updated_at?->diffForHumans()),
                    ])
                    ->columns(2)
                    ->hidden(fn (?TipoPago $record) => $record === null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('descripcion')->searchable()->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),                                // mostrar/ocultar eliminados
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),         // si deseas eliminación definitiva
                Tables\Actions\RestoreBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
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
            'index' => Pages\ListTipoPagos::route('/'),
            'create' => Pages\CreateTipoPagos::route('/create'),
            'edit' => Pages\EditTipoPagos::route('/{record}/edit'),
        ];
    }
}
