<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use App\Models\Pacientes;
use App\Models\Medico;
use App\Models\Consulta;
use App\Models\Receta;
use App\Models\Examenes;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class CreateConsultaWithPatientSearch extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ConsultasResource::class;

    protected static string $view = 'filament.resources.consultas.pages.create-consulta-with-patient-search';

    public ?array $patientSearchData = [];
    public ?array $consultaData = [];
    public bool $showConsultaForm = false;
    public ?Pacientes $selectedPatient = null;

    protected function loadSelectedPatientWithContext(int|string $patientId): ?Pacientes
    {
        return Pacientes::with([
            'persona.nacionalidad',
            'enfermedades',
            'consultas' => fn ($query) => $query->latest()->limit(8),
            'consultas.medico.persona',
            'consultas.recetas',
            'consultas.examenes',
        ])->find($patientId);
    }

    public function mount(): void
    {
        $this->patientSearchForm->fill();
        $this->consultaForm->fill();

        // Si se pasa un paciente_id en la URL, precargarlo automáticamente
        if (request()->has('paciente_id')) {
            $pacienteId = request()->get('paciente_id');
            $citaId = request()->get('cita_id'); // Capturar también el cita_id
            $paciente = $this->loadSelectedPatientWithContext($pacienteId);

            if ($paciente && $paciente->persona) {
                $this->selectedPatient = $paciente;
                $this->showConsultaForm = true;

                // Prellenar los formularios
                $this->patientSearchForm->fill(['paciente_id' => $pacienteId]);
                // Multi-tenant: centro_id no es necesario
                $this->consultaForm->fill([
                    'paciente_id' => $pacienteId,
                    'cita_id' => $citaId,
                ]);

                // Verificar que el paciente fue encontrado
                $message = "Paciente precargado: {$paciente->persona->nombre_completo}.";
                if ($citaId) {
                    $message .= " (Cita ID: {$citaId})";
                }

                // Mostrar notificación de paciente precargado
                Notification::make()
                    ->title('Paciente precargado')
                    ->body($message)
                    ->success()
                    ->send();
            }
        }
    }    public function getTitle(): string|Htmlable
    {
        return 'Crear Nueva Consulta';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Volver al listado')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    public function patientSearchForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Buscar Paciente')
                    ->description('Seleccione el paciente para quien desea crear la consulta')
                    ->schema([
                        Forms\Components\Select::make('paciente_id')
                            ->label('Buscar Paciente')
                            ->options(function () {
                                return Pacientes::with('persona')
                                    ->get()
                                    ->filter(function ($p) {
                                        return $p->persona !== null;
                                    })
                                    ->mapWithKeys(function ($p) {
                                        return [$p->id => $p->persona->nombre_completo . ' - DNI: ' . $p->persona->dni];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Escriba el nombre del paciente...')
                            ->helperText('Busque y seleccione el paciente.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan('full'),
            ])
            ->statePath('patientSearchData');
    }

    public function consultaForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('centro_id')
                    ->default(fn () => Auth::check() ? Auth::user()->centro_id : null),

                Forms\Components\Section::make('Información de la Consulta')
                    ->schema([
                        Forms\Components\Hidden::make('paciente_id')
                            ->default(fn () => $this->selectedPatient?->id),

                        // Campo cita_id oculto - se debe asignar al crear la consulta desde el calendario
                        Forms\Components\Hidden::make('cita_id')
                            ->default(fn () => request()->get('cita_id')),

                        Forms\Components\Placeholder::make('medico_info')
                            ->label('Médico')
                            ->content(function () {
                                $user = Auth::user();

                                // Primero intentar con la relación directa
                                if ($user && $user->medico && $user->medico->persona) {
                                    $nombre = $user->medico->persona->nombre_completo;
                                    $dni = $user->medico->persona->dni ?? 'Sin DNI';
                                    return "{$nombre} - DNI: {$dni}";
                                }

                                // Si no tiene relación directa, buscar por persona_id
                                if ($user && $user->persona_id) {
                                    $medico = Medico::withoutGlobalScopes()->where('persona_id', $user->persona_id)->with('persona')->first();
                                    if ($medico && $medico->persona) {
                                        $nombre = $medico->persona->nombre_completo;
                                        $dni = $medico->persona->dni ?? 'Sin DNI';
                                        return "{$nombre} - DNI: {$dni}";
                                    }
                                }

                                // Si tiene persona pero no es médico, mostrar la información del usuario
                                if ($user && $user->persona) {
                                    $nombre = $user->persona->nombre_completo;
                                    $dni = $user->persona->dni ?? 'Sin DNI';
                                    return "{$nombre} - DNI: {$dni} (Usuario)";
                                }

                                return 'No hay médico asociado al usuario';
                            }),

                        Forms\Components\Hidden::make('medico_id')
                            ->default(function () {
                                $user = Auth::user();

                                // Primero intentar con la relación directa
                                if ($user && $user->medico) {
                                    return $user->medico->id;
                                }

                                // Si no tiene relación directa, buscar por persona_id
                                if ($user && $user->persona_id) {
                                    $medico = Medico::withoutGlobalScopes()->where('persona_id', $user->persona_id)->first();
                                    if ($medico) {
                                        return $medico->id;
                                    }
                                }

                                return null;
                            }),
                    ]),

                Forms\Components\Section::make('Detalles Médicos')
                    ->schema([
                        Forms\Components\Textarea::make('diagnostico')
                            ->label('Diagnóstico')
                            ->required()
                            ->rows(4)
                            ->placeholder('Describa el diagnóstico del paciente...')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('tratamiento')
                            ->label('Tratamiento')
                            ->required()
                            ->rows(4)
                            ->placeholder('Describa el tratamiento prescrito...')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->required()
                            ->rows(3)
                            ->placeholder('Describa las observaciones de la consulta...')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Recetas Médicas')
                    ->description('Crear una o varias recetas para el paciente (opcional)')
                    ->schema([
                        Forms\Components\Repeater::make('recetas')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('medicamentos')
                                    ->label('Medicamentos')
                                    ->required()
                                    ->placeholder('Ej: Loratadina 500 mg, Ibuprofeno 400 mg')
                                    ->columnSpan(1)
                                    ->reactive()
                                    ,

                                Forms\Components\TextInput::make('indicaciones')
                                    ->label('Indicaciones')
                                    ->required()
                                    ->placeholder('Tomar una diaria, cada 8 horas por 3 días')
                                    ->columnSpan(1)
                                    ->reactive()
                                    ,
                            ])
                            ->columns(2)
                            ->addActionLabel('➕ Agregar Nueva Receta')
                            ->addAction(function ($action) {
                                return $action
                                    ->color('success')
                                    ->icon('heroicon-m-plus-circle')
                                    ->after(function (callable $set) {
                                        // Actualizar previsualización cuando se agrega una nueva receta
                                        $set('recetas_preview_trigger', uniqid());
                                    });
                            })
                            ->deleteAction(
                                fn ($action) => $action
                                    ->requiresConfirmation()
                                    ->modalHeading('¿Eliminar receta?')
                                    ->modalDescription('¿Está seguro de que desea eliminar esta receta?')
                                    ->modalSubmitActionLabel('Sí, eliminar')
                                    ->color('danger')
                                    ->icon('heroicon-o-trash')
                                    ->after(function (callable $set) {
                                        // Actualizar previsualización cuando se elimina una receta
                                        $set('recetas_preview_trigger', uniqid());
                                    })
                            )
                            ->reorderAction(
                                fn ($action) => $action
                                    ->icon('heroicon-o-bars-3')
                                    ->color('gray')
                            )
                            ->itemLabel(function (array $state): ?string {
                                static $numero = 0;
                                $numero++;

                                if (empty($state['medicamentos'])) {
                                    return "Receta #{$numero}";
                                }

                                $medicamentos = substr($state['medicamentos'], 0, 40);
                                if (strlen($state['medicamentos']) > 40) {
                                    $medicamentos .= '...';
                                }

                                return "Receta #{$numero}: {$medicamentos}";
                            })
                            ->columnSpanFull()
                            ->defaultItems(0)
                            ->extraAttributes([
                                'class' => 'consultation-recetas-repeater'
                            ])
                            ->hint('💡 Las recetas se crearán automáticamente al guardar la consulta')
                            ->hintColor('gray'),

                        // Campo oculto para triggear actualizaciones de la previsualización
                        Forms\Components\Hidden::make('recetas_preview_trigger'),

                        // Previsualización de las recetas en formato tabla
                        Forms\Components\Placeholder::make('recetas_preview')
                            ->label('📋 Previsualización de Recetas')
                            ->extraAttributes([
                                'class' => 'consultation-recetas-preview',
                'x-data' => '{ updating: false }',
                'x-init' => '$watch("$el.querySelector(\'[wire\\:loading]\')", () => { updating = true; setTimeout(() => updating = false, 300) })'
                            ])
                            ->reactive()
                            ->content(function (callable $get) {
                                $recetas = $get('recetas') ?? [];
                                $timestamp = now()->format('H:i:s');

                                if (empty($recetas) || !is_array($recetas)) {
                                    return new \Illuminate\Support\HtmlString('
                                        <div class="flex items-center justify-center p-8">
                                            <div class="text-center">
                                                <div class="text-5xl mb-3 animate-pulse">📝</div>
                                                <p class="text-gray-500 dark:text-gray-400 font-medium">No hay recetas agregadas aún</p>
                                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Use el botón "➕ Agregar Nueva Receta" arriba</p>
                                                <p class="text-xs text-gray-400 mt-2">⏰ Actualizado: ' . $timestamp . '</p>
                                            </div>
                                        </div>
                                    ');
                                }

                                // Filtrar recetas que tengan contenido
                                $recetasValidas = array_filter($recetas, function($receta) {
                                    return !empty($receta['medicamentos']) && !empty($receta['indicaciones']);
                                });

                                if (empty($recetasValidas)) {
                                    $totalRecetas = count($recetas);
                                    $recetasCompletas = count($recetasValidas);

                                    return new \Illuminate\Support\HtmlString('
                                        <div class="flex items-center justify-center p-8">
                                            <div class="text-center">
                                                <div class="text-5xl mb-3 animate-bounce">⚠️</div>
                                                <p class="text-amber-600 dark:text-amber-400 font-medium text-lg">Recetas incompletas</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Complete todos los campos de medicamentos e indicaciones</p>
                                                <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                                                    <p class="text-sm text-amber-700 dark:text-amber-300">
                                                        📊 Progreso: ' . $recetasCompletas . '/' . $totalRecetas . ' recetas completas
                                                    </p>
                                                </div>
                                                <p class="text-xs text-gray-400 mt-2">⏰ Actualizado: ' . $timestamp . '</p>
                                            </div>
                                        </div>
                                    ');
                                }

                                $totalRecetas = count($recetasValidas);

                                $html = '
                                <div class="space-y-4">
                                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800/60 dark:to-slate-900/70 p-4 rounded-lg border border-slate-200 dark:border-slate-700">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-1">📋 Recetas Médicas - Previsualización en Tiempo Real</h4>
                                                <p class="text-xs text-slate-600 dark:text-slate-300">
                                                    ✅ ' . $totalRecetas . ' receta' . ($totalRecetas != 1 ? 's' : '') . ' lista' . ($totalRecetas != 1 ? 's' : '') . ' para crear
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <div class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-100">
                                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                                    Actualizado: ' . $timestamp . '
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead>
                                                <tr class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">💊 Medicamentos</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">📋 Indicaciones</th>
                                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">';

                                $contador = 0;
                                foreach ($recetasValidas as $receta) {
                                    $contador++;
                                    $fecha = now()->format('d/m/Y');
                                    $medicamentos = htmlspecialchars($receta['medicamentos']);
                                    $indicaciones = htmlspecialchars($receta['indicaciones']);

                                    $html .= '
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">' . $contador . '</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' . $fecha . '</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 max-w-xs">
                                                <div class="truncate">' . nl2br($medicamentos) . '</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 max-w-xs">
                                                <div class="truncate">' . nl2br($indicaciones) . '</div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    ✅ Listo
                                                </span>
                                            </td>
                                        </tr>';
                                }

                                $html .= '
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800/60 dark:to-slate-900/70 p-4 rounded-lg border border-slate-200 dark:border-slate-700">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 bg-slate-200 dark:bg-slate-700 rounded-full flex items-center justify-center">
                                                        <span class="text-slate-700 dark:text-slate-200 text-sm font-bold">✓</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100">
                                                        🎯 <strong>' . count($recetasValidas) . ' receta' . (count($recetasValidas) != 1 ? 's' : '') . '</strong> lista' . (count($recetasValidas) != 1 ? 's' : '') . ' para crear
                                                    </p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-300 mt-1">
                                                        Se crearán automáticamente al guardar la consulta
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <div class="w-2 h-2 bg-slate-400 rounded-full animate-pulse"></div>
                                                <span class="text-xs text-slate-500 dark:text-slate-300 font-medium">Actualización en tiempo real</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->reactive()
                            ->columnSpanFull()
                            ->extraAttributes([
                                'style' => 'margin-top: 16px;'
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(false)
                    ->extraAttributes([
                        'class' => 'consultation-recetas-section'
                    ]),

                // 🔬 EXÁMENES MÉDICOS - NUEVA SECCIÓN AGREGADA
                Forms\Components\Section::make('🔬 Exámenes Médicos')
                    ->description('Solicitar exámenes médicos necesarios para el diagnóstico (opcional)')
                    ->schema([
                        Forms\Components\Repeater::make('examenes')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('tipo_examen')
                                    ->label('Tipo de Examen')
                                    ->required()
                                    ->placeholder('Ej: Examen de orina, Hemograma completo, Rayos X')
                                    ->columnSpan(1),

                                Forms\Components\Textarea::make('observaciones')
                                    ->label('Observaciones del Examen')
                                    ->placeholder('Instrucciones especiales para el examen')
                                    ->rows(2)
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->addActionLabel('➕ Agregar Nuevo Examen')
                            ->addAction(function ($action) {
                                return $action
                                    ->color('success')
                                    ->icon('heroicon-m-plus-circle');
                            })
                            ->deleteAction(
                                fn ($action) => $action
                                    ->requiresConfirmation()
                                    ->modalHeading('¿Eliminar examen?')
                                    ->modalDescription('¿Está seguro de que desea eliminar este examen?')
                                    ->modalSubmitActionLabel('Sí, eliminar')
                                    ->color('danger')
                                    ->icon('heroicon-o-trash')
                            )
                            ->reorderAction(
                                fn ($action) => $action
                                    ->icon('heroicon-o-bars-3')
                                    ->color('gray')
                            )
                            ->itemLabel(function (array $state): ?string {
                                static $numero = 0;
                                $numero++;

                                if (empty($state['tipo_examen'])) {
                                    return "Examen #{$numero}";
                                }

                                $tipoExamen = substr($state['tipo_examen'], 0, 40);
                                if (strlen($state['tipo_examen']) > 40) {
                                    $tipoExamen .= '...';
                                }

                                return "🔬 Examen #{$numero}: {$tipoExamen}";
                            })
                            ->columnSpanFull()
                            ->defaultItems(0)
                            ->extraAttributes([
                                'class' => 'consultation-examenes-repeater'
                            ])
                            ->hint('💡 Los exámenes se crearán automáticamente al guardar la consulta')
                            ->hintColor('gray'),
                    ])
                    ->collapsible()
                    ->collapsed(false)
                    ->extraAttributes([
                        'class' => 'consultation-examenes-section'
                    ]),
            ])
            ->statePath('consultaData');
    }

    public function selectPatient(): void
    {
        $data = $this->patientSearchForm->getState();

        if (!$data['paciente_id']) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar un paciente.')
                ->danger()
                ->send();
            return;
        }

        $this->selectedPatient = $this->loadSelectedPatientWithContext($data['paciente_id']);

        if (!$this->selectedPatient) {
            Notification::make()
                ->title('Error')
                ->body('Paciente no encontrado.')
                ->danger()
                ->send();
            return;
        }

        $this->showConsultaForm = true;

        // Prellenar el paciente_id en el formulario de consulta
        // Multi-tenant: centro_id no es necesario
        $this->consultaForm->fill([
            'paciente_id' => $this->selectedPatient->id,
        ]);

        // Forzar actualización del formulario para que se refresquen las opciones
        $this->dispatch('refreshForm');

        // Verificar información del médico para debugging
        $user = Auth::user();
        $medicoInfo = 'Sin médico';

        if ($user && $user->medico) {
            $medicoInfo = "Médico ID: {$user->medico->id}";
        } elseif ($user && $user->persona_id) {
            $medico = Medico::withoutGlobalScopes()->where('persona_id', $user->persona_id)->first();
            if ($medico) {
                $medicoInfo = "Médico encontrado por persona_id: {$medico->id}";
            }
        }

        $message = "Ahora puede proceder a crear la consulta para {$this->selectedPatient->persona->nombre_completo}. ({$medicoInfo})";

        Notification::make()
            ->title('Paciente seleccionado')
            ->body($message)
            ->success()
            ->send();
    }

    public function changePatient(): void
    {
        $this->showConsultaForm = false;
        $this->selectedPatient = null;
        $this->patientSearchForm->fill();
        $this->consultaForm->fill();
    }

    public function create(): void
    {
        $data = $this->consultaForm->getState();

        // Asegurar que el paciente_id esté presente
        $data['paciente_id'] = $this->selectedPatient->id;

        // Capturar cita_id desde múltiples fuentes
        $citaId = $data['cita_id'] ?? request()->get('cita_id') ?? session('cita_en_consulta');
        if ($citaId) {
            $data['cita_id'] = $citaId;
        }

        // Agregar centro_id si está disponible en el usuario autenticado
        if (Auth::check() && Auth::user()->centro_id) {
            $data['centro_id'] = Auth::user()->centro_id;
        }

        // Verificar y obtener medico_id si está vacío
        if (empty($data['medico_id'])) {
            $user = Auth::user();

            // Intentar obtener médico por relación directa
            if ($user && $user->medico) {
                $data['medico_id'] = $user->medico->id;
            }
            // Si no, buscar por persona_id
            elseif ($user && $user->persona_id) {
                $medico = Medico::withoutGlobalScopes()->where('persona_id', $user->persona_id)->first();
                if ($medico) {
                    $data['medico_id'] = $medico->id;
                }
            }
        }

        // Validar que tenemos un medico_id válido
        if (empty($data['medico_id'])) {
            Notification::make()
                ->title('Error: No se pudo determinar el médico')
                ->body('No se encontró un médico asociado al usuario actual. Contacte al administrador.')
                ->danger()
                ->send();
            return;
        }

        // Log para debugging
        Log::info('CREANDO CONSULTA - Datos:', [
            'paciente_id' => $data['paciente_id'] ?? 'null',
            'medico_id' => $data['medico_id'] ?? 'null',
            'cita_id' => $data['cita_id'] ?? 'null',
            'centro_id' => $data['centro_id'] ?? 'null',
            'request_cita_id' => request()->get('cita_id'),
            'session_cita_id' => session('cita_en_consulta')
        ]);

        // Extraer las recetas y exámenes del data para procesarlas por separado
        $recetas = $data['recetas'] ?? [];
        $examenes = $data['examenes'] ?? [];
        unset($data['recetas']); // Remover recetas del data de consulta
        unset($data['examenes']); // Remover exámenes del data de consulta

        try {
            // Crear la consulta
            $consulta = Consulta::create($data);

            Log::info('CONSULTA CREADA EXITOSAMENTE', [
                'consulta_id' => $consulta->id,
                'cita_id_guardado' => $consulta->cita_id,
                'datos_enviados' => $data
            ]);

            $recetasCreadas = 0;
            $examenesCreados = 0;

            // Crear las recetas si existen
            if (!empty($recetas)) {
                foreach ($recetas as $recetaData) {
                    if (!empty($recetaData['medicamentos']) && !empty($recetaData['indicaciones'])) {
                        Receta::create([
                            'medicamentos' => $recetaData['medicamentos'],
                            'indicaciones' => $recetaData['indicaciones'],
                            'paciente_id' => $this->selectedPatient->id,
                            'consulta_id' => $consulta->id,
                            'medico_id' => $data['medico_id'],
                            'centro_id' => $data['centro_id'] ?? null,
                        ]);
                        $recetasCreadas++;
                    }
                }
            }

            // Crear los exámenes si existen
            if (!empty($examenes)) {
                foreach ($examenes as $examenData) {
                    if (!empty($examenData['tipo_examen'])) {
                        \App\Models\Examenes::create([
                            'tipo_examen' => $examenData['tipo_examen'],
                            'observaciones' => $examenData['observaciones'] ?? null,
                            'paciente_id' => $this->selectedPatient->id,
                            'consulta_id' => $consulta->id,
                            'medico_id' => $data['medico_id'],
                            'centro_id' => $data['centro_id'] ?? null,
                            'estado' => 'Solicitado',
                        ]);
                        $examenesCreados++;
                    }
                }
            }

            $message = 'La consulta para ' . $this->selectedPatient->persona->nombre_completo . ' ha sido creada.';
            if ($recetasCreadas > 0) {
                $message .= " Se crearon {$recetasCreadas} receta(s) médica(s).";
            }
            if ($examenesCreados > 0) {
                $message .= " Se solicitaron {$examenesCreados} examen(es) médico(s).";
            }

            // Actualizar estado de la cita si existe
            if ($consulta->cita_id) {
                Log::info('BUSCANDO CITA PARA ACTUALIZAR', ['cita_id' => $consulta->cita_id]);
                
                $cita = \App\Models\Citas::find($consulta->cita_id);

                if ($cita) {
                    Log::info('CITA ENCONTRADA - Estado actual:', [
                        'cita_id' => $cita->id,
                        'estado_anterior' => $cita->estado
                    ]);

                    // Actualizar el estado de la cita a "Realizado"
                    $cita->estado = 'Realizado';
                    $cita->save();

                    Log::info('CITA ACTUALIZADA', [
                        'cita_id' => $cita->id,
                        'estado_nuevo' => $cita->estado
                    ]);

                    // Crear notificación adicional
                    Notification::make()
                        ->title('Cita completada')
                        ->body('La cita ha sido marcada como realizada')
                        ->success()
                        ->send();
                        
                    $message .= ' La cita asociada ha sido marcada como realizada.';
                } else {
                    Log::warning('CITA NO ENCONTRADA', ['cita_id' => $consulta->cita_id]);
                }

                // Limpiar la sesión
                session()->forget('cita_en_consulta');
            } else {
                Log::info('NO HAY CITA_ID EN LA CONSULTA CREADA');
            }

            Notification::make()
                ->title('Consulta creada exitosamente')
                ->body($message)
                ->success()
                ->send();

            // Redirigir a la vista previa de la consulta recién creada
            $this->redirect($this->getResource()::getUrl('view', ['record' => $consulta->id]));
        } catch (\Exception $e) {
            Log::error('ERROR AL CREAR CONSULTA', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);

            Notification::make()
                ->title('Error al crear la consulta')
                ->body('Ocurrió un error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function redirectToCreatePatient(): void
    {
        // Construir la URL para crear un nuevo paciente con un parámetro de retorno
        $createPatientUrl = \App\Filament\Resources\PacientesResource::getUrl('create') . '?return_to=create_consulta';

        // Mostrar notificación informativa
        Notification::make()
            ->title('Redirigiendo a crear paciente')
            ->body('Será redirigido al formulario de creación de paciente. Después de crear el paciente, podrá regresar a crear la consulta.')
            ->info()
            ->send();

        // Redirigir a la página de creación de pacientes
        $this->redirect($createPatientUrl);
    }

    public function getRecentConsultas(): Collection
    {
        if (! $this->selectedPatient) {
            return collect();
        }

        return $this->selectedPatient->consultas->take(5);
    }

    public function getRecentRecetas(): Collection
    {
        if (! $this->selectedPatient) {
            return collect();
        }

        return Receta::query()
            ->where('paciente_id', $this->selectedPatient->id)
            ->latest()
            ->limit(5)
            ->get();
    }

    public function getRecentExamenes(): Collection
    {
        if (! $this->selectedPatient) {
            return collect();
        }

        return Examenes::query()
            ->where('paciente_id', $this->selectedPatient->id)
            ->latest()
            ->limit(5)
            ->get();
    }

    public function getMedicamentosActivos(): Collection
    {
        if (! $this->selectedPatient) {
            return collect();
        }

        // 1) Priorizar tratamientos guardados al crear/editar paciente
        //    (tabla enfermedades_pacientes, campo pivot.tratamiento).
        $tratamientosDesdeAntecedentes = $this->selectedPatient->enfermedades
            ->map(function ($enfermedad) {
                return $enfermedad->pivot->tratamiento ?? null;
            })
            ->filter(fn ($valor) => filled(trim((string) $valor)))
            ->flatMap(function ($texto) {
                return preg_split('/[,;\n]+/', (string) $texto) ?: [];
            })
            ->map(fn ($valor) => trim((string) $valor))
            ->filter()
            ->unique()
            ->values();

        if ($tratamientosDesdeAntecedentes->isNotEmpty()) {
            return $tratamientosDesdeAntecedentes->take(8);
        }

        $medicamentosDesdeRecetas = Receta::query()
            ->where('paciente_id', $this->selectedPatient->id)
            ->latest()
            ->limit(15)
            ->pluck('medicamentos')
            ->filter(fn ($valor) => filled(trim((string) $valor)))
            ->flatMap(function ($texto) {
                return preg_split('/[,;\n]+/', (string) $texto) ?: [];
            })
            ->map(fn ($medicamento) => trim((string) $medicamento))
            ->filter()
            ->unique()
            ->values();

        if ($medicamentosDesdeRecetas->isNotEmpty()) {
            return $medicamentosDesdeRecetas->take(8);
        }

        return Consulta::query()
            ->where('paciente_id', $this->selectedPatient->id)
            ->whereNotNull('tratamiento')
            ->where('tratamiento', '!=', '')
            ->latest()
            ->limit(8)
            ->pluck('tratamiento')
            ->map(fn ($tratamiento) => trim((string) $tratamiento))
            ->filter()
            ->unique()
            ->values()
            ->take(5);
    }

    protected function getForms(): array
    {
        return [
            'patientSearchForm',
            'consultaForm',
        ];
    }
}
