<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
    <div class="flex items-center space-x-3 mb-3">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                <x-heroicon-s-user class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ $patient->persona->nombre_completo }}
            </h3>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($patient->persona->identificacion)
        <div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Identificación:</span>
            <span class="text-sm text-gray-900 dark:text-gray-100 ml-2">{{ $patient->persona->identificacion }}</span>
        </div>
        @endif

        @if($patient->persona->telefono)
        <div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono:</span>
            <span class="text-sm text-gray-900 dark:text-gray-100 ml-2">{{ $patient->persona->telefono }}</span>
        </div>
        @endif

        @if($patient->persona->email)
        <div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Email:</span>
            <span class="text-sm text-gray-900 dark:text-gray-100 ml-2">{{ $patient->persona->email }}</span>
        </div>
        @endif

        @if($patient->persona->fecha_nacimiento)
        <div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Nacimiento:</span>
            <span class="text-sm text-gray-900 dark:text-gray-100 ml-2">
                {{ \Carbon\Carbon::parse($patient->persona->fecha_nacimiento)->format('d/m/Y') }}
                ({{ \Carbon\Carbon::parse($patient->persona->fecha_nacimiento)->age }} años)
            </span>
        </div>
        @endif
    </div>
</div>
