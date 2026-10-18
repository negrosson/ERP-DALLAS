<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
       class="-translate-x-full fixed inset-y-0 left-0 z-50 flex flex-col w-72 flex-shrink-0 h-screen px-4 py-6 overflow-y-auto bg-slate-950/95 md:bg-slate-900/90 backdrop-blur-2xl border-r border-slate-800/80 shadow-2xl transition-transform duration-200 ease-out md:relative md:translate-x-0" 
       x-cloak>
    
    <!-- Header: Logo & Branding -->
    <div class="flex items-center justify-between mb-8 px-2">
        <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" class="flex items-center gap-3 group">
            <div class="relative flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-cyan-400 shadow-[0_0_20px_rgba(99,102,241,0.35)] group-hover:shadow-[0_0_25px_rgba(99,102,241,0.6)] group-hover:scale-105 transition-all duration-300">
                <!-- Icono de Red / Cubo / Control ERP -->
                <svg class="w-6 h-6 text-white transition-transform duration-300 group-hover:rotate-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <!-- Indicador de estado en vivo -->
                <span class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-slate-950 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
            </div>
            <div>
                <span class="text-xl font-bold tracking-tight text-white group-hover:text-indigo-300 transition-colors block leading-tight">
                    DALLAS
                </span>
                <span class="text-[10px] font-semibold tracking-widest text-indigo-400 uppercase block">
                    Manager ERP
                </span>
            </div>
        </a>
        
        <!-- Botón cerrar para móvil -->
        <button @click="sidebarOpen = false" class="md:hidden p-2 text-slate-400 hover:text-white rounded-xl bg-slate-800/60 hover:bg-slate-800 transition-colors border border-slate-700/60">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="flex flex-col justify-between flex-1">
        <nav class="space-y-1" @click="
            let a = $event.target.closest('a');
            if (a) {
                sidebarOpen = false;
                if (!a.hasAttribute('target') && !$event.ctrlKey && !$event.metaKey && !$event.shiftKey) {
                    $event.preventDefault();
                    setTimeout(() => { window.location.href = a.href; }, 120);
                }
            }
        ">
            
            <!-- Dashboard Principal -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-200 group relative overflow-hidden {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-indigo-500/20 via-indigo-500/10 to-transparent text-white border-l-2 border-indigo-500 font-semibold shadow-[0_0_15px_rgba(99,102,241,0.15)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('dashboard') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(99,102,241,0.6)]' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="mx-3 text-xs tracking-wider uppercase font-medium">Dashboard</span>
                @if(request()->routeIs('dashboard'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_6px_rgba(99,102,241,0.9)]"></span>
                @endif
            </a>

            <!-- CATEGORÍA: ADMINISTRACIÓN -->
            <div class="pt-5 pb-1.5 px-3">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                    <p class="text-[10px] font-bold tracking-widest text-slate-500 uppercase">Administración</p>
                </div>
            </div>

            <!-- Proveedores -->
            <a href="{{ route('proveedores.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('proveedores.*') ? 'bg-gradient-to-r from-indigo-500/20 via-indigo-500/10 to-transparent text-white border-l-2 border-indigo-500 font-semibold shadow-[0_0_15px_rgba(99,102,241,0.15)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('proveedores.*') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(99,102,241,0.6)]' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="mx-3 text-xs tracking-wider uppercase font-medium">Proveedores</span>
                @if(request()->routeIs('proveedores.*'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_6px_rgba(99,102,241,0.9)]"></span>
                @endif
            </a>

            <!-- Catálogo Maestro -->
            <a href="{{ route('catalogo.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('catalogo.*') ? 'bg-gradient-to-r from-indigo-500/20 via-indigo-500/10 to-transparent text-white border-l-2 border-indigo-500 font-semibold shadow-[0_0_15px_rgba(99,102,241,0.15)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('catalogo.*') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(99,102,241,0.6)]' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span class="mx-3 text-xs tracking-wider uppercase font-medium">Catálogo Maestro</span>
                @if(request()->routeIs('catalogo.*'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_6px_rgba(99,102,241,0.9)]"></span>
                @endif
            </a>

            <!-- Mapeo de Códigos -->
            <a href="{{ route('mapeos.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('mapeos.*') ? 'bg-gradient-to-r from-indigo-500/20 via-indigo-500/10 to-transparent text-white border-l-2 border-indigo-500 font-semibold shadow-[0_0_15px_rgba(99,102,241,0.15)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('mapeos.*') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(99,102,241,0.6)]' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span class="mx-3 text-xs tracking-wider uppercase font-medium">Mapeo de Códigos</span>
                @if(request()->routeIs('mapeos.*'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_6px_rgba(99,102,241,0.9)]"></span>
                @endif
            </a>

            <!-- CATEGORÍA: INVENTARIO FEFO -->
            <div class="pt-5 pb-1.5 px-3">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                    <p class="text-[10px] font-bold tracking-widest text-slate-500 uppercase">Inventario FEFO</p>
                </div>
            </div>
            
            <!-- Bodegas con Sub-Menú Acordeón -->
            <div x-data="{ open: {{ request()->routeIs('bodegas.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('bodegas.*') ? 'bg-gradient-to-r from-indigo-500/20 via-indigo-500/10 to-transparent text-white border-l-2 border-indigo-500 font-semibold shadow-[0_0_15px_rgba(99,102,241,0.15)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('bodegas.*') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(99,102,241,0.6)]' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span class="mx-3 text-xs tracking-wider uppercase font-medium">Bodegas</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('bodegas.*') ? 'text-indigo-400' : 'text-slate-500' }}" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Sub-menú de Bodegas -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-150" 
                     x-transition:enter-start="opacity-0 -translate-y-1" 
                     x-transition:enter-end="opacity-100 translate-y-0" 
                     class="mt-1 ml-4 pl-3 border-l border-slate-800/80 space-y-1" 
                     style="display: none;">
                    
                    <a href="{{ route('bodegas.index') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium uppercase tracking-wide transition-all {{ request()->routeIs('bodegas.index') ? 'text-indigo-300 bg-indigo-500/10' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40' }}">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <span>Todas las Bodegas</span>
                    </a>

                    @foreach($sidebarBodegas as $sb)
                        @php $isCurrentBodega = request()->route('bodega') && request()->route('bodega')->id === $sb->id; @endphp
                        <a href="{{ route('bodegas.show', $sb) }}"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium uppercase tracking-wide transition-all {{ $isCurrentBodega ? 'text-cyan-300 bg-cyan-500/15 border-l-2 border-cyan-400 pl-2' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40' }}">
                            @if($sb->es_refrigerada)
                                <span class="text-cyan-400 shrink-0 text-xs">❄️</span>
                            @else
                                <span class="text-slate-400 shrink-0 text-xs">📦</span>
                            @endif
                            <span class="truncate">{{ $sb->nombre }}</span>
                            @if($isCurrentBodega)
                                <span class="ml-auto text-[9px] font-bold text-cyan-300 bg-cyan-500/20 border border-cyan-400/30 px-1.5 py-0.5 rounded uppercase tracking-wider shrink-0">ACTIVA</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Máquinas (Visicoolers) -->
            <a href="{{ route('maquinas.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('maquinas.*') ? 'bg-gradient-to-r from-indigo-500/20 via-indigo-500/10 to-transparent text-white border-l-2 border-indigo-500 font-semibold shadow-[0_0_15px_rgba(99,102,241,0.15)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('maquinas.*') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(99,102,241,0.6)]' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="mx-3 text-xs tracking-wider uppercase font-medium">Máquinas</span>
                @if(request()->routeIs('maquinas.*'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_6px_rgba(99,102,241,0.9)]"></span>
                @endif
            </a>
            
            <!-- Recepciones -->
            <a href="{{ route('recepciones.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('recepciones.*') ? 'bg-gradient-to-r from-indigo-500/20 via-indigo-500/10 to-transparent text-white border-l-2 border-indigo-500 font-semibold shadow-[0_0_15px_rgba(99,102,241,0.15)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('recepciones.*') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(99,102,241,0.6)]' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span class="mx-3 text-xs tracking-wider uppercase font-medium">Recepciones</span>
                @if(request()->routeIs('recepciones.*'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_6px_rgba(99,102,241,0.9)]"></span>
                @endif
            </a>

            <!-- Vencimiento de Productos (Radar FEFO) -->
            <a href="{{ route('lotes.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('lotes.*') ? 'bg-gradient-to-r from-indigo-500/20 via-indigo-500/10 to-transparent text-white border-l-2 border-indigo-500 font-semibold shadow-[0_0_15px_rgba(99,102,241,0.15)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('lotes.*') ? 'text-rose-400 drop-shadow-[0_0_8px_rgba(244,63,94,0.6)]' : 'text-slate-500 group-hover:text-rose-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="mx-3 text-xs tracking-wider uppercase font-medium">Vencimiento FEFO</span>
                <span class="ml-auto text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-rose-500/20 text-rose-300 border border-rose-500/30">
                    FEFO
                </span>
            </a>

            <!-- CATEGORÍA: MOVIMIENTOS DE STOCK -->
            <div class="pt-5 pb-1.5 px-3">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <p class="text-[10px] font-bold tracking-widest text-slate-500 uppercase">Movimientos de Stock</p>
                </div>
            </div>

            <!-- Carga de Ventas -->
            <a href="{{ route('ventas.index') }}" 
               class="flex items-center px-3.5 py-2.5 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('ventas.*') ? 'bg-gradient-to-r from-indigo-500/20 via-indigo-500/10 to-transparent text-white border-l-2 border-indigo-500 font-semibold shadow-[0_0_15px_rgba(99,102,241,0.15)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('ventas.*') ? 'text-emerald-400 drop-shadow-[0_0_8px_rgba(16,185,129,0.6)]' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="mx-3 text-xs tracking-wider uppercase font-medium">Carga de Ventas</span>
                @if(request()->routeIs('ventas.*'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(16,185,129,0.9)]"></span>
                @endif
            </a>

        </nav>

        <!-- User Profile & Logout (Executive Card) -->
        <div class="mt-8 pt-4 border-t border-slate-800/80">
            <div class="p-3 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-md mb-2">
                <div class="flex items-center gap-3">
                    <div class="relative w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-cyan-400 flex items-center justify-center text-white font-bold text-sm shadow-md shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 rounded-full border-2 border-slate-900"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-xs font-bold text-white truncate block leading-tight">
                            {{ Auth::user()->name }}
                        </span>
                        <span class="text-[10px] text-slate-400 block truncate">
                            Administrador General
                        </span>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}"
                   @click="sidebarOpen = false"
                   onclick="event.preventDefault(); this.closest('form').submit();"
                   class="flex items-center justify-center gap-2 w-full px-3 py-2 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-all duration-200 rounded-xl text-xs font-medium border border-rose-500/20">
                    <svg class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Cerrar Sesión</span>
                </a>
            </form>
        </div>
    </div>
</aside>
