<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacientesResource\Pages;
use App\Models\Pacientes;
use App\Models\Persona;
use App\Models\Nacionalidad;
use App\Models\Enfermedade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rule;
use Filament\Forms\Components\Section;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique; // ImportaciÃ³n aÃ±adida
use Carbon\Carbon;

class PacientesResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('crear pacientes');
        return auth()->user()?->can('ver pacientes');
        return auth()->user()?->can('actualizar pacientes');
        return auth()->user()?->can('borrar pacientes');
    }

    protected static ?string $model = Pacientes::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Panel Diario';
    protected static ?string $navigationLabel = 'Pacientes';
    protected static ?string $modelLabel = 'Paciente';
    protected static ?string $pluralModelLabel = 'Pacientes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Datos Personales')
                        ->schema([
                            // DNI COMO PRIMER CAMPO CON VALIDACIÃ“N MEJORADA
                            Forms\Components\TextInput::make('dni')
    ->label('DNI/CÃ©dula')
    ->required()
    ->maxLength(255)
    ->reactive()
    ->disabled(fn ($operation) => $operation === 'edit')
    ->unique(
        table: Persona::class,
        column: 'dni',
        modifyRuleUsing: function (Unique $rule, callable $get, $context) {
            $personaId = $get('persona_id');

            // Solo ignorar si existe una persona asociada
            if ($personaId) {
                $rule->ignore($personaId);
            }

            return $rule->where('dni', $get('dni'));
        }
    )
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state) {
                                        $existingPersona = Persona::where('dni', $state)->first();
                                        if ($existingPersona) {
                                            // Verificar si ya es paciente
                                            $existingPaciente = Pacientes::where('persona_id', $existingPersona->id)->first();
                                            if ($existingPaciente) {
                                                Notification::make()
                                                    ->title('Paciente ya existe')
                                                    ->body("El DNI {$state} ya estÃ¡ registrado como paciente. SerÃ¡ redirigido para editarlo.")
                                                    ->warning()
                                                    ->persistent()
                                                    ->actions([
                                                        \Filament\Notifications\Actions\Action::make('edit')
                                                            ->label('Ir a editar')
                                                            ->url(route('filament.admin.resources.pacientes.edit', $existingPaciente))
                                                            ->button(),
                                                        \Filament\Notifications\Actions\Action::make('view')
                                                            ->label('Ver paciente')
                                                            ->url(route('filament.admin.resources.pacientes.view', $existingPaciente))
                                                            ->button(),
                                                    ])
                                                    ->send();
                                                return;
                                            }

                                            // Si la persona existe pero no es paciente, llenar los datos
                                            $set('primer_nombre', $existingPersona->primer_nombre);
                                            $set('segundo_nombre', $existingPersona->segundo_nombre);
                                            $set('primer_apellido', $existingPersona->primer_apellido);
                                            $set('segundo_apellido', $existingPersona->segundo_apellido);
                                            $set('telefono', $existingPersona->telefono);
                                            $set('direccion', $existingPersona->direccion);
                                            $set('sexo', $existingPersona->sexo);
                                            $set('fecha_nacimiento', $existingPersona->fecha_nacimiento);
                                            $set('nacionalidad_id', $existingPersona->nacionalidad_id);

                                            // Handle file upload properly
                                            if ($existingPersona->fotografia) {
                                                $set('fotografia', [
                                                    'path' => $existingPersona->fotografia,
                                                    'name' => basename($existingPersona->fotografia),
                                                    'size' => Storage::disk('public')->size($existingPersona->fotografia),
                                                    'type' => Storage::disk('public')->mimeType($existingPersona->fotografia),
                                                ]);
                                            } else {
                                                $set('fotografia', null);
                                            }

                                            $set('persona_id', $existingPersona->id);

                                            Notification::make()
                                                ->title('Persona encontrada')
                                                ->body("Se encontrÃ³: {$existingPersona->primer_nombre} {$existingPersona->primer_apellido}. Complete los datos mÃ©dicos para registrarlo como paciente.")
                                                ->success()
                                                ->send();
                                        } else {
                                            $set('persona_id', null);
                                            $set('fotografia', null);

                                            // Autocompletar desde API externa por nÃºmero de identidad
                                            // (solo cuando no existe una persona local con ese DNI).
                                            self::autocompletarDesdeApiIdentidad($state, $set);
                                        }
                                    }
                                }),

                            Forms\Components\Hidden::make('persona_id'),

                            // âœ… VALIDACIÃ“N DE SOLO LETRAS PARA NOMBRES
                            Forms\Components\TextInput::make('primer_nombre')
                                ->label('Primer Nombre')
                                ->required()
                                ->maxLength(255)
                                ->reactive()
                                ->rules(['regex:/^[a-zA-ZÃ¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“ÃšÃ±Ã‘\s]+$/'])
                                ->validationMessages([
                                    'regex' => 'El primer nombre solo puede contener letras.',
                                ])
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state && !preg_match('/^[a-zA-ZÃ¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“ÃšÃ±Ã‘\s]+$/', $state)) {
                                        Notification::make()
                                            ->title('Error de validaciÃ³n')
                                            ->body('El primer nombre solo puede contener letras.')
                                            ->danger()
                                            ->send();
                                    }
                                    self::checkPersonExists($state, $set);
                                }),

                            Forms\Components\TextInput::make('segundo_nombre')
                                ->label('Segundo Nombre')
                                ->maxLength(255)
                                ->rules(['regex:/^[a-zA-ZÃ¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“ÃšÃ±Ã‘\s]*$/'])
                                ->validationMessages([
                                    'regex' => 'El segundo nombre solo puede contener letras.',
                                ])
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state && !preg_match('/^[a-zA-ZÃ¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“ÃšÃ±Ã‘\s]*$/', $state)) {
                                        Notification::make()
                                            ->title('Error de validaciÃ³n')
                                            ->body('El segundo nombre solo puede contener letras.')
                                            ->danger()
                                            ->send();
                                    }
                                }),

                            Forms\Components\TextInput::make('primer_apellido')
                                ->label('Primer Apellido')
                                ->required()
                                ->maxLength(255)
                                ->reactive()
                                ->rules(['regex:/^[a-zA-ZÃ¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“ÃšÃ±Ã‘\s]+$/'])
                                ->validationMessages([
                                    'regex' => 'El primer apellido solo puede contener letras.',
                                ])
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state && !preg_match('/^[a-zA-ZÃ¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“ÃšÃ±Ã‘\s]+$/', $state)) {
                                        Notification::make()
                                            ->title('Error de validaciÃ³n')
                                            ->body('El primer apellido solo puede contener letras.')
                                            ->danger()
                                            ->send();
                                    }
                                    self::checkPersonExists($state, $set);
                                }),

                            Forms\Components\TextInput::make('segundo_apellido')
                                ->label('Segundo Apellido')
                                ->maxLength(255)
                                ->rules(['regex:/^[a-zA-ZÃ¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“ÃšÃ±Ã‘\s]*$/'])
                                ->validationMessages([
                                    'regex' => 'El segundo apellido solo puede contener letras.',
                                ])
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state && !preg_match('/^[a-zA-ZÃ¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“ÃšÃ±Ã‘\s]*$/', $state)) {
                                        Notification::make()
                                            ->title('Error de validaciÃ³n')
                                            ->body('El segundo apellido solo puede contener letras.')
                                            ->danger()
                                            ->send();
                                    }
                                }),

                            Forms\Components\TextInput::make('telefono')
                                ->label('TelÃ©fono')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\Textarea::make('direccion')
                                ->label('DirecciÃ³n')
                                ->required()
                                ->rows(3),

                            Forms\Components\Select::make('sexo')
                                ->label('Sexo')
                                ->options([
                                    'M' => 'Masculino',
                                    'F' => 'Femenino',
                                ])
                                ->required(),

                            Forms\Components\DatePicker::make('fecha_nacimiento')
                                ->label('Fecha de Nacimiento')
                                ->required()
                                ->native(false),

                            Forms\Components\Select::make('nacionalidad_id')
                                ->label('Nacionalidad')
                                ->options(Nacionalidad::pluck('nacionalidad', 'id'))
                                ->required()
                                ->searchable(),

                            // âœ… MEJORAR SUBIDA DE ARCHIVOS
                            Forms\Components\FileUpload::make('fotografia')
                                ->label('FotografÃ­a')
                                ->image()
                                ->directory('personas/fotos')
                                ->disk('public')
                                ->visibility('public')
                                ->imageEditor()
                                ->maxSize(2048)
                                ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])
                                ->preserveFilenames(false)
                                ->getUploadedFileNameForStorageUsing(function ($file) {
                                    $extension = $file->getClientOriginalExtension();
                                    $timestamp = now()->format('YmdHis');
                                    $random = Str::random(8);
                                    return "foto_{$timestamp}_{$random}.{$extension}";
                                })
                                ->downloadable()
                                ->openable()
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->afterValidation(function (callable $get) {
                            $requiredFields = [
                                'dni' => 'DNI/CÃ©dula',
                                'primer_nombre' => 'Primer Nombre',
                                'primer_apellido' => 'Primer Apellido',
                                'telefono' => 'TelÃ©fono',
                                'direccion' => 'DirecciÃ³n',
                                'sexo' => 'Sexo',
                                'fecha_nacimiento' => 'Fecha de Nacimiento',
                                'nacionalidad_id' => 'Nacionalidad',
                            ];

                            $missingFields = [];
                            foreach ($requiredFields as $field => $label) {
                                if (empty($get($field))) {
                                    $missingFields[] = $label;
                                }
                            }

                            if (!empty($missingFields)) {
                                Notification::make()
                                    ->title('Campos obligatorios faltantes')
                                    ->body('Complete los siguientes campos: ' . implode(', ', $missingFields))
                                    ->danger()
                                    ->send();

                                throw new \Exception('Campos obligatorios faltantes');
                            }
                        }),

                    Wizard\Step::make('Datos del Paciente')
                        ->schema([
                            Forms\Components\Select::make('grupo_sanguineo')
                                ->label('Grupo SanguÃ­neo')
                                ->options([
                                    'A+' => 'A+',
                                    'A-' => 'A-',
                                    'B+' => 'B+',
                                    'B-' => 'B-',
                                    'O+' => 'O+',
                                    'O-' => 'O-',
                                    'AB+' => 'AB+',
                                    'AB-' => 'AB-',
                                    'No especificado' => 'No especificado',
                                ])
                                ->required(),

                            Forms\Components\TextInput::make('contacto_emergencia')
                                ->label('Contacto de Emergencia')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->columns(2)
                        ->afterValidation(function (callable $get) {
                            $requiredFields = [
                                'grupo_sanguineo' => 'Grupo SanguÃ­neo',
                                'contacto_emergencia' => 'Contacto de Emergencia',
                            ];

                            $missingFields = [];
                            foreach ($requiredFields as $field => $label) {
                                if (empty($get($field))) {
                                    $missingFields[] = $label;
                                }
                            }

                            if (!empty($missingFields)) {
                                Notification::make()
                                    ->title('Campos obligatorios faltantes')
                                    ->body('Complete los siguientes campos: ' . implode(', ', $missingFields))
                                    ->danger()
                                    ->send();

                                throw new \Exception('Campos obligatorios faltantes');
                            }
                        }),

                    Wizard\Step::make('Enfermedades')
                        ->schema([
                            Forms\Components\Toggle::make('sin_enfermedades')
                                ->label('No tengo enfermedades')
                                ->helperText('Marque esta opciÃ³n si el paciente no tiene enfermedades registradas')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        // Si marca "No tengo enfermedades", limpiar los datos de enfermedades
                                        $set('enfermedades_data', []);
                                    } else {
                                        // Si desmarca, agregar una enfermedad vacÃ­a
                                        $set('enfermedades_data', [['enfermedad_id' => null, 'ano_diagnostico' => date('Y'), 'tratamiento' => null]]);
                                    }
                                })
                                ->columnSpanFull(),

                            Repeater::make('enfermedades_data')
                                ->label('Enfermedades del Paciente')
                                ->schema([
                                    Forms\Components\Select::make('enfermedad_id')
                                        ->label('Enfermedad')
                                        ->options(Enfermedade::all()->pluck('enfermedades', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                            $enfermedadesData = $get('../../enfermedades_data') ?? [];
                                            $enfermedadesSeleccionadas = array_filter(
                                                array_column($enfermedadesData, 'enfermedad_id'),
                                                fn($id) => !is_null($id)
                                            );

                                            $repetidas = array_count_values($enfermedadesSeleccionadas);
                                            if (isset($repetidas[$state]) && $repetidas[$state] > 1) {
                                                Notification::make()
                                                    ->title('Enfermedad duplicada')
                                                    ->body('No puede seleccionar la misma enfermedad mÃ¡s de una vez.')
                                                    ->danger()
                                                    ->send();
                                                $set('enfermedad_id', null);
                                            }
                                        }),

                                    Forms\Components\TextInput::make('ano_diagnostico')
                                        ->label('AÃ±o de DiagnÃ³stico')
                                        ->numeric()
                                        ->minValue(1900)
                                        ->maxValue(date('Y'))
                                        ->default(date('Y'))
                                        ->required(),

                                    Forms\Components\Textarea::make('tratamiento')
                                        ->label('Tratamiento')
                                        ->rows(3)
                                        ->columnSpanFull()
                                        ->required()
                                ])
                                ->columns(2)
                                ->defaultItems(1)
                                ->addActionLabel('Agregar Enfermedad')
                                ->itemLabel(fn (array $state): ?string =>
                                    $state['enfermedad_id'] ?
                                    Enfermedade::find($state['enfermedad_id'])?->enfermedades :
                                    'Nueva Enfermedad'
                                )
                                ->collapsible()
                                ->cloneable()
                                ->reorderable()
                                ->deleteAction(
                                    fn (Forms\Components\Actions\Action $action) => $action
                                        ->requiresConfirmation()
                                        ->modalDescription('Â¿EstÃ¡s seguro de que deseas eliminar esta enfermedad?')
                                )
                                ->minItems(0)
                                ->hidden(fn (callable $get) => $get('sin_enfermedades'))
                                ->columnSpanFull(),
                        ])
                        ->afterValidation(function (callable $get) {
                            $sinEnfermedades = $get('sin_enfermedades');
                            $enfermedadesData = $get('enfermedades_data');

                            // Si no marcÃ³ "sin enfermedades" debe tener al menos una enfermedad
                            if (!$sinEnfermedades) {
                                if (empty($enfermedadesData)) {
                                    Notification::make()
                                        ->title('Error de validaciÃ³n')
                                        ->body('Debe agregar al menos una enfermedad o marcar "No tengo enfermedades"')
                                        ->danger()
                                        ->send();

                                    throw new \Exception('Debe agregar al menos una enfermedad o marcar "No tengo enfermedades"');
                                }

                                $enfermedadesSeleccionadas = array_filter(
                                    array_column($enfermedadesData, 'enfermedad_id'),
                                    fn($id) => !is_null($id)
                                );

                                if (count($enfermedadesSeleccionadas) !== count(array_unique($enfermedadesSeleccionadas))) {
                                    Notification::make()
                                        ->title('Enfermedades duplicadas')
                                        ->body('No puede seleccionar la misma enfermedad mÃ¡s de una vez.')
                                        ->danger()
                                        ->send();

                                    throw new \Exception('Enfermedades duplicadas detectadas');
                                }
                            }
                        }),
                ])
                ->columnSpanFull()
                ->persistStepInQueryString(),
            ]);
    }

    protected static function checkPersonExists($state, callable $set)
    {
        // LÃ³gica de verificaciÃ³n si es necesario
    }

    protected static function autocompletarDesdeApiIdentidad(?string $dniInput, callable $set): void
    {
        $dni = preg_replace('/\D+/', '', (string) $dniInput);

        if (!$dni || strlen($dni) < 13) {
            return;
        }

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->get('https://1ug1cfi3ua.execute-api.us-east-2.amazonaws.com/persona', [
                    'numero_identidad' => $dni,
                ]);

            if (!$response->successful()) {
                return;
            }

            $payload = $response->json();

            if (isset($payload['data']) && is_array($payload['data'])) {
                $payload = $payload['data'];
            }

            if (isset($payload[0]) && is_array($payload[0])) {
                $payload = $payload[0];
            }

            if (!is_array($payload) || empty($payload)) {
                return;
            }

            $set('primer_nombre', $payload['primer_nombre'] ?? null);
            $set('segundo_nombre', $payload['segundo_nombre'] ?? null);
            $set('primer_apellido', $payload['primer_apellido'] ?? null);
            $set('segundo_apellido', $payload['segundo_apellido'] ?? null);

            $sexoApi = strtoupper(trim((string) ($payload['sexo'] ?? '')));
            if (str_starts_with($sexoApi, 'M')) {
                $set('sexo', 'M');
            } elseif (str_starts_with($sexoApi, 'F')) {
                $set('sexo', 'F');
            }

            $fechaNacimiento = $payload['fecha_nacimiento'] ?? null;
            if (!empty($fechaNacimiento)) {
                try {
                    $set('fecha_nacimiento', Carbon::parse($fechaNacimiento)->format('Y-m-d'));
                } catch (\Throwable $e) {
                    // Mantener flujo actual si la fecha no puede parsearse.
                }
            }

            // Si existe en API, se asume nacionalidad hondureÃ±a.
            $nacionalidadHondurenaId = Nacionalidad::query()
                ->where('nacionalidad', 'like', 'Hondur%')
                ->value('id');

            if ($nacionalidadHondurenaId) {
                $set('nacionalidad_id', $nacionalidadHondurenaId);
            }

            // DirecciÃ³n: lugar_poblado, aldea, municipio, departamento.
            $direccion = collect([
                $payload['lugar_poblado'] ?? null,
                $payload['aldea'] ?? null,
                $payload['municipio'] ?? null,
                $payload['departamento'] ?? null,
            ])
                ->filter(fn ($item) => filled($item))
                ->map(fn ($item) => trim((string) $item))
                ->implode(', ');

            if (!empty($direccion)) {
                $set('direccion', $direccion);
            }
        } catch (\Throwable $e) {
            // Falla silenciosa para no cambiar el comportamiento actual.
        }
    }

    public static function generateAvatar($nombre, $apellido)
    {
        $iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellido, 0, 1));
        $colores = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8'];
        $color = $colores[array_rand($colores)];

        return "https://ui-avatars.com/api/?name={$iniciales}&background=" . substr($color, 1) . "&color=fff&size=100&font-size=0.5";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['persona', 'enfermedades'])->orderBy('created_at', 'desc')) // EAGER LOADING AÃ‘ADIDO + ORDENAMIENTO
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('persona.fotografia')
                    ->label('Foto')
                    ->circular()
                    ->size(50)
                    ->getStateUsing(function ($record) {
                        if ($record->persona->fotografia) {
                            return asset('storage/' . $record->persona->fotografia);
                        }
                        return self::generateAvatar(
                            $record->persona->primer_nombre,
                            $record->persona->primer_apellido
                        );
                    }),

                Tables\Columns\TextColumn::make('persona.primer_nombre')
                    ->label('Nombre Completo')
                    ->formatStateUsing(fn ($record) =>
                        trim("{$record->persona->primer_nombre} {$record->persona->segundo_nombre} {$record->persona->primer_apellido} {$record->persona->segundo_apellido}"))
                    ->searchable(['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido'])
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('persona.dni')
                    ->label('DNI')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('grupo_sanguineo')
                    ->label('Grupo SanguÃ­neo')
                    ->sortable()
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('contacto_emergencia')
                    ->label('Contacto Emergencia')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->contacto_emergencia),

                // âœ… MEJORADA: Columna de enfermedades mÃ¡s visual
                Tables\Columns\TextColumn::make('enfermedades_count')
                    ->label('Enfermedades')
                    ->getStateUsing(function ($record) {
                        $count = $record->enfermedades->count();
                        if ($count == 0) {
                            return 'Sin enfermedades';
                        }
                        return $count . ' enfermedad' . ($count > 1 ? 'es' : '');
                    })
                    ->badge()
                    ->color(function ($record) {
                        $count = $record->enfermedades->count();
                        if ($count == 0) return 'gray';
                        if ($count <= 2) return 'success';
                        if ($count <= 4) return 'warning';
                        return 'danger';
                    })
                    ->tooltip(function ($record) {
                        $enfermedades = $record->enfermedades->pluck('enfermedades')->toArray();
                        if (empty($enfermedades)) {
                            return 'Sin enfermedades registradas';
                        }
                        return implode(', ', $enfermedades);
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grupo_sanguineo')
                    ->label('Grupo SanguÃ­neo')
                    ->options([
                        'A+' => 'A+',
                        'A-' => 'A-',
                        'B+' => 'B+',
                        'B-' => 'B-',
                        'O+' => 'O+',
                        'O-' => 'O-',
                        'AB+' => 'AB+',
                        'AB-' => 'AB-',
                        'No especificado' => 'No especificado',
                    ]),

                Tables\Filters\SelectFilter::make('sexo')
                    ->label('Sexo')
                    ->options([
                        'M' => 'Masculino',
                        'F' => 'Femenino',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            $query->whereHas('persona', function ($q) use ($data) {
                                $q->where('sexo', $data['value']);
                            });
                        }
                    }),
            ])
            ->actions([
                // âœ… NUEVO: AcciÃ³n principal para crear consulta
                Tables\Actions\Action::make('crear_consulta')
                    ->label('Crear Consulta')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->size('sm')
                    ->button()
                    ->url(fn ($record) => \App\Filament\Resources\Consultas\ConsultasResource::getUrl('create', ['paciente_id' => $record->id])),

                // âœ… NUEVO: Dropdown con todas las acciones agrupadas
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                    ->label('Ver InformaciÃ³n')
                    ->icon('heroicon-o-eye')
                        ->icon('heroicon-m-eye')
                        ->label('Ver'),
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-m-pencil-square')
                        ->label('Editar'),
                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-m-trash')
                        ->label('Eliminar'),
                ])
                ->label('Opciones')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
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
            \App\Filament\Resources\Pacientes\PacientesResource\RelationManagers\ConsultasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPacientes::route('/'),
            'create' => Pages\CreatePacientes::route('/create'),
            'view' => Pages\ViewPacientes::route('/{record}'),
            'edit' => Pages\EditPacientes::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Multi-tenant: el contexto del tenant ya filtra los datos
        // No es necesario filtrar por centro_id

        return $query;
    }
}

