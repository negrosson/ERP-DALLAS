<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('catalogo.index') }}" class="p-2 transition-colors rounded-lg bg-slate-800/50 hover:bg-slate-700 text-slate-400 hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <h2 class="text-2xl font-bold leading-tight text-white">
                    Detalles del Producto
                </h2>
            </div>
            <a href="{{ route('catalogo.edit', $catalogo) }}" class="px-5 py-2.5 text-sm font-semibold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-lg shadow-indigo-600/30">
                Editar Producto
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
            
            <!-- Tarjeta de Info Principal -->
            <div class="overflow-hidden relative bg-slate-900/50 backdrop-blur-xl shadow-2xl sm:rounded-2xl border border-slate-800/60 p-8 group">
                <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
                    <svg class="w-48 h-48 text-indigo-400" fill="currentColor" viewBox="0 0 24 24"><path d="M20 7h-4V4c0-1.1-.9-2-2-2h-4C8.9 2 8 2.9 8 4v3H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zM10 4h4v3h-4V4zm10 15H4V9h16v10z"/></svg>
                </div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <span class="px-3 py-1 text-xs font-bold tracking-wider text-indigo-400 uppercase bg-indigo-500/10 rounded-full border border-indigo-500/20 shadow-[0_0_10px_rgba(99,102,241,0.2)]">SKU: {{ $catalogo->sku }}</span>
                            @if($catalogo->activo)
                                <span class="px-3 py-1 text-xs font-bold tracking-wider text-emerald-400 uppercase bg-emerald-500/10 rounded-full border border-emerald-500/20 shadow-[0_0_10px_rgba(16,185,129,0.2)]">Activo</span>
                            @else
                                <span class="px-3 py-1 text-xs font-bold tracking-wider text-red-400 uppercase bg-red-500/10 rounded-full border border-red-500/20 shadow-[0_0_10px_rgba(239,68,68,0.2)]">Inactivo</span>
                            @endif
                        </div>
                        <h3 class="text-4xl font-black text-white tracking-tight">{{ $catalogo->nombre }}</h3>
                        <p class="mt-2 text-lg text-slate-400 max-w-2xl">{{ $catalogo->descripcion ?? 'Sin descripción disponible' }}</p>
                    </div>
                    <div class="text-left md:text-right">
                        <p class="text-sm font-medium text-slate-500 uppercase tracking-widest mb-1">Precio Venta</p>
                        <p class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400">
                            ${{ number_format($catalogo->precio_venta, 0, ',', '.') }}
                        </p>
                        <p class="mt-2 text-sm text-slate-400">Unidad: <span class="font-bold text-slate-300">{{ $catalogo->unidad_medida }}</span></p>
                    </div>
                </div>
            </div>

            <!-- Grid: Stock por Bodega y Recepciones -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Stock por Bodega -->
                <div class="lg:col-span-1 space-y-4">
                    <h4 class="text-lg font-bold text-white flex items-center gap-2">
                        <div class="p-2 bg-indigo-500/20 rounded-lg">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        Stock en Bodegas
                    </h4>
                    
                    @php
                        // Agrupar lotes por bodega y sumar cantidad
                        $stockPorBodega = $catalogo->lotesStock->groupBy('bodega_id')->map(function($lotes) {
                            return [
                                'bodega' => $lotes->first()->bodega->nombre ?? 'Desconocida',
                                'total' => $lotes->sum('cantidad_disponible')
                            ];
                        });
                    @endphp

                    @forelse($stockPorBodega as $stock)
                        <div class="p-5 bg-slate-900/40 backdrop-blur-md border border-slate-800/60 rounded-xl hover:bg-slate-800/40 transition-colors shadow-lg">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-slate-300">{{ $stock['bodega'] }}</span>
                                <span class="text-3xl font-black text-white">{{ number_format($stock['total'], 2, ',', '.') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center bg-slate-900/30 border border-slate-800/50 rounded-xl shadow-inner">
                            <p class="text-slate-500 text-sm">No hay stock disponible en ninguna bodega.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Historial de Recepciones -->
                <div class="lg:col-span-2 space-y-4">
                    <h4 class="text-lg font-bold text-white flex items-center gap-2">
                        <div class="p-2 bg-cyan-500/20 rounded-lg">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        Historial de Compras/Recepciones
                    </h4>
                    
                    <div class="overflow-hidden bg-slate-900/50 backdrop-blur-xl border border-slate-800/60 rounded-2xl shadow-xl">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs tracking-wider text-slate-400 uppercase bg-slate-800/50 border-b border-slate-800">
                                <tr>
                                    <th class="px-6 py-4">Fecha</th>
                                    <th class="px-6 py-4">Factura</th>
                                    <th class="px-6 py-4">Proveedor</th>
                                    <th class="px-6 py-4">Bodega Destino</th>
                                    <th class="px-6 py-4 text-right">Ingresado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                @forelse($catalogo->recepcionDetalles->sortByDesc('created_at') as $detalle)
                                    <tr class="hover:bg-slate-800/30 transition-colors group">
                                        <td class="px-6 py-4 font-medium text-slate-300 group-hover:text-white transition-colors">
                                            {{ optional($detalle->recepcion)->fecha_recepcion ? \Carbon\Carbon::parse($detalle->recepcion->fecha_recepcion)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs font-bold text-indigo-400">
                                            {{ optional($detalle->recepcion)->numero_factura ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-300 group-hover:text-white transition-colors">
                                            {{ optional(optional($detalle->recepcion)->proveedor)->nombre ?? 'Desconocido' }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-400 group-hover:text-slate-300 transition-colors">
                                            {{ optional(optional($detalle->recepcion)->bodega)->nombre ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-black text-emerald-400">
                                            +{{ number_format($detalle->cantidad, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center justify-center space-y-3">
                                                <div class="p-3 bg-slate-800/50 rounded-full">
                                                    <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                                </div>
                                                <p class="text-slate-500 text-sm">Este producto no ha tenido recepciones aún.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
