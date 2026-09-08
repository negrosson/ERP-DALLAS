<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex flex-col w-72 flex-shrink-0 h-screen px-5 py-8 overflow-y-auto bg-slate-900 border-r border-slate-800 shadow-2xl transition-transform duration-200 ease-out md:relative md:translate-x-0">
    
    <!-- Logo & Branding -->
    <div class="flex items-center justify-between mb-10 px-2">
        <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" class="flex items-center gap-3 group">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-cyan-400 shadow-lg shadow-indigo-500/30 group-hover:shadow-indigo-500/50 transition-all duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <span class="text-2xl font-bold tracking-tight text-white group-hover:text-indigo-400 transition-colors uppercase">
                Dallas<span class="font-light text-slate-400"> Manager</span>
            </span>
        </a>
        <button @click="sidebarOpen = false" class="md:hidden p-1 text-slate-400 hover:text-white rounded-md hover:bg-slate-800">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="flex flex-col justify-between flex-1">
        <nav class="space-y-1.5" @click="if($event.target.closest('a')) sidebarOpen = false">
            
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('dashboard') ? 'bg-teal-900/50 text-teal-400 shadow-[inset_4px_0_0_0_rgba(20,184,166,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('dashboard') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="mx-3 font-medium uppercase text-sm tracking-wide">Dashboard</span>
            </a>

            <!-- Sección: Administración -->
            <div class="pt-6 pb-2 px-4">
                <p class="text-xs font-bold tracking-widest text-slate-500 uppercase">Administración</p>
            </div>

            <!-- Proveedores -->
            <a href="{{ route('proveedores.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('proveedores.*') ? 'bg-teal-900/50 text-teal-400 shadow-[inset_4px_0_0_0_rgba(20,184,166,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('proveedores.*') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="mx-3 font-medium uppercase text-sm tracking-wide">Proveedores</span>
            </a>

            
            <!-- Mapeo de Códigos -->
            <a href="{{ route('mapeos.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('mapeos.*') ? 'bg-teal-900/50 text-teal-400 shadow-[inset_4px_0_0_0_rgba(20,184,166,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('mapeos.*') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span class="mx-3 font-medium uppercase text-sm tracking-wide">Mapeo de Códigos</span>
            </a>

            <!-- Sección: Inventario -->
            <div class="pt-6 pb-2 px-4">
                <p class="text-xs font-bold tracking-widest text-slate-500 uppercase">Inventario FEFO</p>
            </div>
            
            <!-- Bodegas con sub-menú -->
            <div x-data="{ open: {{ request()->routeIs('bodegas.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('bodegas.*') ? 'bg-teal-900/50 text-teal-400 shadow-[inset_4px_0_0_0_rgba(20,184,166,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('bodegas.*') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span class="mx-3 font-medium uppercase text-sm tracking-wide">Bodegas</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200 text-slate-500" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Sub-menú: lista de bodegas activas -->
                <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="mt-1 ml-4 space-y-1" style="display: none;">
                    <!-- Enlace al listado general -->
                    <a href="{{ route('bodegas.index') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium uppercase tracking-wide transition-all {{ request()->routeIs('bodegas.index') ? 'text-teal-400 bg-teal-900/30' : 'text-slate-500 hover:text-slate-200 hover:bg-slate-800/40' }}">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Todas las bodegas
                    </a>
                    <!-- Sub-items: una bodega por línea -->
                    @foreach($sidebarBodegas as $sb)
                        @php $isCurrentBodega = request()->route('bodega') && request()->route('bodega')->id === $sb->id; @endphp
                        <a href="{{ route('bodegas.show', $sb) }}"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium uppercase tracking-wide transition-all {{ $isCurrentBodega ? 'text-teal-300 bg-teal-900/40 border-l-2 border-teal-400 pl-2' : 'text-slate-500 hover:text-slate-200 hover:bg-slate-800/40' }}">
                            @if($sb->es_refrigerada)
                                <span class="text-cyan-400 shrink-0">❄</span>
                            @else
                                <span class="text-slate-600 shrink-0">📦</span>
                            @endif
                            <span class="truncate">{{ $sb->nombre }}</span>
                            @if($isCurrentBodega)
                                <span class="ml-auto text-[9px] font-bold text-teal-400 bg-teal-900/50 border border-teal-700 px-1.5 py-0.5 rounded uppercase tracking-wider shrink-0">AQUÍ</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Máquinas (Visicoolers) -->
            <a href="{{ route('maquinas.index') }}" class="mt-2 flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('maquinas.*') ? 'bg-teal-900/50 text-teal-400 shadow-[inset_4px_0_0_0_rgba(20,184,166,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('maquinas.*') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="mx-3 font-medium uppercase text-sm tracking-wide">Máquinas</span>
            </a>
            
            <!-- Recepciones -->
            <a href="{{ route('recepciones.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('recepciones.index', 'recepciones.show', 'recepciones.edit') ? 'bg-teal-900/50 text-teal-400 shadow-[inset_4px_0_0_0_rgba(20,184,166,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('recepciones.index', 'recepciones.show', 'recepciones.edit') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span class="mx-3 font-medium uppercase text-sm tracking-wide">Recepciones</span>
            </a>


            <!-- Semaforo Preventivo -->
            <a href="{{ route('lotes.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('lotes.*') ? 'bg-teal-900/50 text-teal-400 shadow-[inset_4px_0_0_0_rgba(20,184,166,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('lotes.*') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="mx-3 font-bold uppercase text-xs tracking-wide">Vencimiento de Productos</span>
            </a>

            <!-- Sección: Movimientos y Salidas -->
            <div class="pt-6 pb-2 px-4">
                <p class="text-xs font-bold tracking-widest text-slate-500 uppercase">Movimientos de Stock</p>
            </div>

            <!-- Ventas (CSV) -->
            <a href="{{ route('ventas.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('ventas.*') ? 'bg-teal-900/50 text-teal-400 shadow-[inset_4px_0_0_0_rgba(20,184,166,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('ventas.*') ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="mx-3 font-medium uppercase text-sm tracking-wide">Carga de Ventas</span>
            </a>

        </nav>

        <!-- User Profile & Logout -->
        <div class="mt-10 pt-6 border-t border-slate-800">
            <div class="flex items-center px-4 mb-4">
                <div class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center text-indigo-400 font-bold border border-slate-700 shadow-sm">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="mx-3 flex flex-col">
                    <span class="text-sm font-bold text-slate-200 leading-tight uppercase">{{ Auth::user()->name }}</span>
                    <span class="text-xs text-slate-500 uppercase">Administrador</span>
                </div>
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}"
                        @click="sidebarOpen = false"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                        class="flex items-center px-4 py-3 text-red-400 transition-all duration-200 rounded-xl hover:bg-red-500/10 hover:text-red-300 group">
                    <svg class="w-5 h-5 transition-transform duration-200 group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="mx-3 font-medium text-sm">Cerrar Sesión</span>
                </a>
            </form>
        </div>
    </div>
</aside>
