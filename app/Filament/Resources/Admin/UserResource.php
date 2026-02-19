<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\UserResource\Pages;
use App\Filament\Resources\Admin\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Textarea;

class UserResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
    return auth()->user()?->can('crear usuario');
    }
    
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Gestión de Seguridad';

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Información Personal')
                        ->schema([
                            TextInput::make('persona.dni')
                                ->label('DNI')
                                ->required()
                                ->maxLength(20)
                                ->live(debounce: 500)
                                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set, Forms\Get $get, $livewire) {
                                    // Solo ejecutar durante la creación
                                    if (!($livewire instanceof \Filament\Resources\Pages\CreateRecord) || !$state) {
                                        return;
                                    }
                                    
                                    // Buscar persona existente por DNI
                                    $persona = \App\Models\Persona::where('dni', $state)->first();
                                    
                                    if ($persona) {
                                        // Verificar si esta persona ya tiene un usuario asociado
                                        $usuarioExistente = \App\Models\User::where('persona_id', $persona->id)->first();
                                        
                                        if ($usuarioExistente) {
                                            // Mostrar advertencia de que ya existe un usuario - esto impedirá continuar
                                            \Filament\Notifications\Notification::make()
                                                ->title('❌ No se puede continuar')
                                                ->body("Esta persona ya tiene un usuario asociado: {$usuarioExistente->name} ({$usuarioExistente->email}). No se puede crear otro usuario para la misma persona.")
                                                ->danger()
                                                ->persistent()
                                                ->send();
                                        } else {
                                            // Llenar automáticamente los campos con los datos encontrados
                                            $set('persona.primer_nombre', $persona->primer_nombre);
                                            $set('persona.segundo_nombre', $persona->segundo_nombre);
                                            $set('persona.primer_apellido', $persona->primer_apellido);
                                            $set('persona.segundo_apellido', $persona->segundo_apellido);
                                            $set('persona.telefono', $persona->telefono);
                                            $set('persona.direccion', $persona->direccion);
                                            $set('persona.sexo', $persona->sexo);
                                            $set('persona.fecha_nacimiento', $persona->fecha_nacimiento);
                                            $set('persona.nacionalidad_id', $persona->nacionalidad_id);
                                            
                                            // También llenar el email del usuario si el usuario tiene email
                                            // Nota: El email está en la tabla users, no en personas
                                            
                                            // Mostrar notificación de éxito
                                            \Filament\Notifications\Notification::make()
                                                ->title('✅ Persona encontrada')
                                                ->body("Se encontraron datos para el DNI: {$state}. Los campos se han llenado automáticamente. Puede continuar creando el usuario para esta persona.")
                                                ->success()
                                                ->send();
                                        }
                                    } else {
                                        // Nueva persona
                                        \Filament\Notifications\Notification::make()
                                            ->title('📝 Nueva persona')
                                            ->body("No se encontraron datos para el DNI: {$state}. Complete los campos para crear una nueva persona.")
                                            ->info()
                                            ->send();
                                    }
                                })
                                ->helperText(function($livewire) {
                                    if ($livewire instanceof \Filament\Resources\Pages\CreateRecord) {
                                        return 'Ingrese el DNI para buscar automáticamente los datos de la persona si ya existe en el sistema';
                                    }
                                    return 'DNI de la persona asociada a este usuario';
                                })
                                ->rules([
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            if (!$value) return;
                                            
                                            // Solo validar durante la creación
                                            $livewire = request()->route()->getController();
                                            if (!($livewire instanceof \Filament\Resources\Pages\CreateRecord)) {
                                                return; // Skip validation during edit
                                            }
                                            
                                            $persona = \App\Models\Persona::where('dni', $value)->first();
                                            if ($persona) {
                                                // Verificar si esta persona ya tiene un usuario asociado
                                                $usuarioExistente = \App\Models\User::where('persona_id', $persona->id)->first();
                                                if ($usuarioExistente) {
                                                    $fail("Esta persona ya tiene un usuario asociado: {$usuarioExistente->name} ({$usuarioExistente->email})");
                                                }
                                                // Si no tiene usuario asociado, permitir continuar
                                            }
                                            // Si no existe la persona, también permitir continuar (nueva persona)
                                        };
                                    }
                                ]),
                            
                            Forms\Components\Placeholder::make('estado_persona')
                                ->label('Estado de la persona')
                                ->content(function (Forms\Get $get, $livewire) {
                                    // Solo mostrar durante la creación
                                    if (!($livewire instanceof \Filament\Resources\Pages\CreateRecord)) {
                                        return null;
                                    }
                                    
                                    $dni = $get('persona.dni');
                                    if (!$dni) {
                                        return 'Ingrese un DNI para verificar si la persona existe';
                                    }
                                    
                                    $persona = \App\Models\Persona::where('dni', $dni)->first();
                                    if (!$persona) {
                                        return '✅ Nueva persona - Se creará un nuevo registro';
                                    }
                                    
                                    $usuarioExistente = \App\Models\User::where('persona_id', $persona->id)->first();
                                    if ($usuarioExistente) {
                                        return "❌ ERROR: Esta persona ya tiene usuario: {$usuarioExistente->name} - No se puede continuar";
                                    }
                                    
                                    return '✅ Persona existente encontrada - Se puede crear usuario para esta persona';
                                })
                                ->live()
                                ->columnSpanFull()
                                ->visible(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),

                            TextInput::make('persona.primer_nombre')
                                ->label('Primer Nombre')
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('persona.segundo_nombre')
                                ->label('Segundo Nombre')
                                ->maxLength(255),
                            
                            TextInput::make('persona.primer_apellido')
                                ->label('Primer Apellido')
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('persona.segundo_apellido')
                                ->label('Segundo Apellido')
                                ->maxLength(255),
                            
                            TextInput::make('persona.telefono')
                                ->label('Teléfono')
                                ->tel()
                                ->required()
                                ->maxLength(20),
                            
                            Textarea::make('persona.direccion')
                                ->label('Dirección')
                                ->rows(3)
                                ->required()
                                ->columnSpanFull(),
                            
                            Select::make('persona.sexo')
                                ->label('Sexo')
                                ->options([
                                    'M' => 'Masculino',
                                    'F' => 'Femenino'
                                ])
                                ->required(),
                            
                            DatePicker::make('persona.fecha_nacimiento')
                                ->label('Fecha de Nacimiento')
                                ->required()
                                ->maxDate(now()->subYears(18)),
                            
                            Select::make('persona.nacionalidad_id')
                                ->label('Nacionalidad')
                                ->relationship('persona.nacionalidad', 'nacionalidad')
                                ->preload()
                                ->searchable()
                                ->required(),
                            
                            FileUpload::make('persona.fotografia')
                                ->label('Fotografía')
                                ->image()
                                ->directory('personas')
                                ->maxSize(2048)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    
                    Wizard\Step::make('Información de Usuario')
                        ->schema([
                            TextInput::make('name')
                                ->label('Nombre de Usuario')
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            
                            Forms\Components\TextInput::make('password')
                                ->label('Contraseña')
                                ->password()
                                ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                ->dehydrateStateUsing(fn($state) => !empty($state) ? bcrypt($state) : null)
                                ->dehydrated(fn($state) => filled($state))
                                ->minLength(8)
                                ->revealable()
                                ->autocomplete('new-password'),

                            Forms\Components\TextInput::make('password_confirmation')
                                ->label('Confirmar Contraseña')
                                ->password()
                                ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                ->same('password')
                                ->dehydrated(false)
                                ->revealable()
                                ->autocomplete('new-password'),
                            
                            Select::make('roles')
                                ->label('Roles')
                                ->multiple()
                                ->relationship('roles', 'name', function ($query) {
                                    if (!auth()->user()?->hasRole('root')) {
                                       // Multi-tenant: los roles ya están en el tenant actual
                                       $query->where('name', '!=', 'root'); // Excluir rol root
                                    }
                                    return $query;
                                })
                                ->preload()
                                ->required(),
                            
                            Select::make('centro_id')
                                ->label('Centro Médico')
                                ->options(\App\Models\Centros_Medico::pluck('nombre_centro', 'id'))
                                ->required()
                                ->visible(fn () => auth()->user()?->hasRole('root'))
                                ->default(fn () => auth()->user()?->centro_id),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull()
                ->persistStepInQueryString()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre de Usuario')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('persona.nombre_completo')
                    ->label('Persona')
                    ->getStateUsing(fn ($record) => $record->persona ? 
                        $record->persona->primer_nombre . ' ' . $record->persona->primer_apellido : 
                        'Sin persona'
                    )
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('persona', function ($q) use ($search) {
                            $q->where('primer_nombre', 'like', "%{$search}%")
                              ->orWhere('primer_apellido', 'like', "%{$search}%");
                        });
                    }),
                
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->limit(2),
                
                Tables\Columns\TextColumn::make('centro.nombre_centro')
                    ->label('Centro Médico')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),  
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['persona', 'roles', 'centro']);

        // Multi-tenant: ocultar usuarios root
        if (!auth()->user()?->hasRole('root')) {
            $query->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'root');
            });
        }

        return $query;
    }

    
}
