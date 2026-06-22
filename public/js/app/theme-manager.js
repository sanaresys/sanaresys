// Sistema de Gestión de Temas Avanzado
class ThemeManager {
    constructor() {
        this.themes = {
            light: 'Tema Claro',
            dark: 'Tema Oscuro',
            'custom-dark': 'Tema Oscuro Personalizado'
        };
        
        this.init();
    }

    init() {
        // Cargar tema guardado
        const savedTheme = localStorage.getItem('theme') || 'light';
        this.applyTheme(savedTheme);
        
        // Configurar observadores
        this.setupObservers();
        
        // Registrar eventos globales
        this.registerGlobalEvents();
        
        console.log('🎨 Sistema de temas inicializado');
    }

    applyTheme(theme) {
        const html = document.documentElement;
        
        // Limpiar clases previas
        html.classList.remove('dark', 'custom-dark-theme');
        
        // Aplicar nuevo tema
        switch(theme) {
            case 'light':
                // Tema claro por defecto
                break;
            case 'dark':
                html.classList.add('dark');
                break;
            case 'custom-dark':
                html.classList.add('dark', 'custom-dark-theme');
                this.activateCustomEffects();
                break;
        }
        
        // Guardar preferencia
        localStorage.setItem('theme', theme);
        
        // Disparar evento personalizado
        this.dispatchThemeChange(theme);
        
        console.log(`✨ Tema aplicado: ${this.themes[theme]}`);
    }

    activateCustomEffects() {
        // Efectos especiales para el tema personalizado
        setTimeout(() => {
            // Añadir efectos de partículas si no existen
            if (!document.querySelector('.theme-particles')) {
                this.createParticleEffect();
            }
            
            // Animaciones especiales
            this.addCustomAnimations();
        }, 100);
    }

    createParticleEffect() {
        const particles = document.createElement('div');
        particles.className = 'theme-particles';
        particles.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(6, 182, 212, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
            animation: particleFloat 20s ease-in-out infinite;
        `;
        
        document.body.appendChild(particles);
    }

    addCustomAnimations() {
        // Agregar CSS para animaciones si no existe
        if (!document.querySelector('#custom-theme-animations')) {
            const style = document.createElement('style');
            style.id = 'custom-theme-animations';
            style.textContent = `
                @keyframes particleFloat {
                    0%, 100% { transform: translateY(0px) rotate(0deg); }
                    50% { transform: translateY(-10px) rotate(180deg); }
                }
                
                html.custom-dark-theme .fi-sidebar-nav-item {
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }
                
                html.custom-dark-theme .fi-btn:hover {
                    transform: translateY(-1px) scale(1.02);
                }
            `;
            document.head.appendChild(style);
        }
    }

    setupObservers() {
        // Observar cambios en las clases del HTML
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const currentTheme = localStorage.getItem('theme');
                    if (currentTheme === 'custom-dark') {
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
    }

    registerGlobalEvents() {
        // Función global para cambiar tema
        window.setTheme = (theme) => {
            this.applyTheme(theme);
        };

        // Función global para obtener tema actual
        window.getCurrentTheme = () => {
            return localStorage.getItem('theme') || 'light';
        };

        // Función global para activar tema personalizado
        window.activateCustomDarkTheme = () => {
            this.applyTheme('custom-dark');
        };
    }

    dispatchThemeChange(theme) {
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { 
                theme: theme,
                themeName: this.themes[theme],
                timestamp: new Date().toISOString()
            }
        }));
    }

    // Método para mostrar notificación
    showNotification(message, type = 'success') {
        // Intentar usar el sistema de notificaciones de Filament
        if (window.$wire && typeof window.$wire.call === 'function') {
            try {
                window.$wire.call('$notify', type, message);
            } catch (e) {
                console.log(message);
            }
        } else {
            console.log(message);
        }
    }

    // Método para obtener estadísticas del tema
    getThemeStats() {
        return {
            currentTheme: localStorage.getItem('theme') || 'light',
            availableThemes: Object.keys(this.themes),
            customEffectsActive: document.documentElement.classList.contains('custom-dark-theme')
        };
    }
}

// Inicializar el gestor de temas
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});

// También inicializar si el DOM ya está cargado
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.themeManager) {
            window.themeManager = new ThemeManager();
        }
    });
} else {
    if (!window.themeManager) {
        window.themeManager = new ThemeManager();
    }
}
