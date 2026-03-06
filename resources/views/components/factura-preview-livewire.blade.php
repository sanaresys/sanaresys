<div x-data="facturaPreview()" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Panel de Vista Previa -->
    <div class="order-2 lg:order-1">
        <div class="sticky top-4">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                📄 Vista Previa de Factura
            </h3>
            <div class="border rounded-lg overflow-hidden bg-white shadow-lg max-h-[800px] overflow-y-auto">
                @livewire('factura-vista-previa', ['disenoId' => $getRecord()?->id])
            </div>
            
            <!-- Botones de Acción -->
            <div class="mt-4 flex gap-2 flex-wrap">
                <button type="button" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-sm"
                        onclick="window.open('/facturas/preview/' + ({{ $getRecord()?->id ?? 'null' }}) + '/pdf', '_blank')">
                    📄 Ver PDF Completo
                </button>
                <button type="button" 
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors text-sm"
                        onclick="actualizarVistaPrevia()">
                    🔄 Actualizar Vista
                </button>
                <button type="button" 
                        class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition-colors text-sm"
                        onclick="recargarDatosReales()">
                    📊 Recargar Datos Reales
                </button>
            </div>
        </div>
    </div>
    
    <!-- Formulario de Configuración -->
    <div class="order-1 lg:order-2">
        <!-- El formulario se renderiza aquí automáticamente por Filament -->
    </div>
</div>

<script>
function facturaPreview() {
    return {
        init() {
            // Escuchar cambios en el formulario
            this.setupFormListeners();
            
            // Escuchar eventos de Livewire
            Livewire.on('actualizarVista', (data) => {
                this.actualizarVistaPrevia(data);
            });
            
            // Escuchar cambios en los toggles específicamente
            document.addEventListener('change', (e) => {
                if (e.target.type === 'checkbox' || e.target.classList.contains('filament-toggle-input')) {
                    this.actualizarVistaPrevia();
                }
            });
        },
        
        setupFormListeners() {
            const form = document.querySelector('form');
            if (form) {
                // Observar cambios en todos los inputs
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' || mutation.type === 'childList') {
                            this.actualizarVistaPrevia();
                        }
                    });
                });
                
                observer.observe(form, {
                    attributes: true,
                    childList: true,
                    subtree: true
                });

                // Manejar eventos de input y change
                form.addEventListener('change', (e) => {
                    this.actualizarVistaPrevia();
                }, true);

                form.addEventListener('input', (e) => {
                    clearTimeout(this.updateTimeout);
                    this.updateTimeout = setTimeout(() => {
                        this.actualizarVistaPrevia();
                    }, 300);
                }, true);
            }
        },
        
        actualizarVistaPrevia(data = null) {
            // Si no se proporcionan datos, recopilarlos del formulario
            const formData = data || this.recopilarDatosFormulario();
            
            // Usar el nuevo método dispatch de Livewire 3
            Livewire.dispatch('actualizarVista', { data: formData });
            Livewire.dispatch('refresh-preview');
        },
        
        recopilarDatosFormulario() {
            const form = document.querySelector('form');
            const formData = new FormData(form);
            const datos = {};
            
            // Convertir FormData a objeto
            for (let [key, value] of formData.entries()) {
                if (key.includes('[') && key.includes(']')) {
                    // Manejar campos anidados
                    const cleanKey = key.replace(/.*\[(.+)\]/, '$1');
                    datos[cleanKey] = value;
                } else {
                    datos[key] = value;
                }
            }
            
            // Agregar datos específicos de checkboxes y selects
            const checkboxes = form.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                const name = checkbox.name.replace(/.*\[(.+)\]/, '$1');
                datos[name] = checkbox.checked;
            });
            
            return datos;
        }
    }
}

// Función global para actualizar vista previa
function actualizarVistaPrevia() {
    const form = document.querySelector('form');
    if (form) {
        const event = new Event('change', { bubbles: true });
        form.dispatchEvent(event);
    }
}

// Función global para recargar datos reales
function recargarDatosReales() {
    Livewire.emit('recargarDatos');
}

// Auto-actualizar cada vez que cambie un valor
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que Filament se haya cargado completamente
    setTimeout(() => {
        const form = document.querySelector('form');
        if (form) {
            // Configurar observador de mutaciones para detectar cambios dinámicos
            const observer = new MutationObserver(() => {
                actualizarVistaPrevia();
            });
            
            observer.observe(form, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['value']
            });
        }
    }, 1000);
});
</script>

<style>
/* Estilos para mejorar la vista previa */
.factura-preview {
    transform: scale(0.85);
    transform-origin: top left;
}

/* Animaciones suaves para los cambios */
.factura-preview * {
    transition: all 0.3s ease;
}

/* Indicador de carga */
.loading-preview {
    opacity: 0.6;
    pointer-events: none;
}

/* Responsive */
@media (max-width: 1024px) {
    .factura-preview {
        transform: scale(0.75);
    }
}
</style>
