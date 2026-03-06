<div class="w-full">
    <!-- Vista Previa -->
    <div class="mb-4">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Vista Previa de Factura
        </h3>
        
        <div class="factura-container rounded-xl overflow-hidden">
            <div class="bg-white dark:bg-gray-800 p-6">
                <div class="max-h-[600px] overflow-y-auto">
                    @livewire('factura-vista-previa')
                    
                    <!-- Fallback si Livewire no carga -->
                    <div id="livewire-fallback" class="text-center py-8" style="display: none;">
                        <div class="text-gray-400 dark:text-gray-500 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">Cargando vista previa de factura...</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Si esto no se carga, verifica la configuración de Livewire</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botones de Acción -->
        <div class="mt-4 flex gap-2 flex-wrap">
            <button type="button" 
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-sm flex items-center"
                    onclick="actualizarVistaPrevia()">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Actualizar Vista Previa
            </button>
            <button type="button" 
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors text-sm flex items-center"
                    onclick="recargarDatos()">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                Recargar Datos
            </button>
        </div>
        
        <!-- Información de ayuda -->
        <div class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="text-sm text-blue-700 dark:text-blue-300 font-medium mb-1">
                        Consejos para el Diseño
                    </p>
                    <ul class="text-sm text-blue-600 dark:text-blue-400 space-y-1">
                        <li>• Los cambios se reflejan automáticamente en la vista previa</li>
                        <li>• Usa colores contrastantes para mejor legibilidad</li>
                        <li>• El diseño se adapta automáticamente a tema claro/oscuro</li>
                        <li>• Guarda cuando estés satisfecho con el resultado</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Funciones globales para la vista previa
function actualizarVistaPrevia() {
    console.log('🔄 Actualizando vista previa...');
    if (window.Livewire) {
        Livewire.emit('recargarDatos');
    }
}

function recargarDatos() {
    console.log('✨ Recargando datos...');
    if (window.Livewire) {
        Livewire.emit('recargarDatos');
    }
}

// Configurar actualización automática
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 Vista previa de diseño de factura inicializada');
    
    // Verificar si Livewire se carga
    setTimeout(() => {
        if (!window.Livewire) {
            console.warn('⚠️ Livewire no se ha cargado correctamente');
            document.getElementById('livewire-fallback').style.display = 'block';
        } else {
            console.log('✅ Livewire cargado correctamente');
        }
    }, 3000);
    
    // Escuchar cambios en los campos del formulario
    let updateTimeout;
    
    document.addEventListener('input', function(e) {
        if (e.target.matches('input, select, textarea')) {
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(() => {
                console.log('📝 Campo modificado, actualizando vista previa...');
                actualizarVistaPrevia();
            }, 800); // Esperar 800ms después del último cambio
        }
    });
    
    // Escuchar cambios en toggles (inmediato)
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[type="checkbox"]')) {
            console.log('🔘 Toggle modificado, actualizando vista previa...');
            setTimeout(() => {
                actualizarVistaPrevia();
            }, 200);
        }
    });
    
    // Escuchar cambios en selects
    document.addEventListener('change', function(e) {
        if (e.target.matches('select')) {
            console.log('📋 Select modificado, actualizando vista previa...');
            setTimeout(() => {
                actualizarVistaPrevia();
            }, 200);
        }
    });
});

// Mostrar notificación cuando Livewire esté listo
document.addEventListener('livewire:load', function () {
    console.log('⚡ Livewire cargado correctamente');
});
</script>
@endpush

<style>
/* Estilos base para la vista previa */
.factura-preview {
    font-family: Arial, sans-serif;
    line-height: 1.4;
    background-color: white !important;
    color: black !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Asegurar que la vista previa sea visible en tema oscuro */
.dark .factura-preview {
    background-color: white !important;
    color: black !important;
}

.dark .factura-preview * {
    --tw-text-opacity: 1 !important;
    color: black !important;
}

/* Mantener colores específicos para elementos que lo necesiten */
.factura-preview [style*="color:"] {
    color: var(--custom-color, inherit) !important;
}

/* Estilos para el contenedor exterior en tema oscuro */
.dark .factura-container {
    background-color: rgb(17, 24, 39);
    padding: 1.5rem;
    border-radius: 0.5rem;
}

/* Animaciones y transiciones */
.factura-preview * {
    transition: all 0.2s ease-in-out;
}

/* Indicador de carga mejorado */
.loading-preview {
    position: relative;
}

.loading-preview::after {
    content: "🔄 Actualizando...";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(59, 130, 246, 0.95);
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    z-index: 10;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Mejorar la apariencia de los botones */
button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

button:active {
    transform: translateY(0);
}

/* Estilos de impresión */
@media print {
    .factura-preview {
        background-color: white !important;
        color: black !important;
        box-shadow: none !important;
    }
    
    .factura-preview * {
        color: black !important;
    }
}
</style>
