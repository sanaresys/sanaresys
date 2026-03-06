<?php

namespace App\Filament\Resources\CentrosMedico;

use App\Filament\Resources\CentrosMedico\CentrosMedicoResource\Pages;
use App\Filament\Resources\CentrosMedico\CentrosMedicoResource\RelationManagers;
use App\Models\Centros_Medico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CentrosMedicoResource extends Resource
{
    protected static ?string $navigationGroup = 'Gestión de Centros Médicos';
    protected static ?string $model = Centros_Medico::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $modelLabel = 'Centro Médico';
    
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('root');
    }

    

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre_centro')->label('Nombre Centro Médico')->required()->maxLength(255)->unique(),
            Forms\Components\TextInput::make('direccion')->label('Dirección')->required()->maxLength(255),
            Forms\Components\TextInput::make('telefono')->label('Teléfono')->required()->tel(),
            Forms\Components\TextInput::make('rtn')->label('RTN')->required()->maxLength(100)->unique(),
            Forms\Components\FileUpload::make('fotografia')->label('Fotografía')->image()->directory('centros_medicos'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nombre_centro')->label('Nombre Centro Médico')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('direccion')->label('Dirección')->limit(30),
            Tables\Columns\TextColumn::make('telefono')->label('Teléfono'),
            Tables\Columns\TextColumn::make('rtn')->label('RTN')->limit(50),
            Tables\Columns\ImageColumn::make('fotografia')->label('Fotografía')->circular(),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(), 
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);

    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public function medicos()
    {
        return $this->belongsToMany(
            \App\Models\Medico::class,
            'centros_medicos_medico',
            'centro_medico_id',
            'medico_id'
        )->withPivot('horario')->withTimestamps();
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCentrosMedicos::route('/'),
            'create' => Pages\CreateCentrosMedico::route('/create'),
            'edit' => Pages\EditCentrosMedico::route('/{record}/edit'),
        ];
    }
    
}
