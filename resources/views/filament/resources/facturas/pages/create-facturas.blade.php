<x-filament-panels::page>
    <div x-data="formPersistence()" x-init="initPersistence()">
        {{-- El contenido del formulario se renderiza normalmente aquí --}}
    </div>

    {{-- Sistema de persistencia profesional --}}
    <script>
        function formPersistence() {
            return {
                consultaId: '{{ request()->get("consulta_id") }}',
                storageKey: '',
                sessionKey: '',
                debounceTimer: null,
                isInitialized: false,
                
                initPersistence() {
                    if (!this.consultaId) {
                        console.warn('⚠️ No hay consultaId, persistencia deshabilitada');
                        return;
                    }
                    
                    this.storageKey = `factura_pagos_v2_${this.consultaId}`;
                    this.sessionKey = `session_factura_pagos_v2_${this.consultaId}`;
                    
                    console.log('🚀 Iniciando sistema de persistencia profesional');
                    
                    // Configurar eventos de guardado
                    this.setupAutoSave();
                    
                    // Restaurar datos con múltiples intentos
                    this.scheduleRestore();
                    
                    // Manejar eventos de navegación
                    this.setupNavigationHandlers();
                    
                    this.isInitialized = true;
                },
                
                setupAutoSave() {
                    // Eventos de guardado inmediato
                    const eventos = ['input', 'change', 'blur'];
                    eventos.forEach(evento => {
                        document.addEventListener(evento, (e) => {
                            if (this.isPaymentField(e.target)) {
                                this.debouncedSave();
                            }
                        });
                    });
                    
                    // Guardado periódico como respaldo
                    setInterval(() => {
                        this.saveFormState();
                    }, 2000);
                },
                
                setupNavigationHandlers() {
                    // Guardar antes de salir
                    window.addEventListener('beforeunload', () => {
                        this.saveFormState();
                    });
                    
                    // Manejar cambios de visibilidad
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden) {
                            this.saveFormState();
                        } else {
                            setTimeout(() => this.restoreFormState(), 100);
                        }
                    });
                    
                    // Manejar navegación del historial
                    window.addEventListener('pageshow', (event) => {
                        if (event.persisted) {
                            setTimeout(() => this.restoreFormState(), 150);
                        }
                    });
                },
                
                scheduleRestore() {
                    // Múltiples intentos de restauración
                    const delays = [100, 500, 1000, 2000, 5000];
                    delays.forEach(delay => {
                        setTimeout(() => this.restoreFormState(), delay);
                    });
                    
                    // Restauración periódica
                    setInterval(() => {
                        this.restoreFormState();
                    }, 3000);
                },
                
                isPaymentField(element) {
                    if (!element) return false;
                    
                    const name = element.name?.toLowerCase() || '';
                    const parentText = element.closest('div')?.textContent?.toLowerCase() || '';
                    const labelText = element.closest('[data-field-wrapper]')?.textContent?.toLowerCase() || '';
                    
                    return name.includes('tipo_pago') || 
                           name.includes('monto_recibido') ||
                           (parentText.includes('tipo') && parentText.includes('pago')) ||
                           (parentText.includes('monto') && parentText.includes('recibido')) ||
                           (labelText.includes('tipo') && labelText.includes('pago')) ||
                           (labelText.includes('monto') && labelText.includes('recibido'));
                },
                
                debouncedSave() {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.saveFormState();
                    }, 300);
                },
                
                saveFormState() {
                    if (!this.consultaId) return;
                    
                    try {
                        const formData = this.extractFormData();
                        
                        if (formData.metodos && formData.metodos.length > 0) {
                            // Guardar en ambos storages
                            localStorage.setItem(this.storageKey, JSON.stringify(formData));
                            sessionStorage.setItem(this.sessionKey, JSON.stringify(formData));
                            
                            console.log('💾 Estado guardado:', formData.metodos.length, 'métodos');
                        }
                    } catch (error) {
                        console.error('❌ Error al guardar estado:', error);
                    }
                },
                
                extractFormData() {
                    const metodos = [];
                    
                    // Buscar campos de pago en el DOM
                    const repeaterItems = document.querySelectorAll('[data-repeater-item]') || 
                                         document.querySelectorAll('[data-sortable-item]') ||
                                         document.querySelectorAll('fieldset') ||
                                         document.querySelectorAll('.fi-fo-repeater-item');
                    
                    if (repeaterItems.length === 0) {
                        // Fallback: buscar por estructura conocida
                        const allSelects = document.querySelectorAll('select');
                        const allInputs = document.querySelectorAll('input[type="number"], input[type="text"]');
                        
                        const tipoSelects = Array.from(allSelects).filter(s => this.isPaymentField(s));
                        const montoInputs = Array.from(allInputs).filter(i => this.isPaymentField(i));
                        
                        const maxItems = Math.max(tipoSelects.length, montoInputs.length);
                        
                        for (let i = 0; i < maxItems; i++) {
                            const tipo = tipoSelects[i]?.value || '';
                            const monto = montoInputs[i]?.value || '';
                            
                            if (tipo || monto) {
                                metodos.push({
                                    tipo: tipo,
                                    monto_recibido: monto,
                                    index: i
                                });
                            }
                        }
                    } else {
                        // Buscar dentro de cada item del repeater
                        repeaterItems.forEach((item, index) => {
                            const tipoSelect = item.querySelector('select') || 
                                             item.querySelector('[name*="tipo_pago"]');
                            const montoInput = item.querySelector('input[type="number"]') || 
                                             item.querySelector('[name*="monto_recibido"]');
                            
                            const tipo = tipoSelect?.value || '';
                            const monto = montoInput?.value || '';
                            
                            if (tipo || monto) {
                                metodos.push({
                                    tipo: tipo,
                                    monto_recibido: monto,
                                    index: index
                                });
                            }
                        });
                    }
                    
                    return { metodos, timestamp: Date.now() };
                },
                
                restoreFormState() {
                    if (!this.consultaId) return;
                    
                    try {
                        // Priorizar sessionStorage, luego localStorage
                        let savedData = sessionStorage.getItem(this.sessionKey) || 
                                       localStorage.getItem(this.storageKey);
                        
                        if (!savedData) return;
                        
                        const formData = JSON.parse(savedData);
                        
                        if (!formData.metodos || formData.metodos.length === 0) return;
                        
                        console.log('🔄 Restaurando estado:', formData.metodos.length, 'métodos');
                        
                        // Restaurar con múltiples estrategias
                        this.restoreUsingRepeaterStrategy(formData.metodos) ||
                        this.restoreUsingFallbackStrategy(formData.metodos);
                        
                    } catch (error) {
                        console.error('❌ Error al restaurar estado:', error);
                    }
                },
                
                restoreUsingRepeaterStrategy(metodos) {
                    const repeaterItems = document.querySelectorAll('[data-repeater-item]') || 
                                         document.querySelectorAll('[data-sortable-item]') ||
                                         document.querySelectorAll('fieldset') ||
                                         document.querySelectorAll('.fi-fo-repeater-item');
                    
                    if (repeaterItems.length === 0) return false;
                    
                    let restored = 0;
                    
                    metodos.forEach((metodo, index) => {
                        const item = repeaterItems[index];
                        if (!item) return;
                        
                        const tipoSelect = item.querySelector('select') || 
                                         item.querySelector('[name*="tipo_pago"]');
                        const montoInput = item.querySelector('input[type="number"]') || 
                                         item.querySelector('[name*="monto_recibido"]');
                        
                        if (tipoSelect && metodo.tipo && tipoSelect.value !== metodo.tipo) {
                            tipoSelect.value = metodo.tipo;
                            this.triggerEvents(tipoSelect);
                            restored++;
                        }
                        
                        if (montoInput && metodo.monto_recibido && montoInput.value !== metodo.monto_recibido) {
                            montoInput.value = metodo.monto_recibido;
                            this.triggerEvents(montoInput);
                            restored++;
                        }
                    });
                    
                    if (restored > 0) {
                        console.log('✅ Restaurados', restored, 'campos via repeater');
                    }
                    
                    return restored > 0;
                },
                
                restoreUsingFallbackStrategy(metodos) {
                    const allSelects = document.querySelectorAll('select');
                    const allInputs = document.querySelectorAll('input[type="number"], input[type="text"]');
                    
                    const tipoSelects = Array.from(allSelects).filter(s => this.isPaymentField(s));
                    const montoInputs = Array.from(allInputs).filter(i => this.isPaymentField(i));
                    
                    let restored = 0;
                    
                    metodos.forEach((metodo, index) => {
                        const tipoSelect = tipoSelects[index];
                        const montoInput = montoInputs[index];
                        
                        if (tipoSelect && metodo.tipo && tipoSelect.value !== metodo.tipo) {
                            tipoSelect.value = metodo.tipo;
                            this.triggerEvents(tipoSelect);
                            restored++;
                        }
                        
                        if (montoInput && metodo.monto_recibido && montoInput.value !== metodo.monto_recibido) {
                            montoInput.value = metodo.monto_recibido;
                            this.triggerEvents(montoInput);
                            restored++;
                        }
                    });
                    
                    if (restored > 0) {
                        console.log('✅ Restaurados', restored, 'campos via fallback');
                    }
                    
                    return restored > 0;
                },
                
                triggerEvents(element) {
                    const events = ['input', 'change', 'blur', 'keyup'];
                    events.forEach(eventType => {
                        element.dispatchEvent(new Event(eventType, { bubbles: true }));
                    });
                    
                    // Evento específico para Livewire/Alpine
                    element.dispatchEvent(new CustomEvent('livewire:changed', {
                        detail: { value: element.value },
                        bubbles: true
                    }));
                },
                
                clearStoredData() {
                    localStorage.removeItem(this.storageKey);
                    sessionStorage.removeItem(this.sessionKey);
                    console.log('🗑️ Datos de borrador eliminados');
                }
            }
        }
    </script>
