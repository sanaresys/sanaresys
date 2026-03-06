<?php

namespace App\Filament\Resources\Impuestos;

use App\Filament\Resources\Impuestos\ImpuestosResource\Pages;
use App\Filament\Resources\Impuestos\ImpuestosResource\RelationManagers;
use App\Models\Impuesto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImpuestosResource extends Resource
{
    protected static ?string $model = Impuesto::class;

    protected static ?string $navigationGroup = 'Gestión de Facturación';

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Impuesto')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre del Impuesto')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Ej: ISV, Impuesto sobre Ventas')
                            ->helperText('Nombre descriptivo del impuesto'),
                            
                        TextInput::make('porcentaje')
                            ->label('Porcentaje (%)')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->placeholder('15.00')
                            ->helperText('Porcentaje de impuesto a aplicar'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Vigencia del Impuesto')
                    ->schema([
                        DatePicker::make('vigente_desde')
                            ->label('Vigente Desde')
                            ->required()
                            ->default(now())
                            ->helperText('Fecha desde la cual el impuesto es válido'),
                            
                        DatePicker::make('vigente_hasta')
                            ->label('Vigente Hasta')
                            ->nullable()
                            ->helperText('Fecha límite del impuesto (opcional)')
                            ->after('vigente_desde'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                TextColumn::make('porcentaje')
                    ->label('Porcentaje')
                    ->suffix('%')
                    ->sortable()
                    ->alignEnd()
                    ->color('primary')
                    ->weight('medium'),
                    
                TextColumn::make('vigente_desde')
                    ->label('Vigente Desde')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                TextColumn::make('vigente_hasta')
                    ->label('Vigente Hasta')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Sin vencimiento')
                    ->color('gray'),
                    
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(function (Impuesto $record): string {
                        $hoy = now()->toDateString();
                        if ($record->vigente_desde > $hoy) {
                            return 'pendiente';
                        }
                        if ($record->vigente_hasta && $record->vigente_hasta < $hoy) {
                            return 'vencido';
                        }
                        return 'vigente';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'vigente' => 'success',
                        'pendiente' => 'warning',
                        'vencido' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'vigente' => 'Vigente',
                        'pendiente' => 'Pendiente',
                        'vencido' => 'Vencido',
                    }),
                    
                TextColumn::make('centro.nombre_centro')
                    ->label('Centro Médico')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'vigente' => 'Vigente',
                        'pendiente' => 'Pendiente',
                        'vencido' => 'Vencido',
                    ])
                    ->query(function ($query, array $data) {
                        if (!isset($data['value'])) {
                            return $query;
                        }
                        
                        $hoy = now()->toDateString();
                        
                        return match($data['value']) {
                            'vigente' => $query->where('vigente_desde', '<=', $hoy)
                                              ->where(function ($q) use ($hoy) {
                                                  $q->whereNull('vigente_hasta')
                                                    ->orWhere('vigente_hasta', '>=', $hoy);
                                              }),
                            'pendiente' => $query->where('vigente_desde', '>', $hoy),
                            'vencido' => $query->where('vigente_hasta', '<', $hoy),
                            default => $query,
                        };
                    }),
                    
                Tables\Filters\Filter::make('vigentes_hoy')
                    ->label('Solo vigentes hoy')
                    ->query(function ($query) {
                        $hoy = now()->toDateString();
                        return $query->where('vigente_desde', '<=', $hoy)
                                     ->where(function ($q) use ($hoy) {
                                         $q->whereNull('vigente_hasta')
                                           ->orWhere('vigente_hasta', '>=', $hoy);
                                     });
                    })
                    ->default(),
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
            ->defaultSort('vigente_desde', 'desc')
            ->emptyStateHeading('No hay impuestos registrados')
            ->emptyStateDescription('Crea el primer impuesto para comenzar a facturar')
            ->emptyStateIcon('heroicon-o-calculator');
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
            'index' => Pages\ListImpuestos::route('/'),
            'create' => Pages\CreateImpuestos::route('/create'),
            'edit' => Pages\EditImpuestos::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
};