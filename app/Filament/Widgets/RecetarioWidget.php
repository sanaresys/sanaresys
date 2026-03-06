<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Models\Recetario;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RecetarioWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.recetario-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    public static function canView(): bool
    {
        // Solo mostrar si hay tenant activo y usuario es médico
        return tenancy()->initialized && Auth::user()?->medico !== null;
    }
    
    public ?array $data = [];

    public function mount(): void
    {
        $this->loadRecetarioData();
    }

    protected function loadRecetarioData(): void
    {
        $user = Auth::user();
        
        if ($user->medico) {
            $recetario = $user->medico->recetarios()->latest()->first();
            $this->data = [
                'tiene_recetario' => $recetario ? true : false,
            ];
        } else {
            $this->data = [
                'tiene_recetario' => false,
            ];
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Toggle::make('tiene_recetario')
                            ->label('Activar Recetario')
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->actualizarRecetario($state);
                            }),
                    ])
                    ->extraAttributes([
                        'class' => 'flex justify-between items-center',
                    ])
                    ->headerActions([
                        Action::make('ver_perfil')
                            ->label('Configuración Completa')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->button()
                            ->color('gray')
                            ->url(fn () => route('filament.admin.pages.perfil-medico'))
                            ->openUrlInNewTab(false),
                    ])
                    ->compact(),
            ])
            ->statePath('data');
    }

    protected function actualizarRecetario(bool $estado): void
    {
        $user = Auth::user();
        
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

        if (!$medico) {
            Notification::make()
                ->title('Error')
                ->body('No se encontró registro de médico asociado.')
                ->danger()
                ->send();
            return;
        }

        if (!$estado) {
            $medico->recetarios()->delete();
            
            Notification::make()
                ->title('Recetario Desactivado')
                ->success()
                ->send();
        } else {
            $recetario = $medico->recetarios()->latest()->first();
            
            if (!$recetario) {
                // Multi-tenant: centro_id no es necesario
                Recetario::create([
                    'medico_id' => $medico->id,
                    'consulta_id' => null,
                ]);
                
                Notification::make()
                    ->title('Recetario Activado')
                    ->success()
                    ->send();
            }
        }
        
        $this->loadRecetarioData();
    }
}
