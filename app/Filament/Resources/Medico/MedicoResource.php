<?php


namespace App\Filament\Resources\Medico;

use App\Filament\Resources\Medico\MedicoResource\Pages;
use App\Models\Persona;
use App\Models\Medico;
use App\Models\Nacionalidad;
use App\Models\Especialidad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Get;
use Closure;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Centros_Medico;
use Illuminate\Support\Str;


class MedicoResource extends Resource
{
    protected static ?string $model = Medico::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'GestiÃ³n de Personas';
    protected static ?string $navigationLabel = 'MÃ©dicos';
    protected static ?string $modelLabel = 'MÃ©dico';
    protected static ?string $pluralModelLabel = 'MÃ©dicos';

    public static function form(Form $form): Form
    {
    return $form
        ->schema([
            Wizard::make([
                Wizard\Step::make('Datos Personales')
                    ->schema([

                            Forms\Components\TextInput::make('dni')
                                ->label('DNI')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Ingrese su DNI')
                                ->disabled(fn ($operation) => $operation === 'edit')
                                ->dehydrated()
                                ->live(debounce: 500) // Esto hace que se actualice cada 500ms despuÃ©s de dejar de escribir
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if (strlen($state) >= 1) { // Asumiendo que el DNI tiene al menos 1 carÃ¡cter
                                        $existingPersona = Persona::where('dni', $state)->first();
                                        if ($existingPersona) {
                                            $set('primer_nombre', $existingPersona->primer_nombre);
                                            $set('segundo_nombre', $existingPersona->segundo_nombre);
                                            $set('primer_apellido', $existingPersona->primer_apellido);
                                            $set('segundo_apellido', $existingPersona->segundo_apellido);
                                            $set('telefono', $existingPersona->telefono);
                                            $set('direccion', $existingPersona->direccion);
                                            $set('sexo', $existingPersona->sexo);
                                            $set('fecha_nacimiento', $existingPersona->fecha_nacimiento);
                                            $set('nacionalidad_id', $existingPersona->nacionalidad_id);
                                            $set('persona_id', $existingPersona->id);

                                            Notification::make()
                                                ->title('Persona encontrada')
                                                ->body("Se encontrÃ³: {$existingPersona->nombre_completo}")
                                                ->success()
                                                ->send();
                                            } else {
                                                $set('persona_id', null);
                                                                // Opcional: limpiar campos si no se encuentra la persona
                                        if ($get('id') === null) { // Solo en creaciÃ³n
                                            $set('primer_nombre', '');
                                            $set('segundo_nombre', '');
                                            $set('primer_apellido', '');
                                            $set('segundo_apellido', '');
                                            $set('telefono', '');
                                            $set('direccion', '');
                                            $set('sexo', '');
                                            $set('fecha_nacimiento', null);
                                            $set('nacionalidad_id', null);
                                            }
                                        }
                                    }
                                }),
                       /* ->rules([
                            function (Get $get) {
                                return function (string $attribute, $value, Closure $fail) use ($get) {
                                    // Solo validar durante creaciÃ³n
                                    if ($get('id') === null) {
                                        $exists = Persona::where('dni', $value)->exists();
                                        if ($exists) {
                                            $fail('Este DNI ya estÃ¡ registrado por otra persona');
                                        }
                                    }
                                    // Guardar datos en session o en propiedad Livewire si usas Livewire Component
                                    session(['dni' => $value]);
                                };
                            },
                        ]),*/




                    Forms\Components\TextInput::make('primer_nombre')
                        ->label('Primer Nombre')
                        ->required()
                        ->placeholder('Ingrese su primer nombre')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('segundo_nombre')
                        ->label('Segundo Nombre')
                        ->maxLength(255)
                        ->placeholder('Ingrese su segundo nombre')
                        ->nullable(),

                    Forms\Components\TextInput::make('primer_apellido')
                        ->label('Primer Apellido')
                        ->required()
                        ->placeholder('Ingrese su primer apellido')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('segundo_apellido')
                        ->label('Segundo Apellido')
                        ->maxLength(255)
                        ->placeholder('Ingrese su segundo apellido')
                        ->nullable(),


                    Forms\Components\TextInput::make('telefono')
                        ->label('TelÃ©fono')
                        ->maxLength(255)
                        ->placeholder('Ingrese su nÃºmero de telÃ©fono')
                        ->required(),

                    Forms\Components\Textarea::make('direccion')
                        ->label('DirecciÃ³n')
                        ->maxLength(255)
                        ->placeholder('Ingrese su direcciÃ³n')
                        ->required(), // hace obligatorio el campo,
                       // ->columnSpanFull(),

                    Forms\Components\Select::make('sexo')
                        ->label('Sexo')
                        ->placeholder('Seleccione su sexo')
                        ->options([
                            'M' => 'Masculino',
                            'F' => 'Femenino',
                        ])
                        ->required(),



                    Forms\Components\DatePicker::make('fecha_nacimiento')
                        ->label('Fecha de Nacimiento')
                        ->native(false)
                        ->placeholder('Seleccione su fecha de nacimiento')
                        ->maxDate(now()) // No permitir fechas futuras
                        ->minDate(now()->subYears(120)) // No permitir fechas demasiado antiguas
                        ->default(now()->subYears(70)) // Valor por defecto (70 aÃ±os atrÃ¡s)
                        ->displayFormat('d/m/Y') // Formato de visualizaciÃ³n
                        ->required(),

                    Forms\Components\Select::make('nacionalidad_id')
                        ->label('Nacionalidad')
                        ->options(Nacionalidad::pluck('nacionalidad', 'id'))
                        ->searchable()
                        ->placeholder('Seleccione una nacionalidad')
                        ->required(),

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
                ->columns(2),



            Wizard\Step::make('Datos Profesionales')
                ->schema([

                    Forms\Components\TextInput::make('numero_colegiacion')
                        ->label('NÃºmero de ColegiaciÃ³n')
                        ->required()
                        ->maxLength(20)
                        ->placeholder('Ingrese su nÃºmero de colegiaciÃ³n'),
                      // ->unique('medicos', 'numero_colegiacion', ignoreRecord: true),

                       Forms\Components\Grid::make(2)
            ->schema([
                Forms\Components\TimePicker::make('horario_entrada')
                    ->label('Horario de Entrada')
                    ->seconds(false)
                    ->required()
                    ->format('H:i')
                    ->displayFormat('g:i A')
                    ->placeholder('Ej: 8:00 AM')
                    ->suffixIcon('heroicon-o-clock')
                    ->native(false)
                    ->helperText('Horario de inicio de consultas')
                    ->extraAttributes(['class' => 'text-center']),

                Forms\Components\TimePicker::make('horario_salida')
                    ->label('Horario de Salida')
                    ->seconds(false)
                    ->required()
                    ->format('H:i')
                    ->displayFormat('g:i A')
                    ->placeholder('Ej: 5:00 PM')
                    ->suffixIcon('heroicon-o-clock')
                    ->native(false)
                    ->helperText('Horario de fin de consultas')
                    ->extraAttributes(['class' => 'text-center'])
                    ->rules([
                        function (Get $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $entrada = $get('horario_entrada');
                                if ($entrada && $value) {
                                    if (strtotime($value) <= strtotime($entrada)) {
                                        $fail('El horario de salida debe ser posterior al horario de entrada');
                                    }

                                    // Validar que no sea muy temprano o muy tarde
                                    $horaEntrada = (int) date('H', strtotime($entrada));
                                    $horaSalida = (int) date('H', strtotime($value));

                                    if ($horaEntrada < 6 || $horaSalida > 22) {
                                        $fail('Los horarios deben estar entre las 6:00 AM y 10:00 PM');
                                    }

                                    // Validar duraciÃ³n mÃ­nima de 2 horas
                                    $diferencia = strtotime($value) - strtotime($entrada);
                                    if ($diferencia < 7200) { // 2 horas en segundos
                                        $fail('La jornada debe tener al menos 2 horas de duraciÃ³n');
                                    }
                                }
                            };
                        },
                    ]),
            ]),
                ]) ->columns(2),

            Wizard\Step::make('InformaciÃ³n Contractual')
                ->description('InformaciÃ³n del contrato laboral')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('salario_quincenal')
                                ->default(0)
                                ->label('Salario Quincenal')
                                ->required(fn ($operation) => $operation === 'create')
                                ->numeric()
                                ->prefix('L')
                                ->placeholder('0.00')
                                ->extraAttributes([
                                    'title' => 'Monto que recibirÃ¡ el mÃ©dico cada quincena (15 dÃ­as)'
                                ])
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set, Forms\Get $get) {
                                    // Asegurarse de que sea un nÃºmero, defecto 0
                                    $value = is_numeric($state) ? (float) $state : 0;
                                    $set('salario_mensual', $value * 2);
                                    
                                    // ValidaciÃ³n para verificar si ambos valores son cero
                                    $porcentaje = (float) ($get('porcentaje_servicio') ?? 0);
                                    if ($value <= 0 && $porcentaje <= 0) {
                                        $set('validacion_compensacion', false);
                                    } else {
                                        $set('validacion_compensacion', true);
                                    }
                                })
                                ->rules([
                                    function (Forms\Get $get) {
                                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $porcentajeServicio = (float)($get('porcentaje_servicio') ?? 0);
                                            $salario = (float)($value ?? 0);
                                            
                                            if ($salario <= 0 && $porcentajeServicio <= 0) {
                                                $fail('Debe especificar al menos una forma de compensaciÃ³n (salario o porcentaje por servicio).');
                                            }
                                        };
                                    },
                                ]),
                                

                            Forms\Components\TextInput::make('salario_mensual')
                                ->label('Salario Mensual')
                                ->required(fn ($operation) => $operation === 'create')
                                ->numeric()
                                ->prefix('L')
                                ->placeholder('0.00')
                                ->extraAttributes([
                                    'title' => 'Salario completo mensual (calculado automÃ¡ticamente)'
                                ])
                                ->disabled()
                                ->dehydrated(),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('porcentaje_servicio')
                                ->default(0)
                                ->label('Porcentaje por Servicios')
                                ->numeric()
                                ->suffix('%')
                                ->placeholder('0')
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(100)
                                ->required(fn ($operation) => $operation === 'create')
                                ->extraAttributes([
                                    'title' => 'Porcentaje de comisiÃ³n que recibe por servicios mÃ©dicos realizados'
                                ])
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state === '' || $state === null) {
                                        $set('porcentaje_servicio', 0);
                                    }
                                    // Convertir a nÃºmero para evitar problemas con strings vacÃ­os
                                    $set('porcentaje_servicio', floatval($state ?? 0));
                                }),

                            Forms\Components\DatePicker::make('fecha_inicio')
                                ->label('Fecha de Inicio')
                                ->required(fn ($operation) => $operation === 'create')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->default(now()),
                                //->minDate(now()),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DatePicker::make('fecha_fin')
                                ->label('Fecha de FinalizaciÃ³n')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                
                              //  ->minDate(fn (Get $get) => $get('fecha_inicio'))
                                ->placeholder('Sin fecha de finalizaciÃ³n')
                                ->helperText('Dejar vacÃ­o si el contrato es indefinido'),

                            Forms\Components\Toggle::make('activo')
                                ->label('Contrato Activo')
                                ->helperText('Indica si el contrato estÃ¡ vigente')
                                ->default(true)
                                ->inline(false),
                        ]),

                    Forms\Components\Textarea::make('observaciones_contrato')
                        ->label('Observaciones del Contrato')
                        ->placeholder('Ingrese cualquier observaciÃ³n relevante sobre el contrato')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ]),

            Wizard\Step::make('Especialidades')
                ->schema([
                    Forms\Components\CheckboxList::make('especialidades')
                        ->relationship('especialidades', 'especialidad')
                        ->required()
                        ->columns(2),
                ]),

            Wizard\Step::make('Usuario de Acceso')
                ->description('Configure los datos de acceso del mÃ©dico al sistema')
                ->schema([
                    Forms\Components\Section::make('Â¿Crear usuario de acceso?')
                        ->description('Determine si este mÃ©dico necesita acceso al sistema')
                        ->schema([
                            Forms\Components\Toggle::make('crear_usuario')
                                ->label(fn ($operation) => 
                                    $operation === 'edit' 
                                        ? 'Gestionar usuario de acceso'
                                        : 'Crear usuario de acceso para este mÃ©dico'
                                )
                                ->helperText(fn ($operation) => 
                                    $operation === 'edit' 
                                        ? 'Active para modificar o crear datos de usuario del sistema'
                                        : 'Active esta opciÃ³n si el mÃ©dico necesita acceder al sistema'
                                )
                                ->default(fn ($operation) => $operation === 'create' ? true : false)
                                ->live()
                                ->inline(false)
                                ->dehydrated(),
                        ]),

                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('auto_generate')
                            ->label('ðŸŽ² Generar datos automÃ¡ticamente')
                            ->icon('heroicon-o-sparkles')
                            ->size('lg')
                            ->color('success')
                            ->outlined()
                            ->extraAttributes([
                                'class' => 'w-full justify-center'
                            ])
                            ->action(function (callable $set, Forms\Get $get) {
                                // Obtener nombre de los datos de persona
                                $primerNombre = $get('primer_nombre');
                                $primerApellido = $get('primer_apellido');

                                if ($primerNombre && $primerApellido) {
                                    $username = strtolower($primerNombre . '.' . $primerApellido);
                                    $username = preg_replace('/[^a-z0-9.]/', '', $username);

                                    $email = $username . '@clinica.com';
                                    $password = 'Temp' . rand(1000, 9999);

                                    $set('username', $username);
                                    $set('user_email', $email);
                                    $set('user_password', $password);
                                    $set('user_password_confirmation', $password);

                                    Notification::make()
                                        ->title('Datos generados automÃ¡ticamente')
                                        ->body("Usuario: {$username}\nEmail: {$email}\nContraseÃ±a: {$password}")
                                        ->icon('heroicon-o-sparkles')
                                        ->iconColor('success')
                                        ->success()
                                        ->persistent()
                                        ->actions([
                                            \Filament\Notifications\Actions\Action::make('copy')
                                                ->label('Copiar contraseÃ±a')
                                                ->icon('heroicon-o-clipboard')
                                                ->button()
                                                ->color('success')
                                                ->action(function () use ($password) {
                                                    // Copiar la contraseÃ±a al portapapeles
                                                    Notification::make()
                                                        ->title('Â¡Copiado!')
                                                        ->body('La contraseÃ±a ha sido copiada al portapapeles')
                                                        ->success()
                                                        ->send();

                                                    return $password;
                                                }),
                                        ])
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Error')
                                        ->body('Se necesita el nombre y apellido del mÃ©dico para generar los datos')
                                        ->danger()
                                        ->send();
                                }
                            })
                    ])
                    ->visible(fn (Forms\Get $get) => $get('crear_usuario'))
                    ->columnSpanFull(),

                    Forms\Components\Section::make('Datos del Usuario')
                        ->description('Complete la informaciÃ³n de acceso del mÃ©dico')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('username')
                                        ->label('Nombre de usuario')
                                        ->required(fn (Forms\Get $get, $operation) => 
                                            $get('crear_usuario') && $operation === 'create'
                                        )
                                        ->maxLength(255)
                                        ->placeholder('Ej: juan.perez')
                                        ->helperText(fn ($operation) => 
                                            $operation === 'edit' 
                                                ? 'DÃ©jalo igual si no quieres cambiar el acceso'
                                                : 'Usado para iniciar sesiÃ³n en el sistema'
                                        )
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, callable $set, $operation) {
                                            // Auto-generar email basado en username si estÃ¡ vacÃ­o y es creaciÃ³n
                                            if ($operation === 'create' && $state) {
                                                $set('user_email', strtolower($state) . '@clinica.com');
                                            }
                                        })
                                        ->reactive()
                                        ->rules([
                                            'regex:/^[a-zA-Z0-9._-]+$/',
                                            function ($operation) {
                                                return function (string $attribute, $value, \Closure $fail) use ($operation) {
                                                    // Solo validar si hay valor
                                                    if (empty($value)) {
                                                        return;
                                                    }
                                                    
                                                    if ($operation === 'create') {
                                                        // En creaciÃ³n, verificar duplicados
                                                        if (\App\Models\User::where('name', $value)->exists()) {
                                                            $fail('Este nombre de usuario ya estÃ¡ en uso.');
                                                        }
                                                    }
                                                    // En ediciÃ³n, no validar aquÃ­ - se validarÃ¡ en el backend
                                                };
                                            },
                                        ])
                                        ->validationAttribute('nombre de usuario')
                                        ->dehydrated(),

                                    Forms\Components\TextInput::make('user_email')
                                        ->label('Email corporativo')
                                        ->email()
                                        ->required(fn (Forms\Get $get, $operation) => 
                                            $get('crear_usuario') && $operation === 'create'
                                        )
                                        ->maxLength(255)
                                        ->placeholder('Ej: juan.perez@clinica.com')
                                        ->helperText(fn ($operation) => 
                                            $operation === 'edit' 
                                                ? 'Email para notificaciones - dÃ©jalo igual si no quieres cambiar'
                                                : 'Email para notificaciones y recuperaciÃ³n de contraseÃ±a'
                                        )
                                        ->reactive()
                                        ->rules([
                                            'email',
                                            function ($operation) {
                                                return function (string $attribute, $value, \Closure $fail) use ($operation) {
                                                    // Solo validar si hay valor
                                                    if (empty($value)) {
                                                        return;
                                                    }
                                                    
                                                    if ($operation === 'create') {
                                                        // En creaciÃ³n, verificar duplicados
                                                        if (\App\Models\User::where('email', $value)->exists()) {
                                                            $fail('Este email ya estÃ¡ en uso.');
                                                        }
                                                    }
                                                    // En ediciÃ³n, no validar aquÃ­ - se validarÃ¡ en el backend
                                                };
                                            },
                                        ])
                                        ->validationAttribute('email corporativo')
                                        ->dehydrated(),
                                ]),

                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('user_password')
                                        ->label('ContraseÃ±a')
                                        ->password()
                                        ->required(fn (Forms\Get $get, $operation) => 
                                            $get('crear_usuario') && $operation === 'create'
                                        )
                                        ->minLength(8)
                                        ->maxLength(255)
                                        ->placeholder(fn ($operation) => 
                                            $operation === 'edit' 
                                                ? 'Dejar vacÃ­o para mantener la contraseÃ±a actual'
                                                : 'MÃ­nimo 8 caracteres'
                                        )
                                        ->helperText(fn ($operation) => 
                                            $operation === 'edit' 
                                                ? 'Solo complete si desea cambiar la contraseÃ±a'
                                                : 'ContraseÃ±a inicial del mÃ©dico (puede cambiarla despuÃ©s)'
                                        )
                                        ->revealable()
                                        ->dehydrated(),

                                    Forms\Components\TextInput::make('user_password_confirmation')
                                        ->label('Confirmar contraseÃ±a')
                                        ->password()
                                        ->required(fn (Forms\Get $get, $operation) => 
                                            $get('crear_usuario') && $operation === 'create' && $get('user_password')
                                        )
                                        ->revealable()
                                        ->same('user_password')
                                        ->placeholder(fn ($operation) => 
                                            $operation === 'edit' 
                                                ? 'Confirme solo si cambiÃ³ la contraseÃ±a'
                                                : 'Repita la contraseÃ±a'
                                        )
                                        ->helperText(fn ($operation) => 
                                            $operation === 'edit' 
                                                ? 'Solo necesario si cambiÃ³ la contraseÃ±a arriba'
                                                : 'Debe coincidir con la contraseÃ±a anterior'
                                        )
                                        ->dehydrated(false),
                                ]),

                            Forms\Components\Select::make('user_role')
                                ->label('Rol en el sistema')
                                ->options([
                                    'medico' => 'MÃ©dico - Puede gestionar pacientes y consultas',
                                    
                                ])
                                ->default('medico')
                                ->required(fn (Forms\Get $get, $operation) => 
                                    $get('crear_usuario') && $operation === 'create'
                                )
                                ->helperText('Define los permisos del usuario en el sistema')
                                ->dehydrated(),

                            Forms\Components\Toggle::make('user_active')
                                ->label('Usuario activo')
                                ->helperText('Determine si el usuario puede acceder inmediatamente')
                                ->default(true)
                                ->inline(false)
                                ->dehydrated(),


                        ])
                        ->visible(fn (Forms\Get $get) => $get('crear_usuario'))
                        ->columns(1),

                    
                ]),
        ])
        ->columnSpanFull() //  Esto harÃ¡ que el Wizard ocupe el 100% del ancho
            ->nextAction(
                fn ($action) => $action->label('Siguiente')  // "Next" â†’ "Siguiente"
            )


        ->persistStepInQueryString(),
    ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('persona.primer_nombre')
                    ->label('Nombre')
                    ->formatStateUsing(fn ($record) =>
                        "{$record->persona->primer_nombre} {$record->persona->primer_apellido}")
                    ->searchable(['primer_nombre', 'primer_apellido']),

                Tables\Columns\TextColumn::make('persona.dni')
                    ->label('DNI')
                    ->searchable(),

                Tables\Columns\TextColumn::make('numero_colegiacion')
                    ->label('NÂ° ColegiaciÃ³n')
                    ->searchable(),

                Tables\Columns\TextColumn::make('persona.telefono')
                    ->label('TelÃ©fono')
                    ->searchable(),

                Tables\Columns\TextColumn::make('especialidades.especialidad')
                    ->label('Especialidades')
                    ->badge()
                    ->separator(',')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('horario_entrada')
                    ->label('Hora de Entrada')
                    ->time('g:i A'),

                Tables\Columns\TextColumn::make('horario_salida')
                    ->label('Hora de Salida')
                    ->time('g:i A'),

                Tables\Columns\IconColumn::make('persona.user.id')
                    ->label('Usuario')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($record) => $record->persona->user ? 'Tiene usuario: ' . $record->persona->user->name : 'Sin usuario de acceso'),
            ])
            ->filters([
                // Filtros opcionales
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver')
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\EditAction::make()
                        ->label('Editar')
                        ->icon('heroicon-o-pencil'),

                    Tables\Actions\Action::make('crear_usuario')
                        ->label('Crear Usuario')
                        ->icon('heroicon-o-user-plus')
                        ->color('success')
                        ->visible(fn (Medico $record) => !$record->persona->user)
                        ->modalHeading('Crear Usuario de Acceso')
                        ->modalDescription('Complete los datos para crear un usuario de acceso al sistema para este mÃ©dico')
                        ->form([
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('auto_generate_modal')
                                    ->label('ðŸŽ² Generar datos automÃ¡ticamente')
                                    ->icon('heroicon-o-sparkles')
                                    ->size('lg')
                                    ->color('success')
                                    ->outlined()
                                    ->extraAttributes([
                                        'class' => 'w-full justify-center'
                                    ])
                                    ->action(function (callable $set, Medico $record) {
                                        $primerNombre = $record->persona->primer_nombre;
                                        $primerApellido = $record->persona->primer_apellido;

                                        if ($primerNombre && $primerApellido) {
                                            $username = strtolower($primerNombre . '.' . $primerApellido);
                                            $username = preg_replace('/[^a-z0-9.]/', '', $username);

                                            $email = $username . '@clinica.com';
                                            $password = 'Temp' . rand(1000, 9999);

                                            $set('username', $username);
                                            $set('user_email', $email);
                                            $set('password', $password);
                                            $set('password_confirmation', $password);

                                            Notification::make()
                                                ->title('Datos generados automÃ¡ticamente')
                                                ->body("Usuario: {$username}\nEmail: {$email}\nContraseÃ±a: {$password}")
                                                ->icon('heroicon-o-sparkles')
                                                ->iconColor('success')
                                                ->success()
                                                ->persistent()
                                                ->actions([
                                                    \Filament\Notifications\Actions\Action::make('copy')
                                                        ->label('Copiar contraseÃ±a')
                                                        ->icon('heroicon-o-clipboard')
                                                        ->button()
                                                        ->color('success')
                                                        ->close()
                                                        ->action(function () use ($password) {
                                                            return $password;
                                                        }),
                                                ])
                                                ->send();
                                            $username = strtolower($primerNombre . '.' . $primerApellido);
                                            $username = preg_replace('/[^a-z0-9.]/', '', $username);

                                            $email = $username . '@clinica.com';
                                            $password = 'Temp' . rand(1000, 9999);

                                            $set('username', $username);
                                            $set('user_email', $email);
                                            $set('password', $password);
                                            $set('password_confirmation', $password);

                                            Notification::make()
                                                ->title('Datos generados automÃ¡ticamente')
                                                ->success()
                                                ->send();
                                        }
                                    }),
                            ])->columnSpanFull(),

                            Forms\Components\Section::make('Datos del Usuario')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('username')
                                                ->label('Nombre de usuario')
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder('Ej: juan.perez')
                                                ->helperText('Usado para iniciar sesiÃ³n en el sistema')
                                                ->live(debounce: 500)
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    $set('user_email', strtolower($state) . '@clinica.com');
                                                })
                                                ->rules([
                                                    'regex:/^[a-zA-Z0-9._-]+$/',
                                                    function () {
                                                        return function (string $attribute, $value, \Closure $fail) {
                                                            if (\App\Models\User::where('name', $value)->exists()) {
                                                                $fail('Este nombre de usuario ya estÃ¡ en uso.');
                                                            }
                                                        };
                                                    },
                                                ]),

                                            Forms\Components\TextInput::make('user_email')
                                                ->label('Email corporativo')
                                                ->email()
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder('Ej: juan.perez@clinica.com')
                                                ->rules([
                                                    function () {
                                                        return function (string $attribute, $value, \Closure $fail) {
                                                            if (\App\Models\User::where('email', $value)->exists()) {
                                                                $fail('Este email ya estÃ¡ en uso.');
                                                            }
                                                        };
                                                    },
                                                ]),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('user_password')
                                                ->label('ContraseÃ±a')
                                                ->password()
                                                ->required()
                                                ->minLength(8)
                                                ->maxLength(255)
                                                ->placeholder('MÃ­nimo 8 caracteres'),

                                            Forms\Components\TextInput::make('user_password_confirmation')
                                                ->label('Confirmar contraseÃ±a')
                                                ->password()
                                                ->required()
                                                ->same('user_password')
                                                ->placeholder('Repita la contraseÃ±a'),
                                        ]),

                                    Forms\Components\Select::make('user_role')
                                        ->label('Rol en el sistema')
                                        ->options([
                                            'medico' => 'MÃ©dico - Puede gestionar pacientes y consultas',
                                            
                                        ])
                                        ->default('medico')
                                        ->required(),

                                    Forms\Components\Toggle::make('user_active')
                                        ->label('Usuario activo')
                                        ->helperText('Determine si el usuario puede acceder inmediatamente')
                                        ->default(true)
                                        ->inline(false),
                                ])
                        ])
                        ->action(function (Medico $record, array $data) {
                            try {
                                // Multi-tenant: el usuario se crea en el tenant actual
                                // No es necesario especificar centro_id
                                
                                // Crear el usuario
                                $user = \App\Models\User::create([
                                    'name' => $data['username'],
                                    'email' => $data['user_email'],
                                    'password' => Hash::make($data['user_password']),
                                    'persona_id' => $record->persona->id,
                                    'email_verified_at' => $data['user_active'] ? now() : null,
                                ]);

                                // Asignar rol
                                $user->assignRole($data['user_role']);

                                Notification::make()
                                    ->title('âœ… Usuario creado exitosamente')
                                    ->body("Usuario '{$data['username']}' creado para {$record->persona->primer_nombre} {$record->persona->primer_apellido}")
                                    ->success()
                                    ->persistent()
                                    ->send();

                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('âŒ Error al crear usuario')
                                    ->body("Error: " . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar')
                        ->icon('heroicon-o-trash')
                        ->modalHeading('Eliminar MÃ©dico')
                        ->modalDescription('Â¿EstÃ¡s seguro de que deseas eliminar este mÃ©dico y sus datos personales? Esta acciÃ³n no se puede deshacer.')
                        ->modalSubmitActionLabel('SÃ­, eliminar')
                        ->modalCancelActionLabel('Cancelar')
                        ->action(function (Medico $record) {
                            DB::transaction(function () use ($record) {
                                $record->delete();
                                $record->persona()->delete();
                            });
                        })
                        ->successNotificationTitle('MÃ©dico y datos personales eliminados correctamente'),
                ])
                ->label('Opciones')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('success')
                ->button()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->searchPlaceholder('Buscar');
    }

    protected function getCreateFormAction(): PageAction
    {
        return PageAction::make('create')
            ->label('Crear MÃ©dico') // Texto personalizado del botÃ³n
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicos::route('/'),
            'create' => Pages\CreateMedico::route('/create'),
            'view' => Pages\ViewMedico::route('/{record}'), //
            'edit' => Pages\EditMedico::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Ordenar por fecha de creaciÃ³n descendente
        $query->orderBy('created_at', 'desc');

        return $query;
    }

    public static function handleMedicoCreation(array $data): Medico
    {
        DB::beginTransaction();

        try {
            // Resolver centro solo si alguna tabla aÃºn conserva la columna centro_id.
            $centroId = tenancy()->initialized ? tenancy()->tenant?->centro_id : null;

            $persona = Persona::where('dni', $data['dni'])->first();

            if (!$persona) {
                $personaData = [
                    'dni' => $data['dni'],
                    'primer_nombre' => $data['primer_nombre'],
                    'segundo_nombre' => $data['segundo_nombre'] ?? null,
                    'primer_apellido' => $data['primer_apellido'],
                    'segundo_apellido' => $data['segundo_apellido'] ?? null,
                    'telefono' => $data['telefono'] ?? null,
                    'direccion' => $data['direccion'] ?? null,
                    'sexo' => $data['sexo'],
                    'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                    'nacionalidad_id' => $data['nacionalidad_id'] ?? null,
                ];
                $persona = Persona::create($personaData);
            }
            // Siempre guardar la fotografÃ­a si viene en el formulario
            if (isset($data['fotografia']) && $data['fotografia']) {
                $persona->fotografia = $data['fotografia'];
                $persona->save();
            }

            $medicoData = [
                'persona_id' => $persona->id,
                'numero_colegiacion' => $data['numero_colegiacion'],
                'horario_entrada' => $data['horario_entrada'],
                'horario_salida' => $data['horario_salida'],
            ];

            if (Schema::hasColumn('medicos', 'centro_id')) {
                $medicoData['centro_id'] = $centroId;
            }

            $medico = Medico::create($medicoData);

            if (isset($data['especialidades'])) {
                $medico->especialidades()->sync($data['especialidades']);
            }

            // Crear el contrato mÃ©dico
            if (isset($data['salario_quincenal']) && isset($data['porcentaje_servicio'])) {
                $contratoData = [
                    'medico_id' => $medico->id,
                    'salario_quincenal' => $data['salario_quincenal'],
                    'salario_mensual' => $data['salario_quincenal'] * 2,
                    'porcentaje_servicio' => $data['porcentaje_servicio'] ?? 0,
                    'fecha_inicio' => $data['fecha_inicio'],
                    'fecha_fin' => isset($data['fecha_fin']) && $data['fecha_fin'] ? $data['fecha_fin'] : null,
                    'activo' => $data['activo'] ?? true,
                    'observaciones' => $data['observaciones_contrato'] ?? null, // AÃ±adir observaciones si existen
                ];

                if (Schema::hasColumn('contratos_medicos', 'centro_id')) {
                    $contratoData['centro_id'] = $centroId;
                }

                $contrato = \App\Models\ContabilidadMedica\ContratoMedico::create($contratoData);
            }

            // Crear usuario si se ha solicitado
            if (isset($data['crear_usuario']) && $data['crear_usuario']) {
                
                // Validar que se proporcionaron todos los datos requeridos
                if (empty($data['username']) || empty($data['user_email']) || empty($data['user_password'])) {
                    throw new \Exception("Para crear el usuario debe proporcionar: nombre de usuario, email y contraseÃ±a.");
                }
                
                try {
                    $userData = [
                        'name' => $data['username'],
                        'email' => $data['user_email'],
                        'password' => Hash::make($data['user_password']),
                        'persona_id' => $persona->id,
                        'email_verified_at' => $data['user_active'] ? now() : null,
                    ];

                    if (Schema::hasColumn('users', 'centro_id')) {
                        $userData['centro_id'] = $centroId;
                    }

                    $user = \App\Models\User::create($userData);

                    // Asignar rol
                    $user->assignRole($data['user_role'] ?? 'medico');

                    Notification::make()
                        ->title('âœ… Usuario creado exitosamente')
                        ->body("Usuario '{$data['username']}' creado para {$persona->primer_nombre} {$persona->primer_apellido}")
                        ->success()
                        ->persistent()
                        ->send();
                } catch (\Exception $e) {
                    throw new \Exception("Error al crear el usuario: " . $e->getMessage());
                }
            }

            DB::commit();

            return $medico;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}






