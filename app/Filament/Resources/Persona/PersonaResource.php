<?php

namespace App\Filament\Resources\Persona;

use App\Filament\Resources\Persona\PersonaResource\Pages;
use App\Filament\Resources\Persona\PersonaResource\RelationManagers;
use App\Models\Persona;
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
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Storage;

class PersonaResource extends Resource
{
    /*public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('crear personas');
    }*/

    protected static ?string $model = Persona::class;

    protected static ?string $navigationGroup = 'Gestión de Personas';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('dni')->label('DNI')->required()->unique(ignoreRecord: true),
                TextInput::make('primer_nombre')->label('Primer Nombre')->required(),
                TextInput::make('segundo_nombre')->label('Segundo Nombre'),
                TextInput::make('primer_apellido')->label('Primer Apellido')->required(),
                TextInput::make('segundo_apellido')->label('Segundo Apellido'),
                TextInput::make('telefono')->label('Teléfono')->required(),
                TextInput::make('direccion')->label('Dirección')->required(),
                Select::make('sexo')
                    ->label('Sexo')
                    ->options([
                        'M' => 'Masculino',
                        'F' => 'Femenino',
                    ])
                    ->required(),
                DatePicker::make('fecha_nacimiento')->label('Fecha de Nacimiento')->date()->required(),
                Select::make('nacionalidad_id')
                    ->label('Nacionalidad')
                    ->relationship('nacionalidad', 'nacionalidad')
                    ->required()
                    ->searchable()
                    ->preload(),
                FileUpload::make('fotografia')
                    ->label('Fotografía')
                    ->image()
                    ->directory('personas')
                    ->maxSize(2048)
                    ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                    ->nullable(),
                // El campo centro_id se asigna automáticamente en el modelo, no es necesario en el formulario
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('primer_nombre')->label('Primer Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('primer_apellido')->label('Primer Apellido')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('dni')->label('DNI')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('telefono')->label('Teléfono')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('direccion')->label('Dirección')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nacionalidad.nacionalidad')->label('Nacionalidad')->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListPersonas::route('/'),
            'create' => Pages\CreatePersonaTenant::route('/create'),
            'edit' => Pages\EditPersona::route('/{record}/edit'),
        ];
    }

    // Controlar quién puede eliminar según permiso
    

    // app/Filament/Resources/PersonaResource.php

    // El filtrado por centro_id se realiza automáticamente por el scope global en el modelo
}
