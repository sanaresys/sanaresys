<?php

namespace App\Filament\Resources\Facturas\FacturasResource\Pages;

use App\Filament\Resources\Facturas\FacturasResource;
use Filament\Actions;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Get;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use App\Models\Consulta;
use Illuminate\Http\Request;

class CreateFacturaWizard extends CreateRecord
{
    protected static string $resource = FacturasResource::class;

    protected static ?string $title = 'Crear Factura - Wizard';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Consulta')
                        ->description('Información de la consulta')
                        ->schema([
                            Placeholder::make('consulta_info')
                                ->label('Información de la Consulta')
                                ->content(function () {
                                    $consulta_id = request()->get('consulta_id');
                                    if ($consulta_id) {
                                        $consulta = Consulta::find($consulta_id);
                                        if ($consulta) {
                                            return "Consulta ID: {$consulta->id} - Paciente: {$consulta->paciente->persona->nombres} {$consulta->paciente->persona->apellidos}";
                                        }
                                    }
                                    return 'No se encontró información de la consulta';
                                }),
                                
                            Select::make('consulta_id')
                                ->label('Consulta')
                                ->relationship('consulta', 'id')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->default(fn () => request()->get('consulta_id')),
                        ]),
                        
                    Step::make('Servicios')
                        ->description('Agregar servicios')
                        ->schema([
                            TextInput::make('servicios_temp')
                                ->label('Servicios (Temporal)')
                                ->placeholder('Aquí irán los servicios...')
                                ->helperText('En el siguiente paso implementaremos la selección de servicios'),
                        ]),
                        
                    Step::make('Factura')
                        ->description('Datos de facturación')
                        ->schema([
                            DatePicker::make('fecha_emision')
                                ->label('Fecha de Emisión')
                                ->required()
                                ->default(now()),
                                
                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()
                                ->prefix('L.')
                                ->default(0)
                                ->step(0.01),
                                
                            TextInput::make('descuento_total')
                                ->label('Descuento Total')
                                ->numeric()
                                ->prefix('L.')
                                ->default(0)
                                ->step(0.01),
                                
                            TextInput::make('impuesto_total')
                                ->label('Impuesto Total')
                                ->numeric()
                                ->prefix('L.')
                                ->default(0)
                                ->step(0.01),
                                
                            TextInput::make('total')
                                ->label('Total')
                                ->numeric()
                                ->prefix('L.')
                                ->default(0)
                                ->step(0.01),
                                
                            Select::make('estado')
                                ->label('Estado')
                                ->options([
                                    'PENDIENTE' => 'Pendiente',
                                    'PAGADA' => 'Pagada',
                                    'ANULADA' => 'Anulada',
                                    'PARCIAL' => 'Parcial',
                                ])
                                ->default('PENDIENTE')
                                ->required(),
                        ]),
                        
                    Step::make('Confirmación')
                        ->description('Revisar y confirmar')
                        ->schema([
                            Placeholder::make('confirmacion')
                                ->label('Confirmación')
                                ->content('Revisa todos los datos antes de crear la factura. Una vez creada, se generará automáticamente el número de factura.'),
                        ]),
                ])
                // Quitar la línea problemática submitAction
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Procesar datos antes de crear la factura
        $consulta_id = request()->get('consulta_id');
        if ($consulta_id) {
            $data['consulta_id'] = $consulta_id;
            
            // Obtener datos de la consulta
            $consulta = Consulta::find($consulta_id);
            if ($consulta) {
                $data['paciente_id'] = $consulta->paciente_id;
                $data['medico_id'] = $consulta->medico_id;
                $data['cita_id'] = $consulta->cita_id;
            }
        }

        // Limpiar datos temporales
        unset($data['servicios_temp']);

        return $data;
    }
}