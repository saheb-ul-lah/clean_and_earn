<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Clean-Earn Ecosystem</title>
    <meta name="description" content="Turn your waste into wealth while contributing to a cleaner, greener future." />

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                        display: ['Montserrat', 'sans-serif'],
                        alt: ['Raleway', 'sans-serif']
                    },
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                    },
                    keyframes: {
                        'fade-in-up': {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(20px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            }
                        },
                        'fade-in-right': {
                            '0%': {
                                opacity: '0',
                                transform: 'translateX(-20px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateX(0)'
                            }
                        },
                        'fade-in-left': {
                            '0%': {
                                opacity: '0',
                                transform: 'translateX(20px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateX(0)'
                            }
                        },
                        'float': {
                            '0%, 100%': {
                                transform: 'translateY(0)'
                            },
                            '50%': {
                                transform: 'translateY(-10px)'
                            }
                        },
                        'pulse-glow': {
                            '0%, 100%': {
                                boxShadow: '0 0 10px 5px rgba(34, 197, 94, 0.2)',
                                transform: 'scale(1)'
                            },
                            '50%': {
                                boxShadow: '0 0 20px 10px rgba(34, 197, 94, 0.4)',
                                transform: 'scale(1.02)'
                            }
                        }
                    },
                    animation: {
                        'fade-in-up': 'fade-in-up 0.8s ease-out forwards',
                        'fade-in-up-slow': 'fade-in-up 1.2s ease-out forwards',
                        'fade-in-right': 'fade-in-right 0.8s ease-out forwards',
                        'fade-in-left': 'fade-in-left 0.8s ease-out forwards',
                        'float': 'float 5s ease-in-out infinite',
                        'pulse-glow': 'pulse-glow 3s infinite'
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS -->
    <style>
        /* Global Styles */
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 80px;
        }

        body {
            overflow-x: hidden;
        }

        /* Gradient Backgrounds */
        .gradient-primary {
            background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
        }

        .gradient-hero {
            background: linear-gradient(135deg, rgba(240, 253, 244, 0.9) 0%, rgba(220, 252, 231, 0.8) 50%, rgba(187, 247, 208, 0.7) 100%);
        }

        .gradient-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.7) 0%, rgba(249, 250, 251, 0.8) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        /* Animation Delays */
        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-500 {
            animation-delay: 0.5s;
        }

        .delay-600 {
            animation-delay: 0.6s;
        }

        .delay-700 {
            animation-delay: 0.7s;
        }

        .delay-800 {
            animation-delay: 0.8s;
        }

        /* Animations for sections that come into view */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
            transition: all 0.7s ease-out;
        }

        /* Modal */
        .modal {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease-out;
        }

        .modal.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.3s ease-out;
        }

        .modal.active .modal-content {
            transform: scale(1);
            opacity: 1;
        }

        /* Mobile Menu */
        .mobile-menu {
            transform: translateX(100%);
            transition: transform 0.4s ease-out;
        }

        .mobile-menu.open {
            transform: translateX(0);
        }

        /* Hover effects */
        .hover-scale {
            transition: transform 0.3s ease-out;
        }

        .hover-scale:hover {
            transform: scale(1.03);
        }

        /* Underline animation for links */
        .link-underline {
            position: relative;
        }

        .link-underline::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 100%;
            height: 2px;
            background-color: currentColor;
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease-out;
        }

        .link-underline:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        /* Custom tabs styling */
        .tab-button {
            position: relative;
            transition: all 0.3s ease;
        }

        .tab-button::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            height: 3px;
            width: 100%;
            background-color: #22c55e;
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .tab-button.active::after {
            transform: scaleX(1);
        }

        .tab-content {
            display: none;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .tab-content.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        /* Wavy divider */
        .wavy-divider {
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z' opacity='.25' fill='%2322c55e'%3E%3C/path%3E%3Cpath d='M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z' opacity='.5' fill='%2322c55e'%3E%3C/path%3E%3Cpath d='M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z' fill='%2322c55e'%3E%3C/path%3E%3C/svg%3E") top center no-repeat;
            background-size: 100% 100px;
            height: 100px;
            width: 100%;
        }

        /* Background patterns */
        .bg-pattern-dots {
            background-image: radial-gradient(#22c55e 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .bg-pattern-grid {
            background-size: 40px 40px;
            background-image:
                linear-gradient(to right, rgba(34, 197, 94, 0.1) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(34, 197, 94, 0.1) 1px, transparent 1px);
        }
    </style>
</head>

<body class="bg-white font-sans text-gray-800">
    <!-- Mobile Menu Overlay -->
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden md:hidden" onclick="toggleMobileMenu()"></div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="mobile-menu fixed top-0 right-0 bottom-0 w-[280px] bg-white shadow-lg z-50 md:hidden">
        <div class="flex flex-col h-full">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                        CEE
                    </div>
                    <h2 class="text-lg font-bold">Menu</h2>
                </div>
                <button
                    onclick="toggleMobileMenu()"
                    class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1">
                    <li>
                        <a href="#home" onclick="toggleMobileMenu()" class="flex items-center justify-between px-6 py-3 text-gray-700 hover:bg-gray-50 transition-colors">
                            <span>Home</span>
                            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#how-it-works" onclick="toggleMobileMenu()" class="flex items-center justify-between px-6 py-3 text-gray-700 hover:bg-gray-50 transition-colors">
                            <span>How It Works</span>
                            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#waste-types" onclick="toggleMobileMenu()" class="flex items-center justify-between px-6 py-3 text-gray-700 hover:bg-gray-50 transition-colors">
                            <span>Waste Types</span>
                            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#features" onclick="toggleMobileMenu()" class="flex items-center justify-between px-6 py-3 text-gray-700 hover:bg-gray-50 transition-colors">
                            <span>Features</span>
                            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#testimonials" onclick="toggleMobileMenu()" class="flex items-center justify-between px-6 py-3 text-gray-700 hover:bg-gray-50 transition-colors">
                            <span>Testimonials</span>
                            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#faq" onclick="toggleMobileMenu()" class="flex items-center justify-between px-6 py-3 text-gray-700 hover:bg-gray-50 transition-colors">
                            <span>FAQ</span>
                            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#contact" onclick="toggleMobileMenu()" class="flex items-center justify-between px-6 py-3 text-gray-700 hover:bg-gray-50 transition-colors">
                            <span>Contact</span>
                            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="px-6 py-4 border-t border-gray-100">
                <a
                    href="./dashboard/index.php"
                    onclick="toggleModal('registrationModal'); toggleMobileMenu();"
                    class="block py-3 px-4 rounded-full bg-primary-500 text-white font-medium text-center hover:bg-primary-600 transition-all shadow-sm">
                    Get Started
                </a>
            </div>
        </div>
    </div>

    <!-- Header / Navbar -->
    <header class="fixed top-0 inset-x-0 z-30 bg-white/90 backdrop-blur-md shadow-sm">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:h-20">
                <!-- Logo -->
                <a href="#" class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-lg bg-primary-500 flex items-center justify-center text-white font-bold text-lg">
                        CEE
                    </div>
                    <div>
                        <span class="text-lg font-bold font-display">Clean-Earn</span>
                        <span class="text-primary-500 font-medium hidden sm:inline-block"> Ecosystem</span>
                    </div>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-1">
                    <a href="#home" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors">Home</a>
                    <a href="#how-it-works" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors">How It Works</a>
                    <a href="#waste-types" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors">Waste Types</a>
                    <a href="#features" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors">Features</a>
                    <a href="#testimonials" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors">Testimonials</a>
                    <a href="#faq" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors">FAQ</a>
                    <a href="#contact" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors">Contact</a>
                </nav>

                <!-- CTA Button & Mobile Menu Button -->
                <div class="flex items-center gap-4">
                    <a
                        href="./dashboard/index.php"
                        onclick="toggleModal('registrationModal')"
                        class="hidden md:block px-4 py-2 rounded-full text-sm font-medium bg-primary-500 text-white hover:bg-primary-600 transition-colors">
                        Get Started
                    </a>

                    <button
                        onclick="toggleMobileMenu()"
                        class="md:hidden w-10 h-10 flex items-center justify-center">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-16 md:pt-20">
        <!-- Hero Section -->
        <section id="home" class="relative pt-20 pb-24 md:pt-28 md:pb-32 overflow-hidden gradient-hero">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute top-20 left-[10%] w-64 h-64 bg-primary-200 rounded-full opacity-30 mix-blend-multiply animate-float"></div>
                <div class="absolute top-40 right-[15%] w-48 h-48 bg-primary-300 rounded-full opacity-20 mix-blend-multiply animate-float" style="animation-delay: 1s;"></div>
                <div class="absolute bottom-[10%] left-[20%] w-56 h-56 bg-primary-100 rounded-full opacity-25 mix-blend-multiply animate-float" style="animation-delay: 2s;"></div>
            </div>

            <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="max-w-4xl mx-auto text-center">
                    <span class="inline-block px-4 py-2 rounded-full bg-white/80 shadow-sm backdrop-blur-sm text-primary-600 font-medium text-sm mb-6 animate-fade-in-up">
                        Revolutionizing Waste Management
                    </span>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold font-display leading-tight mb-6 animate-fade-in-up delay-100">
                        <span class="text-primary-600">Clean</span> and <span class="text-primary-600">Earn</span> Ecosystem
                    </h1>

                    <p class="text-lg md:text-xl text-gray-700 mb-10 max-w-2xl mx-auto animate-fade-in-up delay-200">
                        Join the revolution in waste management. Turn your waste into wealth while contributing to a cleaner, greener future.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12 animate-fade-in-up delay-300">
                        <a
                            href="./dashboard/index.php"
                            onclick="toggleModal('registrationModal')"
                            class="px-8 py-4 rounded-full bg-primary-500 text-white font-medium hover:bg-primary-600 shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                            Get Started <i class="fas fa-arrow-right text-sm"></i>
                        </a>
                        <a
                            href="#how-it-works"
                            class="px-8 py-4 rounded-full border border-gray-300 text-gray-700 font-medium hover:bg-white hover:border-gray-400 transition-all">
                            Learn More
                        </a>
                    </div>

                    <!-- Supporting Logos/Text -->
                    <div class="animate-fade-in-up delay-400">
                        <p class="text-sm font-medium text-gray-500 mb-3">
                            Supporting Sustainable Development Goals
                        </p>
                        <div class="flex flex-wrap justify-center gap-6 items-center">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-leaf text-primary-500"></i>
                                <span class="text-sm font-medium">Eco-Friendly</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-recycle text-primary-500"></i>
                                <span class="text-sm font-medium">Circular Economy</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-hand-holding-dollar text-primary-500"></i>
                                <span class="text-sm font-medium">Economic Benefits</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-globe-asia text-primary-500"></i>
                                <span class="text-sm font-medium">Sustainable Future</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wavy-divider absolute bottom-0 left-0 right-0"></div>
        </section>

        <!-- Stats Section -->
        <section class="gradient-primary py-16 relative">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 reveal">
                    <div class="text-center p-6 rounded-lg bg-white/10 backdrop-blur-sm">
                        <p class="text-4xl font-bold text-white mb-2">10K+</p>
                        <p class="text-sm font-medium text-green-50">Households Participating</p>
                    </div>
                    <div class="text-center p-6 rounded-lg bg-white/10 backdrop-blur-sm">
                        <p class="text-4xl font-bold text-white mb-2">500+</p>
                        <p class="text-sm font-medium text-green-50">Waste Collectors</p>
                    </div>
                    <div class="text-center p-6 rounded-lg bg-white/10 backdrop-blur-sm">
                        <p class="text-4xl font-bold text-white mb-2">100+</p>
                        <p class="text-sm font-medium text-green-50">Storage Houses</p>
                    </div>
                    <div class="text-center p-6 rounded-lg bg-white/10 backdrop-blur-sm">
                        <p class="text-4xl font-bold text-white mb-2">5000+</p>
                        <p class="text-sm font-medium text-green-50">Tons of Waste Recycled</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section id="how-it-works" class="py-24 sm:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center mb-16 reveal">
                    <span class="inline-block px-4 py-1 rounded-full bg-primary-50 text-primary-600 font-medium text-sm mb-3">
                        Process
                    </span>
                    <h2 class="text-3xl md:text-4xl font-bold font-display mb-4">How It Works</h2>
                    <p class="text-gray-600 text-lg">
                        Clean-Earn Ecosystem connects waste providers, collectors, storage houses, and buyers in a seamless ecosystem
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 reveal">
                    <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                        <div class="flex items-center justify-center w-14 h-14 rounded-full bg-primary-100 mb-5">
                            <i class="fas fa-home text-primary-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Step 1: List Your Waste</h3>
                        <p class="text-gray-600 mb-4">
                            Households and businesses list available waste materials through our platform.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Categorize your waste (paper, plastic, metal, etc.)</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Estimate the weight of your waste</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Schedule a convenient pickup time</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                        <div class="flex items-center justify-center w-14 h-14 rounded-full bg-primary-100 mb-5">
                            <i class="fas fa-truck text-primary-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Step 2: Waste Collection</h3>
                        <p class="text-gray-600 mb-4">
                            Collectors receive notifications and pick up the waste from your location.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Collectors receive pickup requests in their area</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>They arrive at the scheduled time</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Waste is collected and transported to storage</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                        <div class="flex items-center justify-center w-14 h-14 rounded-full bg-primary-100 mb-5">
                            <i class="fas fa-warehouse text-primary-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Step 3: Storage Processing</h3>
                        <p class="text-gray-600 mb-4">
                            Storage houses weigh and categorize the waste for further processing.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Waste is accurately weighed at storage facilities</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Materials are sorted and categorized</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Inventory is updated in the system</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                        <div class="flex items-center justify-center w-14 h-14 rounded-full bg-primary-100 mb-5">
                            <i class="fas fa-user-tie text-primary-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Step 4: Buyer Purchase</h3>
                        <p class="text-gray-600 mb-4">
                            Buyers purchase waste materials for recycling and reprocessing.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Buyers browse available waste inventory</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>They place orders for specific materials</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Payment is processed through the platform</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                        <div class="flex items-center justify-center w-14 h-14 rounded-full bg-primary-100 mb-5">
                            <i class="fas fa-box text-primary-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Step 5: Material Delivery</h3>
                        <p class="text-gray-600 mb-4">
                            Purchased materials are delivered to buyers for processing.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Buyers collect materials from storage houses</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Delivery confirmation is recorded</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Transaction is completed</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                        <div class="flex items-center justify-center w-14 h-14 rounded-full bg-primary-100 mb-5">
                            <i class="fas fa-recycle text-primary-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Step 6: Recycling & Rewards</h3>
                        <p class="text-gray-600 mb-4">
                            Materials are recycled and participants earn rewards for their contribution.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Waste providers earn points based on weight</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Collectors receive payment for their services</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check text-primary-600 mt-1 flex-shrink-0"></i>
                                <span>Materials are recycled into new products</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-16 text-center reveal">
                    <a
                        href="#"
                        onclick="toggleModal('demoModal')"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-primary-500 text-white font-medium hover:bg-primary-600 transition-colors">
                        <i class="fas fa-play-circle"></i>
                        Watch How It Works
                    </a>
                </div>
            </div>
        </section>

        <!-- Waste Types Section -->
        <section id="waste-types" class="py-24 sm:py-32 bg-gray-50 bg-pattern-dots">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center mb-16 reveal">
                    <span class="inline-block px-4 py-1 rounded-full bg-primary-50 text-primary-600 font-medium text-sm mb-3">
                        Materials
                    </span>
                    <h2 class="text-3xl md:text-4xl font-bold font-display mb-4">Waste Types & Rates</h2>
                    <p class="text-gray-600 text-lg">
                        We accept various types of waste materials at competitive rates
                    </p>
                </div>

                <!-- Tabs Navigation -->
                <div class="mb-10 flex justify-center overflow-x-auto pb-2 reveal">
                    <div class="inline-flex border border-gray-200 rounded-lg shadow-sm bg-white">
                        <button class="tab-button active px-6 py-3 text-sm font-medium" data-tab="tab-paper">
                            <i class="fas fa-newspaper mr-2"></i> Paper
                        </button>
                        <button class="tab-button px-6 py-3 text-sm font-medium" data-tab="tab-plastic">
                            <i class="fas fa-wine-bottle mr-2"></i> Plastic
                        </button>
                        <button class="tab-button px-6 py-3 text-sm font-medium" data-tab="tab-metal">
                            <i class="fas fa-bolt mr-2"></i> Metal
                        </button>
                        <button class="tab-button px-6 py-3 text-sm font-medium" data-tab="tab-glass">
                            <i class="fas fa-glass-martini-alt mr-2"></i> Glass
                        </button>
                        <button class="tab-button px-6 py-3 text-sm font-medium" data-tab="tab-ewaste">
                            <i class="fas fa-laptop mr-2"></i> E-Waste
                        </button>
                        <button class="tab-button px-6 py-3 text-sm font-medium" data-tab="tab-organic">
                            <i class="fas fa-apple-alt mr-2"></i> Organic
                        </button>
                    </div>
                </div>

                <!-- Tab Contents -->
                <div class="waste-tab-contents reveal">
                    <!-- Paper Waste Tab -->
                    <div id="tab-paper" class="tab-content active">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Newspaper</h3>
                                <p class="text-gray-600 mt-2">Clean, dry newspapers without inserts or glossy pages.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹12/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Newspaper', 'Clean, dry newspapers without inserts or glossy pages.', '₹12/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Magazines</h3>
                                <p class="text-gray-600 mt-2">Glossy magazines, catalogs, and brochures with minimal staples.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹8/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Magazines', 'Glossy magazines, catalogs, and brochures with minimal staples.', '₹8/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Cardboard</h3>
                                <p class="text-gray-600 mt-2">Clean cardboard boxes, flattened and free from tape and staples.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹10/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Cardboard', 'Clean cardboard boxes, flattened and free from tape and staples.', '₹10/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Office Paper</h3>
                                <p class="text-gray-600 mt-2">White or colored printer paper, copy paper, and stationery.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹15/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Office Paper', 'White or colored printer paper, copy paper, and stationery.', '₹15/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Books</h3>
                                <p class="text-gray-600 mt-2">Paperback and hardcover books with covers removed.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹6/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Books', 'Paperback and hardcover books with covers removed.', '₹6/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Mixed Paper</h3>
                                <p class="text-gray-600 mt-2">Assorted paper waste including envelopes, flyers, and more.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹5/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Mixed Paper', 'Assorted paper waste including envelopes, flyers, and more.', '₹5/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Plastic Waste Tab -->
                    <div id="tab-plastic" class="tab-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">PET Bottles</h3>
                                <p class="text-gray-600 mt-2">Clean plastic bottles like soda, water, and juice containers.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹20/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'PET Bottles', 'Clean plastic bottles like soda, water, and juice containers.', '₹20/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">HDPE Containers</h3>
                                <p class="text-gray-600 mt-2">Milk jugs, detergent bottles, and other thick plastic containers.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹15/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'HDPE Containers', 'Milk jugs, detergent bottles, and other thick plastic containers.', '₹15/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Plastic Bags</h3>
                                <p class="text-gray-600 mt-2">Clean, dry plastic bags and film packaging.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹8/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Plastic Bags', 'Clean, dry plastic bags and film packaging.', '₹8/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">PVC</h3>
                                <p class="text-gray-600 mt-2">PVC pipes, vinyl records, and plastic window frames.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹10/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'PVC', 'PVC pipes, vinyl records, and plastic window frames.', '₹10/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">PP Plastic</h3>
                                <p class="text-gray-600 mt-2">Yogurt containers, bottle caps, and microwave-safe containers.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹12/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'PP Plastic', 'Yogurt containers, bottle caps, and microwave-safe containers.', '₹12/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Mixed Plastic</h3>
                                <p class="text-gray-600 mt-2">Miscellaneous plastic items that can't be easily categorized.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹5/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Mixed Plastic', 'Miscellaneous plastic items that can\'t be easily categorized.', '₹5/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Metal Waste Tab -->
                    <div id="tab-metal" class="tab-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Aluminum Cans</h3>
                                <p class="text-gray-600 mt-2">Soda, beer, and other beverage cans, clean and free of liquid.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹100/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Aluminum Cans', 'Soda, beer, and other beverage cans, clean and free of liquid.', '₹100/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Steel Cans</h3>
                                <p class="text-gray-600 mt-2">Food cans, pet food cans, and other tin-plated steel containers.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹45/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Steel Cans', 'Food cans, pet food cans, and other tin-plated steel containers.', '₹45/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Copper</h3>
                                <p class="text-gray-600 mt-2">Copper wire, piping, and other copper materials.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹450/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Copper', 'Copper wire, piping, and other copper materials.', '₹450/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Brass</h3>
                                <p class="text-gray-600 mt-2">Brass fixtures, decorative items, and hardware.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹300/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Brass', 'Brass fixtures, decorative items, and hardware.', '₹300/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Iron Scrap</h3>
                                <p class="text-gray-600 mt-2">Various iron and steel scrap metals from household items.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹30/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Iron Scrap', 'Various iron and steel scrap metals from household items.', '₹30/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Metal Mix</h3>
                                <p class="text-gray-600 mt-2">Mixed metal items of various types and compositions.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹25/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Metal Mix', 'Mixed metal items of various types and compositions.', '₹25/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Glass Waste Tab -->
                    <div id="tab-glass" class="tab-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Clear Glass</h3>
                                <p class="text-gray-600 mt-2">Transparent glass bottles and jars without colored tints.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹3/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Clear Glass', 'Transparent glass bottles and jars without colored tints.', '₹3/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Green Glass</h3>
                                <p class="text-gray-600 mt-2">Green-tinted glass bottles and containers.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹2/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Green Glass', 'Green-tinted glass bottles and containers.', '₹2/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Brown Glass</h3>
                                <p class="text-gray-600 mt-2">Brown or amber glass bottles and containers.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹2/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Brown Glass', 'Brown or amber glass bottles and containers.', '₹2/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Mixed Glass</h3>
                                <p class="text-gray-600 mt-2">Mixed colors of glass containers and bottles.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹1/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Mixed Glass', 'Mixed colors of glass containers and bottles.', '₹1/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- E-Waste Tab -->
                    <div id="tab-ewaste" class="tab-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Computers</h3>
                                <p class="text-gray-600 mt-2">Laptops, desktops, and computer components.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹50/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Computers', 'Laptops, desktops, and computer components.', '₹50/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Mobile Phones</h3>
                                <p class="text-gray-600 mt-2">Smartphones, feature phones, and accessories.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹200/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Mobile Phones', 'Smartphones, feature phones, and accessories.', '₹200/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">TV & Monitors</h3>
                                <p class="text-gray-600 mt-2">LCD, LED, CRT televisions and computer monitors.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹30/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'TV & Monitors', 'LCD, LED, CRT televisions and computer monitors.', '₹30/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Batteries</h3>
                                <p class="text-gray-600 mt-2">Rechargeable and non-rechargeable batteries of all types.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹25/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Batteries', 'Rechargeable and non-rechargeable batteries of all types.', '₹25/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Appliances</h3>
                                <p class="text-gray-600 mt-2">Small and medium household appliances and electronics.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹20/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Appliances', 'Small and medium household appliances and electronics.', '₹20/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Cables & Wires</h3>
                                <p class="text-gray-600 mt-2">Various types of electrical wires and cables.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹70/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Cables & Wires', 'Various types of electrical wires and cables.', '₹70/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Organic Waste Tab -->
                    <div id="tab-organic" class="tab-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Food Waste</h3>
                                <p class="text-gray-600 mt-2">Kitchen scraps, leftover food, and spoiled produce.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹2/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Food Waste', 'Kitchen scraps, leftover food, and spoiled produce.', '₹2/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Garden Waste</h3>
                                <p class="text-gray-600 mt-2">Grass clippings, plant trimmings, and fallen leaves.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹1/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Garden Waste', 'Grass clippings, plant trimmings, and fallen leaves.', '₹1/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>

                            <div class="rounded-xl border bg-white p-6 shadow-md hover-scale">
                                <h3 class="text-xl font-bold">Wood Waste</h3>
                                <p class="text-gray-600 mt-2">Untreated wood, branches, and natural wood products.</p>
                                <div class="mt-4 p-3 bg-primary-50 rounded-lg text-center">
                                    <span class="text-2xl font-bold text-primary-600">₹3/kg</span>
                                </div>
                                <button
                                    onclick="toggleModal('wasteDetailModal', 'Wood Waste', 'Untreated wood, branches, and natural wood products.', '₹3/kg')"
                                    class="mt-4 w-full py-2 border border-primary-500 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors text-sm font-medium">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-24 sm:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center mb-16 reveal">
                    <span class="inline-block px-4 py-1 rounded-full bg-primary-50 text-primary-600 font-medium text-sm mb-3">
                        Platform Features
                    </span>
                    <h2 class="text-3xl md:text-4xl font-bold font-display mb-4">Features of Clean-Earn Ecosystem</h2>
                    <p class="text-gray-600 text-lg">
                        Our platform offers a comprehensive solution for waste management and recycling
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 reveal">
                    <div class="rounded-xl border bg-white p-8 shadow-md hover-scale">
                        <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center mb-6">
                            <i class="fas fa-recycle text-primary-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Waste Collection</h3>
                        <p class="text-gray-600">
                            Schedule pickups for various types of waste materials from your home or business. Our platform allows you to list available waste with weight estimates and set convenient pickup times.
                        </p>
                    </div>

                    <div class="rounded-xl border bg-white p-8 shadow-md hover-scale">
                        <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center mb-6">
                            <i class="fas fa-truck text-primary-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Efficient Logistics</h3>
                        <p class="text-gray-600">
                            Real-time tracking and notifications for waste collectors and storage houses. Track your waste collection in real-time and receive notifications when your waste is collected and processed.
                        </p>
                    </div>

                    <div class="rounded-xl border bg-white p-8 shadow-md hover-scale">
                        <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center mb-6">
                            <i class="fas fa-wallet text-primary-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Reward System</h3>
                        <p class="text-gray-600">
                            Earn points based on the weight and type of waste materials you provide. Convert your points to cash rewards and contribute to a cleaner environment while earning money.
                        </p>
                    </div>

                    <div class="rounded-xl border bg-white p-8 shadow-md hover-scale">
                        <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center mb-6">
                            <i class="fas fa-users text-primary-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Multiple User Roles</h3>
                        <p class="text-gray-600">
                            Specialized interfaces for waste providers, collectors, storage keepers, and buyers. Our platform caters to all stakeholders in the waste management ecosystem with role-specific features.
                        </p>
                    </div>

                    <div class="rounded-xl border bg-white p-8 shadow-md hover-scale">
                        <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center mb-6">
                            <i class="fas fa-chart-line text-primary-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Analytics Dashboard</h3>
                        <p class="text-gray-600">
                            Track your contribution to waste management with detailed analytics. View your recycling history, earnings, and environmental impact through an intuitive dashboard.
                        </p>
                    </div>

                    <div class="rounded-xl border bg-white p-8 shadow-md hover-scale">
                        <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center mb-6">
                            <i class="fas fa-hand-holding-heart text-primary-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Environmental Impact</h3>
                        <p class="text-gray-600">
                            See the positive environmental impact of your recycling efforts. We quantify carbon footprint reduction, resource conservation, and other environmental benefits of your participation.
                        </p>
                    </div>
                </div>

                <div class="mt-16 text-center reveal">
                    <a
                        href="./dashboard/index.php"
                        onclick="toggleModal('registrationModal')"
                        class="inline-flex items-center gap-2 px-8 py-4 rounded-full bg-primary-500 text-white font-medium hover:bg-primary-600 transition-colors shadow-lg hover:shadow-xl">
                        Get Started Today <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimonials" class="py-24 sm:py-32 bg-gray-50 bg-pattern-grid">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center mb-16 reveal">
                    <span class="inline-block px-4 py-1 rounded-full bg-primary-50 text-primary-600 font-medium text-sm mb-3">
                        Testimonials
                    </span>
                    <h2 class="text-3xl md:text-4xl font-bold font-display mb-4">What Our Users Say</h2>
                    <p class="text-gray-600 text-lg">
                        Hear from people who are making a difference with Clean-Earn Ecosystem
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 reveal">
                    <div class="rounded-xl border bg-white p-8 shadow-md">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                                <i class="fas fa-user text-gray-400 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">Rajesh Kumar</h3>
                                <p class="text-gray-500">Household User</p>
                            </div>
                        </div>
                        <div class="mb-6">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 italic">
                            "I've been using Clean-Earn Ecosystem for 6 months now. It's amazing how much waste we were throwing away that could be recycled. Now I earn points every week and feel good about helping the environment."
                        </p>
                    </div>

                    <div class="rounded-xl border bg-white p-8 shadow-md">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                                <i class="fas fa-user text-gray-400 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">Priya Sharma</h3>
                                <p class="text-gray-500">Waste Collector</p>
                            </div>
                        </div>
                        <div class="mb-6">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 italic">
                            "This platform has given me a stable income and dignity in my work. The app makes it easy to find collection points, and I'm proud to be part of the solution for a cleaner future."
                        </p>
                    </div>

                    <div class="rounded-xl border bg-white p-8 shadow-md">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                                <i class="fas fa-user text-gray-400 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">Amit Patel</h3>
                                <p class="text-gray-500">Storage House Owner</p>
                            </div>
                        </div>
                        <div class="mb-6">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 italic">
                            "Managing a storage facility has become so much easier with this system. Everything is tracked digitally, and connecting with buyers is seamless. My business has grown 3x since joining."
                        </p>
                    </div>
                </div>

                <div class="mt-16 text-center reveal">
                    <a
                        href="#"
                        onclick="toggleModal('reviewModal')"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        <i class="fas fa-comment-dots"></i> Share Your Experience
                    </a>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="py-24 sm:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center mb-16 reveal">
                    <span class="inline-block px-4 py-1 rounded-full bg-primary-50 text-primary-600 font-medium text-sm mb-3">
                        Frequently Asked Questions
                    </span>
                    <h2 class="text-3xl md:text-4xl font-bold font-display mb-4">Got Questions?</h2>
                    <p class="text-gray-600 text-lg">
                        Find answers to common questions about our platform
                    </p>
                </div>

                <div class="max-w-3xl mx-auto reveal">
                    <div class="space-y-6">
                        <div class="faq-item">
                            <button class="faq-question w-full flex items-center justify-between bg-white p-6 rounded-xl border shadow-sm text-left">
                                <span class="text-lg font-bold">How do I schedule a waste pickup?</span>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                            </button>
                            <div class="faq-answer bg-white px-6 pb-6 rounded-b-xl border-l border-r border-b hidden">
                                <p class="text-gray-600">
                                    To schedule a waste pickup, simply sign up for an account, go to the "Schedule Pickup" section, and follow the prompts to specify the type and quantity of waste, along with your preferred date and time. Our system will match you with a collector in your area.
                                </p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question w-full flex items-center justify-between bg-white p-6 rounded-xl border shadow-sm text-left">
                                <span class="text-lg font-bold">How much can I earn from recycling my waste?</span>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                            </button>
                            <div class="faq-answer bg-white px-6 pb-6 rounded-b-xl border-l border-r border-b hidden">
                                <p class="text-gray-600">
                                    Your earnings depend on the type and quantity of waste you provide. Different materials have different rates, with metals typically fetching higher prices than paper or plastic. Check our Waste Types section for current rates. The average household can earn between ₹500-₹1500 per month.
                                </p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question w-full flex items-center justify-between bg-white p-6 rounded-xl border shadow-sm text-left">
                                <span class="text-lg font-bold">Do I need to clean the waste before collection?</span>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                            </button>
                            <div class="faq-answer bg-white px-6 pb-6 rounded-b-xl border-l border-r border-b hidden">
                                <p class="text-gray-600">
                                    Yes, waste should be reasonably clean and dry. For containers and bottles, a simple rinse is sufficient. Items with food residue or other contaminants might be rejected or fetch lower rates. Cleaner materials are easier to recycle and therefore more valuable.
                                </p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question w-full flex items-center justify-between bg-white p-6 rounded-xl border shadow-sm text-left">
                                <span class="text-lg font-bold">How do I become a waste collector?</span>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                            </button>
                            <div class="faq-answer bg-white px-6 pb-6 rounded-b-xl border-l border-r border-b hidden">
                                <p class="text-gray-600">
                                    To become a waste collector, register on our platform as a collector, provide the necessary documents for verification (ID proof, address proof, and vehicle information), complete a brief onboarding process, and start accepting collection requests in your area.
                                </p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question w-full flex items-center justify-between bg-white p-6 rounded-xl border shadow-sm text-left">
                                <span class="text-lg font-bold">How is the waste weighed and verified?</span>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                            </button>
                            <div class="faq-answer bg-white px-6 pb-6 rounded-b-xl border-l border-r border-b hidden">
                                <p class="text-gray-600">
                                    Collectors have portable scales for initial weight estimates. The final weight is determined at storage facilities using calibrated industrial scales. The weight is recorded in our system, and both providers and collectors receive notifications about the verified weight and calculated payment.
                                </p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question w-full flex items-center justify-between bg-white p-6 rounded-xl border shadow-sm text-left">
                                <span class="text-lg font-bold">How are the payments processed?</span>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                            </button>
                            <div class="faq-answer bg-white px-6 pb-6 rounded-b-xl border-l border-r border-b hidden">
                                <p class="text-gray-600">
                                    Payments are processed through our secure platform. Waste providers earn points that can be converted to cash and transferred to their linked bank accounts or mobile wallets. Payments are typically processed within 3-5 business days after waste verification.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-24 sm:py-32 bg-gradient-to-br from-primary-50 to-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center mb-16 reveal">
                    <span class="inline-block px-4 py-1 rounded-full bg-primary-50 text-primary-600 font-medium text-sm mb-3">
                        Get In Touch
                    </span>
                    <h2 class="text-3xl md:text-4xl font-bold font-display mb-4">Contact Us</h2>
                    <p class="text-gray-600 text-lg">
                        Have questions about Clean-Earn Ecosystem? Get in touch with our team.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-4xl mx-auto reveal">
                    <div>
                        <form class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Your Name</label>
                                <input type="text" id="name" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-3 px-4" placeholder="John Doe">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" id="email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-3 px-4" placeholder="john@example.com">
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                                <input type="text" id="subject" name="subject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-3 px-4" placeholder="What is this regarding?">
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                                <textarea id="message" name="message" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-3 px-4" placeholder="Your message here..."></textarea>
                            </div>

                            <div>
                                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    Send Message
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="flex flex-col justify-between">
                        <div class="space-y-8">
                            <div>
                                <h3 class="text-lg font-bold mb-2">Contact Information</h3>
                                <div class="space-y-4">
                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-map-marker-alt text-primary-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium">Our Address</h4>
                                            <p class="text-gray-600">123 Green Street, Eco City, 560001</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-phone-alt text-primary-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium">Phone Number</h4>
                                            <p class="text-gray-600">+91 123 456 7890</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-envelope text-primary-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium">Email Address</h4>
                                            <p class="text-gray-600">info@clean-earn.com</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold mb-2">Connect With Us</h3>
                                <div class="flex gap-4">
                                    <a href="#" class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center hover:bg-primary-200 transition-colors">
                                        <i class="fab fa-facebook-f text-primary-600"></i>
                                    </a>
                                    <a href="#" class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center hover:bg-primary-200 transition-colors">
                                        <i class="fab fa-twitter text-primary-600"></i>
                                    </a>
                                    <a href="#" class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center hover:bg-primary-200 transition-colors">
                                        <i class="fab fa-instagram text-primary-600"></i>
                                    </a>
                                    <a href="#" class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center hover:bg-primary-200 transition-colors">
                                        <i class="fab fa-linkedin-in text-primary-600"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 p-6 rounded-xl bg-white shadow-md border animate-pulse-glow">
                            <h3 class="text-lg font-bold mb-2">Need Immediate Help?</h3>
                            <p class="text-gray-600 mb-4">Our support team is available 24/7 to assist you.</p>
                            <a href="tel:+911234567890" class="inline-flex items-center gap-2 text-primary-600 font-medium hover:text-primary-700 transition-colors">
                                <i class="fas fa-headset"></i> Call Support Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-16 pb-8">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-primary-500 flex items-center justify-center text-white font-bold text-lg">
                            CEE
                        </div>
                        <span class="text-xl font-bold font-display">Clean-Earn</span>
                    </div>
                    <p class="text-gray-300 mb-6">
                        Transforming waste management through technology and incentives. Join us in building a cleaner, more sustainable future.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-bold mb-6">Quick Links</h3>
                    <ul class="space-y-4">
                        <li><a href="#home" class="text-gray-300 hover:text-white transition-colors">Home</a></li>
                        <li><a href="#how-it-works" class="text-gray-300 hover:text-white transition-colors">How It Works</a></li>
                        <li><a href="#waste-types" class="text-gray-300 hover:text-white transition-colors">Waste Types</a></li>
                        <li><a href="#features" class="text-gray-300 hover:text-white transition-colors">Features</a></li>
                        <li><a href="#testimonials" class="text-gray-300 hover:text-white transition-colors">Testimonials</a></li>
                        <li><a href="#faq" class="text-gray-300 hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#contact" class="text-gray-300 hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-bold mb-6">Resources</h3>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Blog</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Case Studies</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Sustainability Reports</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Waste Management Guide</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Recycling Tips</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Download App</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-bold mb-6">Newsletter</h3>
                    <p class="text-gray-300 mb-6">
                        Subscribe to our newsletter to get the latest updates, news, and tips on waste management and recycling.
                    </p>
                    <form class="flex gap-2">
                        <input
                            type="email"
                            placeholder="Your email address"
                            class="flex-1 px-4 py-2 rounded-lg border border-gray-700 bg-gray-800 text-white placeholder-gray-400 focus:outline-none focus:border-primary-500" />
                        <button
                            type="submit"
                            class="px-6 py-2 rounded-lg bg-primary-500 text-white hover:bg-primary-600 transition-colors">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="border-t border-gray-800 pt-8 mt-8 text-center md:text-left">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-gray-300">
                        &copy; 2023 Clean-Earn Ecosystem. All rights reserved.
                    </p>
                    <div class="flex gap-6">
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">Privacy Policy</a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">Terms of Service</a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modals -->
    <!-- Registration Modal -->
    <div id="registrationModal" class="modal fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
        <div class="modal-content bg-white rounded-xl max-w-md mx-auto p-8 relative">
            <button
                onclick="toggleModal('registrationModal')"
                class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                <i class="fas fa-times text-gray-600"></i>
            </button>
            <h2 class="text-2xl font-bold mb-6">Get Started</h2>
            <form class="space-y-6">
                <div>
                    <label for="regName" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input
                        type="text"
                        id="regName"
                        name="regName"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-3 px-4"
                        placeholder="John Doe" />
                </div>
                <div>
                    <label for="regEmail" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input
                        type="email"
                        id="regEmail"
                        name="regEmail"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-3 px-4"
                        placeholder="john@example.com" />
                </div>
                <div>
                    <label for="regPassword" class="block text-sm font-medium text-gray-700">Password</label>
                    <input
                        type="password"
                        id="regPassword"
                        name="regPassword"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-3 px-4"
                        placeholder="••••••••" />
                </div>
                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Sign Up
                    </button>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <a href="#" class="text-primary-600 hover:text-primary-700">Log in</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Waste Detail Modal -->
    <div id="wasteDetailModal" class="modal fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
        <div class="modal-content bg-white rounded-xl max-w-md mx-auto p-8 relative">
            <button
                onclick="toggleModal('wasteDetailModal')"
                class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                <i class="fas fa-times text-gray-600"></i>
            </button>
            <h2 id="wasteTitle" class="text-2xl font-bold mb-4"></h2>
            <p id="wasteDescription" class="text-gray-600 mb-6"></p>
            <div class="bg-primary-50 p-4 rounded-lg mb-6">
                <p class="text-sm text-gray-600">Rate:</p>
                <p id="wasteRate" class="text-2xl font-bold text-primary-600"></p>
            </div>
            <button
                onclick="toggleModal('wasteDetailModal')"
                class="w-full py-3 px-4 rounded-md bg-primary-500 text-white hover:bg-primary-600 transition-colors">
                Close
            </button>
        </div>
    </div>

    <!-- Demo Modal -->
    <div id="demoModal" class="modal fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
        <div class="modal-content bg-white rounded-xl max-w-2xl mx-auto p-8 relative">
            <button
                onclick="toggleModal('demoModal')"
                class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                <i class="fas fa-times text-gray-600"></i>
            </button>
            <h2 class="text-2xl font-bold mb-6">Watch How It Works</h2>
            <div class="aspect-w-16 aspect-h-9">
                <iframe
                    src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    class="rounded-lg"></iframe>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="reviewModal" class="modal fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
        <div class="modal-content bg-white rounded-xl max-w-md mx-auto p-8 relative">
            <button
                onclick="toggleModal('reviewModal')"
                class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                <i class="fas fa-times text-gray-600"></i>
            </button>
            <h2 class="text-2xl font-bold mb-6">Share Your Experience</h2>
            <form class="space-y-6">
                <div>
                    <label for="reviewName" class="block text-sm font-medium text-gray-700">Your Name</label>
                    <input
                        type="text"
                        id="reviewName"
                        name="reviewName"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-3 px-4"
                        placeholder="John Doe" />
                </div>
                <div>
                    <label for="reviewRating" class="block text-sm font-medium text-gray-700">Rating</label>
                    <div class="flex gap-1 mt-1">
                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400"></i>
                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400"></i>
                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400"></i>
                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400"></i>
                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400"></i>
                    </div>
                </div>
                <div>
                    <label for="reviewMessage" class="block text-sm font-medium text-gray-700">Your Review</label>
                    <textarea
                        id="reviewMessage"
                        name="reviewMessage"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-3 px-4"
                        placeholder="Share your experience..."></textarea>
                </div>
                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle Mobile Menu
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
            mobileMenu.classList.toggle('open');
            mobileMenuOverlay.classList.toggle('hidden');
        }

        // Toggle Modals
        function toggleModal(modalId, title = '', description = '', rate = '') {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('hidden');

            if (modalId === 'wasteDetailModal') {
                document.getElementById('wasteTitle').textContent = title;
                document.getElementById('wasteDescription').textContent = description;
                document.getElementById('wasteRate').textContent = rate;
            }
        }

        // Toggle FAQ Answers
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const answer = question.nextElementSibling;
                answer.classList.toggle('hidden');
                question.querySelector('i').classList.toggle('rotate-180');
            });
        });

        // Toggle Tabs
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.getAttribute('data-tab');
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                button.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Reveal Animations on Scroll
        const revealElements = document.querySelectorAll('.reveal');
        const observer = new IntersectionObserver(
            entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                    }
                });
            }, {
                threshold: 0.1
            }
        );
        revealElements.forEach(element => observer.observe(element));
    </script>
</body>

</html>