<div class="flex items-center space-x-2 text-sm">
    <div class="flex items-center space-x-1">
        <span class="theme-indicator" id="theme-indicator">🌞</span>
        <span class="theme-name" id="theme-name">Tema Claro</span>
    </div>
</div>

<script>
    function updateThemeIndicator() {
        const indicator = document.getElementById('theme-indicator');
        const name = document.getElementById('theme-name');
        const currentTheme = localStorage.getItem('theme') || 'light';
        
        switch(currentTheme) {
            case 'light':
                indicator.textContent = '🌞';
                name.textContent = 'Tema Claro';
                break;
            case 'dark':
                indicator.textContent = '🌙';
                name.textContent = 'Tema Oscuro';
                break;
            case 'custom-dark':
                indicator.textContent = '✨';
                name.textContent = 'Tema Premium';
                break;
        }
    }
    
    // Actualizar al cargar
    document.addEventListener('DOMContentLoaded', updateThemeIndicator);
    
    // Escuchar cambios de tema
    window.addEventListener('themeChanged', updateThemeIndicator);
    
    // También escuchar cambios en localStorage
    window.addEventListener('storage', function(e) {
        if (e.key === 'theme') {
            updateThemeIndicator();
        }
    });
</script>
