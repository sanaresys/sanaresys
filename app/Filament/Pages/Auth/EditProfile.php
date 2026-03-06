<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Notifications\Notification;
use App\Models\Recetario;
use Illuminate\Support\Facades\Auth;

class EditProfile extends BaseEditProfile
{
    protected function getFormSchema(): array
    {
        return [
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
            
            // Solo mostrar sección de recetario si el usuario tiene rol de médico
            ...($this->getUser()->hasRole('medico') ? [$this->getRecetarioSection()] : []),
        ];
    }

    protected function getRecetarioSection(): Component
    {
        $user = $this->getUser();
        $recetario = $user->medico?->recetarios()->latest()->first();
        
        return Section::make('Configuración del Recetario')
            ->description('Configure su recetario médico para prescribir medicamentos')
            ->schema([
                Toggle::make('tiene_recetario')
                    ->label('Activar Recetario Médico')
                    ->helperText('Active esta opción para habilitar su recetario médico')
                    ->default($recetario ? true : false)
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->actualizarRecetario($state);
                    }),
                    
                TextInput::make('info_recetario')
                    ->label('Estado del Recetario')
                    ->default($this->getEstadoRecetario())
                    ->disabled()
                    ->visible(fn ($get) => $get('tiene_recetario'))
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->visible($user->hasRole('medico'));
    }

    protected function getEstadoRecetario(): string
    {
        $user = $this->getUser();
        
        if (!$user->hasRole('medico')) {
            return 'No es médico';
        }
        
        if (!$user->medico) {
            return 'Debe completar registro de médico';
        }
        
        $recetario = $user->medico->recetarios()->latest()->first();
        
        if ($recetario) {
            return "Recetario activo - ID: {$recetario->id}";
        }
        
        return 'Sin recetario configurado';
    }

    protected function actualizarRecetario(bool $estado): void
    {
        $user = $this->getUser();
        
        if (!$user->hasRole('medico')) {
            Notification::make()
                ->title('Error')
                ->body('Solo los médicos pueden activar recetarios.')
                ->danger()
                ->send();
            return;
        }

        if (!$user->medico) {
            Notification::make()
                ->title('Registro Incompleto')
                ->body('Necesita completar su registro de médico para activar el recetario.')
                ->warning()
                ->send();
            return;
        }

        $medico = $user->medico;

        if (!$estado) {
            // Desactivar recetario
            $medico->recetarios()->delete();
            
            Notification::make()
                ->title('Recetario Desactivado')
                ->body('Su recetario ha sido desactivado correctamente.')
                ->success()
                ->send();
        } else {
            // Activar recetario
            $recetario = $medico->recetarios()->latest()->first();
            
            if (!$recetario) {
                Recetario::create([
                    'medico_id' => $medico->id,
                    'consulta_id' => null,
                ]);
                
                Notification::make()
                    ->title('Recetario Activado')
                    ->body('Su recetario ha sido activado correctamente.')
                    ->success()
                    ->send();
            }
        }
    }
}
