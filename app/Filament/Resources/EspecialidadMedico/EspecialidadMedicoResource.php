<?php

namespace App\Filament\Resources\EspecialidadMedico;

use App\Filament\Resources\EspecialidadMedico\EspecialidadMedicoResource\Pages;
use App\Models\Especialidad_Medico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EspecialidadMedicoResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = Especialidad_Medico::class;
    protected static ?string $navigationLabel = 'Especialidades y Médicos';
    protected static ?string $modelLabel = 'Especialidad y Médicos';
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('medico_id')
                    ->label('Médico')
                    ->relationship('medico', 'persona.primer_nombre')
                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                        $record->persona->primer_nombre.' '.$record->persona->primer_apellido)
                    ->searchable(['persona.primer_nombre', 'persona.primer_apellido'])
                    ->preload()
                    ->required(),
                    
                Forms\Components\Select::make('especialidad_id')
                    ->label('Especialidad')
                    ->relationship('especialidad', 'especialidad')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('medico.persona.nombre_completo')
                    ->label('Médico')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('especialidad.especialidad')
                    ->label('Especialidad')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('especialidad')
                    ->relationship('especialidad', 'especialidad')
                    ->searchable(),
                    
                Tables\Filters\SelectFilter::make('medico')
                    ->relationship('medico', 'persona.primer_nombre')
                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                        $record->persona->primer_nombre.' '.$record->persona->primer_apellido)
                    ->searchable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEspecialidadMedicos::route('/'),
            'create' => Pages\CreateEspecialidadMedico::route('/create'),
            'edit' => Pages\EditEspecialidadMedico::route('/{record}/edit'),
        ];
    }
}