</x-filament-panels::page>
            
            guardarDatos() {
                if (!this.consultaId) return;
                
                const datos = [];
                
                // Buscar TODOS los campos posibles de múltiples maneras con mejor detección
                const selects = document.querySelectorAll('select');
                const inputs = document.querySelectorAll('input[type="number"], input[type="text"]');
                
                // Mejorar detección de selects de tipo de pago
                selects.forEach((select, index) => {
                    const texto = select.parentElement?.textContent?.toLowerCase() || '';
                    const label = select.closest('div')?.querySelector('label')?.textContent?.toLowerCase() || '';
                    const name = select.name?.toLowerCase() || '';
                    
                    if (texto.includes('tipo') && texto.includes('pago') || 
                        label.includes('tipo') && label.includes('pago') ||
                        name.includes('tipo_pago') ||
                        select.closest('[data-field-wrapper]')?.textContent?.toLowerCase().includes('tipo')) {
                        
                        datos.push({
                            tipo: 'tipo_pago',
                            index: index,
                            valor: select.value,
                            selector: this.getSelector(select),
                            name: select.name,
                            timestamp: Date.now()
                        });
                    }
                });
                
                // Mejorar detección de inputs de monto
                inputs.forEach((input, index) => {
                    const texto = input.parentElement?.textContent?.toLowerCase() || '';
                    const label = input.closest('div')?.querySelector('label')?.textContent?.toLowerCase() || '';
                    const name = input.name?.toLowerCase() || '';
                    
                    if (texto.includes('monto') && texto.includes('recibido') || 
                        label.includes('monto') && label.includes('recibido') ||
                        name.includes('monto_recibido') ||
                        input.closest('[data-field-wrapper]')?.textContent?.toLowerCase().includes('monto')) {
                        
                        datos.push({
                            tipo: 'monto_recibido',
                            index: index,
                            valor: input.value,
                            selector: this.getSelector(input),
                            name: input.name,
                            timestamp: Date.now()
                        });
                    }
                });
                
                // Guardar tanto en localStorage como en sessionStorage para mayor persistencia
                const key = 'factura_pagos_' + this.consultaId;
                const keySession = 'session_factura_pagos_' + this.consultaId;
                
                localStorage.setItem(key, JSON.stringify(datos));
                sessionStorage.setItem(keySession, JSON.stringify(datos));
                
                if (datos.length > 0) {
                    console.log('💾 Datos guardados:', datos.length, 'campos');
                }
            },
            
            restaurarDatos() {
                if (!this.consultaId) return;
                
                const key = 'factura_pagos_' + this.consultaId;
                const keySession = 'session_factura_pagos_' + this.consultaId;
                
                // Intentar obtener datos de sessionStorage primero, luego localStorage
                let datosGuardados = sessionStorage.getItem(keySession) || localStorage.getItem(key);
                
                if (datosGuardados) {
                    try {
                        const datos = JSON.parse(datosGuardados);
                        console.log('🔄 Restaurando datos:', datos.length, 'elementos');
                        
                        let restaurados = 0;
                        
                        datos.forEach(dato => {
                            // Buscar elemento de múltiples maneras
                            let elemento = null;
                            
                            // Método 1: Por selector específico
                            if (dato.selector) {
                                elemento = document.querySelector(dato.selector);
                            }
                            
                            // Método 2: Por nombre del campo
                            if (!elemento && dato.name) {
                                elemento = document.querySelector(`[name="${dato.name}"]`);
                            }
                            
                            // Método 3: Búsqueda inteligente por tipo e índice
                            if (!elemento) {
                                if (dato.tipo === 'tipo_pago') {
                                    const selects = document.querySelectorAll('select');
                                    const selectsPago = Array.from(selects).filter(select => {
                                        const texto = select.parentElement?.textContent?.toLowerCase() || '';
                                        const label = select.closest('div')?.querySelector('label')?.textContent?.toLowerCase() || '';
                                        const name = select.name?.toLowerCase() || '';
                                        return texto.includes('tipo') && texto.includes('pago') || 
                                               label.includes('tipo') && label.includes('pago') ||
                                               name.includes('tipo_pago') ||
                                               select.closest('[data-field-wrapper]')?.textContent?.toLowerCase().includes('tipo');
                                    });
                                    elemento = selectsPago[dato.index];
                                } else if (dato.tipo === 'monto_recibido') {
                                    const inputs = document.querySelectorAll('input[type="number"], input[type="text"]');
                                    const inputsMonto = Array.from(inputs).filter(input => {
                                        const texto = input.parentElement?.textContent?.toLowerCase() || '';
                                        const label = input.closest('div')?.querySelector('label')?.textContent?.toLowerCase() || '';
                                        const name = input.name?.toLowerCase() || '';
                                        return texto.includes('monto') && texto.includes('recibido') || 
                                               label.includes('monto') && label.includes('recibido') ||
                                               name.includes('monto_recibido') ||
                                               input.closest('[data-field-wrapper]')?.textContent?.toLowerCase().includes('monto');
                                    });
                                    elemento = inputsMonto[dato.index];
                                }
                            }
                            
                            // Restaurar valor si se encontró el elemento y es diferente
                            if (elemento && elemento.value !== dato.valor) {
                                elemento.value = dato.valor;
                                
                                // Disparar múltiples eventos para asegurar que Filament detecte el cambio
                                const eventos = ['input', 'change', 'blur', 'keyup'];
                                eventos.forEach(evento => {
                                    elemento.dispatchEvent(new Event(evento, { bubbles: true }));
                                });
                                
                                // Evento personalizado para Alpine.js/Livewire
                                elemento.dispatchEvent(new CustomEvent('restored-value', { 
                                    detail: { valor: dato.valor },
                                    bubbles: true 
                                }));
                                
                                restaurados++;
                                console.log('✅ Restaurado:', dato.tipo, dato.valor);
                            }
                        });
                        
                        if (restaurados > 0) {
                            console.log(`✅ Total restaurados: ${restaurados}/${datos.length} campos`);
                        }
                        
                    } catch (e) {
                        console.error('❌ Error al restaurar:', e);
                    }
                }
            },
            
            getSelector(elemento) {
                // Generar un selector único y más robusto para el elemento
                if (elemento.id) return '#' + elemento.id;
                if (elemento.name) return '[name="' + elemento.name + '"]';
                
                // Intentar crear un selector usando atributos de Filament
                if (elemento.hasAttribute('wire:model')) {
                    return '[wire\\:model="' + elemento.getAttribute('wire:model') + '"]';
                }
                
                // Selector por posición dentro del repeater
                const repeaterContainer = elemento.closest('[data-field-wrapper]');
                if (repeaterContainer) {
                    const index = Array.from(repeaterContainer.parentElement.children).indexOf(repeaterContainer);
                    const tagName = elemento.tagName.toLowerCase();
                    return `[data-field-wrapper]:nth-child(${index + 1}) ${tagName}`;
                }
                
                // Último recurso: usar la clase
                if (elemento.className) {
                    const firstClass = elemento.className.split(' ')[0];
                    return '.' + firstClass;
                }
                
                return null;
            }
        }" 
         x-init="init()">
        
        {{-- El contenido del formulario se renderiza normalmente aquí --}}
        
    </div>

    {{-- Script adicional para persistencia mejorada --}}
    <script>
        // Sistema de persistencia mejorado adicional
        document.addEventListener('DOMContentLoaded', function() {
            const consultaId = '{{ request()->get("consulta_id") }}';
            
            console.log('📄 DOM cargado con persistencia mejorada, consultaId:', consultaId);
            
            if (consultaId) {
                let lastSaveTime = 0;
                
                // Función mejorada para guardar datos
                function guardarDatosMejorado() {
                    const now = Date.now();
                    if (now - lastSaveTime < 200) return; // Throttle
                    lastSaveTime = now;
                    
                    const datos = [];
                    
                    // Buscar todos los selects y inputs con detección mejorada
                    document.querySelectorAll('select, input').forEach((elemento, index) => {
                        const texto = elemento.parentElement?.textContent?.toLowerCase() || '';
                        const label = elemento.closest('div')?.querySelector('label')?.textContent?.toLowerCase() || '';
                        const name = elemento.name?.toLowerCase() || '';
                        
                        // Identificar campos de pago con mejor precisión
                        const esTipoPago = (texto.includes('tipo') && texto.includes('pago')) || 
                                          (label.includes('tipo') && label.includes('pago')) ||
                                          name.includes('tipo_pago');
                                          
                        const esMonto = (texto.includes('monto') && texto.includes('recibido')) ||
                                       (label.includes('monto') && label.includes('recibido')) ||
                                       name.includes('monto_recibido');
                        
                        if ((esTipoPago || esMonto) && elemento.value && elemento.value.trim() !== '') {
                            datos.push({
                                tag: elemento.tagName,
                                index: index,
                                value: elemento.value,
                                name: elemento.name || '',
                                id: elemento.id || '',
                                className: elemento.className || '',
                                textContent: texto.substring(0, 50),
                                wireModel: elemento.getAttribute('wire:model') || '',
                                tipo: esTipoPago ? 'tipo_pago' : 'monto_recibido',
                                timestamp: now
                            });
                        }
                    });
                    
                    if (datos.length > 0) {
                        const keyLocal = 'pagos_mejorado_' + consultaId;
                        const keySession = 'session_pagos_mejorado_' + consultaId;
                        
                        localStorage.setItem(keyLocal, JSON.stringify(datos));
                        sessionStorage.setItem(keySession, JSON.stringify(datos));
                        console.log('💾 Guardado mejorado:', datos.length, 'elementos');
                    }
                }
                
                // Función mejorada para restaurar datos
                function restaurarDatosMejorado() {
                    const keyLocal = 'pagos_mejorado_' + consultaId;
                    const keySession = 'session_pagos_mejorado_' + consultaId;
                    
                    // Priorizar sessionStorage
                    let datosGuardados = sessionStorage.getItem(keySession) || localStorage.getItem(keyLocal);
                    
                    if (datosGuardados) {
                        try {
                            const datos = JSON.parse(datosGuardados);
                            console.log('🔄 Restaurando mejorado:', datos.length, 'elementos');
                            
                            let restaurados = 0;
                            
                            datos.forEach(dato => {
                                let elemento = null;
                                
                                // Múltiples estrategias de búsqueda
                                if (dato.wireModel) {
                                    elemento = document.querySelector(`[wire\\:model="${dato.wireModel}"]`);
                                }
                                if (!elemento && dato.name) {
                                    elemento = document.querySelector(`[name="${dato.name}"]`);
                                }
                                if (!elemento && dato.id) {
                                    elemento = document.getElementById(dato.id);
                                }
                                if (!elemento) {
                                    // Búsqueda por tipo y contexto
                                    const elementos = document.querySelectorAll(dato.tag);
                                    const filtrados = Array.from(elementos).filter(el => {
                                        const textoEl = el.parentElement?.textContent?.toLowerCase() || '';
                                        const nameEl = el.name?.toLowerCase() || '';
                                        
                                        if (dato.tipo === 'tipo_pago') {
                                            return textoEl.includes('tipo') && textoEl.includes('pago') || 
                                                   nameEl.includes('tipo_pago');
                                        } else {
                                            return textoEl.includes('monto') && textoEl.includes('recibido') ||
                                                   nameEl.includes('monto_recibido');
                                        }
                                    });
                                    
                                    elemento = filtrados[dato.index] || elementos[dato.index];
                                }
                                
                                if (elemento && elemento.value !== dato.value) {
                                    elemento.value = dato.value;
                                    
                                    // Disparar eventos múltiples
                                    ['input', 'change', 'blur', 'keyup', 'keydown'].forEach(evento => {
                                        elemento.dispatchEvent(new Event(evento, { bubbles: true }));
                                    });
                                    
                                    // Evento Livewire específico
                                    if (elemento.hasAttribute('wire:model')) {
                                        elemento.dispatchEvent(new CustomEvent('livewire:changed', {
                                            detail: { value: dato.value },
                                            bubbles: true
                                        }));
                                    }
                                    
                                    restaurados++;
                                    console.log('✅ Restaurado mejorado:', dato.tipo, dato.value);
                                }
                            });
                            
                            if (restaurados > 0) {
                                console.log(`✅ Total mejorado restaurados: ${restaurados}/${datos.length} campos`);
                            }
                            
                        } catch (e) {
                            console.error('❌ Error restauración mejorada:', e);
                        }
                    }
                }
                
                // Configurar guardado automático más agresivo
                setInterval(guardarDatosMejorado, 300); // Cada 300ms
                
                // Configurar restauración múltiple
                setTimeout(restaurarDatosMejorado, 100);
                setTimeout(restaurarDatosMejorado, 500);
                setTimeout(restaurarDatosMejorado, 1500);
                setTimeout(restaurarDatosMejorado, 3000);
                
                // Repetir restauración cada 2 segundos
                setInterval(restaurarDatosMejorado, 2000);
                
                // Eventos de guardado
                ['change', 'input', 'blur'].forEach(evento => {
                    document.addEventListener(evento, function(e) {
                        if (e.target.matches('select, input')) {
                            setTimeout(guardarDatosMejorado, 50);
                        }
                    });
                });
                
                // Guardar al salir
                window.addEventListener('beforeunload', guardarDatosMejorado);
                window.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        guardarDatosMejorado();
                    } else {
                        setTimeout(restaurarDatosMejorado, 100);
                    }
                });
                
                // Configurar eventos en campos cuando aparezcan
                function configurarEventosNuevos() {
                    document.querySelectorAll('select, input').forEach(elemento => {
                        if (!elemento.hasAttribute('data-persistence-mejorada')) {
                            ['change', 'input', 'blur'].forEach(evento => {
                                elemento.addEventListener(evento, guardarDatosMejorado);
                            });
                            elemento.setAttribute('data-persistence-mejorada', 'true');
                        }
                    });
                }
                
                configurarEventosNuevos();
                setInterval(configurarEventosNuevos, 1000); // Revisar cada segundo
            }
        });
    </script>
                    
                    logDebugGlobal(`🔄 Elementos totales: ${allInputs.length} inputs, ${allSelects.length} selects`);
                    
                    // Filtrar campos de monto
                    const montoInputs = Array.from(allInputs).filter(input => {
                        const name = input.name || '';
                        const parentText = input.parentElement?.textContent || '';
                        const labelText = input.closest('div')?.querySelector('label')?.textContent || '';
                        
                        return name.includes('monto_recibido') || 
                               parentText.toLowerCase().includes('monto') && parentText.toLowerCase().includes('recibido') ||
                               labelText.toLowerCase().includes('monto') && labelText.toLowerCase().includes('recibido');
                    });
                    
                    // Filtrar campos de tipo de pago
                    const tipoSelects = Array.from(allSelects).filter(select => {
                        const name = select.name || '';
                        const parentText = select.parentElement?.textContent || '';
                        const labelText = select.closest('div')?.querySelector('label')?.textContent || '';
                        
                        return name.includes('tipo_pago_id') ||
                               parentText.toLowerCase().includes('tipo') && parentText.toLowerCase().includes('pago') ||
                               labelText.toLowerCase().includes('tipo') && labelText.toLowerCase().includes('pago');
                    });
                    
                    logDebugGlobal(`🔄 Campos filtrados: ${montoInputs.length} inputs monto, ${tipoSelects.length} selects tipo`);
                    
                    if (montoInputs.length > 0 || tipoSelects.length > 0) {
                        pagos.forEach((pago, index) => {
                            if (tipoSelects[index] && pago.tipo_pago_id) {
                                tipoSelects[index].value = pago.tipo_pago_id;
                                
                                // Disparar eventos de cambio
                                tipoSelects[index].dispatchEvent(new Event('change', { bubbles: true }));
                                tipoSelects[index].dispatchEvent(new Event('input', { bubbles: true }));
                                
                                logDebugGlobal(`🔄 Alpine restauró tipo ${index + 1}:`, pago.tipo_pago_id);
                            }
                            
                            if (montoInputs[index] && pago.monto_recibido) {
                                montoInputs[index].value = pago.monto_recibido;
                                
                                // Disparar eventos de cambio
                                montoInputs[index].dispatchEvent(new Event('input', { bubbles: true }));
                                montoInputs[index].dispatchEvent(new Event('change', { bubbles: true }));
                                montoInputs[index].dispatchEvent(new Event('blur', { bubbles: true }));
                                
                                logDebugGlobal(`🔄 Alpine restauró monto ${index + 1}:`, pago.monto_recibido);
                            }
                        });
                        
                        logDebugGlobal('🔄 ✅ RestaurarPagosAlpine completado');
                        
                        // Configurar eventos después de la restauración
                        setTimeout(configurarEventosPagos, 300);
                    } else {
                        logDebugGlobal('🔄 ❌ No se encontraron campos para restaurar');
                    }
                    
                } catch (e) {
                    logDebugGlobal('🔄 ❌ Error en RestaurarPagosAlpine:', e);
                }
            } else {
                logDebugGlobal('🔄 ℹ️ No hay datos para RestaurarPagosAlpine');
            }
        }
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const consultaId = '{{ request()->get("consulta_id") }}';
            
            if (consultaId) {
                // Aplicar descuento desde sessionStorage al cargar
                aplicarDescuentoGuardado();
                
                // Restaurar valores de pagos
                restaurarValoresPagos();
                
                // Intentar restaurar múltiples veces para asegurar éxito
                setTimeout(restaurarValoresPagos, 1000);
                setTimeout(restaurarValoresPagos, 2000);
                setTimeout(restaurarValoresPagos, 5000);
                
                // Guardar valores de pagos automáticamente
                configurarAutoGuardadoPagos();
                
                // Recalcular totales cuando la ventana recupera el foco
                window.addEventListener('focus', recalcularTotales);
                
                // También ejecutar cuando la página se muestra (navegación hacia atrás/adelante)
                window.addEventListener('pageshow', function(event) {
                    if (event.persisted) {
                        // La página fue restaurada desde caché
                        setTimeout(() => {
                            restaurarValoresPagos();
                            aplicarDescuentoGuardado();
                        }, 100);
                    }
                });
                
                // Escuchar cambios en sessionStorage
                window.addEventListener('storage', function(e) {
                    if (e.key && e.key.includes('selected_descuento_' + consultaId)) {
                        aplicarDescuentoGuardado();
                    }
                });
            }
            
            function aplicarDescuentoGuardado() {
                const descuentoKey = 'selected_descuento_' + consultaId;
                const savedDescuento = sessionStorage.getItem(descuentoKey);
                
                if (savedDescuento) {
                    try {
                        const descuento = JSON.parse(savedDescuento);
                        if (descuento.monto_calculado) {
                            // Usar Livewire Alpine para actualizar datos
                            if (window.Livewire) {
                                const component = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                                if (component) {
                                    component.set('data.descuento_total', descuento.monto_calculado);
                                    // Recalcular total
                                    const subtotal = parseFloat(component.get('data.subtotal') || 0);
                                    const impuesto = parseFloat(component.get('data.impuesto_total') || 0);
                                    const nuevoTotal = subtotal + impuesto - descuento.monto_calculado;
                                    component.set('data.total', nuevoTotal);
                                    component.set('data.saldo_pendiente', nuevoTotal);
                                }
                            }
                        }
                    } catch (e) {
                        // Error silencioso
                    }
                }
            }
            
            function recalcularTotales() {
                if (window.Livewire) {
                    const component = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                    if (component) {
                        component.call('recalcularTotales').then(() => {
                            // Después de recalcular, aplicar descuento
                            setTimeout(aplicarDescuentoGuardado, 100);
                        });
                    }
                }
            }
            
            // Función para restaurar valores de pagos desde sessionStorage
            function restaurarValoresPagos() {
                const consultaId = '{{ request()->get("consulta_id") }}';
                const pagoKey = 'pagos_factura_' + consultaId;
                const savedPagos = sessionStorage.getItem(pagoKey);
                
                logDebugGlobal('🔄 Iniciando restauración de valores', { key: pagoKey, hasData: !!savedPagos });
                
                if (savedPagos) {
                    try {
                        const pagos = JSON.parse(savedPagos);
                        logDebugGlobal('🔄 Datos encontrados en sessionStorage', pagos);
                        
                        // Intentar varias veces hasta que los campos estén disponibles
                        let intentos = 0;
                        const maxIntentos = 15;
                        
                        function intentarRestaurar() {
                            intentos++;
                            logDebugGlobal(`🔄 Intento de restauración ${intentos}/${maxIntentos}`);
                            
                            // Buscar todos los campos de tipo de pago
                            const tipoSelects = document.querySelectorAll('select[name*="tipo_pago_id"]');
                            const montoInputs = document.querySelectorAll('input[name*="monto_recibido"]');
                            
                            logDebugGlobal(`🔄 Campos encontrados: ${tipoSelects.length} selects y ${montoInputs.length} inputs`);
                            
                            // Búsqueda alternativa
                            let extraTipoSelects = [];
                            let extraMontoInputs = [];
                            
                            if (tipoSelects.length === 0) {
                                const allSelects = document.querySelectorAll('select');
                                extraTipoSelects = Array.from(allSelects).filter(select => {
                                    const parentText = select.parentElement.textContent || '';
                                    const labelText = select.closest('div').querySelector('label')?.textContent || '';
                                    return parentText.toLowerCase().includes('tipo') && parentText.toLowerCase().includes('pago') ||
                                           labelText.toLowerCase().includes('tipo') && labelText.toLowerCase().includes('pago');
                                });
                                logDebugGlobal(`🔄 Selects por contenido: ${extraTipoSelects.length}`);
                            }
                            
                            if (montoInputs.length === 0) {
                                const allInputs = document.querySelectorAll('input[type="number"], input[type="text"]');
                                extraMontoInputs = Array.from(allInputs).filter(input => {
                                    const parentText = input.parentElement.textContent || '';
                                    const labelText = input.closest('div').querySelector('label')?.textContent || '';
                                    return parentText.toLowerCase().includes('monto') && parentText.toLowerCase().includes('recibido') ||
                                           labelText.toLowerCase().includes('monto') && labelText.toLowerCase().includes('recibido');
                                });
                                logDebugGlobal(`🔄 Inputs por contenido: ${extraMontoInputs.length}`);
                            }
                            
                            const todosLosSelects = [...tipoSelects, ...extraTipoSelects];
                            const todosLosInputs = [...montoInputs, ...extraMontoInputs];
                            
                            if (todosLosSelects.length > 0 && todosLosInputs.length > 0) {
                                logDebugGlobal('🔄 ✅ Campos encontrados, restaurando valores...');
                                
                                // Restaurar valores
                                pagos.forEach((pago, index) => {
                                    if (todosLosSelects[index] && pago.tipo_pago_id) {
                                        todosLosSelects[index].value = pago.tipo_pago_id;
                                        todosLosSelects[index].dispatchEvent(new Event('change', { bubbles: true }));
                                        logDebugGlobal(`🔄 Restaurado select ${index + 1}:`, { value: pago.tipo_pago_id });
                                    }
                                    
                                    if (todosLosInputs[index] && pago.monto_recibido) {
                                        todosLosInputs[index].value = pago.monto_recibido;
                                        todosLosInputs[index].dispatchEvent(new Event('input', { bubbles: true }));
                                        todosLosInputs[index].dispatchEvent(new Event('blur', { bubbles: true }));
                                        logDebugGlobal(`🔄 Restaurado input ${index + 1}:`, { value: pago.monto_recibido });
                                    }
                                });
                                
                                logDebugGlobal('🔄 ✅ Restauración completada exitosamente');
                                
                                // Configurar eventos después de restaurar
                                setTimeout(configurarEventosPagos, 200);
                                return; // Éxito, salir
                            }
                            
                            // Si no encontró campos y no hemos alcanzado el máximo, intentar de nuevo
                            if (intentos < maxIntentos) {
                                setTimeout(intentarRestaurar, 500);
                            } else {
                                logDebugGlobal(`🔄 ❌ No se pudieron encontrar los campos de pago después de ${maxIntentos} intentos`);
                            }
                        }
                        
                        // Empezar a intentar inmediatamente
                        intentarRestaurar();
                        
                    } catch (e) {
                        logDebugGlobal('🔄 ❌ Error al restaurar valores de pagos:', e);
                    }
                } else {
                    logDebugGlobal('🔄 ℹ️ No hay datos guardados para restaurar');
                }
            }
            
            // Función para configurar el auto-guardado de valores de pagos
            function configurarAutoGuardadoPagos() {
                // Observar cambios en los campos de pagos
                const observer = new MutationObserver(() => {
                    configurarEventosPagos();
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
                
                // También configurar para los elementos existentes
                setTimeout(configurarEventosPagos, 1000);
                setTimeout(configurarEventosPagos, 3000); // Intentar de nuevo después de 3 segundos
            }
            
            function logDebugGlobal(mensaje, data = null) {
                const timestamp = new Date().toLocaleTimeString();
                console.log(`🔍 [${timestamp}] ${mensaje}`, data || '');
                
                const debugContent = document.getElementById('debug-content');
                if (debugContent) {
                    const logDiv = document.createElement('div');
                    logDiv.style.cssText = 'margin-bottom: 5px; padding: 3px; border-left: 2px solid #00ff00; padding-left: 5px;';
                    logDiv.innerHTML = `<span style="color: #ffff00;">${timestamp}</span> ${mensaje}`;
                    if (data) {
                        logDiv.innerHTML += `<br><pre style="color: #ff9900; font-size: 10px; margin: 2px 0; white-space: pre-wrap;">${JSON.stringify(data, null, 2)}</pre>`;
                    }
                    debugContent.appendChild(logDiv);
                    debugContent.scrollTop = debugContent.scrollHeight;
                    
                    while (debugContent.children.length > 25) {
                        debugContent.removeChild(debugContent.firstChild);
                    }
                }
            }
            
            function configurarEventosPagos() {
                logDebugGlobal('🔧 Configurando eventos de pagos...');
                
                const tipoSelects = document.querySelectorAll('select[name*="tipo_pago_id"]');
                const montoInputs = document.querySelectorAll('input[name*="monto_recibido"]');
                
                logDebugGlobal(`📋 Campos encontrados por name: ${tipoSelects.length} selects y ${montoInputs.length} inputs`);
                
                // También buscar por otros atributos de Filament
                const filamentTipoSelects = document.querySelectorAll('select[x-data*="repeater"], select[wire\\:model*="tipo_pago"]');
                const filamentMontoInputs = document.querySelectorAll('input[x-data*="repeater"], input[wire\\:model*="monto_recibido"]');
                
                logDebugGlobal(`📋 Campos de Filament encontrados: ${filamentTipoSelects.length} selects y ${filamentMontoInputs.length} inputs`);
                
                // También buscar por contenido si no encuentra por name
                let extraTipoSelects = [];
                let extraMontoInputs = [];
                
                if (tipoSelects.length === 0 && filamentTipoSelects.length === 0) {
                    const allSelects = document.querySelectorAll('select');
                    extraTipoSelects = Array.from(allSelects).filter(select => {
                        const parentText = select.parentElement.textContent || '';
                        const labelText = select.closest('div').querySelector('label')?.textContent || '';
                        return parentText.toLowerCase().includes('tipo') && parentText.toLowerCase().includes('pago') ||
                               labelText.toLowerCase().includes('tipo') && labelText.toLowerCase().includes('pago');
                    });
                    logDebugGlobal(`📋 Campos por contenido (tipo): ${extraTipoSelects.length} selects`);
                }
                
                if (montoInputs.length === 0 && filamentMontoInputs.length === 0) {
                    const allInputs = document.querySelectorAll('input[type="number"], input[type="text"]');
                    extraMontoInputs = Array.from(allInputs).filter(input => {
                        const parentText = input.parentElement.textContent || '';
                        const labelText = input.closest('div').querySelector('label')?.textContent || '';
                        return parentText.toLowerCase().includes('monto') && parentText.toLowerCase().includes('recibido') ||
                               labelText.toLowerCase().includes('monto') && labelText.toLowerCase().includes('recibido');
                    });
                    logDebugGlobal(`📋 Campos por contenido (monto): ${extraMontoInputs.length} inputs`);
                }
                
                // Combinar todos los elementos encontrados
                const todosLosSelects = [...tipoSelects, ...filamentTipoSelects, ...extraTipoSelects];
                const todosLosInputs = [...montoInputs, ...filamentMontoInputs, ...extraMontoInputs];
                
                logDebugGlobal(`📋 Total de campos a configurar: ${todosLosSelects.length} selects y ${todosLosInputs.length} inputs`);
                
                // Configurar eventos para todos los selects encontrados
                todosLosSelects.forEach((select, index) => {
                    if (!select.hasAttribute('data-persistence-configured')) {
                        logDebugGlobal(`🔧 Configurando select ${index + 1}`, { element: select.outerHTML.substring(0, 100) });
                        // Remover listeners anteriores si existen
                        select.removeEventListener('change', guardarValoresPagos);
                        select.addEventListener('change', () => {
                            logDebugGlobal('🎯 Cambio en tipo de pago detectado', { value: select.value });
                            guardarValoresPagos();
                        });
                        select.setAttribute('data-persistence-configured', 'true');
                    }
                });
                
                // Configurar eventos para todos los inputs encontrados
                todosLosInputs.forEach((input, index) => {
                    if (!input.hasAttribute('data-persistence-configured')) {
                        logDebugGlobal(`🔧 Configurando input ${index + 1}`, { element: input.outerHTML.substring(0, 100) });
                        // Remover listeners anteriores si existen
                        input.removeEventListener('input', guardarValoresPagos);
                        input.removeEventListener('blur', guardarValoresPagos);
                        input.removeEventListener('keyup', guardarValoresPagos);
                        
                        input.addEventListener('input', () => {
                            logDebugGlobal('🎯 Input en monto recibido detectado', { value: input.value });
                            guardarValoresPagos();
                        });
                        input.addEventListener('blur', () => {
                            logDebugGlobal('🎯 Blur en monto recibido detectado', { value: input.value });
                            guardarValoresPagos();
                        });
                        input.addEventListener('keyup', () => {
                            logDebugGlobal('🎯 Keyup en monto recibido detectado', { value: input.value });
                            guardarValoresPagos();
                        });
                        input.setAttribute('data-persistence-configured', 'true');
                    }
                });
                
                // Guardar valores actuales inmediatamente si encontramos campos
                if (todosLosSelects.length > 0 || todosLosInputs.length > 0) {
                    setTimeout(guardarValoresPagos, 100);
                }
            }
            
            // Función para guardar los valores actuales de pagos
            function guardarValoresPagos() {
                const consultaId = '{{ request()->get("consulta_id") }}';
                const pagoKey = 'pagos_factura_' + consultaId;
                const pagos = [];
                
                logDebugGlobal('💾 Iniciando guardado de valores de pagos');
                
                // Buscar todos los campos de varias maneras
                let tipoSelects = document.querySelectorAll('select[name*="tipo_pago_id"]');
                let montoInputs = document.querySelectorAll('input[name*="monto_recibido"]');
                
                logDebugGlobal(`💾 Encontrados por name: ${tipoSelects.length} selects, ${montoInputs.length} inputs`);
                
                // Búsqueda alternativa si no encuentra campos
                if (tipoSelects.length === 0) {
                    const allSelects = document.querySelectorAll('select');
                    tipoSelects = Array.from(allSelects).filter(select => {
                        const parentText = select.parentElement.textContent || '';
                        const labelText = select.closest('div').querySelector('label')?.textContent || '';
                        return parentText.toLowerCase().includes('tipo') && parentText.toLowerCase().includes('pago') ||
                               labelText.toLowerCase().includes('tipo') && labelText.toLowerCase().includes('pago');
                    });
                    logDebugGlobal(`💾 Encontrados por contenido (tipo): ${tipoSelects.length} selects`);
                }
                
                if (montoInputs.length === 0) {
                    const allInputs = document.querySelectorAll('input[type="number"], input[type="text"]');
                    montoInputs = Array.from(allInputs).filter(input => {
                        const parentText = input.parentElement.textContent || '';
                        const labelText = input.closest('div').querySelector('label')?.textContent || '';
                        return parentText.toLowerCase().includes('monto') && parentText.toLowerCase().includes('recibido') ||
                               labelText.toLowerCase().includes('monto') && labelText.toLowerCase().includes('recibido');
                    });
                    logDebugGlobal(`💾 Encontrados por contenido (monto): ${montoInputs.length} inputs`);
                }
                
                // Recopilar valores usando el índice más alto disponible
                const maxLength = Math.max(tipoSelects.length, montoInputs.length);
                logDebugGlobal(`💾 Procesando ${maxLength} campos`);
                
                for (let i = 0; i < maxLength; i++) {
                    const tipoValue = tipoSelects[i] ? tipoSelects[i].value : '';
                    const montoValue = montoInputs[i] ? montoInputs[i].value : '';
                    
                    const pago = {
                        tipo_pago_id: tipoValue,
                        monto_recibido: montoValue
                    };
                    
                    logDebugGlobal(`💾 Campo ${i + 1}:`, pago);
                    pagos.push(pago);
                }
                
                // Guardar en sessionStorage solo si hay datos válidos
                if (pagos.length > 0 && pagos.some(p => p.tipo_pago_id || p.monto_recibido)) {
                    sessionStorage.setItem(pagoKey, JSON.stringify(pagos));
                    logDebugGlobal('💾 ✅ Valores guardados en sessionStorage', { key: pagoKey, pagos: pagos });
                } else {
                    logDebugGlobal('💾 ❌ No hay datos válidos para guardar');
                }
            }
        });
    </script>
</x-filament-panels::page>
