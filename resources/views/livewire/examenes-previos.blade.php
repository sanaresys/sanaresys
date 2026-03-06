<div class="w-full">
    @if($mostrarAccordion)
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                <h3 class="text-lg font-medium text-gray-900">
                    Exámenes Previos
                    <span class="ml-2 text-sm text-gray-600">({{ count($examenes_previos) }} exámenes)</span>
                </h3>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach($examenes_previos as $examen)
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="font-medium text-gray-900">{{ $examen['tipo_examen'] }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($examen['estado'] === 'Completado') bg-green-100 text-green-800
                                        @elseif($examen['estado'] === 'Solicitado') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ $examen['estado'] }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 mb-3">
                                    <div>
                                        <strong>Médico:</strong> 
                                        {{ $examen['medico']['user']['name'] ?? 'No disponible' }}
                                    </div>
                                    <div>
                                        <strong>Fecha:</strong> 
                                        {{ \Carbon\Carbon::parse($examen['created_at'])->format('d/m/Y H:i') }}
                                    </div>
                                    @if($examen['observaciones'])
                                        <div class="col-span-full">
                                            <strong>Observaciones:</strong> {{ $examen['observaciones'] }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Estado del examen -->
                                @if($examen['estado'] === 'Solicitado')
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                                        <h5 class="text-sm font-medium text-blue-900 mb-2">Subir resultado del examen</h5>
                                        
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1">
                                                <input 
                                                    type="file" 
                                                    wire:model="imagenes.{{ $examen['id'] }}"
                                                    accept="image/*"
                                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                                >
                                                @error("imagenes.{$examen['id']}")
                                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            
                                            <button 
                                                wire:click="subirImagen({{ $examen['id'] }})"
                                                wire:loading.attr="disabled"
                                                wire:target="imagenes.{{ $examen['id'] }}"
                                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50">
                                                <span wire:loading.remove wire:target="imagenes.{{ $examen['id'] }}">Subir</span>
                                                <span wire:loading wire:target="imagenes.{{ $examen['id'] }}">Subiendo...</span>
                                            </button>
                                            
                                            <button 
                                                wire:click="marcarNoPresent({{ $examen['id'] }})"
                                                wire:confirm="¿Está seguro de marcar este examen como 'No presentado'?"
                                                class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                No presentado
                                            </button>
                                        </div>
                                    </div>

                                @elseif($examen['estado'] === 'Completado' && $examen['imagen_resultado'])
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                        <h5 class="text-sm font-medium text-green-900 mb-2">Resultado del examen</h5>
                                        
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1">
                                                <a 
                                                    href="{{ Storage::url('examenes/' . $examen['imagen_resultado']) }}" 
                                                    target="_blank"
                                                    class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    Ver imagen del resultado
                                                </a>
                                            </div>
                                            
                                            @can('manage_examenes')
                                                <button 
                                                    wire:click="eliminarImagen({{ $examen['id'] }})"
                                                    wire:confirm="¿Está seguro de eliminar esta imagen? El examen volverá al estado 'Solicitado'."
                                                    class="px-3 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                    Eliminar
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center py-6 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-2 text-sm">No hay exámenes previos para mostrar</p>
        </div>
    @endif
</div>
