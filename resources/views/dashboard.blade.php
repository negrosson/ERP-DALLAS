<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-light tracking-tight text-white flex items-center gap-3">
            <span class="w-1.5 h-8 bg-cyan-400 rounded-full shadow-[0_0_10px_rgba(34,211,238,0.8)]"></span>
            {{ __('Resumen del Sistema') }}
        </h2>
    </x-slot>

    <!-- Import Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Fondo Principal: Estilo Black Apple + Tron Grid sutil -->
    <div class="py-8 bg-black min-h-screen font-sans text-slate-200 relative">
        <!-- Tron Grid Background Overlay -->
        <div class="absolute inset-0 z-0 opacity-20" 
             style="background-image: linear-gradient(rgba(34, 211, 238, 0.2) 1px, transparent 1px), linear-gradient(90deg, rgba(34, 211, 238, 0.2) 1px, transparent 1px); background-size: 50px 50px;">
        </div>
        
        <!-- Glows de fondo central -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-cyan-600/10 rounded-full blur-[120px] pointer-events-none z-0"></div>

        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8 relative z-10">
            
            <!-- System Alert: Conciliación (Estilo Glassmorphism + Tron) -->
            @if(count($recepcionesPendientes) > 0)
            <div class="bg-slate-900/60 backdrop-blur-xl border border-rose-500/30 p-5 rounded-3xl flex flex-col sm:flex-row sm:justify-between sm:items-center shadow-[0_0_30px_rgba(244,63,94,0.15)] relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-r from-rose-500/10 to-transparent"></div>
                <div class="flex items-center relative z-10">
                    <div class="p-3 bg-rose-500/20 rounded-2xl mr-4 border border-rose-500/30 shadow-[0_0_15px_rgba(244,63,94,0.3)]">
                        <svg class="w-6 h-6 text-rose-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <p class="font-medium text-white tracking-wide text-sm">Alerta de Sistema: {{ count($recepcionesPendientes) }} Facturas Pendientes</p>
                        <p class="text-xs text-rose-300/70 mt-1 font-light">Lotes en cuarentena esperando conciliación de fechas de vencimiento.</p>
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 relative z-10">
                    <a href="{{ route('recepciones.index') }}?estado=PENDIENTE_FECHA" class="inline-block px-6 py-2.5 font-medium text-xs text-white bg-rose-600/80 hover:bg-rose-500 backdrop-blur-md rounded-2xl transition-all duration-300 shadow-[0_0_15px_rgba(225,29,72,0.4)] border border-rose-400/50">
                        Ejecutar Conciliación
                    </a>
                </div>
            </div>
            @endif

            <!-- ROW 1: Top Metrics & Vencimientos Chart -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Tarjeta 1: Productos (Apple Glass Style) -->
                <a href="{{ route('catalogo.index') }}" class="bg-slate-900/50 backdrop-blur-2xl border border-slate-700/50 hover:border-cyan-400/50 rounded-[2rem] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.12)] transition-all duration-300 flex flex-col justify-between group relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div>
                        <p class="text-xs font-medium text-slate-400 tracking-wider">Productos en Catálogo</p>
                        <p class="text-5xl font-thin text-white tracking-tighter mt-3">{{ number_format($totalProductos, 0, ',', '.') }}</p>
                    </div>
                    <div class="mt-6 flex flex-col gap-1.5 text-xs font-light text-slate-400">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-cyan-400 shadow-[0_0_5px_rgba(34,211,238,0.8)]"></div>
                            <span>{{ number_format($productosConStock, 0) }} con stock</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-slate-600"></div>
                            <span>{{ number_format($productosSinStock, 0) }} sin stock</span>
                        </div>
                    </div>
                </a>

                <!-- Tarjeta 2: Valorización -->
                <a href="{{ route('bodegas.index') }}" class="bg-slate-900/50 backdrop-blur-2xl border border-slate-700/50 hover:border-cyan-400/50 rounded-[2rem] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.12)] transition-all duration-300 flex flex-col justify-between group relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div>
                        <p class="text-xs font-medium text-slate-400 tracking-wider">Valorización de Stock</p>
                        <p class="text-4xl font-thin text-white tracking-tighter mt-3">${{ number_format($valorTotal, 0, ',', '.') }}</p>
                    </div>
                    <div class="mt-6 flex items-center justify-between border-t border-slate-700/50 pt-4">
                        <span class="text-xs font-light text-slate-400 tracking-wide">Unidades Totales</span>
                        <span class="text-sm font-medium text-cyan-400">{{ number_format($totalUnidades, 0, ',', '.') }}</span>
                    </div>
                </a>

                <!-- Tarjeta 3: Gráfico Vencimientos (Ocupa 2 columnas) -->
                <div class="lg:col-span-2 bg-slate-900/50 backdrop-blur-2xl border border-slate-700/50 rounded-[2rem] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.12)] relative overflow-hidden flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <p class="text-xs font-medium text-slate-400 tracking-wider">Lotes por Vencer (Próximos 3 meses)</p>
                        <a href="{{ route('lotes.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">Ver Detalles</a>
                    </div>
                    <div class="relative w-full flex-1 min-h-[120px]">
                        <canvas id="vencimientosMesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- ROW 2: Main Flow Chart (Ventas) -->
            <div class="bg-slate-900/50 backdrop-blur-2xl border border-cyan-500/20 rounded-[2rem] p-6 shadow-[0_0_20px_rgba(34,211,238,0.05)] relative overflow-hidden">
                <!-- Decoración Tron -->
                <div class="absolute top-0 right-0 p-6 flex gap-2">
                    <div class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse shadow-[0_0_8px_rgba(34,211,238,1)]"></div>
                    <div class="text-[10px] text-cyan-500/70 font-mono tracking-widest">SYS.FLOW.OK</div>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6">
                    <div>
                        <h3 class="text-xl font-light text-white tracking-tight">Flujo de Ventas</h3>
                        <p class="text-xs font-medium text-slate-400 tracking-wider mt-1">Últimos 7 días</p>
                    </div>
                    <div class="mt-4 md:mt-0 text-right">
                        <div class="text-4xl font-thin text-cyan-400 tracking-tighter">
                            ${{ number_format($chartVentasData->sum(), 0, ',', '.') }}
                        </div>
                        <span class="text-[10px] text-slate-500 uppercase tracking-widest">Total Semanal</span>
                    </div>
                </div>
                <div class="relative w-full h-[300px]">
                    <canvas id="ventasSemanaChart"></canvas>
                </div>
            </div>

            <!-- ROW 3: Status & Doughnuts -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Estado Inventario (Semáforo minimalista) -->
                <div class="bg-slate-900/50 backdrop-blur-2xl border border-slate-700/50 rounded-[2rem] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex flex-col justify-between">
                    <p class="text-xs font-medium text-slate-400 tracking-wider mb-6">Estado de Inventario</p>
                    @php
                        $totalRiesgo = $counts['critico'] + $counts['alto'] + $counts['medio'] + $counts['bajo'];
                        $totalLotes = \App\Models\LoteStock::where('cantidad_disponible', '>', 0)->count();
                        $okCount = $totalLotes - $totalRiesgo;
                    @endphp
                    <div class="space-y-5">
                        <!-- Crítico -->
                        <div class="bg-black/30 border border-slate-800 rounded-2xl p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.8)]"></div>
                                <span class="text-sm font-light text-white">Crítico</span>
                            </div>
                            <span class="text-xl font-light text-rose-400">{{ $counts['critico'] + $counts['alto'] }}</span>
                        </div>
                        <!-- Atención -->
                        <div class="bg-black/30 border border-slate-800 rounded-2xl p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.8)]"></div>
                                <span class="text-sm font-light text-white">Atención</span>
                            </div>
                            <span class="text-xl font-light text-amber-400">{{ $counts['medio'] + $counts['bajo'] }}</span>
                        </div>
                        <!-- Óptimo -->
                        <div class="bg-black/30 border border-slate-800 rounded-2xl p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-cyan-400 shadow-[0_0_10px_rgba(34,211,238,0.8)]"></div>
                                <span class="text-sm font-light text-white">Óptimo</span>
                            </div>
                            <span class="text-xl font-light text-cyan-400">{{ $okCount }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stock por Bodega -->
                <div class="bg-slate-900/50 backdrop-blur-2xl border border-slate-700/50 rounded-[2rem] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex flex-col">
                    <p class="text-xs font-medium text-slate-400 tracking-wider mb-4">Stock por Bodega</p>
                    <div class="flex-1 relative w-full h-[220px]">
                        @if(count($chartBodegaLabels) > 0)
                            <canvas id="stockBodegaChart"></canvas>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-slate-600 font-light text-sm">Sin datos</div>
                        @endif
                    </div>
                </div>

                <!-- Stock por Proveedor -->
                <div class="bg-slate-900/50 backdrop-blur-2xl border border-slate-700/50 rounded-[2rem] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex flex-col">
                    <p class="text-xs font-medium text-slate-400 tracking-wider mb-4">Stock por Proveedor</p>
                    <div class="flex-1 relative w-full h-[220px]">
                        @if(count($chartLabels) > 0)
                            <canvas id="stockProveedorChart"></canvas>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-slate-600 font-light text-sm">Sin datos</div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Chart Configuration Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Paleta Tron / Apple (Cyan vibrante, Azules profundos, Blancos translúcidos)
            const tronColors = [
                'rgba(34, 211, 238, 0.8)',   // Cyan 400
                'rgba(56, 189, 248, 0.7)',   // Light Blue 400
                'rgba(14, 165, 233, 0.6)',   // Sky 500
                'rgba(99, 102, 241, 0.7)',   // Indigo 500
                'rgba(148, 163, 184, 0.5)',  // Slate 400
                'rgba(248, 113, 113, 0.7)'   // Red 400 (if needed)
            ];
            
            const tronBorders = [
                'rgba(34, 211, 238, 1)',
                'rgba(56, 189, 248, 1)',
                'rgba(14, 165, 233, 1)',
                'rgba(99, 102, 241, 1)',
                'rgba(148, 163, 184, 1)',
                'rgba(248, 113, 113, 1)'
            ];

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                color: '#94a3b8',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#94a3b8',
                            font: { family: 'sans-serif', size: 12, weight: '300' },
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#e2e8f0',
                        borderColor: 'rgba(34, 211, 238, 0.3)', // Cyan border on tooltips
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: true,
                        boxPadding: 6,
                        backdropFilter: 'blur(10px)'
                    }
                }
            };

            // 1. Chart: Ventas de la Semana (Tron Line)
            const ctxVentas = document.getElementById('ventasSemanaChart');
            if (ctxVentas) {
                let ctx = ctxVentas.getContext('2d');
                let gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(34, 211, 238, 0.3)'); // Cyan transparent top
                gradient.addColorStop(1, 'rgba(34, 211, 238, 0.0)'); // Fully transparent bottom

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartVentasLabels) !!},
                        datasets: [{
                            label: 'Ventas ($)',
                            data: {!! json_encode($chartVentasData) !!},
                            backgroundColor: gradient,
                            borderColor: 'rgba(34, 211, 238, 1)', // Cyan Neon Line
                            borderWidth: 3,
                            tension: 0.4, // Smooth curve (Apple style)
                            fill: true,
                            pointBackgroundColor: '#000000',
                            pointBorderColor: 'rgba(34, 211, 238, 1)',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            pointHoverBackgroundColor: 'rgba(34, 211, 238, 1)',
                            pointHoverBorderColor: '#ffffff'
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
                                grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                                border: { display: false },
                                ticks: {
                                    color: '#64748b',
                                    font: { family: 'sans-serif', size: 11, weight: '300' },
                                    callback: value => '$' + new Intl.NumberFormat('es-CL').format(value)
                                }
                            },
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { color: '#64748b', font: { family: 'sans-serif', size: 11, weight: '300' } }
                            }
                        }
                    }
                });
            }

            // 2. Chart: Lotes por Vencer
            const ctxVenc = document.getElementById('vencimientosMesChart');
            if (ctxVenc) {
                new Chart(ctxVenc, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($chartVencLabels) !!},
                        datasets: [{
                            label: 'Lotes por vencer',
                            data: {!! json_encode($chartVencData) !!},
                            backgroundColor: 'rgba(244, 63, 94, 0.8)', // Rose 500
                            borderRadius: 6,
                            barThickness: 16
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: commonOptions.plugins.tooltip },
                        scales: {
                            x: { grid: { display: false }, border: { display: false }, ticks: { color: '#64748b', font: { size: 10, weight: '300' } } },
                            y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, border: { display: false }, ticks: { color: '#64748b', font: { size: 10, weight: '300' }, stepSize: 1, beginAtZero: true } }
                        }
                    }
                });
            }

            // 3. Chart: Stock por Bodega (Doughnut)
            @if(count($chartBodegaLabels) > 0)
            const ctxBodega = document.getElementById('stockBodegaChart');
            if (ctxBodega) {
                new Chart(ctxBodega, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($chartBodegaLabels) !!},
                        datasets: [{
                            data: {!! json_encode($chartBodegaData) !!},
                            backgroundColor: tronColors,
                            borderColor: '#000000', // Black gaps between slices
                            borderWidth: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        ...commonOptions,
                        cutout: '80%', // Thinner ring for an Apple/Tron look
                    }
                });
            }
            @endif

            // 4. Chart: Stock por Proveedor (Pie)
            @if(count($chartLabels) > 0)
            const ctxProveedor = document.getElementById('stockProveedorChart');
            if (ctxProveedor) {
                new Chart(ctxProveedor, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($chartLabels) !!},
                        datasets: [{
                            data: {!! json_encode($chartData) !!},
                            backgroundColor: tronColors.slice().reverse(),
                            borderColor: '#000000',
                            borderWidth: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        ...commonOptions,
                        cutout: '80%',
                    }
                });
            }
            @endif

        });
    </script>

    <style>
        /* Minimalist Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(34, 211, 238, 0.5); }
    </style>
</x-app-layout>
