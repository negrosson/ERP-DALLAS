<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold leading-tight tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">
            {{ __('RESUMEN DEL SISTEMA') }}
        </h2>
    </x-slot>

    <!-- Import Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-8 bg-slate-950 min-h-screen font-sans">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- System Alert: Conciliación -->
            @if(count($recepcionesPendientes) > 0)
            <div class="bg-amber-900/30 border-l-4 border-amber-500 p-4 rounded-lg flex flex-col sm:flex-row sm:justify-between sm:items-center shadow-[0_0_15px_rgba(245,158,11,0.1)]">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-4 text-amber-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>
                        <p class="font-semibold text-amber-200 tracking-wide text-sm uppercase">Alerta de Sistema: {{ count($recepcionesPendientes) }} Facturas Pendientes</p>
                        <p class="text-xs text-amber-400/80 mt-1">Lotes en cuarentena esperando conciliación de fechas de vencimiento.</p>
                    </div>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('recepciones.index') }}?estado=PENDIENTE_FECHA" class="inline-block px-5 py-2 font-mono text-xs font-bold text-slate-900 bg-amber-500 hover:bg-amber-400 rounded transition-all duration-200 shadow-[0_0_10px_rgba(245,158,11,0.5)]">
                        EJECUTAR CONCILIACIÓN
                    </a>
                </div>
            </div>
            @endif

            <!-- Main Telemetry Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Metric: SKUs -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg shadow-black/50 group hover:border-cyan-500/30 hover:shadow-[0_0_20px_rgba(34,211,238,0.1)] transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-widest">Nodos Registrados (Catálogo)</p>
                            <h4 class="text-4xl font-light text-slate-200 mt-3 tracking-tight">{{ number_format($totalProductos) }}</h4>
                        </div>
                        <div class="p-3 rounded-xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 text-cyan-400 border border-cyan-500/20 shadow-[0_0_15px_rgba(34,211,238,0.2)]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4 text-xs font-mono text-cyan-500/70 flex items-center">
                        <div class="w-2 h-2 rounded-full bg-cyan-400 mr-2 animate-pulse"></div>
                        <span>ESTADO: ACTIVO</span>
                    </div>
                </div>

                <!-- Metric: Value -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg shadow-black/50 group hover:border-emerald-500/30 hover:shadow-[0_0_20px_rgba(16,185,129,0.1)] transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-widest">Valorización de Stock</p>
                            <h4 class="text-4xl font-light text-emerald-400 mt-3 tracking-tight">${{ number_format($valorTotal, 0, ',', '.') }}</h4>
                        </div>
                        <div class="p-3 rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-600/20 text-emerald-400 border border-emerald-500/20 shadow-[0_0_15px_rgba(16,185,129,0.2)]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4 text-xs font-mono text-emerald-500/70 flex items-center">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 mr-2"></div>
                        <span>CAPITAL INMOVILIZADO</span>
                    </div>
                </div>

                <!-- Metric: Critical (Semaforo Style) -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-lg shadow-black/50 group hover:border-red-500/30 transition-all duration-300 flex flex-col justify-between">
                    @php
                        $totalRiesgo = $counts['critico'] + $counts['alto'] + $counts['medio'] + $counts['bajo'];
                        $isCritical = $counts['critico'] > 0;
                        $isWarning = $totalRiesgo > 0 && !$isCritical;
                        
                        $bannerClass = $isCritical ? 'bg-red-700/90 text-white shadow-[0_4px_15px_rgba(185,28,28,0.5)]' : ($isWarning ? 'bg-yellow-600/90 text-white shadow-[0_4px_15px_rgba(202,138,4,0.5)]' : 'bg-emerald-600/90 text-white shadow-[0_4px_15px_rgba(5,150,105,0.5)]');
                        $bannerTitle = $isCritical ? 'ESTADO CRÍTICO' : ($isWarning ? 'ESTADO DE ATENCIÓN' : 'ESTADO NORMAL');
                        $bannerText = $isCritical ? 'Producto con stock crítico o vencido' : ($isWarning ? 'Productos próximos a vencer' : 'Inventario en óptimas condiciones');
                        
                        $totalLotes = \App\Models\LoteStock::where('cantidad_disponible', '>', 0)->count();
                        $okCount = $totalLotes - $totalRiesgo;
                    @endphp

                    <!-- Banner Top -->
                    <div class="{{ $bannerClass }} rounded-xl p-4 flex items-center gap-4 mb-4 transition-all duration-300">
                        <div class="shrink-0 bg-white/20 p-2 rounded-lg">
                            @if($isCritical)
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            @elseif($isWarning)
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @else
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-bold text-[15px] tracking-wide leading-none mb-1">{{ $bannerTitle }}</h4>
                            <p class="text-[12px] opacity-90 leading-tight">{{ $bannerText }}</p>
                        </div>
                    </div>

                    <!-- Bottom Stats -->
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-slate-950/50 rounded-xl p-2 flex flex-col items-center justify-center border border-slate-800 shadow-inner">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <svg class="w-4 h-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                <span class="text-xl font-bold text-slate-200">{{ $counts['critico'] + $counts['alto'] }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-medium">Críticos</span>
                        </div>
                        <div class="bg-slate-950/50 rounded-xl p-2 flex flex-col items-center justify-center border border-slate-800 shadow-inner">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <div class="w-3.5 h-3.5 rounded-full bg-yellow-500 shadow-[0_0_8px_rgba(234,179,8,0.6)]"></div>
                                <span class="text-xl font-bold text-slate-200">{{ $counts['medio'] + $counts['bajo'] }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-medium">Atención</span>
                        </div>
                        <div class="bg-slate-950/50 rounded-xl p-2 flex flex-col items-center justify-center border border-slate-800 shadow-inner">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                <span class="text-xl font-bold text-slate-200">{{ $okCount }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-medium">OK</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Gráficos & Tabla (Grid Superior) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Panel Principal: Tabla FEFO -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl shadow-lg shadow-black/50 overflow-hidden flex flex-col h-[400px]">
                    <div class="px-6 py-4 border-b border-slate-800 bg-slate-900 flex justify-between items-center shrink-0">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <h3 class="text-sm font-bold tracking-widest text-slate-200 uppercase">Tabla de Datos: Lotes en Riesgo</h3>
                        </div>
                        <a href="{{ route('lotes.index') }}" class="text-xs font-bold font-mono tracking-widest text-cyan-500 hover:text-cyan-300 transition-colors shadow-[0_0_10px_rgba(34,211,238,0)] hover:shadow-[0_0_10px_rgba(34,211,238,0.3)] px-3 py-1 rounded">VER TODOS</a>
                    </div>
                    
                    <div class="overflow-y-auto flex-1 custom-scrollbar">
                        <table class="w-full text-sm text-left text-slate-400">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-800/80 sticky top-0 z-10 border-b border-slate-700 shadow-md backdrop-blur-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">SKU / Lote</th>
                                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Cant.</th>
                                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Bodega</th>
                                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                @php
                                    $alertLotes = collect($lotesVencidos)->concat($lotesPorVencer)->take(20);
                                @endphp
                                
                                @forelse($alertLotes as $lote)
                                    @php
                                        $vencimiento = \Carbon\Carbon::parse($lote->fecha_vencimiento);
                                        $hoy = now();
                                        $dias = $hoy->diffInDays($vencimiento, false);
                                        
                                        if ($dias < 0) {
                                            $badgeClass = "bg-red-950/50 border-red-500/30 text-red-400 shadow-[0_0_10px_rgba(239,68,68,0.3)]";
                                            $badgeText = "FALLO: VENCIDO";
                                        } elseif ($dias < 7) {
                                            $badgeClass = "bg-rose-950/50 border-rose-500/30 text-rose-400 shadow-[0_0_10px_rgba(244,63,94,0.3)]";
                                            $badgeText = "CRÍTICO: " . intval($dias) . " DIAS";
                                        } elseif ($dias <= 14) {
                                            $badgeClass = "bg-orange-950/50 border-orange-500/30 text-orange-400 shadow-[0_0_10px_rgba(249,115,22,0.2)]";
                                            $badgeText = "ALERTA: " . intval($dias) . " DIAS";
                                        } elseif ($dias <= 21) {
                                            $badgeClass = "bg-yellow-950/50 border-yellow-500/30 text-yellow-400";
                                            $badgeText = "PREVENTIVO: " . intval($dias) . " DIAS";
                                        } else {
                                            $badgeClass = "bg-cyan-950/50 border-cyan-500/30 text-cyan-400";
                                            $badgeText = "ATENCIÓN: " . intval($dias) . " DIAS";
                                        }
                                    @endphp
                                    <tr class="bg-slate-900 hover:bg-slate-800/80 transition-colors duration-200">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-200">{{ $lote->producto->nombre }}</div>
                                            <div class="font-mono text-xs text-cyan-500/70 mt-1">LOTE_ID: #{{ str_pad($lote->id, 5, '0', STR_PAD_LEFT) }} | SKU: {{ $lote->producto->sku }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right font-mono font-bold text-slate-300">
                                            {{ number_format($lote->cantidad_disponible) }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs text-slate-400">
                                            > {{ strtoupper($lote->bodega->nombre) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col items-start gap-2">
                                                <span class="inline-flex items-center rounded-full px-3 py-1 border text-[10px] font-bold tracking-widest {{ $badgeClass }}">
                                                    {{ $badgeText }}
                                                </span>
                                                <span class="font-mono text-xs text-slate-500 ml-1">
                                                    EXP: {{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d-m-Y') }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-20 text-center">
                                            <div class="flex flex-col items-center justify-center space-y-4">
                                                <div class="p-5 bg-slate-800 rounded-full border border-slate-700 shadow-[inset_0_2px_10px_rgba(0,0,0,0.5)]">
                                                    <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </div>
                                                <h3 class="text-sm font-mono tracking-widest text-emerald-400 shadow-emerald-400/20">NO SE DETECTARON LOTES CRÍTICOS</h3>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Panel Secundario: Stock por Proveedor -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-lg shadow-black/50 p-6 flex flex-col h-[400px] hover:border-fuchsia-500/30 transition-all duration-300">
                    <div class="flex items-center mb-6">
                        <svg class="w-5 h-5 mr-3 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <h3 class="text-sm font-bold tracking-widest text-slate-200 uppercase">Stock por Proveedor</h3>
                    </div>
                    
                    <div class="flex-1 relative w-full h-full min-h-[250px]">
                        @if(count($chartLabels) > 0)
                            <canvas id="stockProveedorChart"></canvas>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center flex-col text-slate-600">
                                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <span class="font-mono text-xs tracking-widest">DATOS INSUFICIENTES</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Tercera Fila: Gráficos Adicionales -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Panel Gráfico: Stock por Bodega -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-lg shadow-black/50 p-6 flex flex-col h-[400px] hover:border-blue-500/30 transition-all duration-300">
                    <div class="flex items-center mb-6">
                        <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <h3 class="text-sm font-bold tracking-widest text-slate-200 uppercase">Stock por Bodega</h3>
                    </div>
                    <div class="flex-1 relative w-full h-full min-h-[250px]">
                        @if(count($chartBodegaLabels) > 0)
                            <canvas id="stockBodegaChart"></canvas>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center flex-col text-slate-600">
                                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <span class="font-mono text-xs tracking-widest">DATOS INSUFICIENTES</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Panel Gráfico: Ventas de la Semana -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl shadow-lg shadow-black/50 p-6 flex flex-col h-[400px] hover:border-emerald-500/30 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            <h3 class="text-sm font-bold tracking-widest text-slate-200 uppercase">Flujo de Ventas (Últimos 7 Días)</h3>
                        </div>
                        <div class="text-emerald-400 font-mono text-sm tracking-wide">
                            Total Semanal: ${{ number_format($chartVentasData->sum(), 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="flex-1 relative w-full h-full min-h-[250px]">
                        <canvas id="ventasSemanaChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart Configuration Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Common Neon Palette
            const neonColors = [
                'rgba(34, 211, 238, 0.7)',  // Cyan
                'rgba(168, 85, 247, 0.7)',  // Purple
                'rgba(236, 72, 153, 0.7)',  // Pink
                'rgba(16, 185, 129, 0.7)',  // Emerald
                'rgba(249, 115, 22, 0.7)',  // Orange
                'rgba(234, 179, 8, 0.7)',   // Yellow
            ];
            
            const neonBorders = [
                'rgba(34, 211, 238, 1)',
                'rgba(168, 85, 247, 1)',
                'rgba(236, 72, 153, 1)',
                'rgba(16, 185, 129, 1)',
                'rgba(249, 115, 22, 1)',
                'rgba(234, 179, 8, 1)',
            ];

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#94a3b8',
                            font: {
                                family: 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace',
                                size: 11
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#38bdf8',
                        bodyColor: '#e2e8f0',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        boxPadding: 4,
                        cornerRadius: 8
                    }
                }
            };

            // Chart 1: Stock por Proveedor
            @if(count($chartLabels) > 0)
            const ctxProveedor = document.getElementById('stockProveedorChart').getContext('2d');
            new Chart(ctxProveedor, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [{
                        data: {!! json_encode($chartData) !!},
                        backgroundColor: neonColors,
                        borderColor: neonBorders,
                        borderWidth: 2,
                        hoverOffset: 6
                    }]
                },
                options: {
                    ...commonOptions,
                    cutout: '75%',
                }
            });
            @endif

            // Chart 2: Stock por Bodega
            @if(count($chartBodegaLabels) > 0)
            const ctxBodega = document.getElementById('stockBodegaChart').getContext('2d');
            new Chart(ctxBodega, {
                type: 'pie',
                data: {
                    labels: {!! json_encode($chartBodegaLabels) !!},
                    datasets: [{
                        data: {!! json_encode($chartBodegaData) !!},
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.7)', // Blue
                            'rgba(244, 63, 94, 0.7)',  // Rose
                            'rgba(16, 185, 129, 0.7)', // Emerald
                        ],
                        borderColor: [
                            'rgba(59, 130, 246, 1)',
                            'rgba(244, 63, 94, 1)',
                            'rgba(16, 185, 129, 1)',
                        ],
                        borderWidth: 2,
                        hoverOffset: 6
                    }]
                },
                options: {
                    ...commonOptions,
                }
            });
            @endif

            // Chart 3: Ventas de la Semana
            const ctxVentas = document.getElementById('ventasSemanaChart').getContext('2d');
            
            // Create a gradient for the bar chart
            let gradient = ctxVentas.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.8)'); // Emerald top
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0.1)'); // Transparent bottom

            new Chart(ctxVentas, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartVentasLabels) !!},
                    datasets: [{
                        label: 'Ventas ($)',
                        data: {!! json_encode($chartVentasData) !!},
                        backgroundColor: gradient,
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#0f172a',
                        pointBorderColor: 'rgba(16, 185, 129, 1)',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        legend: { display: false },
                        tooltip: commonOptions.plugins.tooltip
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(51, 65, 85, 0.3)', // slate-700 with opacity
                                drawBorder: false,
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: { family: 'monospace' },
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(51, 65, 85, 0.1)',
                                drawBorder: false,
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: { family: 'monospace' }
                            }
                        }
                    }
                }
            });
        });
    </script>

    <style>
        /* Custom scrollbar for data grid to maintain aesthetics */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #0f172a; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155; 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #475569; 
        }
    </style>
</x-app-layout>
