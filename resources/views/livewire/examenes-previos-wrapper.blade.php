@if(!empty($paciente_id))
    <div class="mb-6">
        @livewire('examenes-previos', ['paciente_id' => $paciente_id], key('examenes-previos-' . $paciente_id))
    </div>
@endif
