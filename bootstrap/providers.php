<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\FilamentCustomStylesProvider::class,
    Stancl\Tenancy\TenancyServiceProvider::class,
    App\Providers\TenancyServiceProvider::class, // ← Custom provider for event listeners
];
