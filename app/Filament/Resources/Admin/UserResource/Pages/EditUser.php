<?php

namespace App\Filament\Resources\Admin\UserResource\Pages;

use App\Filament\Resources\Admin\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar datos de la persona relacionada
        if ($this->record->persona) {
            $data['persona'] = $this->record->persona->toArray();
        }
        
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Actualizar la persona si existe
        if ($record->persona && isset($data['persona'])) {
            $personaData = $data['persona'];
            $personaData['updated_by'] = auth()->id();
            
            $record->persona->update($personaData);
        }
        
        // Preparar datos del usuario
        $userData = collect($data)->except('persona')->toArray();
        $userData['updated_by'] = auth()->id();

        // En tenant moderno users.centro_id no existe.
        if (! $this->tableHasColumn('users', 'centro_id')) {
            unset($userData['centro_id']);
        }
        
        // Actualizar el usuario
        $record->update($userData);
        
        // Actualizar roles si existen
        if (isset($data['roles'])) {
            $record->syncRoles($data['roles']);
        }
        
        return $record;
    }

    protected function tableHasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
