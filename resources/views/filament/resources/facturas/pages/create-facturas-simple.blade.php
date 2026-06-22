<x-filament-panels::page>
    <div x-data="{ 
            consultaId: '{{ request()->get("consulta_id") }}',
            
            init() {
                // Sistema simple y directo de persistencia
                this.iniciarPersistencia();
            },
            
            iniciarPersistencia() {
                console.log('🚀 Iniciando sistema de persistencia simple');
                
                // Configurar guardado automático cada segundo
                setInterval(() => {
                    this.guardarDatos();
                }, 1000);
                
                // Restaurar datos inmediatamente y luego cada 2 segundos
                setTimeout(() => {
                    this.restaurarDatos();
                }, 500);
                
                setInterval(() => {
                    this.restaurarDatos();
                }, 2000);
                
                // Guardar antes de salir de la página
                window.addEventListener('beforeunload', () => {
                    this.guardarDatos();
                });
            },
            
            guardarDatos() {
                if (!this.consultaId) return;
                
                const datos = [];
                
                // Buscar TODOS los campos posibles de múltiples maneras
                const selects = document.querySelectorAll('select');
                const inputs = document.querySelectorAll('input[type=\"number\"], input[type=\"text\"]');
                
                // Filtrar y guardar selects de tipo de pago
                selects.forEach((select, index) => {
                    const texto = select.parentElement?.textContent?.toLowerCase() || '';
                    const label = select.closest('div')?.querySelector('label')?.textContent?.toLowerCase() || '';
                    
                    if (texto.includes('tipo') && texto.includes('pago') || 
                        label.includes('tipo') && label.includes('pago') ||
                        select.name?.includes('tipo_pago')) {
                        
                        if (select.value) {
                            datos.push({
                                tipo: 'tipo_pago',
                                index: index,
                                valor: select.value,
                                selector: this.getSelector(select)
                            });
                        }
                    }
                });
                
                // Filtrar y guardar inputs de monto
                inputs.forEach((input, index) => {
                    const texto = input.parentElement?.textContent?.toLowerCase() || '';
                    const label = input.closest('div')?.querySelector('label')?.textContent?.toLowerCase() || '';
                    
                    if (texto.includes('monto') && texto.includes('recibido') || 
                        label.includes('monto') && label.includes('recibido') ||
                        input.name?.includes('monto_recibido')) {
                        
                        if (input.value) {
                            datos.push({
                                tipo: 'monto_recibido',
                                index: index,
                                valor: input.value,
                                selector: this.getSelector(input)
                            });
                        }
                    }
                });
                
                if (datos.length > 0) {
                    const key = 'factura_pagos_' + this.consultaId;
                    localStorage.setItem(key, JSON.stringify(datos));
                    console.log('💾 Datos guardados:', datos);
                }
            },
            
            restaurarDatos() {
                if (!this.consultaId) return;
                
                const key = 'factura_pagos_' + this.consultaId;
                const datosGuardados = localStorage.getItem(key);
                
                if (datosGuardados) {
                    try {
                        const datos = JSON.parse(datosGuardados);
                        console.log('🔄 Restaurando datos:', datos);
                        
                        datos.forEach(dato => {
                            // Buscar elemento por selector y por índice
                            let elemento = null;
                            
                            if (dato.selector) {
                                elemento = document.querySelector(dato.selector);
                            }
                            
                            // Si no lo encuentra por selector, buscar por tipo e índice
                            if (!elemento) {
                                if (dato.tipo === 'tipo_pago') {
                                    const selects = document.querySelectorAll('select');
                                    const selectsPago = Array.from(selects).filter(select => {
                                        const texto = select.parentElement?.textContent?.toLowerCase() || '';
                                        const label = select.closest('div')?.querySelector('label')?.textContent?.toLowerCase() || '';
                                        return texto.includes('tipo') && texto.includes('pago') || 
                                               label.includes('tipo') && label.includes('pago') ||
                                               select.name?.includes('tipo_pago');
                                    });
                                    elemento = selectsPago[dato.index] || selects[dato.index];
                                } else if (dato.tipo === 'monto_recibido') {
                                    const inputs = document.querySelectorAll('input[type=\"number\"], input[type=\"text\"]');
                                    const inputsMonto = Array.from(inputs).filter(input => {
                                        const texto = input.parentElement?.textContent?.toLowerCase() || '';
                                        const label = input.closest('div')?.querySelector('label')?.textContent?.toLowerCase() || '';
                                        return texto.includes('monto') && texto.includes('recibido') || 
                                               label.includes('monto') && label.includes('recibido') ||
                                               input.name?.includes('monto_recibido');
                                    });
                                    elemento = inputsMonto[dato.index] || inputs[dato.index];
                                }
                            }
                            
                            if (elemento && elemento.value !== dato.valor) {
                                elemento.value = dato.valor;
                                
                                // Disparar eventos
                                elemento.dispatchEvent(new Event('input', { bubbles: true }));
                                elemento.dispatchEvent(new Event('change', { bubbles: true }));
                                elemento.dispatchEvent(new Event('blur', { bubbles: true }));
                                
                                console.log('✅ Restaurado:', dato.tipo, dato.valor);
                            }
                        });
                        
                    } catch (e) {
                        console.error('❌ Error al restaurar:', e);
                    }
                }
            },
            
            getSelector(elemento) {
                // Generar un selector único para el elemento
                if (elemento.id) return '#' + elemento.id;
                if (elemento.name) return '[name=\"' + elemento.name + '\"]';
                if (elemento.className) return '.' + elemento.className.split(' ')[0];
                return null;
            }
        }" 
         x-init="init()"
    >
        {{-- El contenido del formulario se renderiza normalmente aquí --}}
        
    </div>

    <script>
        // Sistema simple de persistencia adicional
        document.addEventListener('DOMContentLoaded', function() {
            const consultaId = '{{ request()->get("consulta_id") }}';
            
            console.log('📄 DOM cargado, consultaId:', consultaId);
            
            if (consultaId) {
                // Función simple para guardar datos
                function guardarDatosSimple() {
                    const datos = [];
                    
                    // Buscar todos los selects y inputs
                    document.querySelectorAll('select, input').forEach((elemento, index) => {
                        const texto = elemento.parentElement?.textContent?.toLowerCase() || '';
                        
                        // Identificar campos de pago
                        if ((texto.includes('tipo') && texto.includes('pago')) || 
                            (texto.includes('monto') && texto.includes('recibido'))) {
                            
                            if (elemento.value && elemento.value.trim() !== '') {
                                datos.push({
                                    tag: elemento.tagName,
                                    index: index,
                                    value: elemento.value,
                                    name: elemento.name || '',
                                    id: elemento.id || '',
                                    className: elemento.className || '',
                                    textContent: texto.substring(0, 50)
                                });
                            }
                        }
                    });
                    
                    if (datos.length > 0) {
                        localStorage.setItem('pagos_simple_' + consultaId, JSON.stringify(datos));
                        console.log('💾 Guardado simple:', datos.length, 'elementos');
                    }
                }
                
                // Función simple para restaurar datos
                function restaurarDatosSimple() {
                    const key = 'pagos_simple_' + consultaId;
                    const datosGuardados = localStorage.getItem(key);
                    
                    if (datosGuardados) {
                        try {
                            const datos = JSON.parse(datosGuardados);
                            console.log('🔄 Restaurando simple:', datos.length, 'elementos');
                            
                            datos.forEach(dato => {
                                let elemento = null;
                                
                                // Buscar por name, id, o índice
                                if (dato.name) {
                                    elemento = document.querySelector(`[name="${dato.name}"]`);
                                }
                                if (!elemento && dato.id) {
                                    elemento = document.getElementById(dato.id);
                                }
                                if (!elemento) {
                                    const elementos = document.querySelectorAll(dato.tag);
                                    elemento = elementos[dato.index];
                                }
                                
                                if (elemento && elemento.value !== dato.value) {
                                    elemento.value = dato.value;
                                    elemento.dispatchEvent(new Event('input', { bubbles: true }));
                                    elemento.dispatchEvent(new Event('change', { bubbles: true }));
                                    console.log('✅ Restaurado:', dato.tag, dato.value);
                                }
                            });
                            
                        } catch (e) {
                            console.error('❌ Error restauración simple:', e);
                        }
                    }
                }
                
                // Configurar guardado automático
                setInterval(guardarDatosSimple, 2000);
                
                // Configurar restauración
                setTimeout(restaurarDatosSimple, 1000);
                setTimeout(restaurarDatosSimple, 3000);
                setTimeout(restaurarDatosSimple, 5000);
                
                // Guardar al salir
                window.addEventListener('beforeunload', guardarDatosSimple);
                
                // Configurar eventos en campos existentes
                function configurarEventos() {
                    document.querySelectorAll('select, input').forEach(elemento => {
                        if (!elemento.hasAttribute('data-persistence-added')) {
                            elemento.addEventListener('change', guardarDatosSimple);
                            elemento.addEventListener('input', guardarDatosSimple);
                            elemento.setAttribute('data-persistence-added', 'true');
                        }
                    });
                }
                
                configurarEventos();
                setInterval(configurarEventos, 3000);
            }
        });
    </script>
</x-filament-panels::page>
