<?php

namespace App\Filament\Resources\Examenes;

use App\Filament\Resources\Examenes\ExamenesResource\Pages;
use App\Models\Examenes;
use App\Models\Medico;
use App\Models\Pacientes;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;

class ExamenesResource extends Resource
{
    protected static ?string $model = Examenes::class;

    protected static ?string $navigationLabel = 'Reportes de Exámenes';
    protected static ?string $modelLabel = 'Examen';
    protected static ?string $navigationGroup = 'Gestión Médica';
    protected static ?string $pluralModelLabel = 'Exámenes';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // CAMPOS OCULTOS PARA DATOS AUTOMÁTICOS
                Forms\Components\Hidden::make('centro_id')
                    ->default(fn () => \Illuminate\Support\Facades\Auth::user()->centro_id),

                Forms\Components\Hidden::make('paciente_id'),
                Forms\Components\Hidden::make('consulta_id'),
                Forms\Components\Hidden::make('medico_id'),

                // INFORMACIÓN DE CONTEXTO (SOLO LECTURA)
                Forms\Components\Section::make('Información de la Consulta')
                    ->schema([
                        Forms\Components\Placeholder::make('paciente_info')
                            ->label('👤 Paciente')
                            ->content(function ($get) {
                                $pacienteId = $get('paciente_id') ?? request()->get('paciente_id');
                                if ($pacienteId) {
                                    $paciente = \App\Models\Pacientes::with('persona')->find($pacienteId);
                                    if ($paciente && $paciente->persona) {
                                        return '🔹 ' . $paciente->persona->nombre_completo;
                                    }
                                }
                                return 'No especificado';
                            })
                            ->extraAttributes(['style' => 'font-weight: 600; color: #1f2937;']),

                        Forms\Components\Placeholder::make('medico_info')
                            ->label('👨‍⚕️ Médico Solicitante')
                            ->content(function ($get) {
                                $medicoId = $get('medico_id') ?? request()->get('medico_id');
                                if ($medicoId) {
                                    $medico = \App\Models\Medico::withoutGlobalScopes()->with('persona')->find($medicoId);
                                    if ($medico && $medico->persona) {
                                        return '🔹 ' . $medico->persona->nombre_completo;
                                    }
                                }
                                return 'No especificado';
                            })
                            ->extraAttributes(['style' => 'font-weight: 600; color: #1f2937;']),
                    ])
                    ->columns(2)
                    ->visible(fn ($get) => $get('paciente_id') || request()->has('paciente_id')),

                // DETALLES DEL EXAMEN (CAMPOS EDITABLES)
                Forms\Components\Section::make('🔬 Detalles del Examen')
                    ->schema([
                        Forms\Components\TextInput::make('tipo_examen')
                            ->label('Tipo de Examen')
                            ->required()
                            ->placeholder('Ej: Hemograma completo, Examen de orina, Rayos X de tórax, Electrocardiograma')
                            ->helperText('Especifique el tipo de examen médico que se solicita')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones e Instrucciones')
                            ->placeholder('Instrucciones especiales para el examen, preparación requerida, horarios específicos, etc.')
                            ->helperText('Opcional: Agregue cualquier instrucción especial para el paciente o laboratorio')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),

                // SECCIÓN DE RESULTADOS (SOLO EN EDICIÓN)
                Forms\Components\Section::make('📋 Resultados del Examen')
                    ->schema([
                        Forms\Components\Select::make('estado')
                            ->label('Estado del Examen')
                            ->options([
                                'Solicitado' => '⏳ Solicitado',
                                'Completado' => '✅ Completado',
                                'No presentado' => '❌ No presentado',
                            ])
                            ->default('Solicitado')
                            ->required()
                            ->helperText('Actualice el estado según el progreso del examen'),

                        Forms\Components\FileUpload::make('imagen_resultado')
                            ->label('🖼️ Resultado del Examen')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(10240) // 10MB
                            ->directory('examenes')
                            ->visibility('private')
                            ->downloadable()
                            ->previewable()
                            ->helperText('Suba una imagen o PDF con el resultado del examen')
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('estado', 'Completado');
                                    $set('fecha_completado', now());
                                }
                            }),

