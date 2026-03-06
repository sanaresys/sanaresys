<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;


class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'GestiÃ³n de Seguridad';
    protected static ?string $navigationLabel = 'Roles';
    protected static ?string $label = 'Rol';
    protected static ?string $pluralLabel = 'Roles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('InformaciÃ³n BÃ¡sica')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del Rol')
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                function() {
                                    return function($attribute, $value, $fail) {
                                        // Multi-tenant: validaciÃ³n en el contexto del tenant
                                        $exists = Role::where('name', $value)
                                            ->where('guard_name', 'web')
                                            ->where('id', '!=', request()->route('record'))
                                            ->exists();
                                        
                                        if ($exists) {
                                            $fail('Este nombre de rol ya existe.');
                                        }
                                    };
                                }
                            ]),
                        Select::make('guard_name')
                            ->label('Guard')
                            ->options(['web' => 'web'])
                            ->default('web')
                            ->disabled()
                    ])->columns(2),

                Forms\Components\Section::make('Permisos del Sistema')
                    ->description('Seleccione los permisos que desea asignar a este rol')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                CheckboxList::make('permissions')
                                    ->label('Permisos Disponibles')
                                    ->relationship('permissions', 'name')
                                    ->options(function() {
                                        $permissions = Permission::query();
                                        
                                        if (!auth()->user()?->hasRole('root')) {
                                            $permissions->whereNotIn('name', [
                                                //AQUI SE OCULTAN LOS PERMISOS QUE SOLO DEBE VER EL ROOT
                                                'ver personas', 'crear personas', 'actualizar personas', 'borrar personas',
                                                'ver nacionalidad', 'crear nacionalidad', 'actualizar nacionalidad', 'borrar nacionalidad',
                                                'ver centromedico', 'crear centromedico', 'actualizar centromedico', 'borrar centromedico',
                                                'ver medicocentromedico', 'crear medicocentromedico', 'actualizar medicocentromedico', 'borrar medicocentromedico',
                                                'crear especialidad', 'actualizar especialidad', 'borrar especialidad',
                                                'ver especialidadmedicos', 'crear especialidadmedicos', 'actualizar especialidadmedicos', 'borrar especialidadmedicos'
                                            ]);
                                        }

                                        return $permissions->get()
                                            ->sortBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('guard_name')->label('Guard'),
                TextColumn::make('created_at')->label('Creado')->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                // Puedes agregar filtros personalizados aquÃ­
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\RoleResource\Pages\ListRoles::route('/'),
            'create' => \App\Filament\Resources\RoleResource\Pages\CreateRole::route('/create'),
            'edit' => \App\Filament\Resources\RoleResource\Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Multi-tenant: si el usuario no es root, excluimos el rol root
        if (!auth()->user()?->hasRole('root')) {
            return $query->where('name', '!=', 'root');
        }
        
        return $query;
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('root') || $user->hasRole('administrador');
    }
  
}
