<?php

namespace App\Filament\Resources\ContabilidadMedica;

use App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource\Pages;
use App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource\RelationManagers;
use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;


class ContratoMedicoResource extends Resource
{
    protected static ?string $model = ContratoMedico::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?int $navigationSort = 2;

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }

    public static function form(Form $form): Form
    {
        // Multi-tenant: no es necesario obtener centro_id
        
        return $form
            ->schema([
                Forms\Components\Select::make('medico_id')
                    ->relationship('medico', 'persona_id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->persona->nombre_completo)
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                Forms\Components\TextInput::make('salario_quincenal')
                    ->label('Salario Quincenal')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->prefix('L.')
                    ->required(false)
                    ->live(onBlur: true)
                    ->helperText('Dejar en 0 si el contrato es solo por porcentaje de servicio')
                    ->afterStateUpdated(function ($state, callable $set, Forms\Get $get) {
                        // Asegurarse de que sea un número, defecto 0
                        $value = is_numeric($state) ? (float) $state : 0;
                        $set('salario_mensual', $value * 2);
                        
                        // Validación para verificar si ambos valores son cero
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
                                    $fail('Debe especificar al menos una forma de compensación (salario o porcentaje por servicio).');
                                }
                            };
                        },
                    ]),

                Forms\Components\TextInput::make('salario_mensual')
                    ->label('Salario Mensual')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->prefix('L.')
                    ->required(false)
                    ->helperText('Dejar en 0 si el contrato es solo por porcentaje de servicio')
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\TextInput::make('porcentaje_servicio')
                    ->label('Porcentaje por Servicios')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(false)
                    ->helperText('Dejar en 0 si el contrato es solo por salario fijo')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, Forms\Get $get) {
                        if ($state === '' || $state === null) {
                            $set('porcentaje_servicio', 0);
                        }
                        // Convertir a número para evitar problemas con strings vacíos
                        $set('porcentaje_servicio', floatval($state ?? 0));
                        
                        // Validación para verificar si ambos valores son cero
                        $salario = (float) ($get('salario_quincenal') ?? 0);
                        $porcentaje = floatval($state ?? 0);
                        if ($salario <= 0 && $porcentaje <= 0) {
                            $set('validacion_compensacion', false);
                        } else {
                            $set('validacion_compensacion', true);
                        }
                    })
                    ->rules([
                        function (Forms\Get $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $salarioQuincenal = (float)($get('salario_quincenal') ?? 0);
                                $porcentaje = (float)($value ?? 0);
                                
                                if ($salarioQuincenal <= 0 && $porcentaje <= 0) {
                                    $fail('Debe especificar al menos una forma de compensación (salario o porcentaje por servicio).');
                                }
                            };
                        },
                    ]),

                Forms\Components\DatePicker::make('fecha_inicio')
                    ->required(),
                    
                Forms\Components\DatePicker::make('fecha_fin')
                    ->nullable(),
                
                Forms\Components\Toggle::make('activo')
                    ->inline(false)
                    ->default(true),
                
                // Sección informativa sobre el tipo de contrato
                Forms\Components\Section::make('Tipo de Contrato')
                    ->description('Seleccione al menos una forma de compensación: salario fijo o porcentaje por servicios')
                    ->schema([
                        Forms\Components\Placeholder::make('tipo_contrato_info')
                            ->label('Tipo de Contrato Seleccionado')
                            ->content(function ($get) {
                                $salarioQuincenal = (float) $get('salario_quincenal');
                                $salarioMensual = (float) $get('salario_mensual');
                                $porcentajeServicio = (float) $get('porcentaje_servicio');
                                
                                if ($porcentajeServicio > 0 && $salarioQuincenal == 0 && $salarioMensual == 0) {
                                    return '✅ Contrato solo por porcentaje de servicio ('.$porcentajeServicio.'%)';
                                } elseif ($porcentajeServicio == 0 && ($salarioQuincenal > 0 || $salarioMensual > 0)) {
                                    return '✅ Contrato solo por salario fijo';
                                } elseif ($porcentajeServicio > 0 && ($salarioQuincenal > 0 || $salarioMensual > 0)) {
                                    return '✅ Contrato mixto (salario fijo + '.$porcentajeServicio.'% por servicios)';
                                } else {
                                    return '❌ Debe seleccionar al menos una forma de compensación';
                                }
                            })
                            ->columnSpan(1),
                        
                        Forms\Components\Placeholder::make('validacion_compensacion')
                            ->label('Estado de validación')
                            ->content(function ($get) {
                                $salarioQuincenal = (float) $get('salario_quincenal');
                                $salarioMensual = (float) $get('salario_mensual');
                                $porcentajeServicio = (float) $get('porcentaje_servicio');
                                
                                if ($salarioQuincenal > 0 || $salarioMensual > 0 || $porcentajeServicio > 0) {
                                    return '✅ Formulario válido: Al menos una forma de compensación ha sido especificada';
                                } else {
                                    return '❌ Formulario incompleto: Debe especificar al menos una forma de compensación';
                                }
                            })
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Forms\Components\Placeholder::make('error_message')
                    ->content(fn ($get) => $get('error_message'))
                    ->visible(fn ($get) => $get('error_message') !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Multi-tenant: los datos ya están filtrados por el tenant
        
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['medico.persona', 'centro']))
            ->columns([
                Tables\Columns\TextColumn::make('medico.persona.nombre_completo')
                    ->label('Médico')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('persona', function ($query) use ($search) {
                            $query->where('primer_nombre', 'like', "%{$search}%")
                                  ->orWhere('primer_apellido', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('centro.nombre_centro')
                    ->label('Centro Médico')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('salario_mensual')
                    ->money('HNL')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state > 0 ? "L. " . number_format($state, 2) : "N/A"),
                    
                Tables\Columns\TextColumn::make('porcentaje_servicio')
                    ->suffix('%')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state : "N/A"),
                
                Tables\Columns\TextColumn::make('tipo_contrato')
                    ->label('Tipo de Contrato')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Solo por porcentaje') => 'success',
                        str_contains($state, 'Solo por salario') => 'info',
                        str_contains($state, 'Mixto') => 'warning',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->date(),
                    
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('centro_id')
                    ->relationship('centro', 'nombre_centro'),
                    
                Tables\Filters\TernaryFilter::make('activo'),
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
            'index' => Pages\ListContratoMedico::route('/'),
            'create' => Pages\CreateContratoMedico::route('/create'),
            'edit' => Pages\EditContratoMedico::route('/{record}/edit'),
            'view' => Pages\ViewContratoMedico::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        
        // Multi-tenant: no filtrar por centro_id
        // Los datos ya están filtrados por el tenant
        
        // Obtener el médico vinculado al usuario actual si existe
        $medico = $user->medico;
        
        // Verificar si el usuario está autorizado como médico
        if ($medico && \Spatie\Permission\Models\Role::where('name', 'medico')->whereHas('users', function($q) use ($user) {
            $q->where('model_id', $user->id);
        })->exists()) {
            // Si es médico, filtrar solo sus contratos
            $query = $query->where('medico_id', $medico->id);
        }
        
        return $query->latest('id');
    }
}