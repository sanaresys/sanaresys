<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sanare - Sistema de Gestión Médica</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }

        /* Doctor Carousel Styles */
        .doctor-carousel {
            position: relative;
            height: 400px;
            overflow: hidden;
            border-radius: 20px;
            margin: 3rem auto;
            max-width: 600px;
        }

        .doctor-slide {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.5s ease-in-out;
        }

        .doctor-slide.active {
            opacity: 1;
            transform: translateX(0);
        }

        .doctor-slide.prev {
            transform: translateX(-100%);
        }

        .doctor-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
        }

        .doctor-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            color: white;
            padding: 2rem;
            border-radius: 0 0 20px 20px;
        }

        .doctor-specialty {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #fff;
        }

        .doctor-description {
            font-size: 1rem;
            opacity: 0.9;
            color: #e2e8f0;
        }

        .carousel-controls {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 1rem;
            pointer-events: none;
        }

        .carousel-btn {
            background: rgba(255,255,255,0.9);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            pointer-events: all;
            color: #2563eb;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .carousel-btn:hover {
            background: white;
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }

        .carousel-dots {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(59, 130, 246, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dot.active {
            background: #3b82f6;
            transform: scale(1.2);
        }

        @media (max-width: 768px) {
            .doctor-carousel {
                height: 300px;
                margin: 2rem auto;
            }
            
            .doctor-specialty {
                font-size: 1.2rem;
            }
            
            .doctor-description {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    
    <!-- Navigation Header -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo MEDIANO (48px) - Recomendado para header -->
                    <img src="{{ asset('images/logo.png') }}" alt="Sanare Logo" class="h-12 w-12 mr-3">
                    
                    <h1 class="text-2xl font-bold text-gray-800">Sanare</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ \App\Support\CentralUrl::route('clinica.registro') }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition duration-300 shadow-md hover:shadow-lg">
                        <i class="fas fa-clinic-medical mr-2"></i>Registrar Clínica
                    </a>
                    <a href="{{ \App\Support\CentralUrl::route('filament.admin.auth.login') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition duration-300 shadow-md hover:shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <!-- Logo grande en el centro -->
                <div class="mb-8 flex justify-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Sanare Logo"  class="h-40 w-40 rounded-2xl  shadow-xl bg-white p-3">
                </div>
                <h1 class="text-5xl font-bold text-gray-900 mb-6">
                    Sistema de Gestión Médica
                    <span class="text-blue-600">Integral</span>
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Plataforma completa para la administración de médicos, centros médicos, citas, consultas y recetas. 
                    Optimiza tu práctica médica con tecnología de vanguardia.
                </p>
                
                <!-- Doctor Carousel -->
                <div class="doctor-carousel" id="doctorCarousel">
                    <div class="doctor-slide active">
                        <img src="{{ asset('images/Cardiologo.jpg') }}" alt="Especialista en Cardiología" class="doctor-image">
                        <div class="doctor-overlay">
                            <div class="doctor-specialty">Cardiología</div>
                            <div class="doctor-description">Especialistas en salud cardiovascular con tecnología de vanguardia</div>
                        </div>
                    </div>
                    
                    <div class="doctor-slide">
                        <img src="{{ asset('images/Pediatria.jpg') }}" alt="Especialista en Pediatría" class="doctor-image">
                        <div class="doctor-overlay">
                            <div class="doctor-specialty">Pediatría</div>
                            <div class="doctor-description">Atención especializada para el cuidado integral de los niños</div>
                        </div>
                    </div>
                    
                    <div class="doctor-slide">
                        <img src="{{ asset('images/neurologia.jpg') }}" alt="Especialista en Neurología" class="doctor-image">
                        <div class="doctor-overlay">
                            <div class="doctor-specialty">Neurología</div>
                            <div class="doctor-description">Diagnóstico y tratamiento de trastornos del sistema nervioso</div>
                        </div>
                    </div>
                    
                    <div class="doctor-slide">
                        <img src="{{ asset('images/ginecologia.jpg') }}" alt="Especialista en Ginecología" class="doctor-image">
                        <div class="doctor-overlay">
                            <div class="doctor-specialty">Ginecología</div>
                            <div class="doctor-description">Cuidado integral de la salud femenina en todas las etapas</div>
                        </div>
                    </div>
                    
                    <div class="doctor-slide">
                        <img src="{{ asset('images/traumatologia.jpg') }}" alt="Especialista en Traumatología" class="doctor-image">
                        <div class="doctor-overlay">
                            <div class="doctor-specialty">Traumatología</div>
                            <div class="doctor-description">Especialistas en lesiones del sistema musculoesquelético</div>
                        </div>
                    </div>
                    
                    <div class="carousel-controls">
                        <button class="carousel-btn" id="prevBtn">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="carousel-btn" id="nextBtn">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                
                <div class="carousel-dots" id="carouselDots"></div>
                
                <!-- Features Icons -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12 mt-12">
                    <div class="text-center">
                        <i class="fas fa-user-md text-3xl text-blue-600 mb-2"></i>
                        <p class="text-sm text-gray-700 font-medium">Gestión de Médicos</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-hospital text-3xl text-green-600 mb-2"></i>
                        <p class="text-sm text-gray-700 font-medium">Centros Médicos</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-calendar-alt text-3xl text-purple-600 mb-2"></i>
                        <p class="text-sm text-gray-700 font-medium">Citas y Consultas</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-prescription-bottle-alt text-3xl text-red-600 mb-2"></i>
                        <p class="text-sm text-gray-700 font-medium">Recetas Médicas</p>
                    </div>
                </div>

                <a href="{{ \App\Support\CentralUrl::route('filament.admin.auth.login') }}" 
                   class="inline-flex items-center bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-8 py-4 rounded-lg text-lg font-semibold shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-105">
                    <i class="fas fa-stethoscope mr-3"></i>
                    Acceder al Sistema
                    <i class="fas fa-arrow-right ml-3"></i>
                </a>

                <a href="{{ \App\Support\CentralUrl::route('clinica.registro') }}" 
                   class="inline-flex items-center bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700 text-white px-8 py-4 rounded-lg text-lg font-semibold shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-105 mt-4 sm:mt-0 sm:ml-4">
                    <i class="fas fa-clinic-medical mr-3"></i>
                    Registrar mi Clínica
                    <i class="fas fa-arrow-right ml-3"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Membership Plans Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Planes de Membresía</h2>
                <p class="text-xl text-gray-600">Comienza gratis y elige el plan que mejor se adapte a tus necesidades</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Plan Gratuito -->
                <div class="bg-white rounded-xl shadow-lg border-2 border-green-200 card-hover overflow-hidden relative">
                    <div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-center py-2 text-sm font-semibold">
                        <i class="fas fa-gift mr-2"></i>GRATIS 15 DÍAS
                    </div>
                    <div class="p-8 pt-12">
                        <div class="text-center mb-6">
                            <i class="fas fa-seedling text-4xl text-green-500 mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-900">Plan Gratuito</h3>
                            <p class="text-gray-600 mt-2">Prueba Sanare sin compromisos</p>
                        </div>
                        
                        <div class="text-center mb-6">
                            <span class="text-4xl font-bold text-green-600">$0</span>
                            <span class="text-gray-600">/15 días</span>
                        </div>

                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Hasta 50 pacientes</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Gestión básica de citas</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Historial médico digital</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">1 médico</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Soporte por email</span>
                            </li>
                        </ul>

                        <button class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition duration-300 transform hover:scale-105">
                            <i class="fas fa-rocket mr-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Plan Mensual -->
                <div class="bg-white rounded-xl shadow-xl border-2 border-blue-500 card-hover overflow-hidden relative">
                    <div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-center py-2 text-sm font-semibold">
                        <i class="fas fa-star mr-2"></i>MÁS POPULAR
                    </div>
                    <div class="p-8 pt-12">
                        <div class="text-center mb-6">
                            <i class="fas fa-hospital text-4xl text-blue-500 mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-900">Plan Mensual</h3>
                            <p class="text-gray-600 mt-2">Flexibilidad total mes a mes</p>
                        </div>
                        
                        <div class="text-center mb-6">
                            <span class="text-4xl font-bold text-blue-600">$89.99</span>
                            <span class="text-gray-600">/mes</span>
                            <div class="text-sm text-gray-500 mt-1">Sin permanencia</div>
                        </div>

                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Pacientes ilimitados</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Gestión avanzada de citas</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Múltiples médicos</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Reportes y analíticas</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Recetas digitales</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Soporte prioritario 24/7</span>
                            </li>
                        </ul>

                        <button class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-3 rounded-lg font-semibold transition duration-300 transform hover:scale-105">
                            <i class="fas fa-credit-card mr-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Plan Anual -->
                <div class="bg-white rounded-xl shadow-lg border-2 border-yellow-400 card-hover overflow-hidden relative">
                    <div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-center py-2 text-sm font-semibold">
                        <i class="fas fa-percentage mr-2"></i>AHORRA 44%
                    </div>
                    <div class="p-8 pt-12">
                        <div class="text-center mb-6">
                            <i class="fas fa-crown text-4xl text-yellow-500 mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-900">Plan Anual</h3>
                            <p class="text-gray-600 mt-2">El mejor valor para tu práctica</p>
                        </div>
                        
                        <div class="text-center mb-6">
                            <span class="text-4xl font-bold text-yellow-600">$599</span>
                            <span class="text-gray-600">/año</span>
                            <div class="text-sm text-green-600 font-semibold mt-1">
                                Equivale a $49.92/mes
                            </div>
                            <div class="text-xs text-gray-500 line-through">$1,079.88/año mensual</div>
                        </div>

                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Todo del plan mensual</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">2 meses gratis incluidos</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Integraciones premium</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Backup automático diario</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Capacitación personalizada</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Soporte telefónico directo</span>
                            </li>
                        </ul>

                        <button class="w-full bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white py-3 rounded-lg font-semibold transition duration-300 transform hover:scale-105">
                            <i class="fas fa-calendar-check mr-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Money Back Guarantee -->
            <div class="text-center mt-12">
                <div class="inline-flex items-center bg-green-50 px-6 py-3 rounded-full border border-green-200">
                    <i class="fas fa-shield-alt text-green-600 mr-3"></i>
                    <span class="text-green-800 font-semibold">Garantía de devolución de 30 días en todos los planes</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 gradient-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-4">¿Por qué elegir Sanare?</h2>
                <p class="text-xl text-blue-100">Tecnología avanzada al servicio de la medicina</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-xl p-6 text-center">
                    <i class="fas fa-shield-alt text-4xl text-white mb-4"></i>
                    <h3 class="text-xl font-semibold text-white mb-2">Seguridad Total</h3>
                    <p class="text-blue-100">Cumple con todas las normativas de protección de datos médicos</p>
                </div>
                
                <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-xl p-6 text-center">
                    <i class="fas fa-users text-4xl text-white mb-4"></i>
                    <h3 class="text-xl font-semibold text-white mb-2">Colaboración Efectiva</h3>
                    <p class="text-blue-100">Facilita la comunicación entre médicos y pacientes</p>
                </div>
                
                            
                <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-xl p-6 text-center">
                    <i class="fas fa-chart-line text-4xl text-white mb-4"></i>
                    <h3 class="text-xl font-semibold text-white mb-2">Reportes Inteligentes</h3>
                    <p class="text-blue-100">Analíticas avanzadas para optimizar tu práctica médica</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="flex items-center justify-center mb-4">
                    <img src="{{ asset('images/logo_LETRAS_BLANCAS.png') }}" alt="Sanare Logo" class="h-16 w-16 p-3">
                    
                    <h3 class="text-2xl font-bold">Sanare</h3>
                </div>
                <p class="text-gray-400 mb-6">Transformando la gestión médica con tecnología innovadora</p>
                <div class="flex justify-center space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-facebook-f text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-linkedin-in text-xl"></i>
                    </a>
                </div>
                <div class="mt-8 pt-8 border-t border-gray-800">
                    <p class="text-gray-500">© 2025 Sanare. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        class DoctorCarousel {
            constructor() {
                this.currentSlide = 0;
                this.slides = document.querySelectorAll('.doctor-slide');
                this.totalSlides = this.slides.length;
                this.autoSlideInterval = null;
                
                this.init();
            }
            
            init() {
                this.createDots();
                this.bindEvents();
                this.startAutoSlide();
            }
            
            createDots() {
                const dotsContainer = document.getElementById('carouselDots');
                for (let i = 0; i < this.totalSlides; i++) {
                    const dot = document.createElement('div');
                    dot.className = i === 0 ? 'dot active' : 'dot';
                    dot.addEventListener('click', () => this.goToSlide(i));
                    dotsContainer.appendChild(dot);
                }
            }
            
            bindEvents() {
                document.getElementById('prevBtn').addEventListener('click', () => this.prevSlide());
                document.getElementById('nextBtn').addEventListener('click', () => this.nextSlide());
                
                // Pause on hover
                const carousel = document.getElementById('doctorCarousel');
                carousel.addEventListener('mouseenter', () => this.stopAutoSlide());
                carousel.addEventListener('mouseleave', () => this.startAutoSlide());
            }
            
            updateSlides() {
                this.slides.forEach((slide, index) => {
                    slide.classList.remove('active', 'prev');
                    if (index === this.currentSlide) {
                        slide.classList.add('active');
                    } else if (index < this.currentSlide) {
                        slide.classList.add('prev');
                    }
                });
                
                // Update dots
                const dots = document.querySelectorAll('.dot');
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === this.currentSlide);
                });
            }
            
            nextSlide() {
                this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                this.updateSlides();
            }
            
            prevSlide() {
                this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                this.updateSlides();
            }
            
            goToSlide(index) {
                this.currentSlide = index;
                this.updateSlides();
            }
            
            startAutoSlide() {
                this.stopAutoSlide();
                this.autoSlideInterval = setInterval(() => this.nextSlide(), 4000);
            }
            
            stopAutoSlide() {
                if (this.autoSlideInterval) {
                    clearInterval(this.autoSlideInterval);
                    this.autoSlideInterval = null;
                }
            }
        }

        // Initialize carousel when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new DoctorCarousel();
        });
    </script>

</body>
</html>
