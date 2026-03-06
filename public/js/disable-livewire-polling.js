// Este archivo reduce las actualizaciones automáticas de Livewire sin desactivar la funcionalidad
document.addEventListener('DOMContentLoaded', () => {
    // Esperar a que Livewire esté completamente cargado
    setTimeout(() => {
        if (window.Livewire) {
            console.log('Filtrando actualizaciones automáticas de Livewire');
            
            // Solo desactivar intervalos de polling específicos
            const origSetInterval = window.setInterval;
            window.setInterval = function(fn, delay) {
                const stack = new Error().stack || '';
                
                // Permitir que el calendario se cargue correctamente
                if (stack.includes('livewire') && delay < 10000 && 
                    fn.toString().includes('livewireId') &&
                    !fn.toString().includes('calendario')) {
                    console.log('Bloqueando intervalo de polling Livewire:', delay + 'ms');
                    return null; // No crear el intervalo
                }
                
                // Permitir otros intervalos
                return origSetInterval(fn, delay);
            };
            
            // Asegurar que los eventos de Alpine.js se procesen correctamente
            const origDispatchEvent = window.dispatchEvent;
            window.dispatchEvent = function(event) {
                // Detectar eventos Alpine.js del calendario y asegurar que se procesen
                if (event.type && event.type.includes('mostrar-citas-dia')) {
                    console.log('Detectado evento mostrar-citas-dia, asegurando procesamiento');
                    // Dar prioridad a este evento
                    setTimeout(() => {
                        origDispatchEvent.call(window, event);
                    }, 0);
                    return true;
                }
                
                // Otros eventos normales
                return origDispatchEvent.call(window, event);
            };
            
            // Detectar y registrar actualizaciones para depuración
            document.addEventListener('livewire:update', function(event) {
                console.log('Livewire update detectado:', event.detail);
            });
            
            console.log('Sistema de control de actualizaciones activo');
        }
    }, 1000); // Dar tiempo para que todo se inicialice, pero no demasiado
});
