<?php
namespace App\Filament\Resources\ExamenesResource;

use App\Models\Examenes;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table as FilamentTable;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;
// **aliasamos** el namespace de Pages para poder usar Pages\ListExamenes etc.
use App\Filament\Resources\ExamenesResource\Pages as Pages;
use App\Models\Medico;
use App\Models\Pacientes;
use App\Models\Consulta;


class ExamenesResource extends Resource
{
    protected static ?string $model            = Examenes::class;
    protected static ?string $navigationLabel  = 'Exámenes';
    protected static ?string $navigationGroup  = 'Gestión Médica';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $navigationIcon   = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 Select::make('paciente_id')
                ->label('Paciente')
                ->options(function () {
                    return Pacientes::with('persona')
                        ->get()
                        ->mapWithKeys(fn ($p) => [
                            $p->id => "{$p->persona->primer_nombre} {$p->persona->primer_apellido}",
                        ])
                        ->toArray();
                })
                ->searchable()
                ->required(),

             Select::make('consulta_id')
    ->label('Consulta')
    ->options(function () {
        return Consulta::with(['cita', 'paciente.persona'])
            ->get()
            ->mapWithKeys(function ($c) {
                // Verificar si existe la relación cita y si fecha es un objeto Carbon
                $fecha = optional($c->cita)->fecha;
                $fechaFormateada = $fecha instanceof \Carbon\Carbon 
                    ? $fecha->format('d/m/Y') 
                    : 'Sin fecha';
                
                return [
                    $c->id => "Consulta #{$c->id} – {$fechaFormateada} ({$c->paciente->persona->primer_nombre})",
                ];
            })
            ->toArray();
    })
    ->searchable()
    ->required(),

                Select::make('medico_id')
                    ->label('Médico')
                    ->options(function () {
                        return Medico::with('persona')
                            ->get()
                            ->mapWithKeys(fn($m) => [
                                $m->id => "{$m->persona->primer_nombre} {$m->persona->primer_apellido}",
                            ]);
                    })
                    ->searchable()
                    ->required(),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(4)
                    ->required(),

                TextInput::make('url_archivo')
                    ->label('URL archivo')
                    ->url()
                    ->nullable(),

                DatePicker::make('fecha_resultado')
                    ->label('Fecha de resultado')
                    ->nullable(),
            ]);
    }

    public static function table(FilamentTable $table): FilamentTable
    {
        return $table
            ->columns([
                TextColumn::make('paciente.persona.primer_nombre')
                    ->label('Paciente'),
                TextColumn::make('medico.persona.primer_nombre')
                    ->label('Médico'),
                TextColumn::make('consulta.id')
                    ->label('Consulta'),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('fecha_resultado')
                    ->label('Fecha resultado')
                    ->date(),
            ])
            ->filters([
                SelectFilter::make('paciente_id')
                    ->label('Filtrar por paciente')
                    ->relationship('paciente.persona', 'primer_nombre'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
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
            'index'  => Pages\ListExamenes::route('/'),
            'create' => Pages\CreateExamenes::route('/create'),
            'edit'   => Pages\EditExamenes::route('/{record}/edit'),
        ];
    }
}
