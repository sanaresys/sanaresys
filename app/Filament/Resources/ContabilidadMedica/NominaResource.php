<?php

namespace App\Filament\Resources\ContabilidadMedica;

use App\Filament\Resources\ContabilidadMedica\NominaResource\Pages;
use App\Models\ContabilidadMedica\Nomina;
use App\Models\Medico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class NominaResource extends Resource
{
    protected static ?string $model = Nomina::class;

    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Nóminas';
    protected static ?string $modelLabel = 'Nómina';
    protected static ?string $pluralModelLabel = 'Nóminas';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('📋 Información General')
                    ->description('Configura los datos básicos de la nómina')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('empresa')
                    ->label('🏥 Centro Médico')
                    ->default(function () {
                        $user = Auth::user();
                        if ($user && $user->centro) {
                            return $user->centro->nombre_centro;
                        }
                        return '';
                    })
                    ->required()
                    ->maxLength(255)
                    ->prefixIcon('heroicon-o-building-office-2'),

                                TextInput::make('año')
                                    ->label('📅 Año')
                                    ->required()
                                    ->numeric()
                                    ->default(date('Y'))
                                    ->minValue(2020)
                                    ->maxValue(2030)
                                    ->prefixIcon('heroicon-o-calendar'),

                                Select::make('mes')
                                    ->label('📆 Mes')
                                    ->options([
                                        1 => 'Enero',
                                        2 => 'Febrero',
                                        3 => 'Marzo',
                                        4 => 'Abril',
                                        5 => 'Mayo',
                                        6 => 'Junio',
                                        7 => 'Julio',
                                        8 => 'Agosto',
                                        9 => 'Septiembre',
                                        10 => 'Octubre',
                                        11 => 'Noviembre',
                                        12 => 'Diciembre',
                                    ])
                                    ->required()
                                    ->default(date('n'))
                                    ->native(false),

                                Select::make('tipo_pago')
                                    ->label('💰 Tipo de Pago')
                                    ->options([
                                        'mensual' => 'Mensual',
                                        'quincenal' => 'Quincenal',
                                        'semanal' => 'Semanal',
                                    ])
                                    ->required()
                                    ->default('mensual')
                                    ->native(false),
                            ]),

                        Textarea::make('descripcion')
                            ->label('📝 Descripción')
                            ->placeholder('Describe los detalles de esta nómina...')
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('👨‍⚕️ Médicos en Nómina')
                    ->description('Selecciona los médicos y configura sus salarios')
                    ->icon('heroicon-o-users')
                    ->collapsible()
                    ->schema([
                        Repeater::make('detalles')
                            ->label('Médicos')
                            ->relationship('detalles')
                            ->schema([
                                Select::make('medico_id')
                                    ->label('👨‍⚕️ Médico')
                                    ->relationship('medico', 'id')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->persona->nombre_completo ?? 'Sin nombre')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false)
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $medico = \App\Models\Medico::find($state);
                                            if ($medico && $medico->persona) {
                                                $set('medico_nombre', $medico->persona->nombre_completo);
                                                // Obtener salario base del contrato si existe
                                                $contrato = $medico->contratos()->first();
                                                if ($contrato) {
                                                    $set('salario_base', $contrato->salario_mensual);
                                                }
                                            }
                                        }
                                    }),

                                TextInput::make('medico_nombre')
                                    ->label('📋 Nombre del Médico')
                                    ->required()
                                    ->prefixIcon('heroicon-o-user')
                                    ->disabled(),

                                TextInput::make('salario_base')
                                    ->label('💵 Salario Base')
                                    ->numeric()
                                    ->prefix('L.')
                                    ->required()
                                    ->prefixIcon('heroicon-o-banknotes')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $salario = (float) $state;
                                        $deducciones = (float) $get('deducciones');
                                        $percepciones = (float) $get('percepciones');
                                        $total = $salario - $deducciones + $percepciones;
                                        $set('total_pagar', $total);
                                    }),

                                TextInput::make('deducciones')
                                    ->label('⬇️ Deducciones')
                                    ->numeric()
                                    ->prefix('L.')
                                    ->default(0)
                                    ->prefixIcon('heroicon-o-minus-circle')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $salario = (float) $get('salario_base');
                                        $deducciones = (float) $state;
                                        $percepciones = (float) $get('percepciones');
                                        $total = $salario - $deducciones + $percepciones;
                                        $set('total_pagar', $total);
                                    }),

                                TextInput::make('percepciones')
                                    ->label('⬆️ Percepciones')
                                    ->numeric()
                                    ->prefix('L.')
                                    ->default(0)
                                    ->prefixIcon('heroicon-o-plus-circle')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $salario = (float) $get('salario_base');
                                        $deducciones = (float) $get('deducciones');
                                        $percepciones = (float) $state;
                                        $total = $salario - $deducciones + $percepciones;
                                        $set('total_pagar', $total);
                                    }),

                                TextInput::make('total_pagar')
                                    ->label('💰 Total a Pagar')
                                    ->numeric()
                                    ->prefix('L.')
                                    ->disabled()
                                    ->prefixIcon('heroicon-o-currency-dollar'),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('➕ Agregar Médico')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['medico_nombre'] ?? 'Nuevo médico'),
                    ])
                    ->collapsed()
                    ->persistCollapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('año')
                    ->label('Año')
                    ->sortable(),

                TextColumn::make('mes')
                    ->label('Mes')
                    ->formatStateUsing(function ($state) {
                        $meses = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                        ];
                        return $meses[(int)$state] ?? $state;
                    })
                    ->sortable(),

                TextColumn::make('tipo_pago')
                    ->label('Tipo de Pago')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'mensual' => 'Mensual',
                            'quincenal' => 'Quincenal',
                            'semanal' => 'Semanal',
                            default => ucfirst($state)
                        };
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'mensual' => 'emerald',
                        'quincenal' => 'amber',
                        'semanal' => 'blue',
                        default => 'gray',
                    }),

                IconColumn::make('cerrada')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('rose')
                    ->falseColor('emerald'),

                TextColumn::make('numero_medicos')
                    ->label('Médicos')
                    ->getStateUsing(fn ($record) => $record->numero_empleados)
                    ->badge()
                    ->color('sky'),

                TextColumn::make('total_nomina')
                    ->label('Total')
                    ->getStateUsing(fn ($record) => 'L. ' . number_format($record->total_nomina, 2))
                    ->badge()
                    ->color('emerald')
                    ->weight('bold'),

                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('año')
                    ->options(function () {
                        $currentYear = date('Y');
                        $years = [];
                        for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),

                Tables\Filters\SelectFilter::make('mes')
                    ->options([
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                    ]),

                Tables\Filters\SelectFilter::make('tipo_pago')
                    ->options([
                        'mensual' => 'Mensual',
                        'quincenal' => 'Quincenal',
                        'semanal' => 'Semanal',
                    ]),

                Tables\Filters\TernaryFilter::make('cerrada')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Cerradas')
                    ->falseLabel('Abiertas'),
            ])
            ->actions([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('sky'),

                EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->color('amber')
                    ->visible(fn (Nomina $record): bool => !$record->cerrada),

                Tables\Actions\Action::make('cerrar')
                    ->label('Cerrar')
                    ->icon('heroicon-o-lock-closed')
                    ->color('orange')
                    ->visible(fn (Nomina $record): bool => !$record->cerrada)
                    ->requiresConfirmation()
                    ->modalHeading('🔒 Cerrar Nómina')
                    ->modalDescription('Una vez cerrada la nómina, no podrás editarla ni eliminarla. ¿Estás seguro?')
                    ->modalSubmitActionLabel('✅ Sí, cerrar nómina')
                    ->modalCancelActionLabel('❌ Cancelar')
                    ->action(function (Nomina $record) {
                        $record->cerrar();
                    }),

                Tables\Actions\Action::make('generar_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('emerald')
                    ->url(fn (Nomina $record) => route('nomina.pdf', $record))
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->color('rose')
                    ->visible(fn (Nomina $record): bool => !$record->cerrada),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['detalles.medico.persona']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNominas::route('/'),
            'create' => Pages\CreateNomina::route('/create'),
            'view' => Pages\ViewNomina::route('/{record}'),
            'edit' => Pages\EditNomina::route('/{record}/edit'),
        ];
    }
}
