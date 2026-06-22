<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Pages\CustomProfile;
use App\Filament\Pages\DisenoFactura;
use App\Filament\Pages\CustomLogin; // Agrega esta línea

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(CustomLogin::class) // Modifica esta línea
            ->brandName('Sanare') // Agrega el nombre de la marca
            ->brandLogo(asset('images/logo.png')) // Asegúrate de tener esta imagen en public/images
            ->brandLogoHeight('65px')
            ->favicon(asset('images/logo.png')) // Opcional: agrega un favicon
            ->profile(CustomProfile::class)
            ->colors([
                'primary' => Color::Emerald,
                'gray' => Color::Slate,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Blue,
            ])
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->maxContentWidth('full')
            ->topNavigation(false)
            ->breadcrumbs(true)
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Panel Diario'),
                NavigationGroup::make()
                    ->label('Facturacion'),
                NavigationGroup::make()
                    ->label('Contabilidad Médica'),
                NavigationGroup::make()
                    ->label('Configuracion')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Mi Cuenta'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                DisenoFactura::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])



            ->renderHook(
                'panels::head.end',
                fn () => '<style>[x-cloak]{display:none !important;}</style>'
            )
            ->renderHook(
                'panels::user-menu.before',
                fn () => view('filament.components.centro-selector-topbar')
            )
            ->renderHook(
                'panels::body.end',
                fn () => '<script src="/js/disable-livewire-polling.js"></script>'
            )
            ->renderHook(
                'panels::body.start',
                fn () => view('filament.components.module-renewal-banner')
            )


            
            ->plugins([
            ])
            ->middleware([
                'tenant.resolve',
                'tenant.canonical',
                'tenant.maintenance',
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                'tenant.subscription.active',
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                'require.onboarding',
            ]);
    }
}
