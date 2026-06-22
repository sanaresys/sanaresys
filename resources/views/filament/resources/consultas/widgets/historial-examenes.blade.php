<div class="bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
            📋 Historial de Exámenes del Paciente
        </h3>
        
        @if($this->getPacienteId())
            @livewire('examenes-previos', ['paciente_id' => $this->getPacienteId()], key('examenes-previos-widget-' . $this->getPacienteId()))
        @else
            <div class="text-center py-6">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    No hay información del paciente disponible.
                </p>
            </div>
        @endif
    </div>
</div>
