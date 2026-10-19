{{-- =========================================================================
     COMPONENTE DE BARRA DE NAVEGACIÓN SUPERIOR (NAVBAR)
     Sistema: ERP Dallas
     Propósito: Barra de navegación superior con logotipo, accesos directos, disparador de búsqueda global, menú desplegable de perfil de usuario y navegación adaptativa para dispositivos móviles.
     Framework: Laravel Blade + Tailwind CSS + Alpine.js
     ========================================================================= --}}

{{-- Contenedor principal del navbar con estado Alpine 'open' para el menú móvil y borde inferior sutil --}}
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Menú de Navegación Principal (Escritorio / Pantallas Grandes) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Fila flexible con altura estándar de 64px (h-16) y distribución justificada entre extremos --}}
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logotipo Institucional del Sistema -->
                <div class="shrink-0 flex items-center">
                    {{-- Enlace al panel de control principal (Dashboard) --}}
                    <a href="{{ route('dashboard') }}">
                        {{-- Componente vectorial SVG del logotipo de la aplicación --}}
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Enlaces de Navegación Primarios (Visibles desde pantallas medianas sm en adelante) -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    {{-- Enlace dinámico al Dashboard con indicador visual de ruta activa --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Disparador de Modal de Búsqueda Global (Global Spotlight Search Trigger) -->
            <div class="hidden sm:flex sm:items-center sm:ms-auto">
                {{-- Botón interactivo que emite el evento global Alpine 'open-search-modal' --}}
                <button @click="$dispatch('open-search-modal')" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors focus:outline-none bg-gray-50 hover:bg-indigo-50 rounded-full group" title="¿Buscas algo?">
                    {{-- Icono SVG de lupa con microinteracción de zoom en hover --}}
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
            </div>

            <!-- Menú Desplegable de Configuración y Perfil de Usuario (Settings Dropdown) -->
            <div class="hidden sm:flex sm:items-center sm:ms-4">
                {{-- Componente de dropdown alineado a la derecha con ancho w-48 --}}
                <x-dropdown align="right" width="48">
                    {{-- Disparador del Dropdown: Muestra el nombre del usuario autenticado y una flecha indicadora --}}
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            {{-- Nombre del usuario actualmente autenticado en la sesión --}}
                            <div>{{ Auth::user()->name }}</div>

                            {{-- Icono chevron descendente --}}
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    {{-- Contenido desplegable: Enlace al perfil y acción segura de cierre de sesión --}}
                    <x-slot name="content">
                        {{-- Enlace a la edición del perfil de usuario --}}
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Formulario seguro para Cierre de Sesión (Logout) vía método POST y token CSRF -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            {{-- Enlace que previene la navegación GET por defecto y envía el formulario POST programáticamente --}}
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Botón Hamburguesa para Dispositivos Móviles (Visible solo en pantallas inferiores a sm) -->
            <div class="-me-2 flex items-center sm:hidden">
                {{-- Al hacer clic alterna el valor booleano de 'open' para expandir o contraer el menú móvil --}}
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        {{-- Icono de 3 líneas horizontales cuando el menú está cerrado --}}
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        {{-- Icono de 'X' cuando el menú está abierto --}}
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú Desplegable Responsivo para Pantallas Móviles -->
    {{-- Controlado reactivamente por Alpine.js: visible ('block') si open es true, oculto ('hidden') si false --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        {{-- Enlaces de navegación rápida móvil --}}
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Sección de Configuración de Usuario y Datos de Cuenta Móvil -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            {{-- Datos de identificación del usuario conectado --}}
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            {{-- Opciones de cuenta en menú móvil --}}
            <div class="mt-3 space-y-1">
                {{-- Enlace al perfil --}}
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Formulario de Logout para versión móvil -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    {{-- Enlace interactivo que dispara el submit del formulario POST --}}
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
