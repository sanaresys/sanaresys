<div class="flex items-center space-x-1 ml-2">
    <div class="relative">
        <button 
            type="button" 
            onclick="toggleThemeMenu()"
            class="flex items-center px-2 py-1 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
            title="Cambiar Tema"
        >
            <span id="current-theme-icon">🌞</span>
            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
        
        <div id="theme-dropdown" class="absolute right-0 mt-1 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 z-50 hidden">
            <div class="py-1">
                <button 
                    type="button" 
                    onclick="selectTheme('light')"
                    class="theme-option w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center"
                >
                    <span class="mr-2">🌞</span>
                    Tema Claro
                </button>
                <button 
                    type="button" 
                    onclick="selectTheme('dark')"
                    class="theme-option w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center"
                >
                    <span class="mr-2">🌙</span>
                    Tema Oscuro
                </button>
                <button 
                    type="button" 
                    onclick="selectTheme('custom-dark')"
                    class="theme-option w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center"
                >
                    <span class="mr-2">✨</span>
                    Tema Premium
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let themeMenuOpen = false;
    
    function toggleThemeMenu() {
        const dropdown = document.getElementById('theme-dropdown');
        themeMenuOpen = !themeMenuOpen;
        
        if (themeMenuOpen) {
            dropdown.classList.remove('hidden');
        } else {
            dropdown.classList.add('hidden');
        }
    }
    
    function selectTheme(theme) {
        const html = document.documentElement;
        
        // Limpiar clases previas
        html.classList.remove('dark', 'custom-dark-theme');
        
        // Aplicar nuevo tema
        switch(theme) {
            case 'light':
                // Tema claro por defecto
                break;
            case 'dark':
                html.classList.add('dark');
                break;
            case 'custom-dark':
                html.classList.add('dark', 'custom-dark-theme');
                break;
        }
        
        // Guardar preferencia
        localStorage.setItem('theme', theme);
        
        // Actualizar icono
        updateCurrentThemeIcon();
        
        // Cerrar menú
        document.getElementById('theme-dropdown').classList.add('hidden');
        themeMenuOpen = false;
        
        console.log(`✨ Tema ${theme} activado`);
    }
    
    function updateCurrentThemeIcon() {
        const icon = document.getElementById('current-theme-icon');
        const currentTheme = localStorage.getItem('theme') || 'light';
        
        switch(currentTheme) {
            case 'light':
                icon.textContent = '🌞';
                break;
            case 'dark':
                icon.textContent = '🌙';
                break;
            case 'custom-dark':
                icon.textContent = '✨';
                break;
        }
    }
    
    // Cerrar menú si se hace clic fuera
    document.addEventListener('click', function(event) {
        const themeSelector = event.target.closest('[onclick*="toggleThemeMenu"]') || 
                           event.target.closest('#theme-dropdown');
        
        if (!themeSelector && themeMenuOpen) {
            document.getElementById('theme-dropdown').classList.add('hidden');
            themeMenuOpen = false;
        }
    });
    
    // Inicializar tema al cargar
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        selectTheme(savedTheme);
    });
    
    // También ejecutar si el DOM ya está cargado
    if (document.readyState !== 'loading') {
        const savedTheme = localStorage.getItem('theme') || 'light';
        selectTheme(savedTheme);
    }
</script>
