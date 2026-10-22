{{-- 
========================================================================================
VISTA PARCIAL: TABLA Y TARJETAS DE PRODUCTOS Y LOTES EN BODEGA
RUTA INCLUIDA EN: resources/views/bodegas/show.blade.php
CONTROLADOR: BodegaController@show (Invocado también vía peticiones AJAX)
PROPÓSITO:
- Renderizar el listado consolidado de productos en inventario y sus lotes específicos (FEFO).
- Estructura anidada tipo acordeón interactivo gestionado con Alpine.js (`x-data="{ expanded: false }"`).
- Para cada producto:
  * Muestra el SKU, denominación comercial, atributos de formato y capacidad.
  * Cantidad total disponible agregada o distintivo de "Agotado".
  * Al hacer clic, despliega la subtabla con el desglose de cada lote físico:
    - ID del lote.
    - Documento o recepción de origen.
    - Fecha de elaboración.
    - Fecha de vencimiento con color condicional (rojo si caducó, esmeralda si está vigente).
    - Cantidad en existencia del lote.
    - Acciones individuales: Ajustar stock en caliente, Editar fechas de vida útil o Mover entre bodegas.
- Soporte responsivo dual: Tabla relacional en Desktop y tarjetas desplegables en Mobile.
- Bloque oculto de paginación sincronizable por JavaScript para búsquedas asíncronas.
========================================================================================
--}}

{{-- =========================================================================
     VISTA PC: TABLA TRADICIONAL EXPANDIBLE (Visible en md y superior)
     ========================================================================= --}}
