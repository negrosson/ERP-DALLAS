<!-- VISTA PC: TABLA TRADICIONAL -->
<table class="hidden md:table w-full text-sm text-left text-slate-400 whitespace-nowrap">
    <thead class="text-xs text-slate-400 uppercase bg-slate-950/80 sticky top-0 z-0 shadow-sm border-b border-slate-800 backdrop-blur-sm">
        <tr>
            <th scope="col" class="px-6 py-4">Producto</th>
            <th scope="col" class="px-6 py-4">SKU</th>
            <th scope="col" class="px-6 py-4">Unidad</th>
            <th scope="col" class="px-6 py-4">Precio Ref.</th>
            <th scope="col" class="px-6 py-4">Motor FEFO</th>
            <th scope="col" class="px-6 py-4">Próx. Vencimiento</th>
            <th scope="col" class="px-6 py-4">Estado</th>
            <th scope="col" class="px-6 py-4">Acciones</th>
        </tr>
    </thead>
@forelse ($productos as $producto)
    @php
        // Generar avatar dinámico
        $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $producto->nombre), 0, 2));
        if (strlen($initials) < 2) $initials = str_pad($initials, 2, 'X');
        
        $colors = [
            'from-red-500 to-rose-600', 
            'from-blue-500 to-indigo-600', 
            'from-emerald-500 to-teal-600', 
            'from-amber-500 to-orange-600', 
            'from-purple-500 to-fuchsia-600', 
            'from-pink-500 to-rose-500',
            'from-cyan-500 to-blue-600'
        ];
        $colorClass = $colors[crc32($producto->nombre) % count($colors)];

        // Lotes activos ya eager-loaded (sin nueva query)
        $lotes = $producto->lotesStock;
        $lotesPrimero = $lotes->first();
        $lotesExtra = $lotes->skip(1)->values();
        $hoy = now()->startOfDay();
    @endphp
    <tbody x-data="{ expanded: false }" class="border-b border-slate-800/50">
        <tr @click="expanded = !expanded" class="bg-slate-900/30 hover:bg-slate-800/80 transition-colors group cursor-pointer">
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <svg x-show="!expanded" class="w-4 h-4 mr-3 text-slate-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <svg x-show="expanded" class="w-4 h-4 mr-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br {{ $colorClass }} flex items-center justify-center text-white font-bold text-sm border border-white/10 shadow-lg group-hover:scale-110 transition-transform">
                        {{ $initials }}
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-bold text-white uppercase group-hover:text-indigo-400 transition-colors">{{ $producto->nombre }}</div>
                        <div class="flex items-center gap-2 mt-1">
                            @if($producto->formato)
                                <span class="bg-indigo-900/50 text-indigo-300 text-[10px] font-bold px-1.5 py-0.5 rounded border border-indigo-700/50 uppercase">{{ $producto->formato }}</span>
                            @endif
                            @if($producto->capacidad)
                                <span class="bg-indigo-900/50 text-indigo-300 text-[10px] font-bold px-1.5 py-0.5 rounded border border-indigo-700/50 uppercase">{{ $producto->capacidad }}</span>
                            @endif
                        </div>
                        @if($producto->descripcion)
                            <div class="text-xs text-slate-500 mt-1 truncate max-w-xs">{{ $producto->descripcion }}</div>
                        @endif
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 font-medium text-slate-300 font-mono text-xs">
                <span class="bg-slate-800 px-2 py-1 rounded border border-slate-700">{{ $producto->sku }}</span>
            </td>
            <td class="px-6 py-4 font-medium text-slate-300">
                {{ $producto->unidad_medida }}
            </td>
            <td class="px-6 py-4 font-bold text-emerald-400">
                ${{ number_format($producto->precio_venta, 0, ',', '.') }}
            </td>
            <td class="px-6 py-4">
                @if($producto->tieneAutocalculoVencimiento())
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $producto->constante_vencimiento_meses }} meses
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11"></path></svg>
                        Manual
                    </span>
                @endif
            </td>

            {{-- ── Columna Próximo Vencimiento ─────────────────────────── --}}
            <td class="px-6 py-4">
                @if($lotes->isEmpty())
                    <span class="text-xs text-slate-600 italic">Sin lotes registrados</span>
                @elseif($lotes->sum('cantidad_disponible') <= 0)
                    <span class="text-xs text-slate-400 font-medium px-2.5 py-1 bg-slate-800/80 rounded border border-slate-700">Agotado (0 UN)</span>
                @else
                    @php
                        $venc = $lotesPrimero->fecha_vencimiento;
                        $esVencido = $venc && $venc->lt($hoy);
                        $esCritico = $venc && !$esVencido && $venc->lte($hoy->copy()->addDays(30));
                        $badgeClass = $esVencido
                            ? 'bg-rose-500/10 text-rose-400 border-rose-500/30'
                            : ($esCritico ? 'bg-amber-500/10 text-amber-400 border-amber-500/30' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30');
                        $label = $venc ? $venc->format('d/m/Y') : 'Sin fecha';
                        $humanLabel = $venc
                            ? ($esVencido ? 'Vencido hace ' . $venc->diffForHumans(['parts' => 1, 'join' => true]) : 'Vence ' . $venc->diffForHumans())
                            : null;
                    @endphp
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded border {{ $badgeClass }}" title="{{ $humanLabel }}">
                            @if($esVencido)
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            @else
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @endif
                            {{ $label }}
                        </span>

                        @if($lotesExtra->count() > 0)
                            <span class="inline-flex items-center gap-0.5 text-[10px] font-bold px-1.5 py-0.5 rounded border border-slate-600 text-slate-400 bg-slate-800">
                                +{{ $lotesExtra->count() }} lote(s)
                            </span>
                        @endif
                    </div>
                @endif
            </td>

            <td class="px-6 py-4">
                @if($producto->activo)
                    <span class="bg-emerald-500/10 text-emerald-400 text-xs font-medium px-2.5 py-1 rounded border border-emerald-500/20 shadow-sm">Activo</span>
                @else
                    <span class="bg-rose-500/10 text-rose-400 text-xs font-medium px-2.5 py-1 rounded border border-rose-500/20 shadow-sm">Inactivo</span>
                @endif
            </td>
            <td class="px-6 py-4 space-x-3">
                <a @click.stop href="{{ route('catalogo.show', $producto) }}" class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors" title="Ver Detalles">
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </a>
                <button @click.stop onclick="event.stopPropagation(); window.dispatchEvent(new CustomEvent('open-ajuste-modal', { detail: { id: {{ $producto->id }}, nombre: '{{ addslashes($producto->nombre) }}' } }))" class="font-medium text-amber-400 hover:text-amber-300 transition-colors" title="Ajuste Rápido de Stock">
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                </button>
                <a @click.stop href="{{ route('catalogo.edit', $producto) }}" class="font-medium text-blue-400 hover:text-blue-300 transition-colors" title="Editar">
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </a>
                <form @click.stop action="{{ route('catalogo.destroy', $producto) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de eliminar este producto del catálogo?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="font-medium text-rose-400 hover:text-rose-300 transition-colors" title="Eliminar">
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
            </td>
        </tr>

        <!-- Expanded details (Lots) -->
        <tr x-show="expanded" style="display: none;" class="bg-slate-950">
            <td colspan="8" class="px-0 py-0">
                <div class="border-t border-slate-800/50 bg-slate-950/50 p-6 shadow-inner">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            Stock y Lotes para {{ $producto->nombre }}
                        </h4>
                        <span class="bg-slate-800 text-slate-300 text-xs font-bold px-3 py-1 rounded border border-slate-700">Stock Total: {{ number_format($lotes->sum('cantidad_disponible'), 2, ',', '.') }} {{ $producto->unidad_medida }}</span>
                    </div>
                    
                    @if($lotes->isEmpty())
                        <div class="text-center py-6 text-slate-500 bg-slate-900/50 rounded-xl border border-slate-800/60">
                            Este producto no tiene stock en ninguna bodega.
                        </div>
                    @else
                        <div class="overflow-hidden rounded-xl border border-slate-800/60 bg-slate-900 shadow-sm">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-800/80 text-slate-400 uppercase border-b border-slate-700">
                                    <tr>
                                        <th class="px-4 py-3">Lote ID</th>
                                        <th class="px-4 py-3">Bodega</th>
                                        <th class="px-4 py-3 text-right">Cant. Disp.</th>
                                        <th class="px-4 py-3">F. Elaboración</th>
                                        <th class="px-4 py-3">F. Vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/50">
                                    @foreach($lotes as $lote)
                                        @php
                                            $loteVenc = $lote->fecha_vencimiento;
                                            $isVencido = $loteVenc && $loteVenc->lt($hoy);
                                            $esCritico = $loteVenc && !$isVencido && $loteVenc->lte($hoy->copy()->addDays(30));
                                        @endphp
                                        <tr class="hover:bg-slate-800/40 transition-colors">
                                            <td class="px-4 py-3 font-mono font-medium text-slate-300">#{{ $lote->id }}</td>
                                            <td class="px-4 py-3 text-slate-400">{{ optional($lote->bodega)->nombre ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-right font-bold text-white">{{ number_format($lote->cantidad_disponible, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-slate-400">{{ $lote->fecha_elaboracion ? $lote->fecha_elaboracion->format('d/m/Y') : 'N/A' }}</td>
                                            <td class="px-4 py-3 font-medium {{ $isVencido ? 'text-rose-400' : ($esCritico ? 'text-amber-400' : 'text-emerald-400') }}">
                                                @if($loteVenc)
                                                    {{ $loteVenc->format('d/m/Y') }}
                                                @else
                                                    <span class="text-slate-600">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </tbody>
@empty
    <tbody>
        <tr>
            <td colspan="8" class="px-6 py-12 text-center text-slate-500 bg-slate-900/30">
                <svg class="mx-auto h-12 w-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                <p class="text-lg font-medium text-slate-400">No se encontraron productos</p>
                <p class="text-sm mt-1">Prueba ajustando los filtros o añade un nuevo producto.</p>
            </td>
        </tr>
    </tbody>
@endforelse
</table>

<!-- VISTA MÓVIL: TARJETAS (CARDS) -->
<div class="md:hidden flex flex-col gap-4 p-4">
    @forelse ($productos as $producto)
        @php 
            $lotes = $producto->lotesStock;
            $stockTotal = $lotes->sum('cantidad_disponible');
            $hasLotes = $lotes->count() > 0;
            $hoy = now()->startOfDay();
            $lotesPrimero = $lotes->first();
        @endphp
        <div x-data="{ expanded: false }" class="bg-slate-800/60 rounded-xl border border-slate-700/80 overflow-hidden shadow-sm">
            <div @click="expanded = !expanded" class="p-4 flex flex-col gap-3 cursor-pointer hover:bg-slate-800/80 transition-colors">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs text-slate-400 font-mono block mb-1">SKU: {{ $producto->sku }}</span>
                        <a href="{{ route('catalogo.show', $producto) }}" class="font-bold text-white text-base hover:text-indigo-400 block leading-tight" @click.stop>
                            {{ $producto->nombre }}
                        </a>
                    </div>
                    <div class="text-slate-500 flex-shrink-0 mt-1">
                        <svg x-show="!expanded" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        <svg x-show="expanded" class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @if($producto->formato)
                        <span class="bg-indigo-900/50 text-indigo-300 text-[10px] font-bold px-2 py-0.5 rounded border border-indigo-700/50 uppercase">{{ $producto->formato }}</span>
                    @endif
                    @if($producto->capacidad)
                        <span class="bg-indigo-900/50 text-indigo-300 text-[10px] font-bold px-2 py-0.5 rounded border border-indigo-700/50 uppercase">{{ $producto->capacidad }}</span>
                    @endif
                    @if($producto->activo)
                        <span class="bg-emerald-500/10 text-emerald-400 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-500/20 shadow-sm uppercase">Activo</span>
                    @else
                        <span class="bg-rose-500/10 text-rose-400 text-[10px] font-bold px-2 py-0.5 rounded border border-rose-500/20 shadow-sm uppercase">Inactivo</span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3 mt-1 border-t border-slate-700/50 pt-3">
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">Precio Ref.</span>
                        <span class="font-bold text-emerald-400 text-sm">${{ number_format($producto->precio_venta, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">Vencimiento</span>
                        @if($lotes->isEmpty())
                            <span class="text-xs text-slate-500 italic">Sin lotes</span>
                        @elseif($lotes->sum('cantidad_disponible') <= 0)
                            <span class="text-xs text-slate-400 font-bold">Agotado</span>
                        @else
                            @php
                                $venc = $lotesPrimero->fecha_vencimiento;
                                $esVencido = $venc && $venc->lt($hoy);
                                $esCritico = $venc && !$esVencido && $venc->lte($hoy->copy()->addDays(30));
                                $badgeClass = $esVencido ? 'text-rose-400' : ($esCritico ? 'text-amber-400' : 'text-emerald-400');
                                $label = $venc ? $venc->format('d/m/Y') : 'Sin fecha';
                            @endphp
                            <span class="text-sm font-bold {{ $badgeClass }}">{{ $label }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex justify-between items-center mt-2 border-t border-slate-700/50 pt-3">
                    <div class="flex gap-2">
                        <button @click.stop onclick="event.stopPropagation(); window.dispatchEvent(new CustomEvent('open-ajuste-modal', { detail: { id: {{ $producto->id }}, nombre: '{{ addslashes($producto->nombre) }}' } }))" class="inline-flex items-center justify-center bg-slate-700 hover:bg-slate-600 text-slate-200 border border-slate-600 rounded p-1.5 transition-colors">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        </button>
                        <a @click.stop href="{{ route('catalogo.edit', $producto) }}" class="inline-flex items-center justify-center bg-slate-700 hover:bg-slate-600 text-slate-200 border border-slate-600 rounded p-1.5 transition-colors">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                    </div>
                    <span class="bg-indigo-900/80 text-indigo-300 border border-indigo-600 px-3 py-1 rounded-lg font-bold text-sm">
                        {{ number_format($stockTotal, 0) }} {{ $producto->unidad_medida }}
                    </span>
                </div>
            </div>

            <!-- Contenido Expandible (Lotes del Producto) -->
            @if($hasLotes)
            <div x-show="expanded" style="display: none;" class="bg-slate-900 border-t border-slate-700/50 p-3">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 px-1">Stock por Bodega</div>
                <div class="flex flex-col gap-2">
                    @foreach($lotes as $lote)
                    <div class="bg-slate-800 rounded p-3 border border-slate-700/60">
                        <div class="flex justify-between mb-1">
                            <span class="font-bold text-white text-sm">{{ optional($lote->bodega)->nombre ?? 'N/A' }}</span>
                            <span class="text-sm font-bold text-emerald-400">{{ number_format($lote->cantidad_disponible, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-400">
                            <span>Lote #{{ $lote->id }}</span>
                            <span>Vence: 
                                @if($lote->fecha_vencimiento)
                                    <span class="{{ $lote->fecha_vencimiento->isPast() ? 'text-red-400' : 'text-emerald-400' }}">
                                        {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                                    </span>
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
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
