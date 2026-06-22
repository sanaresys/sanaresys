<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Radio;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile;
use Filament\Support\Facades\FilamentView;

class CustomProfile extends EditProfile
{
    protected static string $view = 'filament.pages.custom-profile';

    protected function getFormSchema(): array
    {
        return [
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
            $this->getThemeFormComponent(),
        ];
    }

    protected function getThemeFormComponent(): Component
    {
        return Radio::make('theme')
            ->label('Tema de la interfaz')
            ->options([
                'light' => 'Tema Claro',
                'dark' => 'Tema Oscuro',
                'custom-dark' => 'Tema Oscuro Personalizado',
            ])
            ->default('light')
            ->inline()
            ->afterStateUpdated(function ($state) {
                // Guardar la preferencia del tema en localStorage via JavaScript
                FilamentView::registerRenderHook(
                    'panels::body.end',
                    fn () => view('filament.components.theme-script', ['theme' => $state])
                );
            });
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->operation('edit')
            ->model($this->getUser())
            ->statePath('data');
    }
}
