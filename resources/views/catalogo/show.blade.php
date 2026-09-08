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
                    <div class="text-left md:text-right flex flex-col md:items-end justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 uppercase tracking-widest mb-1">Precio Venta</p>
                            <p class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400">
                                ${{ number_format($catalogo->precio_venta, 0, ',', '.') }}
                            </p>
                            <p class="mt-2 text-sm text-slate-400">Unidad: <span class="font-bold text-slate-300">{{ $catalogo->unidad_medida }}</span></p>
                        </div>
                        <div class="mt-4 p-3 bg-slate-800/40 rounded-lg border border-slate-700/50 inline-block text-left md:text-right w-max md:w-auto self-start md:self-auto">
                            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Fecha de Ingreso al Sistema</p>
                            <p class="text-slate-200 font-medium">{{ $catalogo->created_at->format('d/m/Y H:i') }}</p>
                        </div>
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
                                <span class="text-3xl font-black text-white">{{ number_format($stock['total'], 0, ',', '.') }}</span>
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
                    
                    <div class="bg-slate-900/50 backdrop-blur-xl border border-slate-800/60 rounded-2xl shadow-xl overflow-hidden">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="hidden md:table w-full text-sm text-left whitespace-nowrap min-w-[600px]">
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
                                            +{{ number_format($detalle->cantidad, 0, ',', '.') }}
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
                        
                        <!-- Vista Móvil: Tarjetas -->
                        <div class="md:hidden flex flex-col divide-y divide-slate-800/50 pb-20">
                            @forelse($catalogo->recepcionDetalles->sortByDesc('created_at') as $detalle)
                                <div class="p-4 flex flex-col gap-2 hover:bg-slate-800/30 transition-colors">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-2">
                                            <span class="p-1.5 bg-indigo-500/10 text-indigo-400 rounded-md">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </span>
                                            <span class="font-mono text-sm font-bold text-indigo-400">
                                                {{ optional($detalle->recepcion)->numero_factura ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <span class="text-xs font-medium text-slate-400">
                                            {{ optional($detalle->recepcion)->fecha_recepcion ? \Carbon\Carbon::parse($detalle->recepcion->fecha_recepcion)->format('d/m/Y') : 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="text-sm font-medium text-slate-200 mt-1">
                                        {{ optional(optional($detalle->recepcion)->proveedor)->nombre ?? 'Desconocido' }}
                                    </div>
                                    <div class="flex justify-between items-end mt-1">
                                        <div>
                                            <span class="text-[10px] text-slate-500 uppercase font-bold block mb-0.5">Destino</span>
                                            <span class="text-xs text-slate-300 border border-slate-700 bg-slate-800/50 px-2 py-0.5 rounded-md">
                                                {{ optional(optional($detalle->recepcion)->bodega)->nombre ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] text-slate-500 uppercase font-bold block mb-0.5">Ingresado</span>
                                            <span class="font-black text-emerald-400 text-lg leading-none">
                                                +{{ number_format($detalle->cantidad, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center flex flex-col items-center justify-center">
                                    <div class="p-3 bg-slate-800/50 rounded-full mb-3">
                                        <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    </div>
                                    <p class="text-slate-500 text-sm">Este producto no ha tenido recepciones aún.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lotes de este Producto -->
            <div x-data="{ 
                showModal: false, 
                editLoteId: null, 
                editElab: '', 
                editVenc: '', 
                formAction: '' 
            }" class="mt-8 space-y-4">
                <h4 class="text-lg font-bold text-white flex items-center gap-2">
                    <div class="p-2 bg-rose-500/20 rounded-lg">
                        <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    </div>
                    Estado de Lotes y Vencimientos
                </h4>

                @if(session('status'))
                    <div class="p-4 mb-4 text-sm text-emerald-400 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="bg-slate-900/50 backdrop-blur-xl border border-slate-800/60 rounded-2xl shadow-xl overflow-hidden">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="hidden md:table w-full text-sm text-left whitespace-nowrap min-w-[600px]">
                            <thead class="text-xs tracking-wider text-slate-400 uppercase bg-slate-800/50 border-b border-slate-800">
                            <tr>
                                <th class="px-6 py-4">ID Lote</th>
                                <th class="px-6 py-4">Bodega</th>
                                <th class="px-6 py-4 text-right">Cant. Disp.</th>
                                <th class="px-6 py-4">F. Elaboración</th>
                                <th class="px-6 py-4">F. Vencimiento</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            @forelse($catalogo->lotesStock->where('cantidad_disponible', '>', 0)->sortBy('fecha_vencimiento') as $lote)
                                @php
                                    $venc = $lote->fecha_vencimiento;
                                    $hoy = now()->startOfDay();
                                    $estadoClass = '';
                                    $estadoText = '';
                                    if ($venc) {
                                        if ($venc->lt($hoy)) {
                                            $estadoClass = 'bg-rose-500/10 text-rose-400 border-rose-500/30';
                                            $estadoText = 'VENCIDO';
                                        } elseif ($venc->lte($hoy->copy()->addDays(7))) {
                                            $estadoClass = 'bg-rose-500/10 text-rose-400 border-rose-500/30';
                                            $estadoText = 'CRÍTICO';
                                        } elseif ($venc->lte($hoy->copy()->addDays(30))) {
                                            $estadoClass = 'bg-amber-500/10 text-amber-400 border-amber-500/30';
                                            $estadoText = 'ALERTA';
                                        } else {
                                            $estadoClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
                                            $estadoText = 'SANO';
                                        }
                                    } else {
                                        $estadoClass = 'bg-slate-500/10 text-slate-400 border-slate-500/30';
                                        $estadoText = 'S/F';
                                    }
                                @endphp
                                <tr class="hover:bg-slate-800/30 transition-colors group">
                                    <td class="px-6 py-4 font-mono text-xs font-bold text-slate-300 group-hover:text-white transition-colors">
                                        #{{ $lote->id }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-300 group-hover:text-white transition-colors">
                                        {{ optional($lote->bodega)->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-white">
                                        {{ number_format($lote->cantidad_disponible, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-300 group-hover:text-white transition-colors">
                                        {{ $lote->fecha_elaboracion ? $lote->fecha_elaboracion->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 font-medium {{ $venc && $venc->lt($hoy) ? 'text-rose-400 font-bold' : 'text-slate-300 group-hover:text-white' }}">
                                        {{ $venc ? $venc->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold border {{ $estadoClass }}">
                                            {{ $estadoText }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button type="button" 
                                                @click="
                                                    editLoteId = {{ $lote->id }}; 
                                                    editElab = '{{ $lote->fecha_elaboracion ? $lote->fecha_elaboracion->format('Y-m-d') : '' }}'; 
                                                    editVenc = '{{ $venc ? $venc->format('Y-m-d') : '' }}'; 
                                                    formAction = '{{ route('lotes.fechas.update', $lote->id) }}';
                                                    showModal = true;
                                                "
                                                class="px-3 py-1.5 bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600/40 border border-indigo-500/30 rounded-lg text-xs font-bold transition-colors">
                                            Editar Fechas
                                        </button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Vista Móvil: Tarjetas de Lotes -->
                    <div class="md:hidden flex flex-col divide-y divide-slate-800/50 pb-24">
                        @forelse($catalogo->lotesStock->where('cantidad_disponible', '>', 0)->sortBy('fecha_vencimiento') as $lote)
                            @php
                                $venc = $lote->fecha_vencimiento;
                                $hoy = now()->startOfDay();
                                $estadoClass = '';
                                $estadoText = '';
                                if ($venc) {
                                    if ($venc->lt($hoy)) {
                                        $estadoClass = 'bg-rose-500/10 text-rose-400 border-rose-500/30';
                                        $estadoText = 'VENCIDO';
                                    } elseif ($venc->lte($hoy->copy()->addDays(7))) {
                                        $estadoClass = 'bg-rose-500/10 text-rose-400 border-rose-500/30';
                                        $estadoText = 'CRÍTICO';
                                    } elseif ($venc->lte($hoy->copy()->addDays(30))) {
                                        $estadoClass = 'bg-amber-500/10 text-amber-400 border-amber-500/30';
                                        $estadoText = 'ALERTA';
                                    } else {
                                        $estadoClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
                                        $estadoText = 'SANO';
                                    }
                                } else {
                                    $estadoClass = 'bg-slate-500/10 text-slate-400 border-slate-500/30';
                                    $estadoText = 'S/F';
                                }
                            @endphp
                            <div class="p-4 flex flex-col gap-3 hover:bg-slate-800/30 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-mono text-xs font-bold text-slate-400 block mb-1">LOTE #{{ $lote->id }}</span>
                                        <span class="text-sm font-medium text-slate-200 block">{{ optional($lote->bodega)->nombre ?? 'Bodega N/A' }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] text-slate-500 uppercase font-bold block mb-0.5">Cant. Disp.</span>
                                        <span class="font-black text-white text-lg leading-none">{{ number_format($lote->cantidad_disponible, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2 text-sm mt-1">
                                    <div>
                                        <span class="text-slate-500 block text-xs">Elaboración:</span>
                                        <span class="text-slate-300 font-medium">{{ $lote->fecha_elaboracion ? $lote->fecha_elaboracion->format('d/m/Y') : 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 block text-xs">Vencimiento:</span>
                                        <span class="font-medium {{ $venc && $venc->lt($hoy) ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                                            {{ $venc ? $venc->format('d/m/Y') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center mt-2 border-t border-slate-700/50 pt-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold border {{ $estadoClass }}">
                                        {{ $estadoText }}
                                    </span>
                                    <button type="button" 
                                            @click="
                                                editLoteId = {{ $lote->id }}; 
                                                editElab = '{{ $lote->fecha_elaboracion ? $lote->fecha_elaboracion->format('Y-m-d') : '' }}'; 
                                                editVenc = '{{ $venc ? $venc->format('Y-m-d') : '' }}'; 
                                                formAction = '{{ route('lotes.fechas.update', $lote->id) }}';
                                                showModal = true;
                                            "
                                            class="px-4 py-2 bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600/40 border border-indigo-500/30 rounded-lg text-xs font-bold transition-colors shadow-sm">
                                        Editar Fechas
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center flex flex-col items-center justify-center">
                                <div class="p-3 bg-slate-800/50 rounded-full mb-3">
                                    <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                </div>
                                <p class="text-slate-500 text-sm">No hay lotes con stock para este producto.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Modal Editar Fechas Lote -->
                <div x-show="showModal" 
                     style="display: none;" 
                     class="fixed inset-0 z-50 overflow-y-auto" 
                     aria-labelledby="modal-title" 
                     role="dialog" 
                     aria-modal="true">
                    
                    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Background overlay -->
                        <div x-show="showModal" 
                             x-transition:enter="ease-out duration-300" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100" 
                             x-transition:leave="ease-in duration-200" 
                             x-transition:leave-start="opacity-100" 
                             x-transition:leave-end="opacity-0" 
                             class="fixed inset-0 transition-opacity bg-slate-900/80 backdrop-blur-sm" 
                             @click="showModal = false" 
                             aria-hidden="true"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <!-- Modal panel -->
                        <div x-show="showModal" 
                             x-transition:enter="ease-out duration-300" 
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                             x-transition:leave="ease-in duration-200" 
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                             class="inline-block px-4 pt-5 pb-4 text-left align-bottom transition-all transform bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                            
                            <form :action="formAction" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div>
                                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-indigo-500/20 rounded-full">
                                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-5">
                                        <h3 class="text-lg font-medium leading-6 text-white" id="modal-title">
                                            Editar Fechas del Lote #<span x-text="editLoteId"></span>
                                        </h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-slate-400">
                                                Modifica las fechas de elaboración y vencimiento de este lote.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-6 space-y-4 text-left">
                                    <div>
                                        <label for="fecha_elaboracion" class="block text-sm font-medium text-slate-300">Fecha de Elaboración</label>
                                        <input type="date" name="fecha_elaboracion" id="fecha_elaboracion" x-model="editElab" class="block w-full mt-1 border-slate-600 bg-slate-900 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-slate-200">
                                    </div>
                                    <div>
                                        <label for="fecha_vencimiento" class="block text-sm font-medium text-slate-300">Fecha de Vencimiento (FEFO)</label>
                                        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" x-model="editVenc" class="block w-full mt-1 border-slate-600 bg-slate-900 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-slate-200">
                                    </div>
                                </div>

                                <div class="mt-8 sm:flex sm:flex-row-reverse gap-3">
                                    <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-indigo-600 border border-transparent rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                                        Guardar Cambios
                                    </button>
                                    <button type="button" @click="showModal = false" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-slate-300 bg-slate-800 border border-slate-600 rounded-lg shadow-sm hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
