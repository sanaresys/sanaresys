<?php

namespace App\Filament\Resources\Persona;

use App\Filament\Resources\Persona\PersonaResource\Pages;
use App\Filament\Resources\Persona\PersonaResource\RelationManagers;
use App\Models\Nacionalidad;
use App\Models\Persona;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\Http;
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
                TextInput::make('dni')
                    ->label('DNI')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set): void {
                        self::autocompletarDesdeApiIdentidad($state, $set);
                    }),
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

    private static function autocompletarDesdeApiIdentidad(?string $dniInput, callable $set): void
    {
        $dni = preg_replace('/\D+/', '', (string) $dniInput);

        // Validación mínima para evitar llamadas innecesarias.
        if (!$dni || strlen($dni) < 13) {
            return;
        }

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->get('https://1ug1cfi3ua.execute-api.us-east-2.amazonaws.com/persona', [
                    'numero_identidad' => $dni,
                ]);

            if (!$response->successful()) {
                return;
            }

            $payload = $response->json();

            if (isset($payload['data']) && is_array($payload['data'])) {
                $payload = $payload['data'];
            }

            if (isset($payload[0]) && is_array($payload[0])) {
                $payload = $payload[0];
            }

            if (!is_array($payload) || empty($payload)) {
                return;
            }

            $set('primer_nombre', $payload['primer_nombre'] ?? null);
            $set('segundo_nombre', $payload['segundo_nombre'] ?? null);
            $set('primer_apellido', $payload['primer_apellido'] ?? null);
            $set('segundo_apellido', $payload['segundo_apellido'] ?? null);

            $sexoApi = strtoupper(trim((string) ($payload['sexo'] ?? '')));
            if (str_starts_with($sexoApi, 'M')) {
                $set('sexo', 'M');
            } elseif (str_starts_with($sexoApi, 'F')) {
                $set('sexo', 'F');
            }

            $fechaNacimiento = $payload['fecha_nacimiento'] ?? null;
            if (!empty($fechaNacimiento)) {
                try {
                    $set('fecha_nacimiento', Carbon::parse($fechaNacimiento)->format('Y-m-d'));
                } catch (\Throwable $e) {
                    // Si no se puede parsear, se mantiene el valor actual del formulario.
                }
            }

            // Si existe en esta API, se asume nacionalidad hondureña.
            $nacionalidadHondurenaId = Nacionalidad::query()
                ->where('nacionalidad', 'like', 'Hondur%')
                ->value('id');

            if ($nacionalidadHondurenaId) {
                $set('nacionalidad_id', $nacionalidadHondurenaId);
            }

            // Concatenar dirección: lugar_poblado, aldea, municipio, departamento.
            $direccion = collect([
                $payload['lugar_poblado'] ?? null,
                $payload['aldea'] ?? null,
                $payload['municipio'] ?? null,
                $payload['departamento'] ?? null,
            ])
                ->filter(fn ($item) => filled($item))
                ->map(fn ($item) => trim((string) $item))
                ->implode(', ');

            if (!empty($direccion)) {
                $set('direccion', $direccion);
            }
        } catch (\Throwable $e) {
            // Falla silenciosa para no alterar el flujo actual del formulario.
        }
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
