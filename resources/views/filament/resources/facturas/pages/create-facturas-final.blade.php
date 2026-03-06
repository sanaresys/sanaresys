<x-filament-panels::page>
    <div x-data="{ 
            consultaId: '{{ request()->get("consulta_id") }}',
            
            init() {
                console.log('🚀 Iniciando sistema de persistencia estilo manage-servicios - Consulta ID:', this.consultaId);
                
                // Restaurar inmediatamente
                if (this.consultaId) {
                    this.restaurarDatosPagos();
                    
                    // Configurar auto-guardado
                    this.configurarAutoGuardado();
                }
            },
            
            configurarAutoGuardado() {
                // Configurar eventos cada cierto tiempo para campos dinámicos
                setInterval(() => {
                    this.configurarEventosCampos();
                }, 2000);
                
                // Guardar antes de salir
                window.addEventListener('beforeunload', () => {
                    this.guardarDatosPagos();
                });
            },
            
            configurarEventosCampos() {
                // Buscar campos de pagos y añadir eventos si no los tienen
                const campos = document.querySelectorAll('select, input[type=\"number\"], input[type=\"text\"]');
                
                campos.forEach(campo => {
                    if (!campo.hasAttribute('data-pago-listener')) {
                        const texto = campo.parentElement?.textContent?.toLowerCase() || '';
                        const label = campo.closest('div')?.querySelector('label')?.textContent?.toLowerCase() || '';
                        
                        // Identificar campos de pago
                        if ((texto.includes('tipo') && texto.includes('pago')) || 
                            (texto.includes('monto') && texto.includes('recibido')) ||
                            (label.includes('tipo') && label.includes('pago')) ||
                            (label.includes('monto') && label.includes('recibido'))) {
                            
                            campo.addEventListener('input', () => this.guardarDatosPagos());
                            campo.addEventListener('change', () => this.guardarDatosPagos());
                            campo.setAttribute('data-pago-listener', 'true');
                            
                            console.log('🔧 Listener configurado para campo:', campo.tagName, texto.substring(0, 30));
                        }
                    }
                });
            },
            
            guardarDatosPagos() {
                if (!this.consultaId) return;
                
                // Usar la misma nomenclatura que manage-servicios
                const pagosKey = `pagos_factura_${this.consultaId}`;
                const pagosData = [];
                
                // Buscar todos los campos de pago
                const tipoSelects = this.buscarCampos('select', ['tipo', 'pago']);
                const montoInputs = this.buscarCampos('input', ['monto', 'recibido']);
                
                // Guardar datos de cada fila de pago
                const maxFilas = Math.max(tipoSelects.length, montoInputs.length);
                
                for (let i = 0; i < maxFilas; i++) {
                    const tipoSelect = tipoSelects[i];
                    const montoInput = montoInputs[i];
                    
                    if ((tipoSelect && tipoSelect.value) || (montoInput && montoInput.value)) {
                        pagosData.push({
                            fila: i,
                            tipo_pago_id: tipoSelect ? tipoSelect.value : '',
                            monto_recibido: montoInput ? montoInput.value : '',
                            tipo_selector: tipoSelect ? this.getUniqueSelector(tipoSelect) : null,
                            monto_selector: montoInput ? this.getUniqueSelector(montoInput) : null
                        });
                    }
                }
                
                if (pagosData.length > 0) {
                    // Usar sessionStorage como manage-servicios
                    sessionStorage.setItem(pagosKey, JSON.stringify(pagosData));
                    console.log('💾 Datos de pagos guardados:', pagosData);
                }
            },
            
            restaurarDatosPagos() {
                if (!this.consultaId) return;
                
                const pagosKey = `pagos_factura_${this.consultaId}`;
                const savedData = sessionStorage.getItem(pagosKey);
                
                if (savedData) {
                    try {
                        const pagosData = JSON.parse(savedData);
                        console.log('🔄 Restaurando datos de pagos:', pagosData);
                        
                        // Intentar restaurar múltiples veces hasta que los campos estén disponibles
                        let intentos = 0;
                        const maxIntentos = 10;
                        
                        const intentarRestaurar = () => {
                            intentos++;
                            
                            let restaurados = 0;
                            
                            pagosData.forEach(pago => {
                                // Buscar por selector específico primero
                                let tipoSelect = null;
                                let montoInput = null;
                                
                                if (pago.tipo_selector) {
                                    tipoSelect = document.querySelector(pago.tipo_selector);
                                }
                                if (pago.monto_selector) {
                                    montoInput = document.querySelector(pago.monto_selector);
                                }
                                
                                // Si no encuentra por selector, buscar por posición
                                if (!tipoSelect || !montoInput) {
                                    const tipoSelects = this.buscarCampos('select', ['tipo', 'pago']);
                                    const montoInputs = this.buscarCampos('input', ['monto', 'recibido']);
                                    
                                    tipoSelect = tipoSelect || tipoSelects[pago.fila];
                                    montoInput = montoInput || montoInputs[pago.fila];
                                }
                                
                                // Restaurar valores
                                if (tipoSelect && pago.tipo_pago_id && tipoSelect.value !== pago.tipo_pago_id) {
                                    tipoSelect.value = pago.tipo_pago_id;
                                    tipoSelect.dispatchEvent(new Event('change', { bubbles: true }));
                                    restaurados++;
                                }
                                
                                if (montoInput && pago.monto_recibido && montoInput.value !== pago.monto_recibido) {
                                    montoInput.value = pago.monto_recibido;
                                    montoInput.dispatchEvent(new Event('input', { bubbles: true }));
                                    montoInput.dispatchEvent(new Event('change', { bubbles: true }));
                                    restaurados++;
                                }
                            });
                            
                            if (restaurados > 0) {
                                console.log(`✅ Restaurados ${restaurados} campos en intento ${intentos}`);
                                this.configurarEventosCampos(); // Configurar eventos después de restaurar
                                return;
                            }
                            
                            // Reintentar si no se restauró nada y no hemos llegado al límite
                            if (intentos < maxIntentos) {
                                setTimeout(intentarRestaurar, 500);
                            } else {
                                console.log(`❌ No se pudieron restaurar campos después de ${maxIntentos} intentos`);
                            }
                        };
                        
                        // Empezar inmediatamente y luego reintentar
                        intentarRestaurar();
                        
                    } catch (e) {
                        console.error('❌ Error al restaurar datos de pagos:', e);
                    }
                }
            },
            
            buscarCampos(tagName, keywords) {
                const elementos = document.querySelectorAll(tagName);
                return Array.from(elementos).filter(elemento => {
                    const texto = elemento.parentElement?.textContent?.toLowerCase() || '';
                    const label = elemento.closest('div')?.querySelector('label')?.textContent?.toLowerCase() || '';
                    const nombre = elemento.name?.toLowerCase() || '';
                    
                    return keywords.every(keyword => 
                        texto.includes(keyword) || 
                        label.includes(keyword) || 
                        nombre.includes(keyword)
                    );
                });
            },
            
            getUniqueSelector(elemento) {
                if (elemento.id) return `#${elemento.id}`;
                if (elemento.name) return `[name=\"${elemento.name}\"]`;
                if (elemento.className) {
                    const classes = elemento.className.split(' ').filter(c => c.length > 0);
                    if (classes.length > 0) return `.${classes[0]}`;
                }
                return null;
            }
        }" 
         x-init="init()"
    >
        {{-- El contenido del formulario se renderiza normalmente aquí --}}
        
    </div>

    <script>
        // Sistema adicional de respaldo basado en DOM - Compatible con manage-servicios
        document.addEventListener('DOMContentLoaded', function() {
            const consultaId = '{{ request()->get("consulta_id") }}';
            
            console.log('📄 Sistema de respaldo DOM iniciado - Consulta ID:', consultaId);
            
            if (consultaId) {
                // Función de respaldo que usa las mismas claves que manage-servicios
                function guardarRespaldo() {
                    const backupKey = `pagos_backup_${consultaId}`;
                    const datos = [];
                    
                    document.querySelectorAll('select, input').forEach((elemento, index) => {
                        const texto = elemento.parentElement?.textContent?.toLowerCase() || '';
                        
                        if ((texto.includes('tipo') && texto.includes('pago')) || 
                            (texto.includes('monto') && texto.includes('recibido'))) {
                            
                            if (elemento.value && elemento.value.trim() !== '') {
                                datos.push({
                                    indice: index,
                                    tag: elemento.tagName,
                                    valor: elemento.value,
                                    name: elemento.name || '',
                                    texto: texto.substring(0, 50)
                                });
                            }
                        }
                    });
                    
                    if (datos.length > 0) {
                        // Usar sessionStorage como manage-servicios
                        sessionStorage.setItem(backupKey, JSON.stringify(datos));
                        console.log('💾 Backup guardado:', datos.length, 'elementos');
                    }
                }
                
                function restaurarRespaldo() {
                    const backupKey = `pagos_backup_${consultaId}`;
                    const datos = sessionStorage.getItem(backupKey);
                    
                    if (datos) {
                        try {
                            const backup = JSON.parse(datos);
                            console.log('🔄 Restaurando backup:', backup.length, 'elementos');
                            
                            backup.forEach(item => {
                                let elemento = null;
                                
                                // Buscar por name
                                if (item.name) {
                                    elemento = document.querySelector(`[name="${item.name}"]`);
                                }
                                
                                // Buscar por posición
                                if (!elemento) {
                                    const elementos = document.querySelectorAll(item.tag);
                                    elemento = elementos[item.indice];
                                }
                                
                                if (elemento && elemento.value !== item.valor) {
                                    elemento.value = item.valor;
                                    elemento.dispatchEvent(new Event('input', { bubbles: true }));
                                    elemento.dispatchEvent(new Event('change', { bubbles: true }));
                                    console.log('✅ Backup restaurado:', item.tag, item.valor);
                                }
                            });
                            
                        } catch (e) {
                            console.error('❌ Error en backup:', e);
                        }
                    }
                }
                
                // Configurar guardado automático de respaldo
                setInterval(guardarRespaldo, 3000);
                
                // Restaurar respaldo
                setTimeout(restaurarRespaldo, 1500);
                
                // Configurar eventos globales
                document.addEventListener('input', function(e) {
                    const texto = e.target.parentElement?.textContent?.toLowerCase() || '';
                    if ((texto.includes('tipo') && texto.includes('pago')) || 
                        (texto.includes('monto') && texto.includes('recibido'))) {
                        setTimeout(guardarRespaldo, 100);
                    }
                });
            }
        });
    </script>
</x-filament-panels::page>
