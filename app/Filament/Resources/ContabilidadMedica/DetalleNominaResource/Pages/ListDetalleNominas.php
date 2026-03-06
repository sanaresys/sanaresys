<?php

namespace App\Filament\Resources\ContabilidadMedica\DetalleNominaResource\Pages;

use App\Filament\Resources\ContabilidadMedica\DetalleNominaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetalleNominas extends ListRecords
{
    protected static string $resource = DetalleNominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No mostrar acción de crear
        ];
    }
}
