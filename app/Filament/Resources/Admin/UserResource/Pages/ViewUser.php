<?php

namespace App\Filament\Resources\Admin\UserResource\Pages;

use App\Filament\Resources\Admin\UserResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->persona) {
            $data['persona'] = $this->record->persona->toArray();
        }
        return $data;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información Personal')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('persona.dni')
                                    ->label('DNI'),
                                TextEntry::make('persona.nombre_completo')
                                    ->label('Nombre Completo')
                                    ->getStateUsing(fn ($record) => 
                                        $record->persona ? 
                                        $record->persona->primer_nombre . ' ' . 
                                        ($record->persona->segundo_nombre ? $record->persona->segundo_nombre . ' ' : '') .
                                        $record->persona->primer_apellido . ' ' . 
                                        ($record->persona->segundo_apellido ?? '') : 'N/A'
                                    ),
                                TextEntry::make('persona.telefono')
                                    ->label('Teléfono'),
                                TextEntry::make('persona.fecha_nacimiento')
                                    ->label('Fecha de Nacimiento')
                                    ->date('d/m/Y'),
                                TextEntry::make('persona.sexo')
                                    ->label('Sexo')
                                    ->formatStateUsing(fn (string $state): string => 
                                        $state === 'M' ? 'Masculino' : 'Femenino'
                                    ),
                                TextEntry::make('persona.nacionalidad.nacionalidad')
                                    ->label('Nacionalidad'),
                            ]),
                        TextEntry::make('persona.direccion')
                            ->label('Dirección')
                            ->columnSpanFull(),
                        ImageEntry::make('persona.fotografia')
                            ->label('Fotografía')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->persona && $record->persona->fotografia),
                    ]),
                Section::make('Información de Usuario')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nombre de Usuario'),
                                TextEntry::make('email')
                                    ->label('Correo Electrónico'),
                                TextEntry::make('roles.name')
                                    ->label('Roles')
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('centro.nombre_centro')
                                    ->label('Centro Médico'),
                            ]),
                    ]),
                Section::make('Información del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Fecha de Creación')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('updated_at')
                                    ->label('Última Actualización')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ]),
            ]);
    }
}
