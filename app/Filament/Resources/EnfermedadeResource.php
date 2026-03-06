<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnfermedadeResource\Pages;
use App\Filament\Resources\EnfermedadeResource\RelationManagers;
use App\Models\Enfermedade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;

class EnfermedadeResource extends Resource
{
    protected static ?string $model = Enfermedade::class;

    protected static ?string $navigationGroup = 'Gestión de Enfermedades';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('enfermedades')
                    ->label('Enfermedad')
                    ->required()
                    ->maxLength(255),
                
                // NO incluir los campos created_by, updated_by, deleted_by
                // porque el modelo los llena automáticamente en el método boot()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('enfermedades')
                    ->label('Enfermedad')
                    ->searchable()
                    
                    ->sortable(),
                
                
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListEnfermedades::route('/'),
            'create' => Pages\CreateEnfermedade::route('/create'),
            'view' => Pages\ViewEnfermedade::route('/{record}'),
            'edit' => Pages\EditEnfermedade::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}