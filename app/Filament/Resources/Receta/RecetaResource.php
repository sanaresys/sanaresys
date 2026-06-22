<?php

namespace App\Filament\Resources\Receta;

use App\Filament\Resources\Receta\RecetaResource\Pages;
use Filament\Forms\Components\Select;
use App\Models\Receta;
use App\Models\Paciente;
use App\Models\Consulta;
use App\Models\Medico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\InfolistsServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;


class RecetaResource extends Resource
{
    protected static ?string $model = Receta::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Recetas';

    protected static ?string $modelLabel = 'Receta';

    protected static ?string $pluralModelLabel = 'Recetas';

    protected static ?string $navigationGroup = 'Panel Diario';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // El campo centro_id se asigna automáticamente y se oculta
                Forms\Components\Hidden::make('centro_id')
                    ->default(fn () => \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->centro_id : null),

                Forms\Components\Section::make('Información de la Receta')
                    ->schema([
                        Forms\Components\DatePicker::make('fecha_receta')
                            ->label('Fecha de la Receta')
                            ->default(now()->format('Y-m-d'))
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('paciente_id')
                            ->label('Paciente')
                            ->options(function () {
                                return ['' => 'Seleccionar'] + \App\Models\Pacientes::with('persona')->get()->filter(function ($p) {
                                    return $p->persona !== null;
                                })->mapWithKeys(function ($p) {
                                    $nombre = $p->persona->primer_nombre . ' ' .
                                             ($p->persona->segundo_nombre ? $p->persona->segundo_nombre . ' ' : '') .
                                             $p->persona->primer_apellido . ' ' .
                                             ($p->persona->segundo_apellido ? $p->persona->segundo_apellido : '');
                                    return [$p->id => trim($nombre)];
                                })->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('medico_id')
                            ->label('Médico')
                            ->options(function () {
                                return ['' => 'Seleccionar'] + \App\Models\Medico::withoutGlobalScopes()->with('persona')->get()->filter(function ($m) {
                                    return $m->persona !== null;
                                })->mapWithKeys(function ($m) {
                                    $nombre = $m->persona->primer_nombre . ' ' .
                                             ($m->persona->segundo_nombre ? $m->persona->segundo_nombre . ' ' : '') .
                                             $m->persona->primer_apellido . ' ' .
                                             ($m->persona->segundo_apellido ? $m->persona->segundo_apellido : '');
                                    return [$m->id => trim($nombre)];
                                })->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('consulta_id')
                            ->label('Consulta')
                            ->options(function () {
                                return \App\Models\Consulta::with(['paciente.persona'])
                                    ->orderBy('created_at', 'desc')
                                    ->get()
                                    ->mapWithKeys(function ($consulta) {
                                        if ($consulta->paciente && $consulta->paciente->persona) {
                                            $pacienteNombre = $consulta->paciente->persona->primer_nombre . ' ' .
                                                             ($consulta->paciente->persona->segundo_nombre ? $consulta->paciente->persona->segundo_nombre . ' ' : '') .
                                                             $consulta->paciente->persona->primer_apellido . ' ' .
                                                             ($consulta->paciente->persona->segundo_apellido ? $consulta->paciente->persona->segundo_apellido : '');
                                            $pacienteNombre = trim($pacienteNombre);
                                        } else {
                                            $pacienteNombre = 'Sin paciente';
                                        }

                                        $fechaFormateada = Carbon::parse($consulta->created_at)->format('d/m/Y');

                                        return [$consulta->id => "Consulta #{$consulta->id} - {$fechaFormateada} ({$pacienteNombre})"];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Seleccionar consulta relacionada (opcional)')
                            ->helperText('Seleccione una consulta para asociar esta receta con una consulta específica.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detalles de la Receta')
                    ->schema([
                        Forms\Components\Textarea::make('medicamentos')
                            ->label('Medicamentos')
                            ->required()
                            ->rows(4)
                            ->placeholder('Ej: Paracetamol 500mg - 1 tableta cada 8 horas por 5 días')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('indicaciones')
                            ->label('Indicaciones')
                            ->required()
                            ->rows(4)
                            ->placeholder('Instrucciones especiales para el paciente...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('paciente_nombre')
                    ->label('Paciente')
                    ->state(function (Receta $record): string {
                        if ($record->paciente && $record->paciente->persona) {
                            return $record->paciente->persona->nombre_completo;
                        }
                        return 'N/A';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('paciente.persona', function (Builder $subQuery) use ($search) {
                            $subQuery->where('primer_nombre', 'like', "%{$search}%")
                                     ->orWhere('segundo_nombre', 'like', "%{$search}%")
                                     ->orWhere('primer_apellido', 'like', "%{$search}%")
                                     ->orWhere('segundo_apellido', 'like', "%{$search}%")
                                     ->orWhere('dni', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('medico_nombre')
                    ->label('Médico')
                    ->state(function (Receta $record): string {
                        if ($record->medico && $record->medico->persona) {
                            return $record->medico->persona->nombre_completo;
                        }

                        // Si no se cargó la relación, intentar cargarla manualmente
                        if ($record->medico_id) {
                            $medico = \App\Models\Medico::withoutGlobalScopes()->with('persona')->find($record->medico_id);
                            if ($medico && $medico->persona) {
                                return $medico->persona->nombre_completo;
                            }
                        }

                        return 'N/A';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('medico.persona', function (Builder $subQuery) use ($search) {
                            $subQuery->where('primer_nombre', 'like', "%{$search}%")
                                     ->orWhere('segundo_nombre', 'like', "%{$search}%")
                                     ->orWhere('primer_apellido', 'like', "%{$search}%")
                                     ->orWhere('segundo_apellido', 'like', "%{$search}%")
                                     ->orWhere('dni', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('consulta_id')
                    ->label('Consulta')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_receta')
                    ->label('Fecha Receta')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('medicamentos')
                    ->label('Medicamentos')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('indicaciones')
                    ->label('Indicaciones')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                // Eliminar o comentar las columnas de fechas en la tabla
                // Tables\Columns\TextColumn::make('created_at')
                //     ->label('Fecha de Creación')
                //     ->dateTime('d/m/Y H:i')
                //     ->sortable()
                //     ->toggleable(),

                // Tables\Columns\TextColumn::make('updated_at')
                //     ->label('Última Actualización')
                //     ->dateTime('d/m/Y H:i')
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('medico_id')
                    ->label('Médico')
                    ->options(function() {
                        // Multi-tenant: los médicos ya están filtrados por el tenant
                        return \App\Models\Medico::withoutGlobalScopes()
                            ->with('persona')
                            ->get()
                            ->mapWithKeys(function($medico) {
                                if ($medico->persona) {
                                    return [$medico->id => $medico->persona->nombre_completo];
                                }
                                return [];
                            });
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('paciente_id')
                    ->label('Paciente')
                    ->options(function() {
                        // Multi-tenant: los pacientes ya están filtrados por el tenant
                        return \App\Models\Pacientes::withoutGlobalScopes()
                            ->with('persona')
                            ->get()
                            ->mapWithKeys(function($paciente) {
                                if ($paciente->persona) {
                                    return [$paciente->id => $paciente->persona->nombre_completo];
                                }
                                return [];
                            });
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('imprimir')
                    ->label('Imprimir Receta')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Receta $record): string => route('recetas.imprimir', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información General')
                    ->schema([
                        Infolists\Components\TextEntry::make('fecha_receta')
                            ->label('Fecha de la Receta')
                            ->date('d/m/Y'),
                        Infolists\Components\TextEntry::make('paciente_nombre')
                            ->label('Paciente')
                            ->state(function (Receta $record): string {
                                if ($record->paciente && $record->paciente->persona) {
                                    return $record->paciente->persona->nombre_completo;
                                }
                                return 'No disponible';
                            }),
                        Infolists\Components\TextEntry::make('medico_nombre')
                            ->label('Médico')
                            ->state(function (Receta $record): string {
                                if ($record->medico && $record->medico->persona) {
                                    return $record->medico->persona->nombre_completo;
                                }

                                // Si no se cargó la relación, intentar cargarla manualmente
                                if ($record->medico_id) {
                                    $medico = \App\Models\Medico::withoutGlobalScopes()->with('persona')->find($record->medico_id);
                                    if ($medico && $medico->persona) {
                                        return $medico->persona->nombre_completo;
                                    }
                                }

                                return 'No disponible (Médico ID: ' . ($record->medico_id ?? 'null') . ')';
                            }),
                        Infolists\Components\TextEntry::make('consulta_id')
                            ->label('Consulta')
                            ->formatStateUsing(fn ($state) => $state ? "Consulta #{$state}" : 'Sin consulta asociada'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Detalles de la Receta')
                    ->schema([
                        Infolists\Components\TextEntry::make('medicamentos')
                            ->label('Medicamentos')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('indicaciones')
                            ->label('Indicaciones')
                            ->columnSpanFull(),
                    ]),

                // Eliminar o comentar las entradas de fechas en los infolists
                // Infolists\Components\Section::make('Información del Sistema')
                //     ->schema([
                //         Infolists\Components\TextEntry::make('created_at')
                //             ->label('Fecha de Creación')
                //             ->dateTime('d/m/Y H:i:s'),
                //         Infolists\Components\TextEntry::make('updated_at')
                //             ->label('Última Actualización')
                //             ->dateTime('d/m/Y H:i:s'),
                //     ])
                //     ->columns(2),
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
            'index' => Pages\ListRecetas::route('/'),
            'create' => Pages\CreateRecetaWithPatientSearch::route('/create'),
            'create-simple' => Pages\CreateReceta::route('/create-simple'),
            'edit' => Pages\EditReceta::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['paciente.persona', 'medico.persona', 'consulta'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $user = \Illuminate\Support\Facades\Auth::user();

        // Si el usuario es un médico, solo mostrar sus recetas
        if ($user) {
            // Primero intentar con la relación directa
            if ($user->medico) {
                $query->where('medico_id', $user->medico->id);
            }
            // Si no tiene relación directa, buscar por persona_id
            elseif ($user->persona_id) {
                $medico = \App\Models\Medico::withoutGlobalScopes()
                    ->where('persona_id', $user->persona_id)
                    ->first();
                    
                if ($medico) {
                    $query->where('medico_id', $medico->id);
                }
            }
        }

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        return null;
    }
}
