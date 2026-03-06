<?php

namespace App\Filament\Resources\CAIAutorizaciones;

use App\Filament\Resources\CAIAutorizaciones\CAIAutorizacionesResource\Pages;
use App\Filament\Resources\CAIAutorizaciones\CAIAutorizacionesResource\RelationManagers;
use App\Models\CAIAutorizaciones;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Filters\SelectFilter;
use App\Models\Consulta;
use App\Models\FacturaDetalle;

class CAIAutorizacionesResource extends Resource
{
    protected static ?string $model = CAIAutorizaciones::class;

    public $record;

    protected static ?string $modelLabel       = 'Correlativo CAI';
    protected static ?string $pluralModelLabel = 'Correlativos CAI';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    protected static ?string $navigationLabel = 'Autorizaciones CAI';
    
    protected static ?string $navigationGroup = 'Gestión de Facturación';
    protected $listeners = ['serviciosActualizados' => '$refresh'];

    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Fiscal')
                    ->schema([
                        TextInput::make('rtn')
                            ->required()
                            ->maxLength(14)
                            ->placeholder('08019999999999')
                            ->helperText('RTN del centro médico'),
                            
                        TextInput::make('cai_codigo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('A1B2-C3D4-E5F6-G7H8-I9J0')
                            ->helperText('Código CAI proporcionado por la SAR'),
                            
                        DatePicker::make('fecha_limite')
                            ->required()
                            ->helperText('Fecha límite de emisión de facturas'),
                    ])->columns(1),
                    
                Forms\Components\Section::make('Configuración de Rangos')
                    ->schema([
                        TextInput::make('rango_inicial')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('000000001')
                            ->helperText('Número inicial del rango')
                            ->live()
                            ->afterStateUpdated(fn (callable $set, callable $get) =>
                                $set('cantidad', max(0, (int)$get('rango_final') - (int)$get('rango_inicial') + 1))
                            ),
                            
                        TextInput::make('rango_final')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('000001000')
                            ->helperText('Número final del rango')
                            ->live()
                            ->afterStateUpdated(fn (callable $set, callable $get) =>
                                $set('cantidad', max(0, (int)$get('rango_final') - (int)$get('rango_inicial') + 1))
                            ),
                            
                        TextInput::make('cantidad')
                            ->disabled()
                            ->dehydrated()
                            ->extraInputAttributes(['class'=>'text-gray-400'])
                            ->default(fn (Forms\Get $get) =>
                                max(0, (int)$get('rango_final') - (int)$get('rango_inicial') + 1)
                            )
                            ->helperText('Calculado automáticamente: (rango_final - rango_inicial + 1)'),
                            
                        TextInput::make('numero_actual')
                            ->disabled()
                            ->numeric()
                            ->default(fn (Forms\Get $get) => $get('rango_inicial'))
                            ->helperText('Número actual a usar (se actualiza automáticamente)'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Estado')
                    ->schema([
                        Select::make('estado')
                            ->required()
                            ->options([
                                'ACTIVA' => 'Activa',
                                'VENCIDA' => 'Vencida',
                                'AGOTADA' => 'Agotada',
                                'ANULADA' => 'Anulada',
                            ])
                            ->default('ACTIVA'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cai_codigo')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->tooltip(fn (CAIAutorizaciones $record): string => $record->cai_codigo),
                    
                TextColumn::make('rango_inicial')
                    ->formatStateUsing(fn (int $state): string => str_pad($state, 9, '0', STR_PAD_LEFT))
                    ->alignCenter(),
                    
                TextColumn::make('rango_final')
                    ->formatStateUsing(fn (int $state): string => str_pad($state, 9, '0', STR_PAD_LEFT))
                    ->alignCenter(),
                    
                TextColumn::make('numero_actual')
                    ->formatStateUsing(fn (int $state): string => str_pad($state, 9, '0', STR_PAD_LEFT))
                    ->alignCenter()
                    ->color('primary'),
                    
                TextColumn::make('progreso')
                    ->getStateUsing(function (CAIAutorizaciones $record): string {
                        return number_format($record->porcentajeUtilizado(), 1) . '%';
                    })
                    ->label('Progreso')
                    ->badge()
                    ->color(function (CAIAutorizaciones $record): string {
                        $percentage = $record->porcentajeUtilizado();
                        return match (true) {
                            $percentage >= 90 => 'danger',
                            $percentage >= 70 => 'warning',
                            default => 'success',
                        };
                    }),
                    
                TextColumn::make('numeros_disponibles')
                    ->getStateUsing(function (CAIAutorizaciones $record): string {
                        return number_format($record->numerosDisponibles());
                    })
                    ->label('Disponibles')
                    ->alignCenter()
                    ->color('success'),
                    
                TextColumn::make('fecha_limite')
                    ->date()
                    ->sortable()
                    ->color(fn (CAIAutorizaciones $record): string => 
                        $record->fecha_limite < now()->addDays(30) ? 'warning' : 'success'
                    ),
                    
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ACTIVA' => 'success',
                        'VENCIDA' => 'warning',
                        'AGOTADA' => 'danger',
                        'ANULADA' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'ACTIVA' => 'Activa',
                        'VENCIDA' => 'Vencida',
                        'AGOTADA' => 'Agotada',
                        'ANULADA' => 'Anulada',
                    }),
                    
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'ACTIVA' => 'Activa',
                        'VENCIDA' => 'Vencida',
                        'AGOTADA' => 'Agotada',
                        'ANULADA' => 'Anulada',
                    ]),
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function mount(int|string $record): void
    {
        $this->record = Consulta::with(['paciente.persona','medico.persona'])
                        ->findOrFail($record);
    }

    public function getServiciosTotal(): float
    {
        return FacturaDetalle::where('consulta_id', $this->record->id)
            ->whereNull('factura_id')
            ->sum('total_linea');
    }

    public function getCantidadServicios(): int
    {
        return FacturaDetalle::where('consulta_id', $this->record->id)
            ->whereNull('factura_id')
            ->count();
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
            'index' => Pages\ListCAIAutorizaciones::route('/'),
            'create' => Pages\CreateCAIAutorizaciones::route('/create'),
            'edit' => Pages\EditCAIAutorizaciones::route('/{record}/edit'),
        ];
    }
}
