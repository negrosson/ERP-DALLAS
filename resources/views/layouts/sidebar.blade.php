<aside class="flex flex-col w-72 h-screen px-5 py-8 overflow-y-auto bg-slate-900 border-r border-slate-800 shadow-2xl transition-all duration-300 z-20">
    
    <!-- Logo & Branding -->
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 mb-10 px-2 group">
        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-cyan-400 shadow-lg shadow-indigo-500/30 group-hover:shadow-indigo-500/50 transition-all duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        <span class="text-2xl font-bold tracking-tight text-white group-hover:text-indigo-400 transition-colors">
            Dallas<span class="font-light text-slate-400"> Manager</span>
        </span>
    </a>

    <div class="flex flex-col justify-between flex-1">
        <nav class="space-y-1.5">
            
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('dashboard') ? 'bg-indigo-600/10 text-indigo-400 shadow-[inset_4px_0_0_0_rgba(79,70,229,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('dashboard') ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="mx-3 font-medium">Dashboard</span>
            </a>

            <!-- Sección: Administración -->
            <div class="pt-6 pb-2 px-4">
                <p class="text-xs font-bold tracking-widest text-slate-500 uppercase">Administración</p>
            </div>

            <!-- Proveedores -->
            <a href="{{ route('proveedores.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('proveedores.*') ? 'bg-indigo-600/10 text-indigo-400 shadow-[inset_4px_0_0_0_rgba(79,70,229,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('proveedores.*') ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="mx-3 font-medium">Proveedores</span>
            </a>

            <!-- Catálogo Maestro -->
            <a href="{{ route('catalogo.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('catalogo.*') ? 'bg-indigo-600/10 text-indigo-400 shadow-[inset_4px_0_0_0_rgba(79,70,229,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('catalogo.*') ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span class="mx-3 font-medium">Catálogo Maestro</span>
            </a>
            
            <!-- Mapeo de Códigos -->
            <a href="{{ route('mapeos.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('mapeos.*') ? 'bg-indigo-600/10 text-indigo-400 shadow-[inset_4px_0_0_0_rgba(79,70,229,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('mapeos.*') ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span class="mx-3 font-medium">Mapeo de Códigos</span>
            </a>

            <!-- Sección: Inventario -->
            <div class="pt-6 pb-2 px-4">
                <p class="text-xs font-bold tracking-widest text-slate-500 uppercase">Inventario FEFO</p>
            </div>
            
            <!-- Bodegas -->
            <a href="{{ route('bodegas.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('bodegas.*') ? 'bg-indigo-600/10 text-indigo-400 shadow-[inset_4px_0_0_0_rgba(79,70,229,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('bodegas.*') ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="mx-3 font-medium">Bodegas</span>
            </a>
            
            <!-- Recepciones -->
            <a href="{{ route('recepciones.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('recepciones.*') ? 'bg-indigo-600/10 text-indigo-400 shadow-[inset_4px_0_0_0_rgba(79,70,229,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('recepciones.*') ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span class="mx-3 font-medium">Ingreso Mercadería</span>
            </a>
            
            <!-- Semaforo Preventivo -->
            <a href="{{ route('lotes.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('lotes.*') ? 'bg-indigo-600/10 text-indigo-400 shadow-[inset_4px_0_0_0_rgba(79,70,229,1)]' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('lotes.*') ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="mx-3 font-bold tracking-wide uppercase text-xs">Semáforo Preventivo</span>
            </a>

        </nav>

        <!-- User Profile & Logout -->
        <div class="mt-10 pt-6 border-t border-slate-800">
            <div class="flex items-center px-4 mb-4">
                <div class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center text-indigo-400 font-bold border border-slate-700 shadow-sm">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="mx-3 flex flex-col">
                    <span class="text-sm font-bold text-slate-200 leading-tight">{{ Auth::user()->name }}</span>
                    <span class="text-xs text-slate-500">Administrador</span>
                </div>
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}"
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
