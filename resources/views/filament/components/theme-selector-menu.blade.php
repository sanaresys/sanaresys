<div class="fi-dropdown-list-item">
    <div class="px-4 py-2">
        <div class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
            🎨 Seleccionar Tema
        </div>
        <div class="flex space-x-2">
            <button 
                type="button" 
                onclick="setTheme('light')"
                id="theme-light-btn"
                class="theme-btn flex items-center px-3 py-1 text-xs border rounded-md transition-all hover:bg-gray-100 dark:hover:bg-gray-700"
                title="Tema Claro"
            >
                🌞
            </button>
            <button 
                type="button" 
                onclick="setTheme('dark')"
                id="theme-dark-btn"
                class="theme-btn flex items-center px-3 py-1 text-xs border rounded-md transition-all hover:bg-gray-100 dark:hover:bg-gray-700"
                title="Tema Oscuro"
            >
                🌙
            </button>
            <button 
                type="button" 
                onclick="setTheme('custom-dark')"
                id="theme-custom-btn"
                class="theme-btn flex items-center px-3 py-1 text-xs border rounded-md transition-all hover:bg-gray-100 dark:hover:bg-gray-700"
                title="Tema Premium"
            >
                ✨
            </button>
        </div>
    </div>
</div>

<script>
    function setTheme(theme) {
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
        
        // Actualizar botones
        updateThemeButtons();
        
        // Mostrar notificación
        console.log(`Tema ${theme} activado`);
    }
    
    function updateThemeButtons() {
        // Remover clases activas
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.classList.remove('bg-emerald-100', 'border-emerald-500', 'text-emerald-700');
            btn.classList.remove('bg-purple-100', 'border-purple-500', 'text-purple-700');
        });
        
        // Agregar clase al botón activo
        const currentTheme = localStorage.getItem('theme') || 'light';
        let activeBtn;
        
        switch(currentTheme) {
            case 'light':
                activeBtn = document.getElementById('theme-light-btn');
                if (activeBtn) {
                    activeBtn.classList.add('bg-emerald-100', 'border-emerald-500', 'text-emerald-700');
                }
                break;
            case 'dark':
                activeBtn = document.getElementById('theme-dark-btn');
                if (activeBtn) {
                    activeBtn.classList.add('bg-emerald-100', 'border-emerald-500', 'text-emerald-700');
                }
                break;
            case 'custom-dark':
                activeBtn = document.getElementById('theme-custom-btn');
                if (activeBtn) {
                    activeBtn.classList.add('bg-purple-100', 'border-purple-500', 'text-purple-700');
                }
                break;
        }
    }
    
    // Cargar tema al inicializar
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        setTheme(savedTheme);
    });
    
    // También ejecutar si el DOM ya está cargado
    if (document.readyState !== 'loading') {
        const savedTheme = localStorage.getItem('theme') || 'light';
        setTheme(savedTheme);
    }
</script>
