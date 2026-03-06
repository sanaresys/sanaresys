<?php

namespace App\Filament\Resources\Citas\CitasResource\Pages;

use App\Filament\Resources\Citas\CitasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCitas extends ListRecords
{
    protected static string $resource = CitasResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Solo mostrar el botón crear si no es médico
        $user = Auth::user();
        if ($user && !$user->roles->contains('name', 'medico')) {
            $actions[] = Actions\CreateAction::make();
        }

        return $actions;
    }
}
