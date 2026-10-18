<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <div class="w-2 sm:w-2.5 h-6 sm:h-8 bg-gradient-to-b from-indigo-500 to-cyan-400 rounded-full shadow-[0_0_12px_rgba(99,102,241,0.6)] shrink-0"></div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold tracking-tight text-white leading-tight">
                        {{ __('Centro de Control & Operaciones') }}
                    </h2>
                </div>
                <p class="text-[11px] sm:text-xs md:text-sm text-slate-400 mt-1 font-normal tracking-normal pl-4 sm:pl-5">
                    Monitoreo en tiempo real de inventario, balance multisede y radar inteligente de vencimientos FEFO.
                </p>
            </div>

            <!-- Badges de sincronización y accesos directos -->
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[11px] sm:text-xs font-medium backdrop-blur-md shadow-[0_0_15px_rgba(16,185,129,0.15)]">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>100% Sincronizado en Vivo</span>
                </div>

                <a href="{{ route('recepciones.index') }}" class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium rounded-xl transition-all duration-200 shadow-lg shadow-indigo-600/20 border border-indigo-400/30">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Recepciones</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Import Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Contenedor Principal con Alpine Component -->
    <div x-data="dashboardHub()" class="w-full space-y-6 sm:space-y-8 relative overflow-x-clip font-sans text-slate-100">
        
        <!-- Ambient Radial Glows (Sleek dark executive vibe) -->
        <div class="absolute top-0 left-1/4 w-[600px] h-[400px] bg-indigo-600/10 rounded-full blur-[140px] pointer-events-none -z-0"></div>
        <div class="absolute top-1/3 right-10 w-[500px] h-[500px] bg-cyan-600/10 rounded-full blur-[150px] pointer-events-none -z-0"></div>
        <div class="absolute bottom-10 left-1/3 w-[600px] h-[400px] bg-rose-600/5 rounded-full blur-[160px] pointer-events-none -z-0"></div>

        <div class="w-full space-y-6 sm:space-y-8 relative z-10">
            
            <!-- ALERTA DE SISTEMA: Facturas Pendientes de Conciliación -->
            @if(count($recepcionesPendientes) > 0)
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-500/15 via-rose-500/10 to-slate-900/80 border border-amber-500/30 p-5 md:p-6 backdrop-blur-xl shadow-2xl shadow-rose-950/20">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="p-3 bg-gradient-to-br from-amber-500/20 to-rose-500/20 rounded-2xl border border-amber-400/30 text-amber-300 shadow-[0_0_20px_rgba(245,158,11,0.25)] shrink-0">
                            <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 text-[10px] font-bold tracking-wider uppercase border border-amber-400/30">Acción Requerida</span>
                                <h4 class="text-base font-semibold text-white tracking-normal">
                                    {{ count($recepcionesPendientes) }} {{ count($recepcionesPendientes) === 1 ? 'Factura Pendiente de Conciliar' : 'Facturas Pendientes de Conciliar' }}
                                </h4>
                            </div>
                            <p class="text-xs text-slate-300 mt-1 font-normal tracking-normal max-w-2xl">
                                Los productos ingresaron al sistema pero están en estado de cuarentena preventiva esperando la asignación de fechas de vencimiento para habilitar su venta.
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 flex items-center gap-3">
                        <a href="{{ route('recepciones.index') }}?estado=PENDIENTE_FECHA" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-400 hover:to-rose-500 text-white font-medium text-xs tracking-normal shadow-lg shadow-rose-900/30 transition-all duration-200 border border-white/20">
                            <span>Conciliar Fechas Ahora</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- ROW 1: EXECUTIVE KPI CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                
                <!-- KPI 1: Valorización Total -->
                <div class="relative overflow-hidden rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl hover:border-indigo-500/40 transition-all duration-300 group shadow-lg">
                    <div class="absolute -top-12 -right-12 w-28 h-28 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/20 transition-all"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400 tracking-wider">VALORIZACIÓN TOTAL</span>
                        <div class="p-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl lg:text-4xl font-bold tracking-tight text-white">
                            ${{ number_format($valorTotal, 0, ',', '.') }}
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                            <span>Unidades en existencia:</span>
                            <span class="font-semibold text-slate-200">{{ number_format($totalUnidades, 0, ',', '.') }} UN</span>
                        </div>
                    </div>
                    <!-- Mini visual distribution bar -->
                    <div class="mt-4 h-1.5 w-full bg-slate-800 rounded-full overflow-hidden flex">
                        @php
                            $colorCycle = ['bg-indigo-500', 'bg-cyan-400', 'bg-emerald-400', 'bg-purple-500'];
                        @endphp
                        @foreach($bodegasDetalle as $idx => $b)
                            @php
                                $pct = $totalUnidades > 0 ? ($b['unidades'] / $totalUnidades) * 100 : 0;
                            @endphp
                            @if($pct > 0)
                                <div class="{{ $colorCycle[$idx % 4] }}" style="width: {{ $pct }}%" title="{{ $b['nombre'] }}: {{ number_format($b['unidades'], 0) }} un"></div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- KPI 2: Catálogo de Productos -->
                <a href="{{ route('catalogo.index') }}" class="relative overflow-hidden rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl hover:border-cyan-500/40 transition-all duration-300 group shadow-lg block">
                    <div class="absolute -top-12 -right-12 w-28 h-28 bg-cyan-500/10 rounded-full blur-2xl group-hover:bg-cyan-500/20 transition-all"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400 tracking-wider">PRODUCTOS EN CATÁLOGO</span>
                        <div class="p-2.5 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl lg:text-4xl font-bold tracking-tight text-white flex items-baseline gap-2">
                            <span>{{ number_format($totalProductos, 0, ',', '.') }}</span>
                            <span class="text-sm font-normal text-slate-400">SKU</span>
                        </div>
                        <div class="mt-3 flex items-center gap-3 text-xs">
                            <span class="inline-flex items-center gap-1.5 text-emerald-400 font-medium">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                {{ number_format($productosConStock, 0) }} con stock
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-slate-400 font-medium">
                                <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                                {{ number_format($productosSinStock, 0) }} agotados
                            </span>
                        </div>
                    </div>
                </a>

                <!-- KPI 3: Semáforo de Vencimientos -->
                <div class="relative overflow-hidden rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl hover:border-rose-500/40 transition-all duration-300 group shadow-lg">
                    <div class="absolute -top-12 -right-12 w-28 h-28 bg-rose-500/10 rounded-full blur-2xl group-hover:bg-rose-500/20 transition-all"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400 tracking-wider">SALUD FEFO</span>
                        <div class="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl lg:text-4xl font-bold tracking-tight {{ ($radarCounts['vencido'] + $radarCounts['critico']) > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                {{ $radarCounts['vencido'] + $radarCounts['critico'] }}
                            </span>
                            <span class="text-xs text-slate-400">lotes en riesgo alto</span>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-1.5 text-center">
                            <div class="p-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20">
                                <span class="block text-[10px] text-rose-300 font-semibold">VENCIDOS</span>
                                <span class="text-sm font-bold text-rose-400">{{ $radarCounts['vencido'] }}</span>
                            </div>
                            <div class="p-1.5 rounded-lg bg-amber-500/10 border border-amber-500/20">
                                <span class="block text-[10px] text-amber-300 font-semibold">&lt; 30 DÍAS</span>
                                <span class="text-sm font-bold text-amber-400">{{ $radarCounts['critico'] + $radarCounts['alerta30'] }}</span>
                            </div>
                            <div class="p-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                                <span class="block text-[10px] text-emerald-300 font-semibold">ÓPTIMOS</span>
                                <span class="text-sm font-bold text-emerald-400">{{ $radarCounts['optimo'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI 4: Ventas Semanales -->
                <a href="{{ route('ventas.index') }}" class="relative overflow-hidden rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl hover:border-emerald-500/40 transition-all duration-300 group shadow-lg block">
                    <div class="absolute -top-12 -right-12 w-28 h-28 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400 tracking-wider">VENTAS (7 DÍAS)</span>
                        <div class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl lg:text-4xl font-bold tracking-tight text-white">
                            ${{ number_format($chartVentasData->sum(), 0, ',', '.') }}
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                            <span>Deducción FEFO:</span>
                            <span class="font-semibold text-emerald-400">Automática</span>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-1.5 text-xs text-indigo-400 group-hover:text-indigo-300">
                        <span>Ver historial de despachos</span>
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>

            </div>

            <!-- ROW 2: HUB DE BODEGAS SINCRONIZADAS (100% Sincronizado en tiempo real) -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                            <h3 class="text-lg font-bold text-white tracking-wide">
                                Hub de Bodegas Sincronizadas
                            </h3>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5 font-normal tracking-normal">
                            Haz clic en cualquier bodega para filtrar instantáneamente el radar de vencimientos y sus lotes activos.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="resetBodegaFilter()" 
                                x-show="selectedBodega !== null" 
                                x-transition
                                class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs text-slate-300 font-medium border border-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            <span>Mostrar Todas las Bodegas</span>
                        </button>
                        <a href="{{ route('bodegas.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium flex items-center gap-1">
                            <span>Gestionar Bodegas</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- Tarjetas Interactivas de Cada Bodega -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($bodegasDetalle as $b)
                    <div @click="toggleBodega({{ $b['id'] }})"
                         :class="selectedBodega === {{ $b['id'] }} ? 'border-cyan-400 ring-2 ring-cyan-500/40 bg-slate-900/90 shadow-cyan-950/50' : 'border-slate-800/80 bg-slate-900/50 hover:border-slate-700'"
                         class="cursor-pointer relative overflow-hidden rounded-3xl border p-5 backdrop-blur-xl transition-all duration-300 group shadow-lg flex flex-col justify-between">
                        
                        <div>
                            <!-- Header de la Bodega -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-xs {{ $b['es_refrigerada'] ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-400/30' : 'bg-indigo-500/20 text-indigo-300 border border-indigo-400/30' }}">
                                        @if($b['es_refrigerada'])
                                            ❄️
                                        @else
                                            📦
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-base font-bold text-white group-hover:text-cyan-300 transition-colors">
                                            {{ $b['nombre'] }}
                                        </h4>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                                {{ $b['codigo'] }}
                                            </span>
                                            <span class="text-[11px] text-slate-400">
                                                {{ $b['es_refrigerada'] ? 'Refrigerada' : 'Bodega Seca' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Active Check Pill -->
                                <div x-show="selectedBodega === {{ $b['id'] }}" class="p-1 rounded-full bg-cyan-500 text-slate-950 shadow-[0_0_10px_rgba(34,211,238,0.8)]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>

                            <!-- Métricas de la Bodega -->
                            <div class="mt-5 grid grid-cols-2 gap-3 pt-4 border-t border-slate-800/80">
                                <div>
                                    <span class="text-[11px] text-slate-400 block font-medium">Existencias</span>
                                    <span class="text-2xl font-bold text-white tracking-tight">
                                        {{ number_format($b['unidades'], 0, ',', '.') }}
                                        <span class="text-xs font-normal text-slate-400">UN</span>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[11px] text-slate-400 block font-medium">Valorización</span>
                                    <span class="text-lg font-bold text-cyan-300 tracking-tight">
                                        ${{ number_format($b['valor'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer con Lotes y Estado -->
                        <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs">
                            <span class="text-slate-400">
                                <strong class="text-slate-200">{{ $b['lotes_count'] }}</strong> lotes registrados
                            </span>

                            @if($b['vencidos_count'] > 0)
                                <span class="px-2.5 py-1 rounded-full bg-rose-500/20 text-rose-300 text-[11px] font-semibold border border-rose-500/30">
                                    🚨 {{ $b['vencidos_count'] }} Vencidos
                                </span>
                            @elseif($b['criticos_count'] > 0)
                                <span class="px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 text-[11px] font-semibold border border-amber-500/30">
                                    ⚠️ {{ $b['criticos_count'] }} Próximos
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-[11px] font-medium border border-emerald-500/20">
                                    ✅ Óptima
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- ROW 3: RADAR INTERACTIVO DE VENCIMIENTOS FEFO (Súper Visual y Filtrable) -->
            <div class="rounded-3xl bg-slate-900/70 border border-slate-800/80 p-6 md:p-8 backdrop-blur-2xl shadow-2xl relative overflow-hidden">
                <!-- Background Accent Glow -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>

                <div class="space-y-6">
                    <!-- Título y Resumen del Radar -->
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.8)]"></span>
                                <h3 class="text-xl font-bold text-white tracking-wide">
                                    Radar de Vencimientos Cercanos (FEFO)
                                </h3>
                            </div>
                            <p class="text-xs text-slate-400 mt-1 font-normal tracking-normal">
                                Visualiza y prioriza la rotación de lotes para evitar mermas. Filtra por horizonte de tiempo o busca por producto.
                            </p>
                        </div>

                        <!-- Contador de Resultados Dinámico -->
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-400 font-normal">
                                Mostrando <strong class="text-white" x-text="filteredLotes.length"></strong> de <strong class="text-white">{{ $radarCounts['todos'] }}</strong> lotes activos
                            </span>
                            <template x-if="selectedBodega !== null">
                                <span class="px-2.5 py-1 rounded-lg bg-cyan-500/15 border border-cyan-400/30 text-cyan-300 text-xs font-medium flex items-center gap-1.5">
                                    <span>Bodega filtrada:</span>
                                    <strong x-text="getBodegaName(selectedBodega)"></strong>
                                    <button @click="resetBodegaFilter()" class="hover:text-white ml-1">✕</button>
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- BARRA DE FILTROS INTERACTIVOS -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-2">
                        
                        <!-- Buscador en Vivo -->
                        <div class="relative flex-1 max-w-md">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" 
                                   x-model="searchQuery" 
                                   placeholder="Buscar por producto, SKU, formato o bodega..." 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 transition-all font-normal">
                            <button x-show="searchQuery.length > 0" 
                                    @click="searchQuery = ''" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-white text-xs">
                                ✕
                            </button>
                        </div>

                        <!-- Selector Rápido de Bodega (Dropdown) -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400 hidden sm:inline">Bodega:</span>
                            <select x-model="selectedBodega" 
                                    class="py-2.5 pl-3 pr-8 rounded-2xl bg-slate-950/80 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-400 font-normal">
                                <option :value="null">Todas las Bodegas</option>
                                @foreach($bodegasDetalle as $b)
                                    <option :value="{{ $b['id'] }}">{{ $b['nombre'] }} ({{ $b['codigo'] }})</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <!-- PÍLDORAS DE HORIZONTE DE VENCIMIENTO (Filtro por Rango) -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                        
                        <!-- Todos -->
                        <button @click="currentFilter = 'todos'" 
                                :class="currentFilter === 'todos' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 border-indigo-400' : 'bg-slate-950/60 text-slate-400 hover:text-white border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5">
                            <span>Todos</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-white/10 text-[10px] font-bold">{{ $radarCounts['todos'] }}</span>
                        </button>

                        <!-- Vencidos -->
                        <button @click="currentFilter = 'vencido'" 
                                :class="currentFilter === 'vencido' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30 border-rose-400' : 'bg-slate-950/60 text-rose-400 hover:bg-rose-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5">
                            <span>🚨 Vencidos</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-rose-500/20 text-[10px] font-bold">{{ $radarCounts['vencido'] }}</span>
                        </button>

                        <!-- Crítico < 7 Días -->
                        <button @click="currentFilter = 'critico'" 
                                :class="currentFilter === 'critico' ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/30 border-amber-400' : 'bg-slate-950/60 text-amber-400 hover:bg-amber-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5">
                            <span>⚡ Crítico (&lt; 7 Días)</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-amber-500/20 text-[10px] font-bold">{{ $radarCounts['critico'] }}</span>
                        </button>

                        <!-- Próximos 30 Días -->
                        <button @click="currentFilter = 'alerta30'" 
                                :class="currentFilter === 'alerta30' ? 'bg-yellow-600 text-white shadow-lg shadow-yellow-600/30 border-yellow-400' : 'bg-slate-950/60 text-yellow-400 hover:bg-yellow-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5">
                            <span>⚠️ Próximos 30 Días</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-yellow-500/20 text-[10px] font-bold">{{ $radarCounts['alerta30'] }}</span>
                        </button>

                        <!-- Próximos 60 Días -->
                        <button @click="currentFilter = 'alerta60'" 
                                :class="currentFilter === 'alerta60' ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30 border-sky-400' : 'bg-slate-950/60 text-sky-400 hover:bg-sky-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5">
                            <span>📅 Próximos 60 Días</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-sky-500/20 text-[10px] font-bold">{{ $radarCounts['alerta60'] }}</span>
                        </button>

                        <!-- Próximos 90 Días -->
                        <button @click="currentFilter = 'alerta90'" 
                                :class="currentFilter === 'alerta90' ? 'bg-purple-600 text-white shadow-lg shadow-purple-600/30 border-purple-400' : 'bg-slate-950/60 text-purple-400 hover:bg-purple-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5">
                            <span>📆 Próximos 90 Días</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-purple-500/20 text-[10px] font-bold">{{ $radarCounts['alerta90'] }}</span>
                        </button>

                        <!-- Óptimos -->
                        <button @click="currentFilter = 'optimo'" 
                                :class="currentFilter === 'optimo' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30 border-emerald-400' : 'bg-slate-950/60 text-emerald-400 hover:bg-emerald-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5">
                            <span>🛡️ Óptimos (&gt; 90 Días)</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-emerald-500/20 text-[10px] font-bold">{{ $radarCounts['optimo'] }}</span>
                        </button>

                    </div>

                    <!-- TABLA / CARDS DE LOTES FEFO -->
                    <div class="sm:hidden flex items-center justify-between text-[11px] text-slate-400 px-1">
                        <span>Lotes en rotación</span>
                        <span class="text-indigo-400 font-medium">← Desliza horizontalmente →</span>
                    </div>
                    <div class="overflow-x-auto rounded-2xl border border-slate-800/80 bg-slate-950/60 scrollbar-thin">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead>
                                <tr class="border-b border-slate-800 text-[11px] font-semibold text-slate-400 tracking-wider bg-slate-900/80">
                                    <th class="py-3 px-4">PRODUCTO & SKU</th>
                                    <th class="py-3 px-4">BODEGA</th>
                                    <th class="py-3 px-4 text-center">STOCK DISPONIBLE</th>
                                    <th class="py-3 px-4">VENCIMIENTO</th>
                                    <th class="py-3 px-4">URGENCIA / CUENTA REGRESIVA</th>
                                    <th class="py-3 px-4 text-right">ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 text-xs">
                                <template x-for="lote in paginatedLotes" :key="lote.id">
                                    <tr class="hover:bg-slate-900/60 transition-colors">
                                        <!-- Producto & SKU -->
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-white text-sm" x-text="lote.producto_nombre"></div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 border border-slate-700" x-text="lote.producto_sku"></span>
                                                <span class="text-[11px] text-slate-400" x-text="lote.formato + ' · ' + lote.capacidad"></span>
                                            </div>
                                        </td>

                                        <!-- Bodega -->
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-200 font-medium">
                                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                                                <span x-text="lote.bodega_nombre"></span>
                                            </span>
                                        </td>

                                        <!-- Stock -->
                                        <td class="py-3 px-4 text-center">
                                            <span class="font-bold text-base text-white" x-text="new Intl.NumberFormat('es-CL').format(lote.cantidad)"></span>
                                            <span class="text-[10px] text-slate-400 block font-normal">unidades</span>
                                        </td>

                                        <!-- Fecha Vencimiento -->
                                        <td class="py-3 px-4 font-mono font-medium text-slate-200" x-text="lote.fecha_vencimiento"></td>

                                        <!-- Urgencia y Cuenta Regresiva -->
                                        <td class="py-3 px-4">
                                            <template x-if="lote.dias_restantes < 0">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40 shadow-[0_0_12px_rgba(244,63,94,0.3)]">
                                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                                    <span>VENCIDO HACE <strong x-text="Math.abs(lote.dias_restantes)"></strong> DÍAS</span>
                                                </span>
                                            </template>
                                            <template x-if="lote.dias_restantes === 0">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40">
                                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                                    <span>¡VENCE HOY!</span>
                                                </span>
                                            </template>
                                            <template x-if="lote.dias_restantes > 0 && lote.dias_restantes <= 7">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                                                    <span>VENCE EN <strong x-text="lote.dias_restantes"></strong> DÍAS</span>
                                                </span>
                                            </template>
                                            <template x-if="lote.dias_restantes > 7 && lote.dias_restantes <= 30">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-300 border border-yellow-500/40">
                                                    <span>⚠️ <strong x-text="lote.dias_restantes"></strong> DÍAS RESTANTES</span>
                                                </span>
                                            </template>
                                            <template x-if="lote.dias_restantes > 30 && lote.dias_restantes <= 60">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-sky-500/10 text-sky-300 border border-sky-500/30">
                                                    <span>📅 <strong x-text="lote.dias_restantes"></strong> Días</span>
                                                </span>
                                            </template>
                                            <template x-if="lote.dias_restantes > 60 && lote.dias_restantes <= 90">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-purple-500/10 text-purple-300 border border-purple-500/30">
                                                    <span>📆 <strong x-text="lote.dias_restantes"></strong> Días</span>
                                                </span>
                                            </template>
                                            <template x-if="lote.dias_restantes > 90">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                                    <span>🛡️ Óptimo (<strong x-text="lote.dias_restantes"></strong>d)</span>
                                                </span>
                                            </template>
                                        </td>

                                        <!-- Acción -->
                                        <td class="py-3 px-4 text-right">
                                            <a :href="'{{ route('lotes.index') }}?lote_id=' + lote.id" 
                                               class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs transition-colors border border-slate-700 font-normal">
                                                <span>Detalle</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </a>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Estado Vacío -->
                                <tr x-show="filteredLotes.length === 0">
                                    <td colspan="6" class="py-12 text-center text-slate-500">
                                        <div class="max-w-xs mx-auto space-y-3">
                                            <div class="w-12 h-12 rounded-2xl bg-slate-800/80 border border-slate-700 text-slate-400 mx-auto flex items-center justify-center text-xl">
                                                🔍
                                            </div>
                                            <p class="text-sm font-medium text-slate-300">No se encontraron lotes</p>
                                            <p class="text-xs text-slate-500">No hay lotes que coincidan con los filtros aplicados en este momento.</p>
                                            <button @click="resetAllFilters()" class="px-4 py-2 rounded-xl bg-indigo-600/80 hover:bg-indigo-500 text-white text-xs font-medium transition-colors">
                                                Restablecer Filtros
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINACIÓN DEL RADAR -->
                    <div x-show="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                        <div class="text-xs text-slate-400 font-normal">
                            Página <strong class="text-white" x-text="currentPage"></strong> de <strong class="text-white" x-text="totalPages"></strong>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="prevPage()" 
                                    :disabled="currentPage === 1" 
                                    :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-slate-900 border-slate-800 text-slate-600' : 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-slate-200'"
                                    class="px-3.5 py-1.5 rounded-xl border text-xs font-medium transition-colors">
                                ← Anterior
                            </button>
                            <button @click="nextPage()" 
                                    :disabled="currentPage === totalPages" 
                                    :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-slate-900 border-slate-800 text-slate-600' : 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-slate-200'"
                                    class="px-3.5 py-1.5 rounded-xl border text-xs font-medium transition-colors">
                                Siguiente →
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ROW 4: ANALYTICS MODERNOS (Chart.js Rediseñados) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Gráfico 1: Flujo Semanal de Ventas (Curva Moderna Indigo) -->
                <div class="rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl shadow-xl flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-xs font-medium text-slate-400 tracking-wider">TENDENCIA SEMANAL</span>
                            <h4 class="text-lg font-bold text-white tracking-tight mt-0.5">Flujo de Salidas / Ventas</h4>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-mono text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-1 rounded-lg border border-emerald-500/20">
                                ${{ number_format($chartVentasData->sum(), 0, ',', '.') }} CLP
                            </span>
                        </div>
                    </div>
                    <div class="relative w-full h-[260px]">
                        <canvas id="ventasSemanaChart"></canvas>
                    </div>
                </div>

                <!-- Gráfico 2: Proyección de Vencimientos por Mes -->
                <div class="rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl shadow-xl flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-xs font-medium text-slate-400 tracking-wider">HORIZONTE TEMPORAL</span>
                            <h4 class="text-lg font-bold text-white tracking-tight mt-0.5">Lotes por Vencer (4 Meses)</h4>
                        </div>
                        <a href="{{ route('lotes.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                            Ver Lotes
                        </a>
                    </div>
                    <div class="relative w-full h-[260px]">
                        <canvas id="vencimientosMesChart"></canvas>
                    </div>
                </div>

            </div>

            <!-- ROW 5: DISTRIBUCIÓN DE INVENTARIO (Donuts Modernos) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Dona 1: Stock por Bodega -->
                <div class="rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-xs font-medium text-slate-400 tracking-wider">DISTRIBUCIÓN FÍSICA</span>
                            <h4 class="text-base font-bold text-white tracking-tight mt-0.5">Stock Disponible por Bodega</h4>
                        </div>
                    </div>
                    <div class="relative w-full h-[240px]">
                        @if(count($chartBodegaLabels) > 0)
                            <canvas id="stockBodegaChart"></canvas>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-slate-600 font-light text-sm">Sin datos registrados</div>
                        @endif
                    </div>
                </div>

                <!-- Dona 2: Stock por Proveedor -->
                <div class="rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-xs font-medium text-slate-400 tracking-wider">ORIGEN DE PROVEEDORES</span>
                            <h4 class="text-base font-bold text-white tracking-tight mt-0.5">Stock por Marca / Distribuidor</h4>
                        </div>
                    </div>
                    <div class="relative w-full h-[240px]">
                        @if(count($chartLabels) > 0)
                            <canvas id="stockProveedorChart"></canvas>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-slate-600 font-light text-sm">Sin datos registrados</div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Script de Alpine.js para interactividad instantánea -->
    <script>
        function dashboardHub() {
            return {
                allLotes: {!! json_encode($lotesRadar) !!},
                bodegas: {!! json_encode($bodegasDetalle) !!},
                selectedBodega: null,
                currentFilter: 'todos',
                searchQuery: '',
                currentPage: 1,
                perPage: 10,

                toggleBodega(id) {
                    if (this.selectedBodega === id) {
                        this.selectedBodega = null;
                    } else {
                        this.selectedBodega = id;
                    }
                    this.currentPage = 1;
                },

                resetBodegaFilter() {
                    this.selectedBodega = null;
                    this.currentPage = 1;
                },

                resetAllFilters() {
                    this.selectedBodega = null;
                    this.currentFilter = 'todos';
                    this.searchQuery = '';
                    this.currentPage = 1;
                },

                getBodegaName(id) {
                    const b = this.bodegas.find(item => item.id == id);
                    return b ? b.nombre : 'Bodega';
                },

                get filteredLotes() {
                    let list = this.allLotes;

                    // Filtro por Bodega
                    if (this.selectedBodega !== null) {
                        list = list.filter(l => l.bodega_id == this.selectedBodega);
                    }

                    // Filtro por Urgencia/Horizonte
                    if (this.currentFilter !== 'todos') {
                        list = list.filter(l => l.filtro === this.currentFilter);
                    }

                    // Búsqueda en Vivo
                    if (this.searchQuery.trim().length > 0) {
                        const q = this.searchQuery.toLowerCase().trim();
                        list = list.filter(l => 
                            (l.producto_nombre && l.producto_nombre.toLowerCase().includes(q)) ||
                            (l.producto_sku && l.producto_sku.toLowerCase().includes(q)) ||
                            (l.bodega_nombre && l.bodega_nombre.toLowerCase().includes(q)) ||
                            (l.formato && l.formato.toLowerCase().includes(q)) ||
                            (l.capacidad && l.capacidad.toLowerCase().includes(q))
                        );
                    }

                    return list;
                },

                get totalPages() {
                    return Math.max(1, Math.ceil(this.filteredLotes.length / this.perPage));
                },

                get paginatedLotes() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.filteredLotes.slice(start, start + this.perPage);
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                    }
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                    }
                }
            };
        }

        // Configuración de Chart.js moderna (Executive Dark Theme)
        document.addEventListener('DOMContentLoaded', function() {
            const chartPalette = [
                '#6366f1', // Indigo 500
                '#06b6d4', // Cyan 500
                '#10b981', // Emerald 500
                '#f59e0b', // Amber 500
                '#a855f7', // Purple 500
                '#f43f5e'  // Rose 500
            ];

            const modernTooltip = {
                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                titleColor: '#ffffff',
                bodyColor: '#e2e8f0',
                borderColor: 'rgba(99, 102, 241, 0.3)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 12,
                boxPadding: 6,
                backdropFilter: 'blur(10px)'
            };

            // 1. Chart: Ventas Semanales (Smooth Area Curve)
            const ctxVentas = document.getElementById('ventasSemanaChart');
            if (ctxVentas) {
                let ctx = ctxVentas.getContext('2d');
                let gradient = ctx.createLinearGradient(0, 0, 0, 260);
                gradient.addColorStop(0, 'rgba(99, 102, 241, 0.4)');
                gradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartVentasLabels) !!},
                        datasets: [{
                            label: 'Ventas Facturadas',
                            data: {!! json_encode($chartVentasData) !!},
                            backgroundColor: gradient,
                            borderColor: '#6366f1',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#0f172a',
                            pointBorderColor: '#818cf8',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                ...modernTooltip,
                                callbacks: {
                                    label: context => 'Ventas: $' + new Intl.NumberFormat('es-CL').format(context.raw)
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(255, 255, 255, 0.04)', drawBorder: false },
                                border: { display: false },
                                ticks: {
                                    color: '#64748b',
                                    font: { family: 'Outfit, sans-serif', size: 11 },
                                    callback: val => '$' + new Intl.NumberFormat('es-CL').format(val)
                                }
                            },
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: {
                                    color: '#64748b',
                                    font: { family: 'Outfit, sans-serif', size: 11 }
                                }
                            }
                        }
                    }
                });
            }

            // 2. Chart: Lotes por Vencer (4 Meses - Modern Rounded Bars)
            const ctxVenc = document.getElementById('vencimientosMesChart');
            if (ctxVenc) {
                let ctx = ctxVenc.getContext('2d');
                let gradient = ctx.createLinearGradient(0, 0, 0, 260);
                gradient.addColorStop(0, 'rgba(244, 63, 94, 0.9)');
                gradient.addColorStop(1, 'rgba(244, 63, 94, 0.4)');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($chartVencLabels) !!},
                        datasets: [{
                            label: 'Lotes por vencer',
                            data: {!! json_encode($chartVencData) !!},
                            backgroundColor: gradient,
                            borderRadius: 8,
                            borderSkipped: false,
                            barThickness: 28
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                ...modernTooltip,
                                callbacks: {
                                    label: context => context.raw + ' lotes por vencer'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(255, 255, 255, 0.04)', drawBorder: false },
                                border: { display: false },
                                ticks: {
                                    color: '#64748b',
                                    font: { family: 'Outfit, sans-serif', size: 11 },
                                    stepSize: 1
                                }
                            },
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: {
                                    color: '#94a3b8',
                                    font: { family: 'Outfit, sans-serif', size: 11, weight: '500' }
                                }
                            }
                        }
                    }
                });
            }

            // 3. Chart: Stock por Bodega (Doughnut Moderno)
            @if(count($chartBodegaLabels) > 0)
            const ctxBodega = document.getElementById('stockBodegaChart');
            if (ctxBodega) {
                new Chart(ctxBodega, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($chartBodegaLabels) !!},
                        datasets: [{
                            data: {!! json_encode($chartBodegaData) !!},
                            backgroundColor: chartPalette,
                            borderColor: '#020617',
                            borderWidth: 3,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '76%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#cbd5e1',
                                    font: { family: 'Outfit, sans-serif', size: 11 },
                                    usePointStyle: true,
                                    padding: 16
                                }
                            },
                            tooltip: {
                                ...modernTooltip,
                                callbacks: {
                                    label: context => context.label + ': ' + new Intl.NumberFormat('es-CL').format(context.raw) + ' UN'
                                }
                            }
                        }
                    }
                });
            }
            @endif

            // 4. Chart: Stock por Proveedor (Doughnut Moderno)
            @if(count($chartLabels) > 0)
            const ctxProveedor = document.getElementById('stockProveedorChart');
            if (ctxProveedor) {
                new Chart(ctxProveedor, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($chartLabels) !!},
                        datasets: [{
                            data: {!! json_encode($chartData) !!},
                            backgroundColor: chartPalette.slice().reverse(),
                            borderColor: '#020617',
                            borderWidth: 3,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '76%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#cbd5e1',
                                    font: { family: 'Outfit, sans-serif', size: 11 },
                                    usePointStyle: true,
                                    padding: 16
                                }
                            },
                            tooltip: {
                                ...modernTooltip,
                                callbacks: {
                                    label: context => context.label + ': ' + new Intl.NumberFormat('es-CL').format(context.raw) + ' UN'
                                }
                            }
                        }
                    }
                });
            }
            @endif
        });
    </script>
</x-app-layout>
