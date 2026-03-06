// Sistema de temas personalizado para Filament
document.addEventListener('DOMContentLoaded', function() {
    // Detectar cambios en el tema de Filament
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && 
                (mutation.attributeName === 'class' || mutation.attributeName === 'data-theme')) {
                checkThemeMode();
            }
        });
    });

    // Observar cambios en el elemento html
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class', 'data-theme']
    });

    // Función para verificar y aplicar el tema
    function checkThemeMode() {
        const htmlElement = document.documentElement;
        const isDark = htmlElement.classList.contains('dark');
        
        // Aquí puedes agregar lógica adicional para detectar 
        // si se selecciona el tema oscuro personalizado
        // Por ahora, aplicaremos el tema personalizado cuando esté en modo oscuro
        
        if (isDark) {
            // Opcionalmente aplicar tema oscuro personalizado
            // htmlElement.classList.add('dark-custom');
        } else {
            htmlElement.classList.remove('dark-custom');
        }
    }

    // Ejecutar al cargar
    checkThemeMode();

    // Agregar botón para alternar al tema oscuro personalizado
    // (esto se puede hacer más tarde si quieres un selector específico)
});

// Función global para activar tema oscuro personalizado
window.activateCustomDarkTheme = function() {
    document.documentElement.classList.add('dark', 'dark-custom');
    localStorage.setItem('theme', 'dark-custom');
};

// Función global para desactivar tema oscuro personalizado
window.deactivateCustomDarkTheme = function() {
    document.documentElement.classList.remove('dark-custom');
    localStorage.setItem('theme', 'light');
};
