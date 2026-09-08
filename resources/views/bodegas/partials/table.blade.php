<!-- VISTA PC: TABLA TRADICIONAL -->
<table class="hidden md:table w-full text-sm text-left text-slate-400 whitespace-nowrap">
    <thead class="text-xs text-slate-300 uppercase bg-slate-800/80 sticky top-0 z-0 shadow-sm border-b border-slate-700 backdrop-blur-sm">
        <tr>
            <th scope="col" class="px-6 py-3">SKU</th>
            <th scope="col" class="px-6 py-3">Producto</th>
            <th scope="col" class="px-6 py-3 text-right">Cantidad Disponible</th>
        </tr>
    </thead>
    @forelse ($productos as $producto)
        @php 
            $lotes = $producto->lotesStock;
            $stockTotal = $lotes->sum('cantidad_disponible');
            $productoJson = json_encode($producto);
            $lotesJson = json_encode($lotes->values());
        @endphp
        <tbody x-data="{ expanded: false }" class="border-b border-slate-800/50">
            <tr @click="expanded = !expanded" class="bg-slate-900/30 hover:bg-slate-800/80 cursor-pointer transition-colors">
                <td class="px-6 py-4 font-medium text-slate-200 font-mono flex items-center">
                    <svg x-show="!expanded" class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <svg x-show="expanded" class="w-4 h-4 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    {{ $producto->sku }}
                </td>
                <td class="px-6 py-4">
                    <span class="font-bold text-white block">{{ $producto->nombre }}</span>
                    <div class="flex items-center gap-2 mt-1">
                        @if($producto->formato)
                            <span class="bg-indigo-900/50 text-indigo-300 text-[10px] font-bold px-1.5 py-0.5 rounded border border-indigo-700/50 uppercase">{{ $producto->formato }}</span>
                        @endif
                        @if($producto->capacidad)
                            <span class="bg-indigo-900/50 text-indigo-300 text-[10px] font-bold px-1.5 py-0.5 rounded border border-indigo-700/50 uppercase">{{ $producto->capacidad }}</span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 text-right">
                    @if($stockTotal > 0)
                        <span class="bg-indigo-900/50 text-indigo-300 border border-indigo-700 px-3 py-1 rounded-lg font-bold text-base">{{ number_format($stockTotal, 0) }} UN</span>
                    @else
                        <span class="inline-flex items-center gap-1.5 bg-slate-800/80 text-slate-400 border border-slate-700 px-3 py-1 rounded-lg font-bold text-xs uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            Agotado
                        </span>
                    @endif
                </td>
            </tr>
            <tr x-show="expanded" style="display: none;" class="bg-slate-950/50">
                <td colspan="3" class="px-10 py-4">
                    <div class="bg-slate-900 rounded-lg border border-slate-700/50 overflow-hidden shadow-inner">
                        <table class="w-full text-xs text-left text-slate-400">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-800/50">
                                <tr>
                                    <th class="px-4 py-3">ID Lote</th>
                                    <th class="px-4 py-3">Origen</th>
                                    <th class="px-4 py-3">Elaboración</th>
                                    <th class="px-4 py-3 text-emerald-400">Vencimiento</th>
                                    <th class="px-4 py-3 text-right">Cant. Lote</th>
                                    <th class="px-4 py-3 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lotes as $lote)
                                <tr class="border-b border-slate-800/30 last:border-0 hover:bg-slate-800/50 transition-colors">
                                    <td class="px-4 py-3 font-mono">#{{ $lote->id }}</td>
                                    <td class="px-4 py-3">
                                        @if($lote->recepcionDetalle && $lote->recepcionDetalle->recepcion)
                                            <a href="{{ route('recepciones.show', $lote->recepcionDetalle->recepcion) }}" class="text-indigo-400 hover:underline">Recepción #{{ $lote->recepcionDetalle->recepcion->id }}</a>
                                        @else
                                            <span class="text-slate-500 italic">Ajuste manual</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $lote->fecha_elaboracion ? $lote->fecha_elaboracion->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-4 py-3 font-medium {{ $lote->fecha_vencimiento && $lote->fecha_vencimiento->isPast() ? 'text-red-400' : 'text-emerald-400' }}">
                                        {{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'S/F' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-200">{{ number_format($lote->cantidad_disponible, 0) }} UN</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button"
                                                class="btn-ajuste-masivo inline-flex justify-center items-center text-xs px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded border border-slate-600 transition-colors"
                                                data-producto="{{ $productoJson }}"
                                                data-lotes="{{ json_encode([$lote]) }}"
                                                onclick="window.dispatchEvent(new CustomEvent('open-ajuste-masivo', { detail: { producto: JSON.parse(this.dataset.producto), lotes: JSON.parse(this.dataset.lotes) } }))"
                                                title="Ajustar Stock">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Ajustar
                                            </button>
                                            <button type="button"
                                                class="inline-flex justify-center items-center text-xs px-2 py-1 bg-indigo-900/40 hover:bg-indigo-900/60 text-indigo-300 rounded border border-indigo-700/50 transition-colors"
                                                onclick="window.dispatchEvent(new CustomEvent('open-fechas-modal', { detail: { id: '{{ $lote->id }}', elab: '{{ $lote->fecha_elaboracion ? $lote->fecha_elaboracion->format('d/m/Y') : 'N/A' }}', venc: '{{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'S/F' }}', url: '{{ route('lotes.fechas.update', $lote->id) }}', vida_util: '{{ $producto->constante_vencimiento_meses ?? '' }}' } }))"
                                                title="Editar Fechas">
                                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                Fechas
                                            </button>
                                            <button type="button"
                                                class="inline-flex justify-center items-center text-xs px-2 py-1 bg-amber-900/40 hover:bg-amber-900/60 text-amber-300 rounded border border-amber-700/50 transition-colors"
                                                onclick="window.dispatchEvent(new CustomEvent('open-transfer-lote-modal', { detail: { id: {{ $lote->id }}, bodegaNombre: '{{ addslashes($bodega->nombre ?? '') }}' } }))">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                                Mover
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr class="border-b border-slate-800/50 hover:bg-slate-900/50 transition-colors">
                                    <td colspan="5" class="px-4 py-4 text-center text-slate-500 font-medium italic">
                                        Sin lotes registrados
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button"
                                                class="btn-ajuste-masivo inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded border border-slate-600 transition-colors"
                                                data-producto="{{ $productoJson }}"
                                                data-lotes="{{ $lotesJson }}"
                                                onclick="window.dispatchEvent(new CustomEvent('open-ajuste-masivo', { detail: { producto: JSON.parse(this.dataset.producto), lotes: JSON.parse(this.dataset.lotes) } }))"
                                                title="Ajustar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Ajustar
                                            </button>
                                            <button type="button"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-900/40 hover:bg-indigo-900/60 text-indigo-300 rounded border border-indigo-700/50 transition-colors opacity-50 cursor-not-allowed"
                                                onclick="alert('No hay lotes para modificar fechas.')"
                                                title="Fechas">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                Fechas
                                            </button>
                                            <button type="button"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-900/40 hover:bg-amber-900/60 text-amber-300 rounded border border-amber-700/50 transition-colors opacity-50 cursor-not-allowed"
                                                onclick="alert('No hay lotes para mover.')"
                                                title="Mover">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                                Mover
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    @empty
        <tbody>
            <tr>
                <td colspan="3" class="px-6 py-12 text-center text-slate-500 bg-slate-900/30">
                    <svg class="mx-auto h-12 w-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    <p class="text-lg font-medium text-slate-400">No se encontraron productos</p>
                    <p class="text-sm mt-1">Prueba ajustando los filtros.</p>
                </td>
            </tr>
        </tbody>
    @endforelse
</table>

<!-- VISTA MÓVIL: TARJETAS (CARDS) -->
<div class="md:hidden flex flex-col gap-4 p-4 pb-24">
    @forelse ($productos as $producto)
        @php 
            $lotes = $producto->lotesStock;
            $stockTotal = $lotes->sum('cantidad_disponible');
            $hasLotes = $lotes->count() > 0;
            $productoJson = json_encode($producto);
            $lotesJson = json_encode($lotes->values());
        @endphp
        <div x-data="{ expanded: false }" class="bg-slate-800/60 rounded-xl border border-slate-700/80 overflow-hidden shadow-sm">
            <div @click="expanded = {{ $hasLotes ? '!expanded' : 'false' }}" class="p-4 flex flex-col gap-3 {{ $hasLotes ? 'cursor-pointer hover:bg-slate-800/80' : '' }} transition-colors">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs text-slate-400 font-mono block mb-1">SKU: {{ $producto->sku }}</span>
                        <span class="font-bold text-white text-base block leading-tight">{{ $producto->nombre }}</span>
                    </div>
                    @if($hasLotes)
                        <div class="text-slate-500 flex-shrink-0 mt-1">
                            <svg x-show="!expanded" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            <svg x-show="expanded" class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @if($producto->formato)
                        <span class="bg-indigo-900/50 text-indigo-300 text-[10px] font-bold px-2 py-0.5 rounded border border-indigo-700/50 uppercase">{{ $producto->formato }}</span>
                    @endif
                    @if($producto->capacidad)
                        <span class="bg-indigo-900/50 text-indigo-300 text-[10px] font-bold px-2 py-0.5 rounded border border-indigo-700/50 uppercase">{{ $producto->capacidad }}</span>
                    @endif
                </div>
                <div class="mt-1 flex justify-between items-center border-t border-slate-700/50 pt-3">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs text-slate-400 font-medium">Stock Total:</span>
                        <button type="button"
                            class="btn-ajuste-masivo inline-flex items-center gap-1 text-[10px] font-bold text-indigo-400 hover:text-indigo-300 uppercase tracking-wider"
                            data-producto="{{ $productoJson }}"
                            data-lotes="{{ $lotesJson }}"
                            onclick="window.dispatchEvent(new CustomEvent('open-ajuste-masivo', { detail: { producto: JSON.parse(this.dataset.producto), lotes: JSON.parse(this.dataset.lotes) } }))">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Ajustar
                        </button>
                    </div>
                    @if($stockTotal > 0)
                        <span class="bg-indigo-900/80 text-indigo-300 border border-indigo-600 px-3 py-1 rounded-lg font-bold text-sm">{{ number_format($stockTotal, 0) }} UN</span>
                    @else
                        <span class="inline-flex items-center gap-1 bg-slate-900/80 text-slate-400 border border-slate-700 px-2.5 py-1 rounded-lg font-bold text-xs uppercase">
                            <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            Agotado
                        </span>
                    @endif
                </div>
            </div>
            <div x-show="expanded" style="display: none;" class="bg-slate-900 border-t border-slate-700/50 p-3">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 px-1">Lotes Disponibles</div>
                <div class="flex flex-col gap-2">
                    @forelse($lotes as $lote)
                    <div class="bg-slate-800 rounded p-3 border border-slate-700/60 relative">
                        <div class="flex justify-between mb-2">
                            <span class="font-mono text-xs text-indigo-300">#{{ $lote->id }}</span>
                            <span class="text-xs font-bold text-white">{{ number_format($lote->cantidad_disponible, 0) }} UN</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                            <div>
                                <span class="text-slate-500 block mb-0.5">Vencimiento:</span>
                                <span class="font-medium {{ $lote->fecha_vencimiento && $lote->fecha_vencimiento->isPast() ? 'text-red-400' : 'text-emerald-400' }}">
                                    {{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'S/F' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-500 block mb-0.5">Origen:</span>
                                @if($lote->recepcionDetalle && $lote->recepcionDetalle->recepcion)
                                    <a href="{{ route('recepciones.show', $lote->recepcionDetalle->recepcion) }}" class="text-indigo-400 hover:underline truncate block">Recepción #{{ $lote->recepcionDetalle->recepcion->id }}</a>
                                @else
                                    <span class="text-slate-500 italic">Ajuste manual</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 mt-3">
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center mb-1">Acciones</div>
                            <button type="button"
                                class="btn-ajuste-masivo w-full flex justify-center items-center gap-2 text-sm py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-600 transition-colors shadow-sm"
                                data-producto="{{ $productoJson }}"
                                data-lotes="{{ $lotesJson }}"
                                onclick="window.dispatchEvent(new CustomEvent('open-ajuste-masivo', { detail: { producto: JSON.parse(this.dataset.producto), lotes: JSON.parse(this.dataset.lotes) } }))"
                                title="Ajustar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Ajustar
                            </button>
                            <button type="button"
                                class="w-full flex justify-center items-center gap-2 text-sm py-2.5 bg-indigo-900/40 hover:bg-indigo-900/60 text-indigo-300 rounded-lg border border-indigo-700/50 transition-colors shadow-sm"
                                onclick="window.dispatchEvent(new CustomEvent('open-fechas-modal', { detail: { id: '{{ $lote->id }}', elab: '{{ $lote->fecha_elaboracion ? $lote->fecha_elaboracion->format('d/m/Y') : 'N/A' }}', venc: '{{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'S/F' }}', url: '{{ route('lotes.fechas.update', $lote->id) }}', vida_util: '{{ $producto->constante_vencimiento_meses ?? '' }}' } }))"
                                title="Fechas">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Fechas
                            </button>
                            <button type="button"
                                class="w-full flex justify-center items-center gap-2 text-sm py-2.5 bg-amber-900/40 hover:bg-amber-900/60 text-amber-300 rounded-lg border border-amber-700/50 transition-colors shadow-sm"
                                onclick="window.dispatchEvent(new CustomEvent('open-transfer-lote-modal', { detail: { id: {{ $lote->id }}, bodegaNombre: '{{ addslashes($bodega->nombre ?? '') }}' } }))"
                                title="Mover">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                Mover
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="bg-slate-800 rounded p-3 border border-slate-700/60 text-center">
                        <span class="text-slate-500 mb-3 block italic font-medium">Sin lotes registrados</span>
                        <div class="flex flex-col gap-2 mt-3">
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center mb-1">Acciones</div>
                            <button type="button"
                                class="btn-ajuste-masivo w-full flex justify-center items-center gap-2 text-sm py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-600 transition-colors shadow-sm"
                                data-producto="{{ $productoJson }}"
                                data-lotes="{{ $lotesJson }}"
                                onclick="window.dispatchEvent(new CustomEvent('open-ajuste-masivo', { detail: { producto: JSON.parse(this.dataset.producto), lotes: JSON.parse(this.dataset.lotes) } }))"
                                title="Ajustar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Ajustar
                            </button>
                            <button type="button"
                                class="w-full flex justify-center items-center gap-2 text-sm py-2.5 bg-indigo-900/40 hover:bg-indigo-900/60 text-indigo-300 rounded-lg border border-indigo-700/50 transition-colors shadow-sm opacity-50 cursor-not-allowed"
                                onclick="alert('No hay lotes para modificar fechas.')"
                                title="Fechas">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Fechas
                            </button>
                            <button type="button"
                                class="w-full flex justify-center items-center gap-2 text-sm py-2.5 bg-amber-900/40 hover:bg-amber-900/60 text-amber-300 rounded-lg border border-amber-700/50 transition-colors shadow-sm opacity-50 cursor-not-allowed"
                                onclick="alert('No hay lotes para mover.')"
                                title="Mover">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                Mover
                            </button>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    @empty
        <div class="bg-slate-800/60 rounded-xl border border-slate-700 p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            <p class="text-base font-medium text-slate-400">No se encontraron productos</p>
        </div>
    @endforelse
</div>

@if($productos instanceof \Illuminate\Pagination\LengthAwarePaginator || $productos instanceof \Illuminate\Pagination\Paginator)
    <div id="pagination-links" class="hidden">
        {{ $productos->links() }}
    </div>
@endif

