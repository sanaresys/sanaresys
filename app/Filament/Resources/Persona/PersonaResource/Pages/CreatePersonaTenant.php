<?php

namespace App\Filament\Resources\Persona\PersonaResource\Pages;

use App\Filament\Resources\Persona\PersonaResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePersonaTenant extends CreateRecord
{
    protected static string $resource = PersonaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = tenancy()->initialized ? tenancy()->tenant : null;

        if ($tenant && ! isset($data['centro_id'])) {
            $data['centro_id'] = $tenant->centro_id ?? null;
        }

        return $data;
    }
}

