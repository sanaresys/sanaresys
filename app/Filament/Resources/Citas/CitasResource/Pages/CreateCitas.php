<?php

namespace App\Filament\Resources\Citas\CitasResource\Pages;

use App\Filament\Resources\Citas\CitasResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Filament\Notifications\Notification;

class CreateCitas extends CreateRecord
{
    protected static string $resource = CitasResource::class;

    public ?array $defaultData = null;

    public function mount(): void
    {
        if (! Gate::allows('create', \App\Models\Citas::class)) {
            Notification::make()
                ->title('Sin permisos')
                ->body('No tienes permisos para crear citas.')
                ->danger()
                ->send();

            $this->redirect(static::getResource()::getUrl('index'));
            return;
        }

        $user = Auth::user();
        $defaultData = [];

        if ($user && $user->roles->contains('name', 'medico') && $user->medico) {
            $defaultData['medico_id'] = $user->medico->id;
        }

        if (request()->has('fecha')) {
            $fechaDesdeCalendario = request()->get('fecha');

            try {
                $fechaFormateada = \Carbon\Carbon::parse($fechaDesdeCalendario)->format('Y-m-d');
                $defaultData['fecha'] = $fechaFormateada;
                $defaultData['hora'] = '09:00';
            } catch (\Exception $e) {
                // noop
            }
        }

        $defaultData['estado'] = 'Pendiente';
        $this->defaultData = $defaultData;

        parent::mount();

        if (! empty($this->defaultData)) {
            $this->form->fill($this->defaultData);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if ($user && $user->roles->contains('name', 'medico') && $user->medico) {
            $data['medico_id'] = $user->medico->id;
        }

        if (! isset($data['medico_id']) || empty($data['medico_id'])) {
            throw new \Exception('No se pudo determinar el medico para la cita');
        }

        if (! isset($data['estado']) || empty($data['estado'])) {
            $data['estado'] = 'Pendiente';
        }

        if (isset($data['fecha'])) {
            $data['fecha'] = \Carbon\Carbon::parse($data['fecha'])->format('Y-m-d');
        }

        if (isset($data['hora'])) {
            if (strlen($data['hora']) === 5) {
                $data['hora'] = $data['hora'] . ':00';
            } else {
                $data['hora'] = \Carbon\Carbon::parse($data['hora'])->format('H:i:s');
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
