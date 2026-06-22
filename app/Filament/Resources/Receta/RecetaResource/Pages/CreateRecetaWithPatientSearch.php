<?php

namespace App\Filament\Resources\Receta\RecetaResource\Pages;

use App\Filament\Resources\Receta\RecetaResource;
use App\Models\Pacientes;
use App\Models\Medico;
use App\Models\Receta;
use App\Models\Consulta;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreateRecetaWithPatientSearch extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = RecetaResource::class;

    protected static string $view = 'filament.resources.receta.pages.create-receta-with-patient-search';

    public ?array $patientSearchData = [];
    public ?array $recetaData = [];
    public bool $showRecetaForm = false;
    public ?Pacientes $selectedPatient = null;

    public function mount(): void
    {
        $this->patientSearchForm->fill();
        $this->recetaForm->fill();
    }

    public function getTitle(): string|Htmlable
    {
        return 'Crear Nueva Receta';
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
                    ->description('Seleccione el paciente para quien desea crear la receta')
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
                            ->helperText('Busque y seleccione el paciente para quien desea crear la receta.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan('full'),
            ])
            ->statePath('patientSearchData');
    }

    public function recetaForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Paciente Seleccionado')
                    ->schema([
                        Forms\Components\Placeholder::make('patient_info')
                            ->label('')
                            ->content(function () {
                                if ($this->selectedPatient && $this->selectedPatient->persona) {
                                    return view('filament.components.patient-info', [
                                        'patient' => $this->selectedPatient
                                    ]);
                                }
                                return 'No hay paciente seleccionado';
                            })
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Información de la Receta')
                    ->schema([
                        Forms\Components\Hidden::make('paciente_id')
                            ->default(fn () => $this->selectedPatient?->id),

                        Forms\Components\Select::make('medico_id')
                            ->label('Médico')
                            ->options(function () {
                                return Medico::with('persona')
                                    ->get()
                                    ->filter(function ($m) {
                                        return $m->persona !== null;
                                    })
                                    ->mapWithKeys(function ($m) {
                                        return [$m->id => $m->persona->nombre_completo];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('consulta_id')
                            ->label('Consulta')
                            ->options(function () {
                                if ($this->selectedPatient) {
                                    // Filtrar consultas por el paciente seleccionado
                                    return \App\Models\Consulta::where('paciente_id', $this->selectedPatient->id)
                                        ->orderBy('created_at', 'desc')
                                        ->get()
                                        ->mapWithKeys(function ($consulta) {
                                            $fechaFormateada = \Carbon\Carbon::parse($consulta->created_at)->format('d/m/Y');
                                            return [$consulta->id => "Consulta #{$consulta->id} - {$fechaFormateada}"];
                                        })
                                        ->toArray();
                                } else {
                                    // Mostrar todas las consultas disponibles cuando no hay paciente seleccionado
                                    return \App\Models\Consulta::with(['paciente.persona'])
                                        ->orderBy('created_at', 'desc')
                                        ->get()
                                        ->mapWithKeys(function ($consulta) {
                                            $pacienteNombre = $consulta->paciente && $consulta->paciente->persona
                                                ? $consulta->paciente->persona->nombre_completo
                                                : 'Sin paciente';

                                            $fechaFormateada = \Carbon\Carbon::parse($consulta->created_at)->format('d/m/Y');
                                            return [$consulta->id => "Consulta #{$consulta->id} - {$fechaFormateada} ({$pacienteNombre})"];
                                        })
                                        ->toArray();
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder($this->selectedPatient
                                ? 'Seleccionar consulta del paciente (opcional)'
                                : 'Seleccionar cualquier consulta disponible (opcional)')
                            ->helperText($this->selectedPatient
                                ? 'Se muestran solo las consultas de ' . $this->selectedPatient->persona->nombre_completo
                                : 'Se muestran todas las consultas. Seleccione una para autocargar paciente y médico.')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state && !$this->selectedPatient) {
                                    // Si se selecciona una consulta sin tener paciente seleccionado,
                                    // obtener el paciente de la consulta
                                    $consulta = \App\Models\Consulta::with('paciente.persona')->find($state);
                                    if ($consulta && $consulta->paciente) {
                                        $this->selectedPatient = $consulta->paciente;
                                        $set('paciente_id', $consulta->paciente->id);
                                        $set('medico_id', $consulta->medico_id);

                                        Notification::make()
                                            ->title('Paciente y Médico autocargados')
                                            ->body('Se ha seleccionado automáticamente el paciente y médico de la consulta.')
                                            ->success()
                                            ->send();
                                    }
                                }
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detalles de la Receta')
                    ->schema([
                        Forms\Components\Textarea::make('medicamentos')
                            ->label('Medicamentos')
                            ->required()
                            ->rows(4)
                            ->placeholder('Ej: Loratadina 500mg ')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('indicaciones')
                            ->label('Indicaciones')
                            ->required()
                            ->rows(4)
                            ->placeholder('Tomar una diaria, etc.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('recetaData');
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

        $this->selectedPatient = Pacientes::with('persona')->find($data['paciente_id']);

        if (!$this->selectedPatient) {
            Notification::make()
                ->title('Error')
                ->body('Paciente no encontrado.')
                ->danger()
                ->send();
            return;
        }

        $this->showRecetaForm = true;

        // Prellenar el paciente_id en el formulario de receta
        $this->recetaForm->fill([
            'paciente_id' => $this->selectedPatient->id
        ]);

        Notification::make()
            ->title('Paciente seleccionado')
            ->body('Ahora puede proceder a crear la receta para ' . $this->selectedPatient->persona->nombre_completo)
            ->success()
            ->send();
    }

    public function changePatient(): void
    {
        $this->showRecetaForm = false;
        $this->selectedPatient = null;
        $this->patientSearchForm->fill();
        $this->recetaForm->fill();
    }

    public function create(): void
    {
        $data = $this->recetaForm->getState();

        // Asegurar que el paciente_id esté presente
        $data['paciente_id'] = $this->selectedPatient->id;

        // Multi-tenant: centro_id no es necesario

        try {
            $receta = Receta::create($data);

            Notification::make()
                ->title('Receta creada exitosamente')
                ->body('La receta para ' . $this->selectedPatient->persona->nombre_completo . ' ha sido creada.')
                ->success()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al crear la receta')
                ->body('Ocurrió un error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getForms(): array
    {
        return [
            'patientSearchForm',
            'recetaForm',
        ];
    }
}
