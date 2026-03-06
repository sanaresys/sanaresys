@php
    $pacienteId = is_callable($paciente_id) ? $paciente_id() : $paciente_id;
@endphp

@if(!empty($pacienteId))
    <div class="mb-6">
        @livewire('examenes-previos', ['paciente_id' => $pacienteId], key('examenes-previos-' . $pacienteId))
    </div>
@else
    <div class="text-gray-500 text-sm p-4 bg-gray-50 rounded-lg">
        Seleccione un paciente para ver sus exámenes previos
    </div>
@endif
