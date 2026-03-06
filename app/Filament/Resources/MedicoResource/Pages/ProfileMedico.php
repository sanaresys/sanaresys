<?php

namespace App\Filament\Resources\MedicoResource\Pages;

use App\Filament\Resources\MedicoResource;
use Filament\Resources\Pages\Page;

class ProfileMedico extends Page
{
    protected static string $resource = MedicoResource::class;

    protected static string $view = 'filament.resources.medico-resource.pages.profile-medico';
}