<table class="hidden md:table w-full text-sm text-left text-slate-400 whitespace-nowrap">
    {{-- Cabecera con posicionamiento sticky para navegación en listas extensas --}}
    <thead class="text-xs text-slate-300 uppercase bg-slate-800/80 sticky top-0 z-0 shadow-sm border-b border-slate-700 backdrop-blur-sm">
        <tr>
            <th scope="col" class="px-6 py-3">SKU</th>
            <th scope="col" class="px-6 py-3">Producto</th>
            <th scope="col" class="px-6 py-3 text-right">Cantidad Disponible</th>
            <th scope="col" class="px-6 py-3 text-center">Acciones</th>
        </tr>
    </thead>
    {{-- Itera sobre la colección de productos asignados a la bodega actual --}}
    @forelse ($productos as $producto)
        @php 
            // Colección de lotes físicos asociados a este producto en la bodega
            $lotes = $producto->lotesStock;
            // Sumatoria total de unidades disponibles sumando todos los lotes
            $stockTotal = $lotes->sum('cantidad_disponible');
            // Serialización JSON para traspasar como datos reactivos a los modales de ajuste
            $productoJson = json_encode($producto);
            $lotesJson = json_encode($lotes->values());
        @endphp
        {{-- Cada producto se encapsula en su propio tbody con estado reactivo Alpine.js para acordeón --}}
        <tbody x-data="{ expanded: false }" class="border-b border-slate-800/50">
            {{-- Fila principal del producto: Al hacer clic conmuta el despliegue del acordeón de lotes --}}
            <tr @click="expanded = !expanded" class="bg-slate-900/30 hover:bg-slate-800/80 cursor-pointer transition-colors">
                {{-- Columna SKU con flecha rotativa que indica el estado del acordeón --}}
                <td class="px-6 py-4 font-medium text-slate-200 font-mono flex items-center">
                    {{-- Icono flecha derecha cuando está colapsado --}}
                    <svg x-show="!expanded" class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    {{-- Icono flecha abajo cuando está expandido --}}
                    <svg x-show="expanded" class="w-4 h-4 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    {{ $producto->sku }}
                </td>
                {{-- Columna Producto: Nombre comercial y badges de formato / capacidad --}}
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
                {{-- Columna Cantidad Disponible: Total consolidado o aviso de sin stock --}}
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
                {{-- Columna Acciones Rápidas del Producto a Nivel Bodega --}}
                <td class="px-6 py-4 text-center" @click.stop>
                    <div class="flex items-center justify-center gap-2">
                        <button type="button"
                            class="btn-ajuste-masivo inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded border border-slate-600 transition-colors text-xs font-medium"
                            data-producto="{{ $productoJson }}"
                            data-lotes="{{ $lotesJson }}"
                            onclick="window.dispatchEvent(new CustomEvent('open-ajuste-masivo', { detail: { producto: JSON.parse(this.dataset.producto), lotes: JSON.parse(this.dataset.lotes) } }))"
                            title="Ajustar Stock">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Ajustar
                        </button>
                        <form action="{{ route('bodegas.producto.eliminar', [$bodega, $producto]) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar el producto \'{{ addslashes($producto->nombre) }}\' de la bodega \'{{ addslashes($bodega->nombre) }}\'?\n\n- Se darán de baja todas sus existencias ({{ number_format($stockTotal, 0) }} UN).\n- La acción quedará registrada en el historial de auditoría con tu usuario.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 hover:text-white rounded border border-rose-800/50 transition-colors text-xs font-semibold shadow-sm"
                                title="Eliminar producto de esta bodega">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                Eliminar
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            {{-- Fila secundaria de detalles: Se revela al expandir el acordeón mostrando los lotes --}}
            <tr x-show="expanded" style="display: none;" class="bg-slate-950/50">
                <td colspan="4" class="px-10 py-4">
                    <div class="bg-slate-900 rounded-lg border border-slate-700/50 overflow-hidden shadow-inner">
                        <table class="w-full text-xs text-left text-slate-400">
                            {{-- Cabecera de la subtabla de lotes individuales --}}
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
                                {{-- Itera sobre cada lote asociado al producto --}}
                                @forelse($lotes as $lote)
                                <tr class="border-b border-slate-800/30 last:border-0 hover:bg-slate-800/50 transition-colors">
                                    {{-- ID identificador único del lote físico --}}
                                    <td class="px-4 py-3 font-mono">#{{ $lote->id }}</td>
                                    {{-- Documento o recepción origen de procedencia --}}
                                    <td class="px-4 py-3">
                                        @if($lote->recepcionDetalle && $lote->recepcionDetalle->recepcion)
                                            <a href="{{ route('recepciones.show', $lote->recepcionDetalle->recepcion) }}" class="text-indigo-400 hover:underline">Recepción #{{ $lote->recepcionDetalle->recepcion->id }}</a>
                                        @else
                                            <span class="text-slate-500 italic">Ajuste manual</span>
                                        @endif
                                    </td>
                                    {{-- Fecha de fabricación/elaboración --}}
                                    <td class="px-4 py-3">{{ $lote->fecha_elaboracion ? $lote->fecha_elaboracion->format('d/m/Y') : 'N/A' }}</td>
                                    {{-- Fecha de vencimiento: Rojo si ya caducó, Verde esmeralda si está vigente --}}
                                    <td class="px-4 py-3 font-medium {{ $lote->fecha_vencimiento && $lote->fecha_vencimiento->isPast() ? 'text-red-400' : 'text-emerald-400' }}">
                                        {{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'S/F' }}
                                    </td>
                                    {{-- Cantidad disponible dentro de este lote en particular --}}
                                    <td class="px-4 py-3 text-right font-bold text-slate-200">{{ number_format($lote->cantidad_disponible, 0) }} UN</td>
                                    {{-- Acciones sobre el lote individual --}}
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            {{-- Botón para abrir el modal de Ajuste Masivo específico para este lote --}}
                                            <button type="button"
                                                class="btn-ajuste-masivo inline-flex justify-center items-center text-xs px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded border border-slate-600 transition-colors"
                                                data-producto="{{ $productoJson }}"
                                                data-lotes="{{ json_encode([$lote]) }}"
                                                onclick="window.dispatchEvent(new CustomEvent('open-ajuste-masivo', { detail: { producto: JSON.parse(this.dataset.producto), lotes: JSON.parse(this.dataset.lotes) } }))"
                                                title="Ajustar Stock">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Ajustar
                                            </button>
                                            {{-- Botón para abrir el modal de edición de fechas de vencimiento --}}
                                            <button type="button"
                                                class="inline-flex justify-center items-center text-xs px-2 py-1 bg-indigo-900/40 hover:bg-indigo-900/60 text-indigo-300 rounded border border-indigo-700/50 transition-colors"
                                                onclick="window.dispatchEvent(new CustomEvent('open-fechas-modal', { detail: { id: '{{ $lote->id }}', elab: '{{ $lote->fecha_elaboracion ? $lote->fecha_elaboracion->format('d/m/Y') : 'N/A' }}', venc: '{{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'S/F' }}', url: '{{ route('lotes.fechas.update', $lote->id) }}', vida_util: '{{ $producto->constante_vencimiento_meses ?? '' }}' } }))"
                                                title="Editar Fechas">
                                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                Fechas
                                            </button>
                                            {{-- Botón para abrir el modal de traslado de stock a otra bodega --}}
                                            <button type="button"
                                                class="inline-flex justify-center items-center text-xs px-2 py-1 bg-amber-900/40 hover:bg-amber-900/60 text-amber-300 rounded border border-amber-700/50 transition-colors"
                                                onclick="window.dispatchEvent(new CustomEvent('open-transfer-lote-modal', { detail: { id: {{ $lote->id }}, bodegaNombre: '{{ addslashes($bodega->nombre ?? '') }}' } }))">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                                Mover
                                            </button>
                                            {{-- Botón para eliminar/dar de baja este lote específico --}}
                                            <form action="{{ route('bodegas.lotes.eliminar', [$bodega, $lote]) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar el Lote #{{ $lote->id }} ({{ number_format($lote->cantidad_disponible, 0) }} UN) de esta bodega?\n\nEsta acción quedará registrada en auditoría.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex justify-center items-center text-xs px-2 py-1 bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 hover:text-white rounded border border-rose-800/50 transition-colors" title="Eliminar este Lote">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    Baja
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                {{-- Fila mostrada cuando el producto no tiene lotes activos en existencia --}}
                                <tr class="border-b border-slate-800/50 hover:bg-slate-900/50 transition-colors">
                                    <td colspan="5" class="px-4 py-4 text-center text-slate-500 font-medium italic">
                                        Sin lotes registrados
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Permite crear un lote mediante ajuste masivo aunque esté agotado --}}
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
    {{-- Estado vacío si no hay ningún producto que coincida con los filtros seleccionados --}}
    @empty
        <tbody>
            <tr>
                <td colspan="4" class="px-6 py-12 text-center text-slate-500 bg-slate-900/30">
                    <svg class="mx-auto h-12 w-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    <p class="text-lg font-medium text-slate-400">No se encontraron productos</p>
                    <p class="text-sm mt-1">Prueba ajustando los filtros.</p>
                </td>
            </tr>
        </tbody>
    @endforelse
