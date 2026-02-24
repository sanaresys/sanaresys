<?php

namespace App\Filament\Resources\Admin\UserResource\Pages;

use App\Filament\Resources\Admin\UserResource;
use App\Models\Persona;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $personaData = $data['persona'] ?? [];

        // Solo asignar centro_id cuando el esquema actual lo soporta.
        if ($this->tableHasColumn('personas', 'centro_id')) {
            if (! auth()->user()->hasRole('root')) {
                $personaData['centro_id'] = auth()->user()->centro_id;
            } else {
                $personaData['centro_id'] = $data['centro_id'] ?? auth()->user()->centro_id;
            }
        }

        $personaData['created_by'] = auth()->id();
        $persona = Persona::create($personaData);

        $userData = collect($data)->except('persona')->toArray();
        $userData['persona_id'] = $persona->id;
        $userData['created_by'] = auth()->id();

        if ($this->tableHasColumn('users', 'centro_id')) {
            if (! auth()->user()->hasRole('root')) {
                $userData['centro_id'] = auth()->user()->centro_id;
            } else {
                $userData['centro_id'] = $data['centro_id'] ?? auth()->user()->centro_id;
            }
        } else {
            unset($userData['centro_id']);
        }

        $user = User::create($userData);

        if (! empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! $this->tableHasColumn('users', 'centro_id')) {
            unset($data['centro_id']);

            return $data;
        }

        if (! auth()->user()->hasRole('root') && ! isset($data['centro_id'])) {
            $data['centro_id'] = auth()->user()->centro_id;
        }

        return $data;
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