                        Forms\Components\DateTimePicker::make('fecha_completado')
                            ->label('📅 Fecha de Completado')
                            ->helperText('Se completa automáticamente al subir el resultado')
                            ->displayFormat('d/m/Y H:i')
                            ->native(false)
                            ->disabled(),
                    ])
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paciente.persona.primer_nombre')
                    ->label('Paciente')
                    ->formatStateUsing(function($state, $record) {
                        if ($record->paciente && $record->paciente->persona) {
                            return $record->paciente->persona->nombre_completo;
                        }
                        return "Paciente ID: {$record->paciente_id}";
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('medico.persona.primer_nombre')
                    ->label('Médico')
                    ->formatStateUsing(function($state, $record) {
                        if ($record->medico && $record->medico->persona) {
                            return $record->medico->persona->nombre_completo;
                        }
                        return "Médico ID: {$record->medico_id}";
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tipo_examen')
                    ->label('Tipo de Examen')
                    ->searchable()
                    ->wrap(),

                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'Solicitado',
                        'success' => 'Completado',
                        'danger' => 'No presentado',
                    ]),

                TextColumn::make('created_at')
                    ->label('Fecha Solicitud')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('fecha_completado')
                    ->label('Fecha Completado')
                    ->date('d/m/Y')
                    ->placeholder('Pendiente')
                    ->sortable(),

                ImageColumn::make('imagen_resultado')
                    ->label('Resultado')
                    ->height(40)
                    ->width(40)
                    ->visibility('private'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'Solicitado' => 'Solicitado',
                        'Completado' => 'Completado',
                        'No presentado' => 'No presentado',
                    ]),

                SelectFilter::make('medico_id')
                    ->label('Médico')
                    ->options(function () {
                        // Multi-tenant: los médicos ya están filtrados por el tenant
                        $query = Medico::withoutGlobalScopes()->with('persona');

                        return $query->get()
                            ->filter(fn($m) => $m->persona !== null)
                            ->mapWithKeys(fn($m) => [
                                $m->id => $m->persona->nombre_completo ?? "Médico ID: {$m->id}",
                            ]);
                    })
                    ->searchable()
                    ->visible(fn () => Auth::user()->roles->contains('name', 'root') || Auth::user()->roles->contains('name', 'administrador')),

                SelectFilter::make('centro_id')
                    ->label('Centro Médico')
                    ->options(function () {
                        return \App\Models\Centros_Medico::all()
                            ->filter(fn($c) => !empty($c->nombre_centro))
                            ->mapWithKeys(fn($c) => [$c->id => $c->nombre_centro ?? "Centro ID: {$c->id}"]);
                    })
                    ->searchable()
                    ->visible(fn () => Auth::user()->roles->contains('name', 'root')),

                Tables\Filters\Filter::make('fecha_range')
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Desde: ' . \Carbon\Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Hasta: ' . \Carbon\Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => Auth::user()->can('update', $record)),
                Action::make('downloadResult')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => $record->imagen_resultado ? storage_path('app/private/' . $record->imagen_resultado) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->imagen_resultado !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->roles->contains('name', 'root') || $user->roles->contains('name', 'administrador')) {
            // Root y administradores ven todos los exámenes del tenant
            return $query->withoutGlobalScopes();
        }

        if ($user->roles->contains('name', 'medico')) {
            // Médicos ven solo sus propios exámenes
            return $query->where('medico_id', $user->medico?->id);
        }

        return $query->whereRaw('1 = 0'); // No mostrar nada si no tiene rol definido
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExamenes::route('/'),
            'create' => Pages\CreateExamenes::route('/create'),
            'view' => Pages\ViewExamenes::route('/{record}'),
            'edit' => Pages\EditExamenes::route('/{record}/edit'),
        ];
    }
}
