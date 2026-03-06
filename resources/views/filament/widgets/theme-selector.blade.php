<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🎨 Selector de Tema de la Interfaz
        </x-slot>
        
        <x-slot name="description">
            Personaliza la apariencia del sistema médico con nuestros temas disponibles
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Tema Claro -->
            <div class="relative">
                <button 
                    type="button" 
                    onclick="setTheme('light')"
                    class="theme-selector-btn light-theme w-full p-4 border-2 border-gray-200 rounded-lg hover:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all duration-300 bg-white"
                >
                    <div class="flex flex-col items-center space-y-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center text-2xl">
                            🌞
                        </div>
                        <h3 class="font-semibold text-gray-900">Tema Claro</h3>
                        <p class="text-sm text-gray-600">Interfaz clara y profesional</p>
                    </div>
                </button>
            </div>

            <!-- Tema Oscuro -->
            <div class="relative">
                <button 
                    type="button" 
                    onclick="setTheme('dark')"
                    class="theme-selector-btn dark-theme w-full p-4 border-2 border-gray-600 rounded-lg hover:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all duration-300 bg-gray-800 text-white"
                >
                    <div class="flex flex-col items-center space-y-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-gray-700 to-gray-800 rounded-full flex items-center justify-center text-2xl">
                            🌙
                        </div>
                        <h3 class="font-semibold text-white">Tema Oscuro</h3>
                        <p class="text-sm text-gray-300">Menos fatiga visual</p>
                    </div>
                </button>
            </div>

            <!-- Tema Oscuro Personalizado -->
            <div class="relative">
                <button 
                    type="button" 
                    onclick="setTheme('custom-dark')"
                    class="theme-selector-btn custom-dark-theme w-full p-4 border-2 border-purple-500 rounded-lg hover:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all duration-300 bg-gradient-to-br from-purple-600 to-blue-600 text-white"
                >
                    <div class="flex flex-col items-center space-y-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center text-2xl shadow-lg">
                            ✨
                        </div>
                        <h3 class="font-semibold text-white">Tema Premium</h3>
                        <p class="text-sm text-purple-100">Experiencia premium con efectos</p>
                    </div>
                </button>
            </div>
        </div>

        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="flex items-center space-x-2">
                <div class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs">
                    ℹ️
                </div>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    Tu selección de tema se guardará automáticamente y se aplicará en todas tus sesiones.
                </p>
            </div>
        </div>
    </x-filament::section>

    <script>
        function updateThemeSelector() {
            // Remover todas las clases de selección
            document.querySelectorAll('.theme-selector-btn').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-emerald-500', 'ring-purple-500', 'scale-105');
            });

            // Agregar clase al tema activo
            const currentTheme = localStorage.getItem('theme') || 'light';
            let activeButton;
            
            switch(currentTheme) {
                case 'light':
                    activeButton = document.querySelector('.theme-selector-btn.light-theme');
                    if (activeButton) {
                        activeButton.classList.add('ring-2', 'ring-emerald-500', 'scale-105');
                    }
                    break;
                case 'dark':
                    activeButton = document.querySelector('.theme-selector-btn.dark-theme');
                    if (activeButton) {
                        activeButton.classList.add('ring-2', 'ring-emerald-500', 'scale-105');
                    }
                    break;
                case 'custom-dark':
                    activeButton = document.querySelector('.theme-selector-btn.custom-dark-theme');
                    if (activeButton) {
                        activeButton.classList.add('ring-2', 'ring-purple-400', 'scale-105');
                    }
                    break;
            }
        }

        // Actualizar selector al cargar
        document.addEventListener('DOMContentLoaded', updateThemeSelector);
        
        // Escuchar cambios de tema
        window.addEventListener('themeChanged', updateThemeSelector);
        
        // También escuchar cambios en localStorage
        window.addEventListener('storage', function(e) {
            if (e.key === 'theme') {
                updateThemeSelector();
            }
        });
    </script>
</x-filament-widgets::widget>
