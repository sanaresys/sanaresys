<!-- resources/views/components/action-buttons.blade.php -->
<div class="flex space-x-3">
    <!-- Botón PDF -->
    <a href="{{ $pdfUrl ?? '#' }}" target="_blank" 
       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 border border-transparent rounded-lg font-medium text-sm text-white hover:from-emerald-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105 hover:shadow-lg">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        📄 Generar PDF
    </a>

    <!-- Botón Editar -->
    @if(!$isLocked ?? false)
    <a href="{{ $editUrl ?? '#' }}" 
       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 border border-transparent rounded-lg font-medium text-sm text-white hover:from-amber-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105 hover:shadow-lg">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        ✏️ Editar
    </a>
    @endif

    <!-- Botón Ver -->
    <a href="{{ $viewUrl ?? '#' }}" 
       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-sky-500 to-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:from-sky-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105 hover:shadow-lg">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        👁️ Ver Detalles
    </a>
</div>
