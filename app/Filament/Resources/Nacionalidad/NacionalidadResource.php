<?php

namespace App\Filament\Resources\Nacionalidad;

use App\Filament\Resources\Nacionalidad\NacionalidadResource\Pages;
use App\Filament\Resources\Nacionalidad\NacionalidadResource\RelationManagers;
use App\Models\Nacionalidad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Searchable;


class NacionalidadResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
    return auth()->user()?->can('crear nacionalidad');
    }

    protected static ?string $modelLabel = 'Nacionalidades';
    
    protected static ?string $navigationGroup = 'Gestión de Catálogos';

    protected static ?string $model = Nacionalidad::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 TextInput::make('nacionalidad')
                    ->label('Nacionalidad')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
               Tables\Columns\TextColumn::make('nacionalidad')
                ->label('Nacionalidad')
                ->searchable()
                ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListNacionalidads::route('/'),
            'create' => Pages\CreateNacionalidad::route('/create'),
            'edit' => Pages\EditNacionalidad::route('/{record}/edit'),
        ];
    }

     // Controlar quién puede eliminar según permiso
    public static function canDelete(
        \Illuminate\Database\Eloquent\Model $record
    ): bool {
        return auth()->user()?->can('borrar nacionalidad');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('borrar nacionalidad');
    }
}
