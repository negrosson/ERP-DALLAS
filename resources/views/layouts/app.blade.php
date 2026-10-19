
{{-- ==============================================================================
     PLANTILLA MAESTRA DE LA APLICACIÓN (resources/views/layouts/app.blade.php)
     ==============================================================================
     Provee el armazón HTML5 principal para todos los usuarios autenticados.
     Integra el sistema de diseño oscuro (Cyber-Executive), la barra lateral (Sidebar),
     el encabezado móvil responsivo, el botón y modal de búsqueda omnibox (Ctrl+K),
     el registro de Service Worker para PWA y la inyección de assets de Vite.
     ============================================================================== --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        {{-- Metadatos obligatorios de codificación y renderizado responsivo --}}
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        {{-- Token CSRF para seguridad en peticiones AJAX/Fetch (utilizado por el buscador global y modales) --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        
        {{-- Configuración de Progressive Web App (PWA) para instalación en dispositivos móviles y escritorio --}}
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#4f46e5">
        <link rel="apple-touch-icon" href="/manifest.json">

        {{-- Tipografía Oficial: Outfit (Google Fonts) en pesos 300 a 700 --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        {{-- Compilación e inyección de hojas de estilo (Tailwind) y JavaScript (Alpine.js) vía Vite --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        {{-- Pila para scripts o estilos adicionales requeridos por vistas específicas --}}
        @stack('head')
    </head>
    <body class="font-sans antialiased text-slate-200 bg-slate-950 selection:bg-indigo-500/30">
        
        {{-- Destellos Ambientales de Fondo (Atmósfera visual oscura con efecto Glassmorphism) --}}
        <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
            {{-- Destello radial superior izquierdo en tono índigo --}}
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-600/10 blur-[120px] animate-float"></div>
            {{-- Destello radial inferior derecho en tono cian con retraso de animación --}}
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-cyan-600/10 blur-[120px] animate-float" style="animation-delay: 2s;"></div>
        </div>

        {{-- Contenedor Raíz de Interfaz con Estado Alpine para el Sidebar Móvil --}}
        <div x-data="{ sidebarOpen: false }" class="relative z-10 flex h-screen overflow-hidden bg-transparent">
            
            {{-- Capa de oscurecimiento (Backdrop Overlay) para cerrar el sidebar en dispositivos móviles al hacer clic afuera --}}
            <div x-show="sidebarOpen" x-transition.opacity.duration.150ms class="fixed inset-0 z-40 bg-slate-950/80 backdrop-blur-md md:hidden" @click="sidebarOpen = false" style="display: none;"></div>

            {{-- Inclusión del Menú Lateral de Navegación del Sistema --}}
            @include('layouts.sidebar')

            {{-- Área de Contenido Principal (Flexbox vertical con scroll interno) --}}
            <div class="flex flex-col flex-1 w-full overflow-hidden min-w-0">
                
                {{-- Encabezado Superior Exclusivo para Pantallas Móviles (Botón Hamburguesa + Logo + Búsqueda) --}}
                <div class="md:hidden flex items-center justify-between px-4 py-3 bg-slate-950/90 backdrop-blur-xl border-b border-slate-800/80 shadow-md z-30 sticky top-0">
                    <div class="flex items-center gap-3">
                        {{-- Botón para desplegar el Sidebar lateral en móviles --}}
                        <button @click="sidebarOpen = true" class="p-2 -ml-1 text-slate-400 hover:text-white rounded-xl bg-slate-900/80 border border-slate-800 focus:outline-none transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        {{-- Logotipo y marca para versión móvil --}}
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-indigo-600 to-cyan-400 flex items-center justify-center text-white text-xs font-bold shadow-md shadow-indigo-500/25">
                                D
                            </div>
                            <span class="text-base font-bold text-white tracking-tight">
                                Dallas <span class="text-xs font-semibold text-indigo-400">Manager</span>
                            </span>
                        </div>
                    </div>
                    {{-- Acceso directo móvil al buscador omnibox --}}
                    <button @click="$dispatch('open-search-modal')" class="p-2 text-slate-400 hover:text-white rounded-xl bg-slate-900/80 border border-slate-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>

                {{-- Encabezado Opcional de Página (Inyectado desde la vista hija mediante el slot $header) --}}
                @isset($header)
                    <header class="z-10 py-3.5 bg-slate-900/50 backdrop-blur-md shadow-[0_4px_30px_rgba(0,0,0,0.3)] border-b border-slate-800/80">
                        <div class="px-3 sm:px-6 lg:px-8 mx-auto w-full">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                {{-- Cuerpo Principal de la Página (Inyección del slot $slot con scroll vertical fluido) --}}
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-950">
                    <div class="w-full px-3 sm:px-6 lg:px-8 py-4 sm:py-6 mx-auto">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        {{-- Registro del Service Worker para capacidades Progressive Web App (PWA) --}}
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then(registration => {
                            console.log('ServiceWorker registration successful with scope: ', registration.scope);
                        });
                });
            }
        </script>

        {{-- Botón Flotante para Búsqueda Global Omnibox (Atajo Universal Ctrl+K) --}}
        <div x-data="{}" class="fixed bottom-24 sm:bottom-6 right-6 z-[99]">
            <button
                @click="$dispatch('open-search-modal')"
                @keydown.ctrl.k.window.prevent="$dispatch('open-search-modal')"
                title="Búsqueda global (Ctrl+K)"
                class="group flex items-center gap-3 bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white border border-slate-700 hover:border-indigo-500 shadow-2xl shadow-black/50 rounded-full sm:rounded-2xl p-3 sm:px-4 sm:py-3 transition-all duration-300 hover:scale-105 hover:shadow-indigo-500/30"
            >
                {{-- Icono de Lupa con micro-rotación en hover --}}
                <svg class="w-5 h-5 transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="hidden sm:inline text-sm font-semibold tracking-wide">¿Buscas algo?</span>
                {{-- Indicador visual del atajo de teclado Ctrl+K --}}
                <span class="hidden sm:flex items-center gap-1 text-[10px] font-mono text-slate-500 group-hover:text-indigo-300 transition-colors bg-slate-900/50 group-hover:bg-indigo-900/50 border border-slate-700 group-hover:border-indigo-700 rounded px-1.5 py-0.5">
                    <span>Ctrl</span><span>+</span><span>K</span>
                </span>
            </button>
        </div>

        {{-- Modal Reactivo de Búsqueda Global (Controlado por Alpine.js) --}}
        <div x-data="{
                open: false,         // Visibilidad del modal
                query: '',           // Texto digitado por el usuario
                productos: [],       // Lista reactiva de coincidencias en catálogo
                lotes: [],           // Lista reactiva de coincidencias en lotes de stock
                loading: false,      // Estado de carga para mostrar indicador visual
                search() {
                    // Si el texto es menor a 2 caracteres se limpian los resultados y se evita la petición
                    if(this.query.length < 2) {
                        this.productos = [];
                        this.lotes = [];
                        return;
                    }
                    this.loading = true;
                    // Consulta asíncrona hacia SearchController@global
                    fetch('/search?q=' + encodeURIComponent(this.query))
                        .then(res => res.json())
                        .then(data => {
                            this.productos = data.productos;
                            this.lotes = data.lotes;
                            this.loading = false;
                        });
                }
            }"
             {{-- Escucha del evento global open-search-modal para abrir y enfocar el input automáticamente --}}
             @open-search-modal.window="open = true; setTimeout(() => $refs.searchInput.focus(), 100)"
             x-show="open" 
             class="fixed inset-0 z-[100] flex items-start justify-center pt-20 bg-black/60 backdrop-blur-sm"
             style="display: none;">
            
            {{-- Cuadro de diálogo del buscador con detección de clic externo para autocierre --}}
            <div @click.outside="open = false" class="bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden" x-transition>
                
                {{-- Barra superior de entrada de texto --}}
                <div class="p-4 border-b border-slate-800 flex items-center bg-slate-800/50">
                    <svg class="w-6 h-6 text-slate-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    {{-- Input reactivo con debounce de 300ms para no saturar el servidor con peticiones --}}
                    <input type="text" x-ref="searchInput" x-model="query" @input.debounce.300ms="search" placeholder="¿Buscas algún producto, SKU o lote?" class="w-full bg-transparent border-none text-white text-lg focus:ring-0 placeholder-slate-500">
                    {{-- Botón para cerrar el modal manualmente --}}
                    <button @click="open = false" class="text-slate-500 hover:text-white p-1 rounded-md transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                
                {{-- Contenedor de Resultados de Búsqueda --}}
                <div class="p-4 max-h-[60vh] overflow-y-auto custom-scrollbar">
                    
                    {{-- Spinner de carga activa mientras se resuelve la petición --}}
                    <div x-show="loading" class="text-center py-8 text-slate-400">
                        <svg class="animate-spin h-8 w-8 mx-auto text-indigo-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Buscando...
                    </div>

                    {{-- Mensaje de ausencia de coincidencias --}}
                    <div x-show="!loading && query.length >= 2 && productos.length === 0 && lotes.length === 0" class="text-center py-8 text-slate-500">
                        No se encontraron resultados para "<span x-text="query" class="text-slate-300"></span>".
                    </div>

                    {{-- Sección: Coincidencias en el Catálogo Maestro de Productos --}}
                    <div x-show="productos.length > 0" class="mb-6">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">En Catálogo</h4>
                        <div class="space-y-2">
                            <template x-for="prod in productos" :key="prod.id">
                                <a :href="'/catalogo/' + prod.id" class="flex items-center justify-between p-3 rounded-xl bg-slate-800/50 hover:bg-slate-700/50 border border-slate-700 transition-colors group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-slate-200 font-medium group-hover:text-indigo-300 transition-colors" x-text="prod.nombre"></p>
                                            <p class="text-slate-500 text-xs font-mono" x-text="prod.sku"></p>
                                        </div>
                                    </div>
                                    <span class="text-xs text-emerald-500 font-mono uppercase tracking-widest">Ver detalle →</span>
                                </a>
                            </template>
                        </div>
                    </div>

                    {{-- Sección: Coincidencias en Lotes de Stock Activos --}}
                    <div x-show="lotes.length > 0">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">En Inventario (Lotes)</h4>
                        <div class="space-y-2">
                            <template x-for="lote in lotes" :key="lote.id">
                                <a href="/lotes" class="flex items-center justify-between p-3 rounded-xl bg-slate-800/50 hover:bg-slate-700/50 border border-slate-700 transition-colors group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-slate-200 font-medium group-hover:text-emerald-300 transition-colors">
                                                <span x-text="lote.producto_nombre"></span> 
                                                <span class="text-slate-500 font-mono text-xs ml-2">#<span x-text="lote.id"></span></span>
                                            </p>
                                            <p class="text-slate-400 text-xs mt-0.5">
                                                <span class="bg-slate-900 px-1.5 py-0.5 rounded text-slate-300 mr-2" x-text="lote.bodega_nombre"></span>
                                                Vence: <span class="text-orange-400" x-text="lote.vencimiento"></span>
                                            </p>
                                        </div>
                                    </div>
                                    {{-- Cantidad disponible en el lote --}}
                                    <span class="text-white font-bold bg-slate-900 border border-slate-700 px-2 py-1 rounded-lg text-sm"><span x-text="lote.cantidad"></span> UN</span>
                                </a>
                            </template>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        {{-- Slot opcional para inyectar scripts al pie de la página --}}
        {{ $scripts ?? '' }}
    </body>
</html>

