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
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
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
                            <span class="text-red-600 dark:text-red-400">
                                L. {{ number_format($detalle->deducciones, 2) }}
                            </span>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">
                                L. 0.00
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 border border-gray-300 dark:border-gray-600">
                        @if($detalle->percepciones > 0)
                            <span class="text-green-600 dark:text-green-400">
                                L. {{ number_format($detalle->percepciones, 2) }}
                            </span>
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
