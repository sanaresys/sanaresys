<x-filament-panels::page>
    {{-- Panel de información de la factura --}}
    <x-filament::section>
        <x-slot name="heading">Información de la Factura</x-slot>

        @php
            // Información de contexto usando el record disponible
            $pacienteNombre = $this->record?->paciente?->persona?->nombre_completo ?? 'Paciente no encontrado';
            $medicoNombre = $this->record?->medico?->persona?->nombre_completo ?? 'Médico no encontrado';
            $centroNombre = auth()->user()->centro?->nombre_centro ?? 'Centro Médico';
            $fecha = $this->record?->created_at?->format('d/m/Y') ?? now()->format('d/m/Y');

            // Estado del CAI (desde request, old o modelo)
            $consultaId = $this->record->id;
            $caiEstadoGuardado = false;

            if (old('usa_cai')) {
                $caiEstadoGuardado = old('usa_cai') == '1';
            } elseif (request()->has('usa_cai')) {
                $caiEstadoGuardado = request()->get('usa_cai') == '1';
            } elseif ($this->record->usa_cai ?? false) {
                $caiEstadoGuardado = true;
            }

            // CAI disponible
            $centroId = auth()->user()->centro_id;
            $cai = \App\Services\CaiNumerador::obtenerCAIDisponible($centroId);
        @endphp

        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                {{-- Paciente --}}
                <div>
                    <x-heroicon-o-user class="w-8 h-8 mx-auto text-blue-600 dark:text-blue-300" />
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mt-2">Paciente</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $pacienteNombre }}</p>
                </div>

                {{-- Médico --}}
                <div>
                    <x-heroicon-o-clipboard-document class="w-8 h-8 mx-auto text-green-600 dark:text-green-300" />
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mt-2">Médico</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $medicoNombre }}</p>
                </div>

                {{-- Centro --}}
                <div>
                    <x-heroicon-o-building-library class="w-8 h-8 mx-auto text-purple-600 dark:text-purple-300" />
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mt-2">Centro</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $centroNombre }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $fecha }}</p>
                </div>
            </div>

            {{-- Segunda fila: Número, CAI Toggle, Estado --}}
            <div class="mt-6 pt-6 border-t border-blue-200 dark:border-blue-700">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Número de Factura --}}
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500 uppercase mb-1">Número de Factura</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Se generará automáticamente</p>
                    </div>

                    {{-- ¿Emitir con CAI? --}}
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">¿Generar Factura?</p>
                        <div class="flex items-center justify-center space-x-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox"
                                       id="emitir_cai_toggle"
                                       {{ $caiEstadoGuardado ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-blue-400">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active para una Factura</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Desactive para generar una factura provisional</p>
                    </div>

                    {{-- Estado --}}
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Estado Previsto</p>
                        <div id="estado_factura_preview" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800 dark:bg-orange-800/30 dark:text-orange-200">
                            <span id="estado_factura_text">Pre-Factura</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Basado en pagos configurados</p>
                    </div>
                </div>

                {{-- Información del CAI --}}
                <div id="cai-info-section" style="display: {{ $caiEstadoGuardado ? 'block' : 'none' }};" class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-700">
                    @if($cai)
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-700">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                                    <x-heroicon-o-check class="w-4 h-4 text-green-600 dark:text-green-300" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-medium text-green-800 dark:text-green-300 mb-1">CAI Disponible</p>
                                <p class="text-sm font-mono font-medium text-green-800 dark:text-green-200">{{ $cai->cai_codigo }}</p>
                                <p class="text-xs text-green-600 dark:text-green-400">Se emitirá automáticamente con CAI al facturar</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 border border-amber-200 dark:border-amber-700">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-500" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Sin CAI disponible</p>
                                <p class="text-xs text-amber-600 dark:text-amber-400">Se emitirá como proforma sin número fiscal</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Script breve para visibilidad inmediata del CAI --}}
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toggle = document.getElementById('emitir_cai_toggle');
                const section = document.getElementById('cai-info-section');
                const key = 'cai_toggle_state_{{ $consultaId }}';

                // Restaurar desde storage
                const saved = sessionStorage.getItem(key) || localStorage.getItem(key);
                if (saved !== null) {
                    toggle.checked = saved === 'true';
                    section.style.display = toggle.checked ? 'block' : 'none';
                }

                // Guardar y reflejar
                toggle.addEventListener('change', () => {
                    const checked = toggle.checked;
                    section.style.display = checked ? 'block' : 'none';
                    sessionStorage.setItem(key, checked);
                    localStorage.setItem(key, checked);
                });
            });
        </script>
    </x-filament::section>

    {{-- Panel de resumen --}}
    @php($subtotal = $this->getServiciosSubtotal())
    @php($impuestos = $this->getServiciosImpuesto())
    @php($total = $this->getServiciosTotal())
    @php($cantidad = $this->getCantidadServicios())

    @if ($subtotal > 0)
        <x-filament::section>
            <x-slot name="heading">
                Resumen de Servicios
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div class="text-center md:text-left">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Servicios agregados
                    </p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                        {{ $cantidad }} servicio(s)
                    </p>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Descuento a Aplicar</p>
                    <select id="descuento_select"
                            name="descuento_aplicado"
                            class="w-full max-w-xs rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm">
                        <option value="">Sin descuento</option>
                        @foreach(\App\Models\Descuento::where('centro_id', Auth::user()->centro_id)->get() as $descuento)
                            <option value="{{ $descuento->id }}"
                                    data-tipo="{{ $descuento->tipo }}"
                                    data-valor="{{ $descuento->valor }}"
                                    data-porcentaje="{{ $descuento->tipo == 'PORCENTAJE' ? $descuento->valor : 0 }}">
                                {{ $descuento->nombre }} ({{ $descuento->valor }}{{ $descuento->tipo == 'PORCENTAJE' ? '%' : '' }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Selecciona un descuento para la factura</p>
                </div>

                <div class="text-center md:text-right">
                   {{-- Total Final --}}
                    <p class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">Total Final</p>
                    <p class="text-xl font-bold text-green-800 dark:text-green-200" id="total_final_display">
                        L. {{ number_format($total, 2) }}
                    </p>
                    <p class="text-xs text-green-600 dark:text-green-400">Subtotal + Impuestos - Descuentos</p>
                </div>
            </div>

            {{-- Sección de Totales Detallados --}}
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Desglose de Totales</h4>

                <div class="flex flex-row gap-4 text-center">
                    {{-- Subtotal --}}
                    <div class="flex-1 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700">
                        <p class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">Subtotal</p>
                        <p class="text-lg font-bold text-blue-800 dark:text-blue-200 subtotal-amount" id="subtotal_display">
                            L. {{ number_format($subtotal, 2) }}
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">Sin impuestos ni descuentos</p>
                    </div>

                    {{-- Impuestos --}}
                    <div class="text-center flex-1 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-700">
                        <p class="text-xs font-medium text-yellow-600 dark:text-yellow-400 uppercase tracking-wide mb-1">Impuestos</p>
                        <p class="text-lg font-bold text-yellow-800 dark:text-yellow-200 total-impuestos" id="impuestos_display">
                            L. {{ number_format($impuestos, 2) }}
                        </p>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400">Total de impuestos</p>
                    </div>

                    {{-- Descuentos --}}
                    <div class="text-center flex-1 bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-700">
                        <p class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wide mb-1">Descuentos</p>
                        <p class="text-lg font-bold text-purple-800 dark:text-purple-200 total-descuentos" id="descuentos_display">
                            L. 0.00
                        </p>
                        <p class="text-xs text-purple-600 dark:text-purple-400">Descuento aplicado</p>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <div class="text-center py-8">
                <div class="text-gray-400 text-6xl mb-4">📋</div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No hay servicios agregados</h3>
                <p class="text-gray-600 dark:text-gray-400">Usa el botón <strong>"Agregar Servicios"</strong> para comenzar.</p>
            </div>
        </x-filament::section>
    @endif

    {{-- Tabla de servicios --}}
    <div class="mt-6">
        {{ $this->table }}
    </div>

    <script>
        // Script principal: gestiona CAI + Descuento con persistencia y resiliencia a Livewire
        (function() {
            'use strict';

            const CONSULTA_ID = '{{ $this->record->id }}';
            const CAI_KEY = `cai_toggle_state_${CONSULTA_ID}`;
            const DESCUENTO_KEY = `selected_descuento_${CONSULTA_ID}`;

            const totalesBase = {
                subtotal: {{ $subtotal }},
                impuestos: {{ $impuestos }},
                total: {{ $total }}
            };

            let isInitialized = false;

            function handleCAIToggle() {
                const toggle = document.getElementById('emitir_cai_toggle');
                const section = document.getElementById('cai-info-section');
                if (!toggle || !section) return;

                if (isInitialized) {
                    const isChecked = toggle.checked;
                    section.style.display = isChecked ? 'block' : 'none';
                    sessionStorage.setItem(CAI_KEY, isChecked);
                    localStorage.setItem(CAI_KEY, isChecked);
                    console.log('✅ CAI estado guardado:', isChecked);
                }
            }

            function calcularDescuento() {
                const select = document.getElementById('descuento_select');
                if (!select) return;

                const selectedOption = select.options[select.selectedIndex];
                let descuento = 0;

                if (selectedOption && selectedOption.value) {
                    const tipo = selectedOption.getAttribute('data-tipo');
                    const valor = parseFloat(selectedOption.getAttribute('data-valor') || 0);

                    if (tipo === 'PORCENTAJE' && valor > 0) {
                        descuento = totalesBase.subtotal * (valor / 100);
                    } else if (tipo === 'MONTO' && valor > 0) {
                        descuento = valor;
                    }
                }

                const totalFinal = totalesBase.subtotal + totalesBase.impuestos - descuento;

                const descuentosDisplay = document.getElementById('descuentos_display');
                const totalDisplay = document.getElementById('total_final_display');

                if (descuentosDisplay) descuentosDisplay.textContent = 'L. ' + descuento.toFixed(2);
                if (totalDisplay) totalDisplay.textContent = 'L. ' + totalFinal.toFixed(2);

                // Guardar descuento (sessionStorage)
                if (selectedOption && selectedOption.value) {
                    const data = {
                        id: selectedOption.value,
                        tipo: selectedOption.getAttribute('data-tipo'),
                        valor: selectedOption.getAttribute('data-valor'),
                        nombre: selectedOption.text,
                        monto_calculado: descuento
                    };
                    sessionStorage.setItem(DESCUENTO_KEY, JSON.stringify(data));
                } else {
                    sessionStorage.removeItem(DESCUENTO_KEY);
                }
            }

            function restaurarEstados() {
                // CAI
                const toggle = document.getElementById('emitir_cai_toggle');
                const section = document.getElementById('cai-info-section');
                if (toggle && section) {
                    let estadoGuardado = sessionStorage.getItem(CAI_KEY);
                    if (estadoGuardado === null) {
                        estadoGuardado = localStorage.getItem(CAI_KEY);
                    }
                    if (estadoGuardado !== null) {
                        const isChecked = estadoGuardado === 'true';
                        toggle.checked = isChecked;
                        section.style.display = isChecked ? 'block' : 'none';
                        console.log('🔄 CAI restaurado:', isChecked);
                    }
                }

                // Descuento
                const descuentoSelect = document.getElementById('descuento_select');
                if (descuentoSelect) {
                    const savedDescuento = sessionStorage.getItem(DESCUENTO_KEY);
                    if (savedDescuento) {
                        try {
                            const data = JSON.parse(savedDescuento);
                            descuentoSelect.value = data.id;
                        } catch (e) {
                            console.log('Error restaurando descuento:', e);
                        }
                    }
                }

                calcularDescuento();
            }

            function init() {
                console.log('🚀 Inicializando ClinicaCAISystem...');

                // CAI
                const toggle = document.getElementById('emitir_cai_toggle');
                if (toggle) {
                    toggle.removeEventListener('change', handleCAIToggle);
                    toggle.addEventListener('change', handleCAIToggle);
                }

                // Descuentos
                const descuentoSelect = document.getElementById('descuento_select');
                if (descuentoSelect) {
                    descuentoSelect.removeEventListener('change', calcularDescuento);
                    descuentoSelect.addEventListener('change', calcularDescuento);
                }

                setTimeout(() => {
                    restaurarEstados();
                    isInitialized = true;
                    console.log('✅ Sistema inicializado');
                }, 100);
            }

            function verificarEstadoCAI() {
                const toggle = document.getElementById('emitir_cai_toggle');
                const section = document.getElementById('cai-info-section');

                if (toggle && section) {
                    const estadoGuardado = sessionStorage.getItem(CAI_KEY) || localStorage.getItem(CAI_KEY);
                    if (estadoGuardado !== null) {
                        const shouldBeChecked = estadoGuardado === 'true';
                        const shouldBeVisible = shouldBeChecked;

                        if (toggle.checked !== shouldBeChecked) {
                            toggle.checked = shouldBeChecked;
                            console.log('🔧 Checkbox CAI corregido');
                        }

                        const isVisible = section.style.display !== 'none';
                        if (isVisible !== shouldBeVisible) {
                            section.style.display = shouldBeVisible ? 'block' : 'none';
                            console.log('🔧 Sección CAI corregida');
                        }
                    }
                }
            }

            // Exponer a window para usos externos si lo necesitas
            window.ClinicaCAISystem = { init, calcularDescuento, verificarEstadoCAI, handleCAIToggle };

            // Inicialización
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    window.ClinicaCAISystem.init();
                });
            } else {
                window.ClinicaCAISystem.init();
            }

            // Eventos de Livewire
            document.addEventListener('livewire:load', () => {
                setTimeout(() => window.ClinicaCAISystem.init(), 200);
            });

            document.addEventListener('livewire:update', () => {
                setTimeout(() => {
                    window.ClinicaCAISystem.init();
                    setTimeout(() => window.ClinicaCAISystem.verificarEstadoCAI(), 100);
                }, 100);
            });

            // Evento personalizado para refresh totales
            document.addEventListener('refresh-totales', function() {
                console.log('🔄 Evento refresh-totales recibido - NO recargando página');
                setTimeout(() => {
                    window.ClinicaCAISystem.init();
                    window.ClinicaCAISystem.calcularDescuento();
                }, 100);
            });

            // Verificación periódica
            setInterval(() => {
                window.ClinicaCAISystem.verificarEstadoCAI();
            }, 1000);

            // Observer de cambios DOM
            const observer = new MutationObserver(function(mutations) {
                let shouldReinit = false;

                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        for (let node of mutation.addedNodes) {
                            if (node.nodeType === 1 && (
                                node.id === 'emitir_cai_toggle' ||
                                node.id === 'cai-info-section' ||
                                (node.querySelector && (
                                    node.querySelector('#emitir_cai_toggle') ||
                                    node.querySelector('#cai-info-section')
                                ))
                            )) {
                                shouldReinit = true;
                                break;
                            }
                        }
                    }
                });

                if (shouldReinit) {
                    console.log('🔄 DOM cambió, reinicializando...');
                    setTimeout(() => {
                        window.ClinicaCAISystem.init();
                        setTimeout(() => window.ClinicaCAISystem.verificarEstadoCAI(), 100);
                    }, 50);
                }
            });

            observer.observe(document.body, { childList: true, subtree: true });

            console.log('✅ Sistema CAI cargado');
        })();
    </script>

    {{-- Script adicional para forzar estado inicial (fallback rápido) --}}
    <script>
        (function() {
            const consultaId = '{{ $this->record->id }}';
            const caiKey = `cai_toggle_state_${consultaId}`;

            const estadoGuardado = sessionStorage.getItem(caiKey) || localStorage.getItem(caiKey);
            if (estadoGuardado === 'true') {
                setTimeout(() => {
                    const section = document.getElementById('cai-info-section');
                    const toggle = document.getElementById('emitir_cai_toggle');

                    if (section) {
                        section.style.display = 'block';
                        console.log('🚀 CAI forzado a mostrar');
                    }
                    if (toggle) {
                        toggle.checked = true;
                    }
                }, 10);
            }
        })();
    </script>
</x-filament-panels::page>
