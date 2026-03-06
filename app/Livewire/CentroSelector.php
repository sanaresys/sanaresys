<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Centros_Medico;

class CentroSelector extends Component
{
    public $search = '';
    public $selectedCentro;
    public $availableCentros;
    public $showDropdown = false;

    public function mount()
    {
        if (! $this->canManageCenters()) {
            $this->availableCentros = collect();
            $this->selectedCentro = null;
            $this->showDropdown = false;
            return;
        }

        $this->selectedCentro = tenancy()->initialized ? tenancy()->tenant?->centro_id : null;
        $this->loadCentros();
    }

    public function loadCentros()
    {
        $user = auth()->user();
        if (! $user || ! $this->canManageCenters()) {
            $this->availableCentros = collect();
            return;
        }

        $this->availableCentros = Centros_Medico::query()
            ->where('tenancy_mode', 'domain')
            ->when($this->search, function ($query) {
                return $query->where('nombre_centro', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nombre_centro')
            ->get();
    }

    public function updatedSearch()
    {
        $this->loadCentros();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        if ($this->showDropdown) {
            $this->search = '';
            $this->loadCentros();
        }
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
        $this->search = '';
    }

    public function render()
    {
        if (! $this->canManageCenters()) {
            return view('livewire.centro-selector-empty');
        }

        return view('livewire.centro-selector');
    }

    protected function canManageCenters(): bool
    {
        $user = auth()->user();

        return (bool) $user?->hasRole('root');
    }
}
