<?php

namespace App\Filament\Resources\CentroMedicoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicosRelationManager extends RelationManager
{
    protected static string $relationship = 'medicos';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('medico_id')
                ->relationship('medico', 'id')
                ->label('Médico')
                ->searchable()
                ->required(),

            Forms\Components\Textarea::make('horario')
                ->label('Horario')
                ->rows(3)
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('medico.id')->label('ID Médico'),
            Tables\Columns\TextColumn::make('horario')->label('Horario')->limit(50),
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }
}
