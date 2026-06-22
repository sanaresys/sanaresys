<?php

namespace App\Filament\Resources\CAICorrelativos\CAICorrelativosResource\Pages;

use App\Filament\Resources\CAICorrelativos\CAICorrelativosResource;
use Filament\Resources\Pages\ListRecords;

class ListCAICorrelativos extends ListRecords
{
    protected static string $resource = CAICorrelativosResource::class;

    protected function getHeaderActions(): array
    {
        return [];   // Solo lectura
    }
}
