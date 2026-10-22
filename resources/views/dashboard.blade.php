{{-- ==============================================================================
     PANEL DE CONTROL PRINCIPAL / DASHBOARD EJECUTIVO (resources/views/dashboard.blade.php)
     ==============================================================================
     Centro neurálgico de operaciones en tiempo real:
     - Métricas financieras consolidadas (Valorización de inventario, existencias).
     - Alerta de facturas en cuarentena preventiva (Recepciones PENDIENTE_FECHA).
     - Warehouse Hub: Monitor visual de todas las bodegas (secas y refrigeradas).
     - Radar FEFO Interactivo: Búsqueda y filtrado dinámico de lotes por horizonte de vencimiento.
     - Gráficos ejecutivos con Chart.js (Ventas semanales, proyección de caducidad, distribución por almacén).
     ============================================================================== --}}
<x-app-layout>
    {{-- Inyección de la barra de título en el slot $header del layout maestro --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
                <div class="flex items-center gap-2.5 sm:gap-3">
                    {{-- Barra decorativa vertical con degradado corporativo índigo-cian --}}
                    <div class="w-2 sm:w-2.5 h-6 sm:h-8 bg-gradient-to-b from-indigo-500 to-cyan-400 rounded-full shadow-[0_0_12px_rgba(99,102,241,0.6)] shrink-0"></div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold tracking-tight text-white leading-tight">
                        {{ __('Centro de Control & Operaciones') }}
                    </h2>
                </div>
                <p class="text-[11px] sm:text-xs md:text-sm text-slate-400 mt-1 font-normal tracking-normal pl-4 sm:pl-5">
                    Monitoreo en tiempo real de inventario, balance multisede y radar inteligente de vencimientos FEFO.
                </p>
            </div>

            {{-- Badges de sincronización y accesos directos superiores --}}
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                {{-- Indicador verde con pulso animado de sincronización en vivo --}}
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[11px] sm:text-xs font-medium backdrop-blur-md shadow-[0_0_15px_rgba(16,185,129,0.15)]">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>100% Sincronizado en Vivo</span>
                </div>

                {{-- Acceso rápido al módulo de recepciones de mercadería --}}
                <a href="{{ route('recepciones.index') }}" class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium rounded-xl transition-all duration-200 shadow-lg shadow-indigo-600/20 border border-indigo-400/30">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Recepciones</span>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Carga de la biblioteca Chart.js para renderizado de gráficos analíticos --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Contenedor Principal montado con el Componente Reactivo de Alpine: dashboardHub() --}}
    <div x-data="dashboardHub()" class="w-full space-y-6 sm:space-y-8 relative overflow-x-clip font-sans text-slate-100">
        
        {{-- Destellos Radiales Ambientales (Efecto Cyber-Executive difuminado en fondo) --}}
        <div class="absolute top-0 left-1/4 w-[600px] h-[400px] bg-indigo-600/10 rounded-full blur-[140px] pointer-events-none -z-0"></div>
        <div class="absolute top-1/3 right-10 w-[500px] h-[500px] bg-cyan-600/10 rounded-full blur-[150px] pointer-events-none -z-0"></div>
        <div class="absolute bottom-10 left-1/3 w-[600px] h-[400px] bg-rose-600/5 rounded-full blur-[160px] pointer-events-none -z-0"></div>

        <div class="w-full space-y-6 sm:space-y-8 relative z-10">
            
            {{-- ALERTA DE SISTEMA: Recepciones de Factura en Cuarentena Preventiva (PENDIENTE_FECHA) --}}
            {{-- Notifica al operador que hay mercadería física ingresada esperando fecha de vencimiento para habilitar venta --}}
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
                    {{-- Botón de llamada a la acción hacia el conciliador de fechas --}}
                    <div class="shrink-0 flex items-center gap-3">
                        <a href="{{ route('recepciones.index') }}?estado=PENDIENTE_FECHA" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-400 hover:to-rose-500 text-white font-medium text-xs tracking-normal shadow-lg shadow-rose-900/30 transition-all duration-200 border border-white/20">
                            <span>Conciliar Fechas Ahora</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- FILA 1: TARJETAS KPI EJECUTIVAS CENTRADAS EN CANTIDADES, FECHAS Y LOTES --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                
                {{-- KPI 1: Cantidades Físicas en Stock (Existencias Totales en Unidades) --}}
                <div class="relative overflow-hidden rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl hover:border-indigo-500/40 transition-all duration-300 group shadow-lg">
                    <div class="absolute -top-12 -right-12 w-28 h-28 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/20 transition-all"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 tracking-wider">EXISTENCIAS EN STOCK</span>
                        <div class="p-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        {{-- Unidades Físicas Totales en Stock --}}
                        <div class="text-3xl lg:text-4xl font-black tracking-tight text-white flex items-baseline gap-2">
                            <span>{{ number_format($totalUnidades, 0, ',', '.') }}</span>
                            <span class="text-base font-bold text-indigo-400">UN</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                            <span>Valorización ref:</span>
                            <span class="font-semibold text-slate-300">${{ number_format($valorTotal, 0, ',', '.') }} CLP</span>
                        </div>
                    </div>
                    {{-- Barra visual de proporción de stock distribuido entre bodegas --}}
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

                {{-- KPI 2: Lotes Activos en Rotación y Conteo de SKUs --}}
                <a href="{{ route('lotes.index') }}" class="relative overflow-hidden rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl hover:border-cyan-500/40 transition-all duration-300 group shadow-lg block">
                    <div class="absolute -top-12 -right-12 w-28 h-28 bg-cyan-500/10 rounded-full blur-2xl group-hover:bg-cyan-500/20 transition-all"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 tracking-wider">LOTES ACTIVOS (STOCK)</span>
                        <div class="p-2.5 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        {{-- Total de Lotes físicos registrados en existencia --}}
                        <div class="text-3xl lg:text-4xl font-black tracking-tight text-white flex items-baseline gap-2">
                            <span>{{ number_format($radarCounts['todos'], 0, ',', '.') }}</span>
                            <span class="text-sm font-semibold text-cyan-400">Lotes</span>
                        </div>
                        {{-- Desglose: SKUs con saldo positivo vs SKUs agotados --}}
                        <div class="mt-3 flex items-center justify-between text-xs text-slate-400">
                            <span class="text-slate-300 font-medium">{{ number_format($totalProductos, 0) }} SKUs en catálogo</span>
                            <span class="text-emerald-400 font-semibold">{{ number_format($productosConStock, 0) }} con stock</span>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-1.5 text-xs text-cyan-400 group-hover:text-cyan-300 font-medium">
                        <span>Ver todos los lotes</span>
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>

                {{-- KPI 3: Semáforo y Salud FEFO (100% Clickeable para filtrar y ver lotes) --}}
                <div class="relative overflow-hidden rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl hover:border-rose-500/40 transition-all duration-300 group shadow-lg">
                    <div class="absolute -top-12 -right-12 w-28 h-28 bg-rose-500/10 rounded-full blur-2xl group-hover:bg-rose-500/20 transition-all"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 tracking-wider">SALUD FEFO & CADUCIDAD</span>
                        <div class="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        {{-- Indicador numérico de lotes que exigen atención inmediata --}}
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl lg:text-4xl font-black tracking-tight {{ ($radarCounts['vencido'] + $radarCounts['mes1']) > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                {{ $radarCounts['vencido'] + $radarCounts['mes1'] }}
                            </span>
                            <span class="text-xs text-slate-400">lotes en atención</span>
                        </div>
                        
                        {{-- Micro-grid CLICKEABLE: Clickea para filtrar instantáneamente el Radar FEFO inferior --}}
                        <div class="mt-3 grid grid-cols-3 gap-1.5 text-center">
                            {{-- Botón Clickeable Vencidos --}}
                            <button type="button" @click.stop="applyFilterAndScroll('vencido')" 
                                    class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/25 border border-rose-500/30 hover:border-rose-400 cursor-pointer transition-all transform hover:scale-105 group/btn" 
                                    title="Haz clic para ver los lotes vencidos">
                                <span class="block text-[9px] text-rose-300 font-bold uppercase tracking-wider group-hover/btn:underline">VENCIDOS</span>
                                <span class="text-sm font-black text-rose-400">{{ $radarCounts['vencido'] }}</span>
                            </button>
                            
                            {{-- Botón Clickeable < 30 Días --}}
                            <button type="button" @click.stop="applyFilterAndScroll('1mes')" 
                                    class="p-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/25 border border-amber-500/30 hover:border-amber-400 cursor-pointer transition-all transform hover:scale-105 group/btn" 
                                    title="Haz clic para ver los lotes que vencen en menos de 30 días">
                                <span class="block text-[9px] text-amber-300 font-bold uppercase tracking-wider group-hover/btn:underline">&lt; 30 DÍAS</span>
                                <span class="text-sm font-black text-amber-400">{{ $radarCounts['mes1'] }}</span>
                            </button>
                            
                            {{-- Botón Clickeable Óptimos --}}
                            <button type="button" @click.stop="applyFilterAndScroll('optimo')" 
                                    class="p-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/25 border border-emerald-500/30 hover:border-emerald-400 cursor-pointer transition-all transform hover:scale-105 group/btn" 
                                    title="Haz clic para ver los lotes en estado óptimo">
                                <span class="block text-[9px] text-emerald-300 font-bold uppercase tracking-wider group-hover/btn:underline">ÓPTIMOS</span>
                                <span class="text-sm font-black text-emerald-400">{{ $radarCounts['optimo'] }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- KPI 4: Próxima Caducidad y Control de Fechas FEFO --}}
                <div @click="applyFilterAndScroll('1mes')" class="cursor-pointer relative overflow-hidden rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl hover:border-amber-500/40 transition-all duration-300 group shadow-lg block">
                    <div class="absolute -top-12 -right-12 w-28 h-28 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-all"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 tracking-wider">PRÓXIMO VENCIMIENTO</span>
                        <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        {{-- Cuenta regresiva o fecha del lote más próximo a caducar --}}
                        <div class="text-2xl lg:text-3xl font-black tracking-tight text-amber-300 truncate">
                            {{ $proximaCaducidadTexto }}
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                            <span>Deducción FEFO:</span>
                            <span class="font-bold text-emerald-400">Automática</span>
                        </div>
                    </div>
                    {{-- Indicador de filtrado directo --}}
                    <div class="mt-4 flex items-center gap-1.5 text-xs text-amber-400 group-hover:text-amber-300 font-medium">
                        <span>Filtrar lotes próximos</span>
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

            </div>


            </div>

            {{-- FILA 2: HUB DE BODEGAS SINCRONIZADAS (Monitoreo Multisede en Tiempo Real) --}}
            {{-- Permite interactuar con cada almacén físico y actuar como filtro rápido para el Radar FEFO inferior --}}
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

                    {{-- Controles de filtro de bodega: restablecer filtro global o ir a gestión de bodegas --}}
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

                {{-- Grilla de Tarjetas Interactivas por cada Bodega Física Activa --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($bodegasDetalle as $b)
                    {{-- Tarjeta interactiva con evento @click="toggleBodega(id)" para activar/desactivar filtro Alpine --}}
                    <div @click="toggleBodega({{ $b['id'] }})"
                         :class="selectedBodega === {{ $b['id'] }} ? 'border-cyan-400 ring-2 ring-cyan-500/40 bg-slate-900/90 shadow-cyan-950/50' : 'border-slate-800/80 bg-slate-900/50 hover:border-slate-700'"
                         class="cursor-pointer relative overflow-hidden rounded-3xl border p-5 backdrop-blur-xl transition-all duration-300 group shadow-lg flex flex-col justify-between">
                        
                        <div>
                            {{-- Cabecera de la Bodega: Ícono de Temperatura, Nombre y Código --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    {{-- Ícono diferenciado: Copo de nieve para refrigerada, Caja para seca --}}
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

                                {{-- Indicador visual de Bodega seleccionada activamente como filtro --}}
                                <div x-show="selectedBodega === {{ $b['id'] }}" class="p-1 rounded-full bg-cyan-500 text-slate-950 shadow-[0_0_10px_rgba(34,211,238,0.8)]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>

                            {{-- Métricas de la Bodega: Unidades físicas en existencia y valorización monetaria --}}
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

                        {{-- Pie de la Tarjeta: Conteo de Lotes y Semáforo de Urgencia --}}
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

            {{-- FILA 3: RADAR INTERACTIVO DE VENCIMIENTOS FEFO --}}
            {{-- Proporciona visualización tabular reactiva con filtros de tiempo y búsqueda instantánea --}}
            <div id="radar-fefo-section" class="rounded-3xl bg-slate-900/70 border border-slate-800/80 p-6 md:p-8 backdrop-blur-2xl shadow-2xl relative overflow-hidden scroll-mt-6">
                {{-- Efecto ambiental decorativo --}}
                <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>

                <div class="space-y-6">
                    {{-- Encabezado del Radar y Resumen de Lotes Filtrados --}}
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

                        {{-- Contador Dinámico de Lotes Visibles según los Filtros Activos --}}
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-400 font-normal">
                                Mostrando <strong class="text-white" x-text="filteredLotes.length"></strong> de <strong class="text-white">{{ $radarCounts['todos'] }}</strong> lotes activos
                            </span>
                            {{-- Pill informativa si hay una bodega filtrada activamente --}}
                            <template x-if="selectedBodega !== null">
                                <span class="px-2.5 py-1 rounded-lg bg-cyan-500/15 border border-cyan-400/30 text-cyan-300 text-xs font-medium flex items-center gap-1.5">
                                    <span>Bodega filtrada:</span>
                                    <strong x-text="getBodegaName(selectedBodega)"></strong>
                                    <button @click="resetBodegaFilter()" class="hover:text-white ml-1">✕</button>
                                </span>
                            </template>
                        </div>
                    </div>

                    {{-- BARRA DE FILTROS INTERACTIVOS (Buscador en vivo + Selector de Bodega) --}}
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-2">
                        
                        {{-- Buscador en Vivo en el Cliente (Filtra por nombre, SKU, formato o bodega) --}}
                        <div class="relative flex-1 max-w-md">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" 
                                   x-model="searchQuery" 
                                   placeholder="Buscar por producto, SKU, formato o bodega..." 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 transition-all font-normal">
                            {{-- Botón para limpiar el texto de búsqueda rápida --}}
                            <button x-show="searchQuery.length > 0" 
                                    @click="searchQuery = ''" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-white text-xs">
                                ✕
                            </button>
                        </div>

                        {{-- Selector Rápido de Bodega en Dropdown --}}
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

                    {{-- PÍLDORAS DE HORIZONTE DE VENCIMIENTO (Filtros: Vencidos, 1m, 2m, 3m, 6m, 12m, +12m) --}}
                    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                        
                        {{-- Botón Filtro: Todos los Lotes --}}
                        <button @click="currentFilter = 'todos'" 
                                :class="currentFilter === 'todos' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 border-indigo-400' : 'bg-slate-950/60 text-slate-400 hover:text-white border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5 cursor-pointer">
                            <span>Todos</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-white/10 text-[10px] font-bold">{{ $radarCounts['todos'] }}</span>
                        </button>

                        {{-- Botón Filtro: Lotes Ya Vencidos --}}
                        <button @click="currentFilter = 'vencido'" 
                                :class="currentFilter === 'vencido' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30 border-rose-400' : 'bg-slate-950/60 text-rose-400 hover:bg-rose-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5 cursor-pointer">
                            <span>🚨 Vencidos</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-rose-500/20 text-[10px] font-bold">{{ $radarCounts['vencido'] }}</span>
                        </button>

                        {{-- Botón Filtro: Hasta 1 Mes --}}
                        <button @click="currentFilter = '1mes'" 
                                :class="currentFilter === '1mes' ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/30 border-amber-400' : 'bg-slate-950/60 text-amber-400 hover:bg-amber-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5 cursor-pointer">
                            <span>⚡ Hasta 1 Mes</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-amber-500/20 text-[10px] font-bold">{{ $radarCounts['mes1'] }}</span>
                        </button>

                        {{-- Botón Filtro: Hasta 2 Meses --}}
                        <button @click="currentFilter = '2meses'" 
                                :class="currentFilter === '2meses' ? 'bg-yellow-600 text-white shadow-lg shadow-yellow-600/30 border-yellow-400' : 'bg-slate-950/60 text-yellow-400 hover:bg-yellow-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5 cursor-pointer">
                            <span>📅 Hasta 2 Meses</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-yellow-500/20 text-[10px] font-bold">{{ $radarCounts['mes2'] }}</span>
                        </button>

                        {{-- Botón Filtro: Hasta 3 Meses --}}
                        <button @click="currentFilter = '3meses'" 
                                :class="currentFilter === '3meses' ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30 border-sky-400' : 'bg-slate-950/60 text-sky-400 hover:bg-sky-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5 cursor-pointer">
                            <span>📆 Hasta 3 Meses</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-sky-500/20 text-[10px] font-bold">{{ $radarCounts['mes3'] }}</span>
                        </button>

                        {{-- Botón Filtro: Hasta 6 Meses --}}
                        <button @click="currentFilter = '6meses'" 
                                :class="currentFilter === '6meses' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 border-indigo-400' : 'bg-slate-950/60 text-indigo-400 hover:bg-indigo-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5 cursor-pointer">
                            <span>📦 Hasta 6 Meses</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-indigo-500/20 text-[10px] font-bold">{{ $radarCounts['mes6'] }}</span>
                        </button>

                        {{-- Botón Filtro: Hasta 12 Meses --}}
                        <button @click="currentFilter = '12meses'" 
                                :class="currentFilter === '12meses' ? 'bg-purple-600 text-white shadow-lg shadow-purple-600/30 border-purple-400' : 'bg-slate-950/60 text-purple-400 hover:bg-purple-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5 cursor-pointer">
                            <span>🎯 Hasta 12 Meses</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-purple-500/20 text-[10px] font-bold">{{ $radarCounts['mes12'] }}</span>
                        </button>

                        {{-- Botón Filtro: Óptimos (> 12 Meses) --}}
                        <button @click="currentFilter = 'optimo'" 
                                :class="currentFilter === 'optimo' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30 border-emerald-400' : 'bg-slate-950/60 text-emerald-400 hover:bg-emerald-500/10 border-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs font-medium border transition-all shrink-0 flex items-center gap-1.5 cursor-pointer">
                            <span>🛡️ +12 Meses (Óptimos)</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-emerald-500/20 text-[10px] font-bold">{{ $radarCounts['optimo'] }}</span>
                        </button>

                    </div>

                    {{-- Indicador visual para pantallas móviles: Desplazamiento horizontal sugerido --}}
                    <div class="sm:hidden flex items-center justify-between text-[11px] text-slate-400 px-1">
                        <span>Lotes en rotación</span>
                        <span class="text-indigo-400 font-medium">← Desliza horizontalmente →</span>
                    </div>

                    {{-- Contenedor con scroll horizontal para la tabla de inventario FEFO --}}
                    <div class="overflow-x-auto rounded-2xl border border-slate-800/80 bg-slate-950/60 scrollbar-thin">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            {{-- Encabezado de columnas de la tabla --}}
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
                            {{-- Cuerpo de la tabla: Renderizado reactivo Alpine x-for sobre 'paginatedLotes' --}}
                            <tbody class="divide-y divide-slate-800/60 text-xs">
                                <template x-for="lote in paginatedLotes" :key="lote.id">
                                    <tr class="hover:bg-slate-900/60 transition-colors">
                                        {{-- Celda 1: Nombre de producto, SKU en monospace y formato/capacidad --}}
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-white text-sm" x-text="lote.producto_nombre"></div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 border border-slate-700" x-text="lote.producto_sku"></span>
                                                <span class="text-[11px] text-slate-400" x-text="lote.formato + ' · ' + lote.capacidad"></span>
                                            </div>
                                        </td>

                                        {{-- Celda 2: Bodega asignada con chip identificador y punto cian --}}
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-200 font-medium">
                                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                                                <span x-text="lote.bodega_nombre"></span>
                                            </span>
                                        </td>

                                        {{-- Celda 3: Stock disponible formateado con separador de miles es-CL --}}
                                        <td class="py-3 px-4 text-center">
                                            <span class="font-bold text-base text-white" x-text="new Intl.NumberFormat('es-CL').format(lote.cantidad)"></span>
                                            <span class="text-[10px] text-slate-400 block font-normal">unidades</span>
                                        </td>

                                        {{-- Celda 4: Fecha de vencimiento exacta (YYYY-MM-DD) --}}
                                        <td class="py-3 px-4 font-mono font-medium text-slate-200" x-text="lote.fecha_vencimiento"></td>

                                        {{-- Celda 5: Badges condicionales de Urgencia y Cuenta Regresiva según dias_restantes --}}
                                        <td class="py-3 px-4">
                                            {{-- Caso 1: Lote ya vencido (días negativos) con ping animado rojo --}}
                                            <template x-if="lote.dias_restantes < 0">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40 shadow-[0_0_12px_rgba(244,63,94,0.3)]">
                                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                                    <span>VENCIDO HACE <strong x-text="Math.abs(lote.dias_restantes)"></strong> DÍAS</span>
                                                </span>
                                            </template>
                                            {{-- Caso 2: Vence hoy exactamente con pulso de advertencia --}}
                                            <template x-if="lote.dias_restantes === 0">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40">
                                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                                    <span>¡VENCE HOY!</span>
                                                </span>
                                            </template>
                                            {{-- Caso 3: Nivel Crítico (1 a 7 días restantes) en tono ámbar vibrante --}}
                                            <template x-if="lote.dias_restantes > 0 && lote.dias_restantes <= 7">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                                                    <span>VENCE EN <strong x-text="lote.dias_restantes"></strong> DÍAS</span>
                                                </span>
                                            </template>
                                            {{-- Caso 4: Alerta 30 Días (8 a 30 días restantes) en tono amarillo --}}
                                            <template x-if="lote.dias_restantes > 7 && lote.dias_restantes <= 30">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-300 border border-yellow-500/40">
                                                    <span>⚠️ <strong x-text="lote.dias_restantes"></strong> DÍAS RESTANTES</span>
                                                </span>
                                            </template>
                                            {{-- Caso 5: Alerta 60 Días (31 a 60 días) en tono celeste --}}
                                            <template x-if="lote.dias_restantes > 30 && lote.dias_restantes <= 60">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-sky-500/10 text-sky-300 border border-sky-500/30">
                                                    <span>📅 <strong x-text="lote.dias_restantes"></strong> Días</span>
                                                </span>
                                            </template>
                                            {{-- Caso 6: Alerta 90 Días (61 a 90 días) en tono morado --}}
                                            <template x-if="lote.dias_restantes > 60 && lote.dias_restantes <= 90">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-purple-500/10 text-purple-300 border border-purple-500/30">
                                                    <span>📆 <strong x-text="lote.dias_restantes"></strong> Días</span>
                                                </span>
                                            </template>
                                            {{-- Caso 7: Estado Óptimo (más de 90 días de vida útil) en tono esmeralda --}}
                                            <template x-if="lote.dias_restantes > 90">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                                    <span>🛡️ Óptimo (<strong x-text="lote.dias_restantes"></strong>d)</span>
                                                </span>
                                            </template>
                                        </td>

                                        {{-- Celda 6: Botón de acción hacia el módulo de lotes con filtro por ID --}}
                                        <td class="py-3 px-4 text-right">
                                            <a :href="'{{ route('lotes.index') }}?lote_id=' + lote.id" 
                                               class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs transition-colors border border-slate-700 font-normal">
                                                <span>Detalle</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </a>
                                        </td>
                                    </tr>
                                </template>

                                {{-- Estado Vacío: Se muestra cuando ningún lote cumple con los filtros activos --}}
                                <tr x-show="filteredLotes.length === 0">
                                    <td colspan="6" class="py-12 text-center text-slate-500">
                                        <div class="max-w-xs mx-auto space-y-3">
                                            {{-- Ícono ilustrativo de búsqueda sin resultados --}}
                                            <div class="w-12 h-12 rounded-2xl bg-slate-800/80 border border-slate-700 text-slate-400 mx-auto flex items-center justify-center text-xl">
                                                🔍
                                            </div>
                                            {{-- Mensaje de información para el usuario --}}
                                            <p class="text-sm font-medium text-slate-300">No se encontraron lotes</p>
                                            <p class="text-xs text-slate-500">No hay lotes que coincidan con los filtros aplicados en este momento.</p>
                                            {{-- Botón para resetear todos los filtros aplicados --}}
                                            <button @click="resetAllFilters()" class="px-4 py-2 rounded-xl bg-indigo-600/80 hover:bg-indigo-500 text-white text-xs font-medium transition-colors">
                                                Restablecer Filtros
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Controles de Paginación del Radar FEFO (Visible solo si totalPages > 1) --}}
                    <div x-show="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                        {{-- Indicador de página actual vs total de páginas disponibles --}}
                        <div class="text-xs text-slate-400 font-normal">
                            Página <strong class="text-white" x-text="currentPage"></strong> de <strong class="text-white" x-text="totalPages"></strong>
                        </div>
                        {{-- Botones de navegación Anterior / Siguiente con desactivación condicional --}}
                        <div class="flex items-center gap-2">
                            {{-- Botón de página anterior --}}
                            <button @click="prevPage()" 
                                    :disabled="currentPage === 1" 
                                    :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-slate-900 border-slate-800 text-slate-600' : 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-slate-200'"
                                    class="px-3.5 py-1.5 rounded-xl border text-xs font-medium transition-colors">
                                ← Anterior
                            </button>
                            {{-- Botón de página siguiente --}}
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

            {{-- ========================================================================= --}}
            {{-- ROW 4: ANALYTICS Y GRÁFICOS DE TENDENCIA TEMPORAL (Chart.js)               --}}
            {{-- ========================================================================= --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- Gráfico 1: Flujo Semanal de Ventas (Curva de Área Indigo con Gradiente) --}}
                <div class="rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl shadow-xl flex flex-col justify-between">
                    {{-- Encabezado del gráfico: Título, indicador de tendencia y monto total semanal --}}
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-xs font-medium text-slate-400 tracking-wider">TENDENCIA SEMANAL</span>
                            <h4 class="text-lg font-bold text-white tracking-tight mt-0.5">Flujo de Salidas / Ventas</h4>
                        </div>
                        {{-- Badge con la suma total facturada en los últimos 7 días --}}
                        <div class="text-right">
                            <span class="text-xs font-mono text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-1 rounded-lg border border-emerald-500/20">
                                ${{ number_format($chartVentasData->sum(), 0, ',', '.') }} CLP
                            </span>
                        </div>
                    </div>
                    {{-- Lienzo Canvas donde Chart.js dibuja la curva suave con gradiente --}}
                    <div class="relative w-full h-[260px]">
                        <canvas id="ventasSemanaChart"></canvas>
                    </div>
                </div>

                {{-- Gráfico 2: Proyección de Vencimientos por Mes (Barras Horizontales 4 Meses) --}}
                <div class="rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl shadow-xl flex flex-col justify-between">
                    {{-- Encabezado del gráfico: Horizonte temporal y enlace rápido al listado de lotes --}}
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-xs font-medium text-slate-400 tracking-wider">HORIZONTE TEMPORAL</span>
                            <h4 class="text-lg font-bold text-white tracking-tight mt-0.5">Lotes por Vencer (4 Meses)</h4>
                        </div>
                        <a href="{{ route('lotes.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                            Ver Lotes
                        </a>
                    </div>
                    {{-- Lienzo Canvas donde Chart.js dibuja las barras de alerta de vencimiento --}}
                    <div class="relative w-full h-[260px]">
                        <canvas id="vencimientosMesChart"></canvas>
                    </div>
                </div>

            </div>

            {{-- ========================================================================= --}}
            {{-- ROW 5: DISTRIBUCIÓN DE INVENTARIO (Gráficos Doughnut / Donas Modernas)     --}}
            {{-- ========================================================================= --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Dona 1: Distribución Física de Stock Disponible por Bodega --}}
                <div class="rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-xs font-medium text-slate-400 tracking-wider">DISTRIBUCIÓN FÍSICA</span>
                            <h4 class="text-base font-bold text-white tracking-tight mt-0.5">Stock Disponible por Bodega</h4>
                        </div>
                    </div>
                    {{-- Contenedor Canvas del gráfico circular o mensaje de estado vacío --}}
                    <div class="relative w-full h-[240px]">
                        @if(count($chartBodegaLabels) > 0)
                            <canvas id="stockBodegaChart"></canvas>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-slate-600 font-light text-sm">Sin datos registrados</div>
                        @endif
                    </div>
                </div>

                {{-- Dona 2: Distribución de Stock por Proveedor / Marca Registrada --}}
                <div class="rounded-3xl bg-slate-900/60 border border-slate-800/80 p-6 backdrop-blur-xl shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-xs font-medium text-slate-400 tracking-wider">ORIGEN DE PROVEEDORES</span>
                            <h4 class="text-base font-bold text-white tracking-tight mt-0.5">Stock por Marca / Distribuidor</h4>
                        </div>
                    </div>
                    {{-- Contenedor Canvas del gráfico circular o mensaje de estado vacío --}}
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

    {{-- ========================================================================= --}}
    {{-- SCRIPTS FRONTEND: Controladores Alpine.js & Inicialización de Chart.js     --}}
    {{-- ========================================================================= --}}
    <script>
        /**
         * Componente reactivo Alpine.js: dashboardHub()
         * Administra en memoria el filtrado instantáneo, paginación local y búsqueda
         * sin recargar la página para el Radar de Vencimientos FEFO y Hub de Bodegas.
         */
        function dashboardHub() {
            return {
                // Colección completa de lotes inyectada desde el controlador Laravel
                allLotes: {!! json_encode($lotesRadar) !!},
                // Metadatos y stock por bodega para la barra superior de bodegas
                bodegas: {!! json_encode($bodegasDetalle) !!},
                // ID de bodega seleccionada para el filtro (null = ver todas)
                selectedBodega: null,
                // Filtro actual de horizonte temporal ('todos', 'vencido', 'critico', etc.)
                currentFilter: 'todos',
                // Texto ingresado en el buscador en vivo
                searchQuery: '',
                // Puntero de paginación actual (base 1)
                currentPage: 1,
                // Cantidad de filas por página en la tabla FEFO
                perPage: 10,

                /**
                 * Alterna la selección de una bodega en el Hub superior.
                 * Si ya está seleccionada la deselecciona; de lo contrario la activa.
                 * Reinicia la paginación a la primera página.
                 */
                toggleBodega(id) {
                    if (this.selectedBodega === id) {
                        this.selectedBodega = null;
                    } else {
                        this.selectedBodega = id;
                    }
                    this.currentPage = 1;
                },

                /**
                 * Restablece únicamente el filtro de bodega seleccionada.
                 */
                resetBodegaFilter() {
                    this.selectedBodega = null;
                    this.currentPage = 1;
                },

                /**
                 * Restablece todos los filtros activos (bodega, horizonte temporal y buscador).
                 */
                resetAllFilters() {
                    this.selectedBodega = null;
                    this.currentFilter = 'todos';
                    this.searchQuery = '';
                    this.currentPage = 1;
                },

                /**
                 * Obtiene el nombre legible de una bodega a partir de su ID.
                 */
                getBodegaName(id) {
                    const b = this.bodegas.find(item => item.id == id);
                    return b ? b.nombre : 'Bodega';
                },

                /**
                 * Aplica un filtro de vencimiento y desplaza suavemente la vista al Radar FEFO.
                 */
                applyFilterAndScroll(filtro) {
                    this.currentFilter = filtro;
                    this.currentPage = 1;
                    this.$nextTick(() => {
                        const el = document.getElementById('radar-fefo-section');
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                },

                /**
                 * Getter computado: Retorna la lista de lotes filtrada en cascada:
                 * 1. Por bodega seleccionada
                 * 2. Por categoría de vencimiento FEFO (dias_restantes exactos)
                 * 3. Por búsqueda de texto (nombre, SKU, bodega, formato, capacidad)
                 */
                get filteredLotes() {
                    let list = this.allLotes;

                    // Paso 1: Filtro por Bodega
                    if (this.selectedBodega !== null) {
                        list = list.filter(l => l.bodega_id == this.selectedBodega);
                    }

                    // Paso 2: Filtro por Urgencia / Horizonte FEFO
                    if (this.currentFilter === 'vencido') {
                        list = list.filter(l => l.dias_restantes < 0);
                    } else if (this.currentFilter === 'critico') {
                        list = list.filter(l => l.dias_restantes >= 0 && l.dias_restantes <= 7);
                    } else if (this.currentFilter === '1mes') {
                        list = list.filter(l => l.dias_restantes >= 0 && l.dias_restantes <= 30);
                    } else if (this.currentFilter === '2meses') {
                        list = list.filter(l => l.dias_restantes >= 0 && l.dias_restantes <= 60);
                    } else if (this.currentFilter === '3meses') {
                        list = list.filter(l => l.dias_restantes >= 0 && l.dias_restantes <= 90);
                    } else if (this.currentFilter === '6meses') {
                        list = list.filter(l => l.dias_restantes >= 0 && l.dias_restantes <= 180);
                    } else if (this.currentFilter === '12meses') {
                        list = list.filter(l => l.dias_restantes >= 0 && l.dias_restantes <= 365);
                    } else if (this.currentFilter === 'optimo') {
                        list = list.filter(l => l.dias_restantes > 365);
                    }

                    // Paso 3: Búsqueda de texto en vivo (case insensitive)
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

                /**
                 * Getter computado: Calcula el total de páginas según la cantidad de items filtrados.
                 */
                get totalPages() {
                    return Math.max(1, Math.ceil(this.filteredLotes.length / this.perPage));
                },

                /**
                 * Getter computado: Aplica el corte (slice) correspondiente a la página activa.
                 */
                get paginatedLotes() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.filteredLotes.slice(start, start + this.perPage);
                },

                /**
                 * Retrocede una página si no se encuentra en la primera.
                 */
                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                    }
                },

                /**
                 * Avanza una página si no se encuentra en la última.
                 */
                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                    }
                }
            };
        }

        // =========================================================================
        // Inicialización y configuración de Chart.js (Tema Ejecutivo Oscuro)
        // =========================================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Paleta armónica de colores para gráficos circulares y métricas
            const chartPalette = [
                '#6366f1', // Indigo 500
                '#06b6d4', // Cyan 500
                '#10b981', // Emerald 500
                '#f59e0b', // Amber 500
                '#a855f7', // Purple 500
                '#f43f5e'  // Rose 500
            ];

            // Configuración visual compartida de tooltips flotantes con efecto glassmorphism
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

            // ---------------------------------------------------------------------
            // 1. Gráfico de Tendencia: Ventas Semanales (Smooth Area Curve)
            // ---------------------------------------------------------------------
            const ctxVentas = document.getElementById('ventasSemanaChart');
            if (ctxVentas) {
                let ctx = ctxVentas.getContext('2d');
                // Gradiente vertical suave desde indigo semitransparente hacia transparente
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
                            tension: 0.4, // Curva Bezier suave
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
                                    // Formato de moneda chilena en el tooltip
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

            // ---------------------------------------------------------------------
            // 2. Gráfico de Barras: Lotes por Vencer en 4 Meses
            // ---------------------------------------------------------------------
            const ctxVenc = document.getElementById('vencimientosMesChart');
            if (ctxVenc) {
                let ctx = ctxVenc.getContext('2d');
                // Gradiente vertical en tono rose para indicar advertencia preventiva
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

            // ---------------------------------------------------------------------
            // 3. Gráfico Circular (Doughnut): Stock Disponible por Bodega
            // ---------------------------------------------------------------------
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
                        cutout: '76%', // Anillo fino moderno
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

            // ---------------------------------------------------------------------
            // 4. Gráfico Circular (Doughnut): Stock por Proveedor / Marca
            // ---------------------------------------------------------------------
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
                        cutout: '76%', // Anillo fino moderno
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
