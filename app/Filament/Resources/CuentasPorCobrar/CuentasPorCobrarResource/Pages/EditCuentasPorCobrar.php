<?php

namespace App\Filament\Resources\CuentasPorCobrar\CuentasPorCobrarResource\Pages;

use App\Filament\Resources\CuentasPorCobrar\CuentasPorCobrarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCuentasPorCobrar extends EditRecord
{
    protected static string $resource = CuentasPorCobrarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
