<?php

namespace App\Filament\Resources\PagosFacturas\PagosFacturasResource\Pages;

use App\Filament\Resources\PagosFacturas\PagosFacturasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPagosFacturas extends ListRecords
{
    protected static string $resource = PagosFacturasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
