<script>
    // Script global para el manejo de temas
    window.themeManager = {
        init: function() {
            // Cargar tema guardado
            const savedTheme = localStorage.getItem('theme') || 'light';
            this.applyTheme(savedTheme);
            
            // Observar cambios en el tema
            this.observeThemeChanges();
        },

        applyTheme: function(theme) {
            const html = document.documentElement;
            
            // Limpiar clases previas
            html.classList.remove('dark', 'custom-dark-theme');
            
            switch(theme) {
                case 'light':
                    // Tema claro por defecto
                    break;
                case 'dark':
                    html.classList.add('dark');
                    break;
                case 'custom-dark':
                    html.classList.add('dark', 'custom-dark-theme');
                    // Activar efectos especiales del tema personalizado
                    setTimeout(() => {
                        if (window.activateCustomDarkTheme) {
                            window.activateCustomDarkTheme();
                        }
                    }, 100);
                    break;
            }
            
            localStorage.setItem('theme', theme);
        },

        observeThemeChanges: function() {
            // Observar cambios en el DOM para mantener el tema
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const currentTheme = localStorage.getItem('theme');
                        if (currentTheme === 'custom-dark') {
                            // Asegurar que el tema personalizado se mantenga
                            const html = document.documentElement;
                            if (!html.classList.contains('custom-dark-theme')) {
                                html.classList.add('custom-dark-theme');
                            }
                        }
                    }
                });
            });

            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        },

        setTheme: function(theme) {
            this.applyTheme(theme);
            
            // Disparar evento personalizado
            window.dispatchEvent(new CustomEvent('themeChanged', {
                detail: { theme: theme }
            }));
        }
    };

    // Función global para cambiar tema
    window.setTheme = function(theme) {
        window.themeManager.setTheme(theme);
    };

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.themeManager.init();
        });
    } else {
        window.themeManager.init();
    }
</script>