</table>

{{-- =========================================================================
     VISTA MÓVIL: TARJETAS (CARDS) DESPLEGABLES (Visible en dispositivos < md)
     ========================================================================= --}}
<div class="md:hidden flex flex-col gap-4 p-4 pb-24">
    @forelse ($productos as $producto)
        @php 
            $lotes = $producto->lotesStock;
            $stockTotal = $lotes->sum('cantidad_disponible');
            $hasLotes = $lotes->count() > 0;
            $productoJson = json_encode($producto);
            $lotesJson = json_encode($lotes->values());
        @endphp
        {{-- Tarjeta móvil con acordeón independiente Alpine.js --}}
        <div x-data="{ expanded: false }" class="bg-slate-800/60 rounded-xl border border-slate-700/80 overflow-hidden shadow-sm">
            {{-- Encabezado táctil de la tarjeta del producto --}}
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
                {{-- Etiquetas de presentación comercial --}}
                <div class="flex items-center gap-2 flex-wrap">
                    @if($producto->formato)
                        <span class="bg-indigo-900/50 text-indigo-300 text-[10px] font-bold px-2 py-0.5 rounded border border-indigo-700/50 uppercase">{{ $producto->formato }}</span>
                    @endif
                    @if($producto->capacidad)
                        <span class="bg-indigo-900/50 text-indigo-300 text-[10px] font-bold px-2 py-0.5 rounded border border-indigo-700/50 uppercase">{{ $producto->capacidad }}</span>
                    @endif
                </div>
                {{-- Resumen de stock total y acceso al ajuste / eliminación --}}
                <div class="mt-1 flex justify-between items-center border-t border-slate-700/50 pt-3">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs text-slate-400 font-medium">Stock Total:</span>
                        <div class="flex items-center gap-3">
                            <button type="button"
                                class="btn-ajuste-masivo inline-flex items-center gap-1 text-[10px] font-bold text-indigo-400 hover:text-indigo-300 uppercase tracking-wider"
                                data-producto="{{ $productoJson }}"
                                data-lotes="{{ $lotesJson }}"
                                onclick="window.dispatchEvent(new CustomEvent('open-ajuste-masivo', { detail: { producto: JSON.parse(this.dataset.producto), lotes: JSON.parse(this.dataset.lotes) } }))">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Ajustar
                            </button>
                            <form action="{{ route('bodegas.producto.eliminar', [$bodega, $producto]) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar el producto \'{{ addslashes($producto->nombre) }}\' de la bodega \'{{ addslashes($bodega->nombre) }}\'?\n\n- Se darán de baja todas sus existencias ({{ number_format($stockTotal, 0) }} UN).\n- Quedará registrado en auditoría.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-400 hover:text-rose-300 uppercase tracking-wider">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Eliminar
                                </button>
                            </form>
                        </div>
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

            {{-- Cajón colapsable con los lotes individuales del producto en vista móvil --}}
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
                        {{-- Botones de acción vertical para facilitar pulsación en smartphones --}}
                        <div class="flex flex-col gap-2 mt-3">
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center mb-1">Acciones</div>
                            <button type="button"
                                class="btn-ajuste-masivo w-full flex justify-center items-center gap-2 text-sm py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-600 transition-colors shadow-sm"
                                data-producto="{{ $productoJson }}"
                                data-lotes="{{ $lotesJson }}"
                                onclick="window.dispatchEvent(new CustomEvent('open-ajuste-masivo', { detail: { producto: JSON.parse(this.dataset.producto), lotes: JSON.parse(this.dataset.lotes) } }))"
                                title="Ajustar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Ajustar Stock
                            </button>
                            <button type="button"
                                class="w-full flex justify-center items-center gap-2 text-sm py-2 bg-indigo-900/40 hover:bg-indigo-900/60 text-indigo-300 rounded-lg border border-indigo-700/50 transition-colors shadow-sm"
                                onclick="window.dispatchEvent(new CustomEvent('open-fechas-modal', { detail: { id: '{{ $lote->id }}', elab: '{{ $lote->fecha_elaboracion ? $lote->fecha_elaboracion->format('d/m/Y') : 'N/A' }}', venc: '{{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'S/F' }}', url: '{{ route('lotes.fechas.update', $lote->id) }}', vida_util: '{{ $producto->constante_vencimiento_meses ?? '' }}' } }))"
                                title="Fechas">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Fechas
                            </button>
                            <button type="button"
                                class="w-full flex justify-center items-center gap-2 text-sm py-2 bg-amber-900/40 hover:bg-amber-900/60 text-amber-300 rounded-lg border border-amber-700/50 transition-colors shadow-sm"
                                onclick="window.dispatchEvent(new CustomEvent('open-transfer-lote-modal', { detail: { id: {{ $lote->id }}, bodegaNombre: '{{ addslashes($bodega->nombre ?? '') }}' } }))"
                                title="Mover">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                Mover a Otra Bodega
                            </button>
                            <form action="{{ route('bodegas.lotes.eliminar', [$bodega, $lote]) }}" method="POST" class="w-full" onsubmit="return confirm('¿Estás seguro de eliminar el Lote #{{ $lote->id }} ({{ number_format($lote->cantidad_disponible, 0) }} UN) de esta bodega?\n\nEsta acción quedará registrada en auditoría.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full flex justify-center items-center gap-2 text-sm py-2 bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 hover:text-white rounded-lg border border-rose-800/50 transition-colors shadow-sm font-semibold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Dar de Baja Lote
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    {{-- Bloque móvil si el producto está sin lotes registrados --}}
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
                            <form action="{{ route('bodegas.producto.eliminar', [$bodega, $producto]) }}" method="POST" class="w-full" onsubmit="return confirm('¿Estás seguro de eliminar este producto del inventario de esta bodega?\n\nEsta acción quedará registrada en auditoría.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full flex justify-center items-center gap-2 text-sm py-2 bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 hover:text-white rounded-lg border border-rose-800/50 transition-colors shadow-sm font-semibold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Eliminar Producto de Bodega
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    @empty
        {{-- Tarjeta de estado vacío en móviles --}}
        <div class="bg-slate-800/60 rounded-xl border border-slate-700 p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            <p class="text-base font-medium text-slate-400">No se encontraron productos</p>
        </div>
    @endforelse
</div>

{{-- =========================================================================
     CONTENEDOR OCULTO DE PAGINACIÓN: UTILIZADO POR EL JS AJAX PARA SINCRONIZAR
     ========================================================================= --}}
@if($productos instanceof \Illuminate\Pagination\LengthAwarePaginator || $productos instanceof \Illuminate\Pagination\Paginator)
    <div id="pagination-links" class="hidden">
        {{ $productos->links() }}
    </div>
@endif
