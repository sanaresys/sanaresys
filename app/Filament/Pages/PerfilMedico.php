<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use App\Models\Medico;
use App\Models\Recetario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Exception;

class PerfilMedico extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Mi Perfil';
    protected static ?string $title = 'Mi Perfil Médico';
    protected static ?string $navigationGroup = 'Mi Cuenta';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.perfil-medico';

    public function getTitle(): string
    {
        $user = Auth::user();
        if ($user && $user->hasRole('root') && !$user->medico) {
            return 'Perfil Médico (Acceso Root)';
        }
        return 'Mi Perfil Médico';
    }

    public ?array $data = [];
    public ?array $recetarioData = [];

    public function mount(): void
    {
        $this->checkMedicoAccess();
        $this->loadMedicoData();
        $this->loadRecetarioData();
        
        // Inicializar los formularios con los datos cargados
        $this->form->fill($this->data);
        $this->getRecetarioForm()->fill($this->recetarioData);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema($this->getFormSchema())
                    ->statePath('data')
            ),
            'recetarioForm' => $this->makeForm()
                ->schema($this->getRecetarioFormSchema())
                ->statePath('recetarioData')
        ];
    }

    protected function checkMedicoAccess(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->hasRole('medico') && !$user->hasRole('root'))) {
            abort(403, 'Solo los usuarios con rol de médico o root pueden acceder a esta página.');
        }
    }

    protected function loadMedicoData(): void
    {
        $user = Auth::user();
        
        // Si tiene un registro de médico, usar esos datos
        if ($user->medico) {
            $medico = $user->medico;
            $persona = $medico->persona;

            $this->data = [
                'nombre_completo' => trim("{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}"),
                'dni' => $persona->dni,
                'telefono' => $persona->telefono,
                'email' => $user->email,
                'numero_colegiacion' => $medico->numero_colegiacion,
                'horario_entrada' => $medico->horario_entrada,
                'horario_salida' => $medico->horario_salida,
            ];
        } else {
            // Si es root o solo tiene rol de médico pero no registro, usar datos de persona o usuario
            $persona = $user->persona;
            
            if ($persona) {
                $this->data = [
                    'nombre_completo' => trim("{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}"),
                    'dni' => $persona->dni,
                    'telefono' => $persona->telefono,
                    'email' => $user->email,
                    'numero_colegiacion' => 'No asignado',
                    'horario_entrada' => 'No definido',
                    'horario_salida' => 'No definido',
                ];
            } else {
                // Para usuario root sin persona asociada
                $this->data = [
                    'nombre_completo' => $user->name ?? 'Usuario Root',
                    'dni' => 'No definido',
                    'telefono' => 'No definido',
                    'email' => $user->email,
                    'numero_colegiacion' => 'No asignado',
                    'horario_entrada' => 'No definido',
                    'horario_salida' => 'No definido',
                ];
            }
        }
    }

    protected function loadRecetarioData(): void
    {
        $user = Auth::user();
        
        // Si tiene registro de médico, buscar recetarios
        if ($user->medico) {
            $medico = $user->medico;
            $recetario = $medico->recetarios()->latest()->first();

            if ($recetario) {
                // Procesar logo correctamente - debe venir como string desde la BD
                $logo = $recetario->logo;
                
                // Debug para ver qué tenemos en la BD
                \Log::info('Logo desde BD:', ['logo' => $logo, 'tipo' => gettype($logo)]);
                
                // Multi-tenant: centro_id no es necesario en recetarios
                $this->recetarioData = [
                    'tiene_recetario' => $recetario->tiene_recetario ?? true,
                    'logo' => $logo, // Mantener como string
                    'color_primario' => $recetario->color_primario ?? '#2563eb',
                    'color_secundario' => $recetario->color_secundario ?? '#64748b',
                    'fuente_familia' => $recetario->fuente_familia ?? 'Arial',
                    'fuente_tamano' => $recetario->fuente_tamano ?? 12,
                    'mostrar_logo' => $recetario->mostrar_logo ?? true,
                    'mostrar_especialidades' => $recetario->mostrar_especialidades ?? true,
                    'mostrar_telefono' => $recetario->mostrar_telefono ?? true,
                    'mostrar_direccion' => $recetario->mostrar_direccion ?? true,
                    'texto_adicional' => $recetario->texto_adicional ?? '',
                    'formato_papel' => $recetario->formato_papel ?? 'half',
                    // Campos personalizados
                    'titulo_medico' => $recetario->titulo ?? 'Dr.',
                    'nombre_mostrar_medico' => $recetario->nombre_mostrar ?? ($medico->persona ? trim("{$medico->persona->primer_nombre} {$medico->persona->segundo_nombre} {$medico->persona->primer_apellido} {$medico->persona->segundo_apellido}") : ''),
                    'telefonos_medico' => $recetario->telefono_mostrar ?? ($medico->persona->telefono ?? ''),
                ];
            } else {
                // Multi-tenant: centro_id no es necesario
                $this->recetarioData = [
                    'tiene_recetario' => false,
                    'logo' => null,
                    'color_primario' => '#2563eb',
                    'color_secundario' => '#64748b',
                    'fuente_familia' => 'Arial',
                    'fuente_tamano' => 12,
                    'mostrar_logo' => true,
                    'mostrar_especialidades' => true,
                    'mostrar_telefono' => true,
                    'mostrar_direccion' => true,
                    'texto_adicional' => '',
                    'formato_papel' => 'half',
                ];
            }
        } else {
            // Si es root o solo tiene rol médico pero no registro de médico
            // Multi-tenant: centro_id no es necesario
            $this->recetarioData = [
                'tiene_recetario' => false,
                'logo' => null,
                'color_primario' => '#2563eb',
                'color_secundario' => '#64748b',
                'fuente_familia' => 'Arial',
                'fuente_tamano' => 12,
                'mostrar_logo' => true,
                'mostrar_especialidades' => true,
                'mostrar_telefono' => true,
                'mostrar_direccion' => true,
                'texto_adicional' => '',
                'formato_papel' => 'half',
            ];
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Información Personal')
                ->description('Información básica del médico')
                ->schema([
                    TextInput::make('nombre_completo')
                        ->label('Nombre Completo')
                        ->disabled()
                        ->columnSpan(2),

                    TextInput::make('dni')
                        ->label('DNI')
                        ->disabled()
                        ->columnSpan(1),

                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->disabled()
                        ->columnSpan(1),

                    TextInput::make('email')
                        ->label('Email')
                        ->disabled()
                        ->columnSpan(2),

                    TextInput::make('numero_colegiacion')
                        ->label('Número de Colegiación')
                        ->disabled()
                        ->columnSpan(1),

                    TextInput::make('horario_entrada')
                        ->label('Horario de Entrada')
                        ->disabled()
                        ->columnSpan(1),

                    TextInput::make('horario_salida')
                        ->label('Horario de Salida')
                        ->disabled()
                        ->columnSpan(1),
                ])
                ->columns(2),
        ];
    }

    protected function getRecetarioFormSchema(): array
    {
        return [
            Section::make('Configuración del Recetario')
                ->description('Active y configure la apariencia de su recetario médico')
                ->schema([
                    Toggle::make('tiene_recetario')
                        ->label('Personalizar Recetario')
                        ->helperText('Active esta opción para habilitar su recetario personalizado')
                        ->live()
                        ->columnSpanFull(),
                ]),
     
            Section::make('Diseño y Personalización')
                ->description('Configure la apariencia visual de su recetario')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            FileUpload::make('logo')
                                ->label('Logo de la Clínica/Consultorio/Centro Médico')
                                ->disk('public')
                                ->directory('recetarios/logos')
                                ->image()
                                ->imageEditor()
                                ->imageEditorAspectRatios([
                                    '16:9',
                                    '4:3',
                                    '1:1',
                                    ])
                                ->maxSize(2048)
                                ->helperText('Imagen que aparecerá en el encabezado (máximo 2MB)')
                                ->disabled(fn() => !$this->canUploadLogo())
                                ->multiple(false)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                                ->live()
                                ->afterStateUpdated(function ($state) {
                                    // Debug para ver qué llega cuando se sube
                                \Log::info('Logo uploaded state:', ['state' => $state, 'tipo' => gettype($state)]);
                                    }),

                            Toggle::make('mostrar_logo')
                                ->label('Mostrar Logo')
                                ->helperText('Mostrar u ocultar el logo en el recetario')
                                ->live(),
                                ]),

                        Grid::make(2)
                                ->schema([
                                    TextInput::make('titulo_medico')
                                        ->label('Título del Médico')
                                        ->helperText('Ej: Dr., Dra., Lic., etc.')
                                        ->live(),

                                    TextInput::make('nombre_mostrar_medico')
                                        ->label('Nombre a Mostrar del Médico')
                                        ->helperText('Nombre completo que aparecerá en el recetario.')
                                        ->live(),

                                    TextInput::make('telefonos_medico')
                                        ->label('Teléfonos del Médico')
                                        ->placeholder('Ej: 1234-5678, 9876-5432')
                                        ->helperText('Ingrese uno o más números de teléfono, separados por comas.')
                                        ->live()
                                        ->rules(['nullable', 'string']), // Removed 'tel' rule for more flexibility with commas

                                ]),

                                
                    ])
                ->visible(fn (callable $get) => $get('tiene_recetario'))
                ->columns(1),
                
            Section::make('Colores y Tipografía')
                ->description('Personalice los colores y fuentes del recetario')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            ColorPicker::make('color_primario')
                                ->label('Color Primario')
                                ->helperText('Color principal del encabezado')
                                ->live(),
                                
                            ColorPicker::make('color_secundario')
                                ->label('Color Secundario')
                                ->helperText('Color de texto secundario')
                                ->live(),
                        ]),
                        
                    Grid::make(2)
                        ->schema([
                            Select::make('fuente_familia')
                                ->label('Familia de Fuente')
                                ->options([
                                    'Arial' => 'Arial',
                                    'Times New Roman' => 'Times New Roman',
                                    'Helvetica' => 'Helvetica',
                                    'Georgia' => 'Georgia',
                                    'Verdana' => 'Verdana',
                                ])
                                ->helperText('Fuente principal del texto')
                                ->live(),
                                
                            
                        ]),
                ])
                ->visible(fn (callable $get) => $get('tiene_recetario'))
                ->columns(1),
                
            Section::make('Información a Mostrar')
                ->description('Seleccione qué información incluir en el recetario')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('mostrar_telefono')
                                ->label('Mostrar Teléfono')
                                ->helperText('Incluir número de teléfono')
                                ->live(),
                                
                            Toggle::make('mostrar_direccion')
                                ->label('Mostrar Dirección')
                                ->helperText('Incluir dirección del consultorio')
                                ->live(),
                                
                            
                        ]),
                ])
                ->visible(fn (callable $get) => $get('tiene_recetario'))
                ->columns(2),
                
            Section::make('Vista Previa del Recetario')
                ->description('Visualización en tiempo real de su recetario')
                ->schema([
                    Placeholder::make('preview')
                        ->label('')
                        ->dehydrated(false)
                        ->content(function (callable $get) {
                            // Verificar permisos para ver la vista previa
                            if (!$this->canViewPreview()) {
                                return new \Illuminate\Support\HtmlString(
                                    '<div style="text-align: center; padding: 40px; color: #666;">
                                        <p>No tiene permisos para ver la vista previa del recetario.</p>
                                    </div>'
                                );
                            }
                            
                            $config = $get();
                            
                            // Debug para ver la configuración completa
                            \Log::info('Config para preview:', [
                                'color_primario' => $config['color_primario'] ?? 'no definido',
                                'color_secundario' => $config['color_secundario'] ?? 'no definido',
                                'encabezado_texto' => $config['encabezado_texto'] ?? 'no definido'
                            ]);
                            
                            return new \Illuminate\Support\HtmlString(
                                view('components.recetario-preview-demo', compact('config'))->render()
                            );
                        }),
                ])
                ->visible(fn (callable $get) => $get('tiene_recetario') && $this->canViewPreview())
                ->collapsible()
                ->collapsed(false),
                
            Section::make('Guardar Configuración')
                ->schema([
                    Placeholder::make('save_button')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString('
                            <div class="flex justify-center">
                                <button 
                                    type="button" 
                                    wire:click="saveRecetario"
                                    style="background-color: #059669; color: white; border: 2px solid #047857; padding: 10px 20px; border-radius: 6px; font-weight: 500; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: inline-flex; align-items: center;"
                                >
                                    <svg style="width: 20px; height: 20px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Guardar Configuración del Recetario
                                </button>
                            </div>
                        ')),
                ])
                ->visible(fn (callable $get) => $get('tiene_recetario')),
        ];
    }

    public function toggleRecetario(): void
    {
        $this->recetarioData['tiene_recetario'] = !$this->recetarioData['tiene_recetario'];
        $this->actualizarRecetario($this->recetarioData['tiene_recetario']);
    }

    public function cambiarRecetario(): void
    {
        // Este método se llama cuando cambia el checkbox
        $this->actualizarRecetario($this->recetarioData['tiene_recetario']);
    }

    public function save()
    {
        Notification::make()
            ->title('Información')
            ->body('La información personal del médico es de solo lectura.')
            ->info()
            ->send();
    }

    public function saveRecetario()
    {
        try {
            $user = auth()->user();
            
            // Validar permisos usando la policy
            if (!$this->canUpdateRecetario()) {
                Notification::make()
                    ->title('Acceso Denegado')
                    ->body('No tiene permisos para actualizar el recetario.')
                    ->warning()
                    ->send();
                return;
            }
            
            // Validar el formulario primero
            $this->getRecetarioForm()->getState();
            

            $recetarioData = $this->recetarioData;

            // Mapear campos personalizados a columnas de BD
            if (isset($recetarioData['titulo_medico'])) {
                $recetarioData['titulo'] = $recetarioData['titulo_medico'];
                unset($recetarioData['titulo_medico']);
            }
            if (isset($recetarioData['nombre_mostrar_medico'])) {
                $recetarioData['nombre_mostrar'] = $recetarioData['nombre_mostrar_medico'];
                unset($recetarioData['nombre_mostrar_medico']);
            }
            if (isset($recetarioData['telefonos_medico'])) {
                $recetarioData['telefono_mostrar'] = $recetarioData['telefonos_medico'];
                unset($recetarioData['telefonos_medico']);
            }

            // Procesar el logo correctamente
            if (isset($recetarioData['logo'])) {
                $logo = $recetarioData['logo'];
                // Debug completo del logo
                \Log::info('Procesando logo:', [
                    'logo_original' => $logo,
                    'es_array' => is_array($logo),
                    'tipo' => gettype($logo)
                ]);
                // Si es array (viene de FileUpload), tomar el primer elemento
                if (is_array($logo) && !empty($logo)) {
                    $logo = reset($logo);
                }
                // Si es string vacío, convertir a null
                if (empty($logo)) {
                    $logo = null;
                }
                // Verificar que el archivo existe antes de guardarlo
                if ($logo && !Storage::disk('public')->exists($logo)) {
                    \Log::warning('Archivo de logo no encontrado:', ['path' => $logo]);
                    // No establecer logo si el archivo no existe
                    $logo = null;
                }
                $recetarioData['logo'] = $logo;
                \Log::info('Logo procesado final:', [
                    'logo_final' => $logo,
                    'existe_archivo' => $logo ? Storage::disk('public')->exists($logo) : false
                ]);
            } else {
                $recetarioData['logo'] = null;
            }

            $medico = $user->medico;
            
            if (!$medico) {
                throw new Exception('No se encontró registro de médico asociado');
            }
            
            // Buscar o crear recetario
            $recetario = Recetario::firstOrNew(['medico_id' => $medico->id]);
            
            // Llenar con todos los datos
            $recetario->fill($recetarioData);
            
            // Guardar
            $recetario->save();
            
            // Verificar qué se guardó realmente
            $recetario->refresh();
            \Log::info('Recetario guardado en BD:', [
                'id' => $recetario->id,
                'logo_en_bd' => $recetario->logo,
                'existe_archivo' => $recetario->logo ? Storage::disk('public')->exists($recetario->logo) : false
            ]);

            Notification::make()
                ->title('Configuración Guardada')
                ->body('La configuración de su recetario se ha guardado correctamente.')
                ->success()
                ->send();

            // Recargar datos desde la BD
            $this->loadRecetarioData();
            $this->getRecetarioForm()->fill($this->recetarioData);

        } catch (Exception $e) {
            \Log::error('Error al guardar recetario: ' . $e->getMessage());
            Notification::make()
                ->title('Error al Guardar')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getRecetarioForm()
    {
        return $this->makeForm()
            ->schema($this->getRecetarioFormSchema())
            ->statePath('recetarioData')
            ->model(Recetario::class);
    }

    public function actualizarRecetario(bool $estado): void
    {
        $user = Auth::user();

        // Verificar que el usuario tenga registro de médico o sea root
        if (!$user->medico && !$user->hasRole('root')) {
            Notification::make()
                ->title('Error')
                ->body('Necesita tener un registro de médico para activar el recetario. Contacte al administrador.')
                ->danger()
                ->send();
            
            // Revertir el estado del toggle
            $this->recetarioData['tiene_recetario'] = false;
            return;
        }

        // Si es root sin registro de médico, mostrar advertencia pero permitir continuar
        if ($user->hasRole('root') && !$user->medico) {
            Notification::make()
                ->title('Advertencia')
                ->body('Usuario root accediendo sin registro de médico asociado.')
                ->warning()
                ->send();
            
            // Para root, solo cambiar el estado en memoria
            $this->recetarioData['tiene_recetario'] = $estado;
            return;
        }

        $medico = $user->medico;

        if (!$medico) {
            Notification::make()
                ->title('Error')
                ->body('No se encontró registro de médico asociado.')
                ->danger()
                ->send();
            
            // Revertir el estado del toggle
            $this->recetarioData['tiene_recetario'] = false;
            return;
        }

        if (!$estado) {
            // Desactivar recetario existente - eliminar registro
            $medico->recetarios()->delete();
            
            Notification::make()
                ->title('Recetario desactivado')
                ->body('Su recetario ha sido desactivado correctamente.')
                ->success()
                ->send();
            return;
        }

        // Buscar recetario existente o crear uno nuevo
        $recetario = $medico->recetarios()->latest()->first();

        if (!$recetario) {
            // Crear nuevo recetario básico
            Recetario::create([
                'medico_id' => $medico->id,
                'consulta_id' => null,
                'centro_id' => $this->recetarioData['centro_id'] ?? null,
            ]);
            
            Notification::make()
                ->title('Recetario activado')
                ->body('Su recetario ha sido activado correctamente.')
                ->success()
                ->send();
        }
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Usar la policy para determinar el acceso
        return $user->hasRole('medico') || $user->hasRole('root');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    // Método para obtener la vista previa actualizada
    public function getPreviewData()
    {
        return $this->recetarioData;
    }

    // Métodos de permisos simplificados
    public function canUploadLogo(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('medico') || $user->hasRole('root'));
    }

    public function canViewPreview(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('medico') || $user->hasRole('root'));
    }

    public function canUpdateRecetario(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('medico') || $user->hasRole('root'));
    }
}