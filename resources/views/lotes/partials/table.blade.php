@forelse ($lotes as $lote)
    @php
        $rowClass = "border-b border-slate-800/60 hover:bg-slate-800/40 transition-all duration-300 backdrop-blur-sm group";
        $badge = "";
        
        if ($lote->fecha_vencimiento) {
            $vencimiento = \Carbon\Carbon::parse($lote->fecha_vencimiento);
            $hoy = now();
            $dias = $hoy->diffInDays($vencimiento, false);
            
            if ($dias < 0) {
                $rowClass = "border-b border-red-900/30 bg-red-950/20 hover:bg-red-900/30 transition-all duration-300 backdrop-blur-sm group";
                $badge = '<span class="inline-flex items-center rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-bold text-red-400 border border-red-500/20 shadow-[0_0_10px_rgba(239,68,68,0.2)]">Vencido</span>';
            } elseif ($dias < 7) {
                $rowClass = "border-b border-rose-900/30 bg-rose-950/10 hover:bg-rose-900/20 transition-all duration-300 backdrop-blur-sm group";
                $badge = '<span class="inline-flex items-center rounded-full bg-rose-500/10 px-2.5 py-0.5 text-xs font-bold text-rose-400 border border-rose-500/20 shadow-[0_0_10px_rgba(244,63,94,0.3)]">CRÍTICO (<7d)</span>';
            } elseif ($dias <= 14) {
                $rowClass = "border-b border-orange-900/30 bg-orange-950/10 hover:bg-orange-900/20 transition-all duration-300 backdrop-blur-sm group";
                $badge = '<span class="inline-flex items-center rounded-full bg-orange-500/10 px-2.5 py-0.5 text-xs font-bold text-orange-400 border border-orange-500/20 shadow-[0_0_10px_rgba(249,115,22,0.2)]">ALERTA (7-14d)</span>';
            } elseif ($dias <= 21) {
                $rowClass = "border-b border-yellow-900/30 hover:bg-yellow-900/20 transition-all duration-300 backdrop-blur-sm group";
                $badge = '<span class="inline-flex items-center rounded-full bg-yellow-500/10 px-2.5 py-0.5 text-xs font-medium text-yellow-400 border border-yellow-500/20 shadow-[0_0_10px_rgba(234,179,8,0.2)]">PREVENTIVO (14-21d)</span>';
            } elseif ($dias <= 30) {
                $rowClass = "border-b border-cyan-900/30 hover:bg-cyan-900/20 transition-all duration-300 backdrop-blur-sm group";
                $badge = '<span class="inline-flex items-center rounded-full bg-cyan-500/10 px-2.5 py-0.5 text-xs font-medium text-cyan-400 border border-cyan-500/20 shadow-[0_0_10px_rgba(6,182,212,0.2)]">ATENCIÓN (21-30d)</span>';
            } else {
                $badge = '<span class="inline-flex items-center rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-400 border border-emerald-500/20 shadow-[0_0_10px_rgba(16,185,129,0.2)]">Sano (+' . intval($dias) . 'd)</span>';
            }
        }
    @endphp
    <tr class="{{ $rowClass }}">
        <td class="px-6 py-4 font-medium text-slate-300 group-hover:text-white transition-colors font-mono text-xs">
            #{{ $lote->id }}
        </td>
        <td class="px-6 py-4 text-sm text-slate-400 group-hover:text-slate-300 transition-colors">
            {{ optional($lote->bodega)->nombre }}
        </td>
        <td class="px-6 py-4 font-mono text-sm text-slate-500 group-hover:text-slate-400 transition-colors">
            {{ optional($lote->producto)->sku }}
        </td>
        <td class="px-6 py-4 font-medium text-slate-200 group-hover:text-white transition-colors">
            @if($lote->producto)
                <a href="{{ route('catalogo.show', $lote->producto) }}" class="text-indigo-400 hover:text-indigo-300 hover:underline">
                    {{ $lote->producto->nombre }}
                </a>
            @else
                N/A
            @endif
        </td>
        <td class="px-6 py-4 text-right font-bold text-slate-200 group-hover:text-white transition-colors">
            {{ number_format($lote->cantidad_disponible, 2, ',', '.') }} <span class="font-normal text-slate-500">{{ optional($lote->producto)->unidad_medida }}</span>
        </td>
        <td class="px-6 py-4 text-right text-slate-400 group-hover:text-slate-300 transition-colors">
            ${{ number_format($lote->costo_unitario, 2, ',', '.') }}
        </td>
        <td class="px-6 py-4 flex items-center space-x-2">
            @if($lote->fecha_vencimiento)
                {!! $badge !!}
                <span class="{{ str_contains($rowClass, 'bg-red') ? 'text-red-400 font-bold' : 'text-slate-400 font-medium group-hover:text-slate-300 transition-colors' }}">
                    {{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}
                </span>
            @else
                <span class="text-slate-600 italic">N/A</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-6 py-16 text-center bg-slate-900/30 backdrop-blur-sm rounded-xl border border-slate-800/50">
            <div class="flex flex-col items-center justify-center space-y-4">
                <div class="p-4 bg-slate-800/50 rounded-full shadow-[0_0_20px_rgba(0,0,0,0.3)] ring-1 ring-slate-700/50">
                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-slate-200">No se encontraron lotes</h3>
                    <p class="text-sm text-slate-400 max-w-sm mt-1">No existen lotes disponibles que coincidan con los filtros seleccionados. Intenta modificar tu búsqueda o selección de bodega/proveedor.</p>
                </div>
            </div>
        </td>
    </tr>
@endforelse

<tr id="pagination-links" class="hidden">
    <td colspan="7">
        {{ $lotes->links() }}
    </td>
</tr>
