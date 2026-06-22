<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Formulario de Información Personal -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Información Personal</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Información básica del médico (solo lectura)</p>
            </div>
            
            <div class="p-6">
                {{ $this->form }}
            </div>
        </div>
        
        <!-- Formulario de Configuración del Recetario -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Configuración del Recetario</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Configure su recetario médico personalizado</p>
            </div>
            
            <div class="p-6">
                {{ $this->getRecetarioForm() }}
            </div>
        </div>

        <!-- Estadísticas del Recetario -->
        @if($this->recetarioData['tiene_recetario'] && auth()->user()->medico)
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Estadísticas del Recetario
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <div class="flex items-center">
                            <x-heroicon-o-document-text class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                    Total Recetas
                                </p>
                                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                    {{ optional(auth()->user()->medico)->recetas()->count() ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <div class="flex items-center">
                            <x-heroicon-o-check-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-600 dark:text-green-400">
                                    Este Mes
                                </p>
                                <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                                    {{ optional(auth()->user()->medico)->recetas()->whereMonth('created_at', now()->month)->count() ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                        <div class="flex items-center">
                            <x-heroicon-o-calendar class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                            <div class="ml-3">
                                <p class="text-sm font-medium text-purple-600 dark:text-purple-400">
                                    Hoy
                                </p>
                                <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                                    {{ optional(auth()->user()->medico)->recetas()->whereDate('created_at', today())->count() ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(!auth()->user()->medico && auth()->user()->hasRole('root'))
            <!-- Aviso especial para usuario root -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <div class="flex items-start">
                    <x-heroicon-o-shield-check class="w-6 h-6 text-blue-600 dark:text-blue-400 mt-1" />
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100">
                            Acceso de Usuario Root
                        </h3>
                        <div class="mt-2 text-sm text-blue-800 dark:text-blue-200">
                            <p>Está accediendo como administrador root. Puede ver y configurar el recetario, pero las configuraciones no se guardarán sin un registro de médico asociado.</p>
                            <p class="mt-2">Para funcionalidad completa, asocie este usuario a un registro de médico en el sistema.</p>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(!auth()->user()->medico)
            <!-- Aviso para usuarios con rol médico pero sin registro -->
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-6">
                <div class="flex items-start">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-amber-600 dark:text-amber-400 mt-1" />
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-amber-900 dark:text-amber-100">
                            Registro de Médico Pendiente
                        </h3>
                        <div class="mt-2 text-sm text-amber-800 dark:text-amber-200">
                            <p>Para acceder al recetario, contacte al administrador para completar su registro médico.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
