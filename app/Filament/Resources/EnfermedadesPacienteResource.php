<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnfermedadesPacienteResource\Pages;
use App\Filament\Resources\EnfermedadesPacienteResource\RelationManagers;
use App\Models\Enfermedades__Paciente;
use App\Models\Pacientes;
use App\Models\Enfermedade;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;



class EnfermedadesPacienteResource extends Resource
{
     protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = Enfermedades__Paciente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Configuracion';
    
    protected static ?string $navigationLabel = 'Enfermedades de Pacientes';
    protected static ?string $modelLabel = 'Enfermedad de Paciente';
    protected static ?string $pluralModelLabel = 'Enfermedades de Pacientes';

   public static function shouldRegisterNavigation(): bool
{
    return false && auth()->user()->can('ver enfermedades_pacientes'); // Siempre será false
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('paciente_id')
                    ->label('Paciente')
                    ->relationship('paciente', 'id', function ($query) {
                        // Multi-tenant: los pacientes ya están filtrados por el tenant
                        return $query->whereHas('persona');
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->persona->nombre_completo)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Seleccionar paciente'),
                    
                Forms\Components\Select::make('enfermedad_id')
                    ->label('Enfermedad')
                    ->relationship('enfermedad', 'enfermedades')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Seleccionar enfermedad'),
                    
                Forms\Components\DatePicker::make('fecha_diagnostico')
                    ->label('Fecha de Diagnóstico')
                    ->default(now())
                    ->required(),
                    
                Forms\Components\Textarea::make('tratamiento')
                    ->label('Tratamiento')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paciente_nombre')
                    ->label('Paciente')
                    ->getStateUsing(fn ($record) => $record->paciente->persona->nombre_completo)
                    ->searchable()
                    ->sortable(false),
                    
                Tables\Columns\TextColumn::make('enfermedad.enfermedades')
                    ->label('Enfermedad')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('fecha_diagnostico')
                    ->label('Fecha de Diagnóstico')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('tratamiento')
                    ->label('Tratamiento')
                    ->limit(50)
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Creado por')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('fecha_diagnostico')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_desde')
                            ->label('Fecha desde'),
                        Forms\Components\DatePicker::make('fecha_hasta')
                            ->label('Fecha hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_diagnostico', '>=', $date),
                            )
                            ->when(
                                $data['fecha_hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_diagnostico', '<=', $date),
                            );
                    }),
                    
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
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
            'index' => Pages\ListEnfermedadesPacientes::route('/'),
            'create' => Pages\CreateEnfermedadesPaciente::route('/create'),
            'edit' => Pages\EditEnfermedadesPaciente::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        // Multi-tenant: los datos ya están filtrados por el tenant
        return parent::getEloquentQuery()
            ->with(['paciente.persona', 'enfermedad', 'createdBy', 'updatedBy'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}