<x-filament-panels::page>
    <x-filament-panels::form 
        :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
        wire:submit="save"
    >
        {{ $this->form }}
        
        <x-filament-panels::form.actions 
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    <div class="mt-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Selector de Tema
                </h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                    <p>Selecciona el tema que prefieras para la interfaz del sistema.</p>
                </div>
                <div class="mt-5">
                    <div class="flex space-x-4">
                        <button 
                            type="button" 
                            onclick="setTheme('light')"
                            class="theme-btn light-theme inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                        >
                            🌞 Tema Claro
                        </button>
                        <button 
                            type="button" 
                            onclick="setTheme('dark')"
                            class="theme-btn dark-theme inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-gray-800 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                        >
                            🌙 Tema Oscuro
                        </button>
                        <button 
                            type="button" 
                            onclick="setTheme('custom-dark')"
                            class="theme-btn custom-dark-theme inline-flex items-center px-4 py-2 border border-purple-500 text-sm font-medium rounded-md text-white bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                        >
                            ✨ Tema Oscuro Personalizado
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setTheme(theme) {
            // Actualizar botones activos
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-emerald-500');
            });

            // Aplicar tema
            const html = document.documentElement;
            
            if (theme === 'light') {
                html.classList.remove('dark', 'custom-dark-theme');
                localStorage.setItem('theme', 'light');
                document.querySelector('.light-theme').classList.add('ring-2', 'ring-emerald-500');
            } else if (theme === 'dark') {
                html.classList.add('dark');
                html.classList.remove('custom-dark-theme');
                localStorage.setItem('theme', 'dark');
                document.querySelector('.dark-theme').classList.add('ring-2', 'ring-emerald-500');
            } else if (theme === 'custom-dark') {
                html.classList.add('dark', 'custom-dark-theme');
                localStorage.setItem('theme', 'custom-dark');
                document.querySelector('.custom-dark-theme').classList.add('ring-2', 'ring-purple-500');
                
                // Activar tema personalizado
                if (window.activateCustomDarkTheme) {
                    window.activateCustomDarkTheme();
                }
            }

            // Mostrar notificación
            if (window.$wire) {
                window.$wire.call('$notify', 'success', `Tema ${theme === 'light' ? 'claro' : theme === 'dark' ? 'oscuro' : 'oscuro personalizado'} activado`);
            }
        }

        // Detectar tema actual al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const currentTheme = localStorage.getItem('theme') || 'light';
            const html = document.documentElement;
            
            // Actualizar estado visual de los botones
            if (currentTheme === 'light') {
                document.querySelector('.light-theme').classList.add('ring-2', 'ring-emerald-500');
            } else if (currentTheme === 'dark') {
                document.querySelector('.dark-theme').classList.add('ring-2', 'ring-emerald-500');
            } else if (currentTheme === 'custom-dark') {
                document.querySelector('.custom-dark-theme').classList.add('ring-2', 'ring-purple-500');
            }
        });
    </script>
</x-filament-panels::page>
