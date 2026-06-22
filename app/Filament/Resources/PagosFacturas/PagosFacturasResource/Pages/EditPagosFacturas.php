<?php

namespace App\Filament\Resources\PagosFacturas\PagosFacturasResource\Pages;

use App\Filament\Resources\PagosFacturas\PagosFacturasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPagosFacturas extends EditRecord
{
    protected static string $resource = PagosFacturasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
