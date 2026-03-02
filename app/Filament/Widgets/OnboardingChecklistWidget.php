<?php

namespace App\Filament\Widgets;

use App\Models\Centros_Medico;
use App\Models\Medico;
use App\Models\Pacientes;
use App\Models\Servicio;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class OnboardingChecklistWidget extends Widget
{
    protected static string $view = 'filament.widgets.onboarding-checklist';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -10; // Mostrar arriba del dashboard

    public function getChecklist(): array
    {
        $centro = $this->getCentro();
        if (!$centro) {
            return [];
        }

        // Solo mostrar el widget si el onboarding fue completado hace menos de 7 días
        if (!$centro->onboarding_completed_at || 
            $centro->onboarding_completed_at->diffInDays(now()) > 7) {
            return [];
        }

        return [
            [
                'title' => 'Agregar médicos/personal',
                'description' => 'Registra a los médicos y personal que usarán el sistema',
                'icon' => 'heroicon-o-user-group',
                'completed' => Medico::count() > 0,
                'url' => route('filament.admin.resources.medicos.index'),
                'color' => 'primary',
            ],
            [
                'title' => 'Registrar pacientes',
                'description' => 'Comienza a registrar tus pacientes en el sistema',
                'icon' => 'heroicon-o-users',
                'completed' => Pacientes::count() > 0,
                'url' => route('filament.admin.resources.pacientes.index'),
                'color' => 'success',
            ],
            [
                'title' => 'Personalizar facturación',
                'description' => 'Configura tus preferencias de facturación y pagos',
                'icon' => 'heroicon-o-document-text',
                'completed' => false, // Siempre mostrar como pendiente
                'url' => '/admin', // Cambiar por la URL real cuando exista
                'color' => 'warning',
            ],
            [
                'title' => 'Configurar roles y permisos',
                'description' => 'Define qué puede hacer cada usuario en el sistema',
                'icon' => 'heroicon-o-shield-check',
                'completed' => false, // Siempre mostrar como pendiente
                'url' => route('filament.admin.resources.roles.index'),
                'color' => 'danger',
            ],
        ];
    }

    public function getProgress(): int
    {
        $checklist = $this->getChecklist();
        if (empty($checklist)) {
            return 0;
        }

        $completed = count(array_filter($checklist, fn($item) => $item['completed']));
        return (int) round(($completed / count($checklist)) * 100);
    }

    public function canDismiss(): bool
    {
        return true;
    }

    public function dismiss(): void
    {
        $centro = $this->getCentro();
        if ($centro) {
            // Actualizar la fecha de completado para que sea hace más de 7 días
            // Esto efectivamente oculta el widget
            $centro->update([
                'onboarding_completed_at' => now()->subDays(8)
            ]);
        }
    }

    protected function getCentro(): ?Centros_Medico
    {
        $user = Auth::user();
        if (!$user || !$user->centro_id) {
            return null;
        }

        return Centros_Medico::on('mysql')->find($user->centro_id);
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user || !$user->centro_id) {
            return false;
        }

        $centro = Centros_Medico::on('mysql')->find($user->centro_id);
        
        // Solo mostrar si el onboarding fue completado recientemente
        return $centro && 
               $centro->onboarding_completed_at && 
               $centro->onboarding_completed_at->diffInDays(now()) <= 7;
    }
}
