<div class="overflow-x-auto">
    <table class="w-full border-collapse border border-gray-300 dark:border-gray-600">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-800">
                <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Médico
                </th>
                <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Salario
                </th>
                <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Deducciones
                </th>
                <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Percepciones
                </th>
                <th class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Total
                </th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900">
            @forelse($getRecord()->detalles as $detalle)
                <tr>
                    <td class="px-4 py-3 border border-gray-300 dark:border-gray-600">
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ $detalle->medico_nombre }}
                        </div>
                    </td>
                    <td class="px-4 py-3 border border-gray-300 dark:border-gray-600">
                        <span class="text-gray-900 dark:text-white">
                            L. {{ number_format($detalle->salario_base, 2) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 border border-gray-300 dark:border-gray-600">
                        @if($detalle->deducciones > 0)
                            <div class="text-red-600 dark:text-red-400">
                                <span>L. {{ number_format($detalle->deducciones, 2) }}</span>
                                @if($detalle->deducciones_detalle)
                                    <button
                                        type="button"
                                        class="ml-1 text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                        x-data="{}"
                                        x-on:click="$dispatch('open-modal', { id: 'deducciones-modal-{{ $detalle->id }}' })"
                                    >
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                    <div
                                        x-data="{ open: false }"
                                        x-on:open-modal.window="if ($event.detail.id === 'deducciones-modal-{{ $detalle->id }}') open = true"
                                        x-on:close-modal.window="if ($event.detail.id === 'deducciones-modal-{{ $detalle->id }}') open = false"
                                        x-on:keydown.escape.window="open = false"
                                        x-show="open"
                                        class="fixed inset-0 z-50 overflow-y-auto"
                                        style="display: none;"
                                    >
                                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                            <div
                                                x-show="open"
                                                x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0"
                                                class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                                                aria-hidden="true"
                                            ></div>

                                            <div
                                                x-show="open"
                                                x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                                            >
                                                <div>
                                                    <div class="mt-3 text-center sm:mt-5">
                                                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                                            Detalles de Deducciones
                                                        </h3>
                                                        <div class="mt-4">
                                                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md text-left">
                                                                <pre class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ $detalle->deducciones_detalle }}</pre>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-5 sm:mt-6">
                                                    <button
                                                        type="button"
                                                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm dark:bg-blue-700 dark:hover:bg-blue-800"
                                                        x-on:click="open = false"
                                                    >
                                                        Cerrar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">
                                L. 0.00
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 border border-gray-300 dark:border-gray-600">
                        @if($detalle->percepciones > 0)
                            <div class="text-green-600 dark:text-green-400">
                                <span>L. {{ number_format($detalle->percepciones, 2) }}</span>
                                @if($detalle->percepciones_detalle)
                                    <button
                                        type="button"
                                        class="ml-1 text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                        x-data="{}"
                                        x-on:click="$dispatch('open-modal', { id: 'percepciones-modal-{{ $detalle->id }}' })"
                                    >
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                    <div
                                        x-data="{ open: false }"
                                        x-on:open-modal.window="if ($event.detail.id === 'percepciones-modal-{{ $detalle->id }}') open = true"
                                        x-on:close-modal.window="if ($event.detail.id === 'percepciones-modal-{{ $detalle->id }}') open = false"
                                        x-on:keydown.escape.window="open = false"
                                        x-show="open"
                                        class="fixed inset-0 z-50 overflow-y-auto"
                                        style="display: none;"
                                    >
                                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                            <div
                                                x-show="open"
                                                x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0"
                                                class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                                                aria-hidden="true"
                                            ></div>

                                            <div
                                                x-show="open"
                                                x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                                            >
                                                <div>
                                                    <div class="mt-3 text-center sm:mt-5">
                                                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                                            Detalles de Percepciones
                                                        </h3>
                                                        <div class="mt-4">
                                                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md text-left">
                                                                <pre class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ $detalle->percepciones_detalle }}</pre>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-5 sm:mt-6">
                                                    <button
                                                        type="button"
                                                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm dark:bg-blue-700 dark:hover:bg-blue-800"
                                                        x-on:click="open = false"
                                                    >
                                                        Cerrar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">
                                L. 0.00
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 border border-gray-300 dark:border-gray-600">
                        <span class="font-semibold text-green-600 dark:text-green-400">
                            L. {{ number_format($detalle->total_pagar, 2) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-600">
                        No hay médicos en esta nómina.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($getRecord()->detalles->count() > 0)
            <tfoot class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <td colspan="4" class="px-4 py-3 border border-gray-300 dark:border-gray-600 text-right font-semibold text-gray-900 dark:text-white">
                        Total Nómina:
                    </td>
                    <td class="px-4 py-3 border border-gray-300 dark:border-gray-600">
                        <span class="font-bold text-lg text-green-600 dark:text-green-400">
                            L. {{ number_format($getRecord()->total_nomina, 2) }}
                        </span>
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
