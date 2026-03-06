<?php

namespace App\Filament\Resources\Persona\PersonaResource\Pages;

use App\Filament\Resources\Persona\PersonaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;
use Spatie\Multitenancy\Models\Tenant;

class CreatePersona extends CreateRecord
{
    protected static string $resource = PersonaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Tenant::current();
        if ($tenant && !isset($data['centro_id'])) {
            $data['centro_id'] = $tenant->id;
            // O si tienes una propiedad específica:
            // $data['centro_id'] = $tenant->centro_id;
        }
        
        return $data;
    }
}
