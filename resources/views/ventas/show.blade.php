{{-- 
========================================================================================
VISTA: DETALLE DE IMPORTACIÓN DE VENTAS Y AUDITORÍA DE DEDUCCIÓN FEFO
RUTA: /ventas/{venta}
CONTROLADOR: VentaController@show
PROPÓSITO:
- Exponer el desglose detallado de los productos liquidados en una venta particular.
- Presentar las tarjetas de metadatos:
  * Bodega o máquina de donde se extrajo el stock físico.
  * Fecha comercial imputada vs fecha/hora del timestamp de inserción.
  * Nombre del archivo CSV origen de la importación.
  * Total facturado acumulado en moneda local.
- Mostrar la tabla de productos vendidos: Código SKU, Nombre del producto, Cantidad vendida,
  Precio unitario de referencia y Subtotal calculado.
- Diseño responsivo adaptado para Desktop y Smartphone.
========================================================================================
--}}

<x-app-layout>
    {{-- Slot del encabezado: Identificador de la venta y botón de retorno --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-200">
                    {{ __('Detalle de Importación de Ventas #') . $venta->id }}
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Deducciones efectuadas en la bodega según rotación FEFO</p>
            </div>
            {{-- Enlace para volver a la tabla general de ventas --}}
            <a href="{{ route('ventas.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-slate-300 transition-all bg-slate-800 hover:bg-slate-700 rounded-xl border border-slate-700">
                &larr; Volver a Ventas
            </a>
        </div>
    </x-slot>

    {{-- Contenedor del contenido principal --}}
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            {{-- =========================================================================
                 TARJETA: METADATOS GENERALES DE LA IMPORTACIÓN
                 ========================================================================= --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl shadow-black/50 p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Bodega o máquina donde impactó el descuento de mercadería --}}
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Bodega / Máquina</p>
                        <p class="text-base font-bold text-slate-200 mt-1">[{{ $venta->bodega->codigo }}] {{ $venta->bodega->nombre }}</p>
                    </div>
                    {{-- Fecha comercial de la transacción y timestamp del sistema --}}
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Fecha Comercial Venta</p>
                        <p class="text-base font-bold text-cyan-400 mt-1">{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</p>
                        <span class="text-[10px] text-slate-500">Ingresado al ERP: {{ $venta->created_at ? $venta->created_at->format('d/m/Y H:i') : '-' }}</span>
                    </div>
                    {{-- Nombre del archivo CSV cargado --}}
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Archivo Origen</p>
                        <p class="text-sm font-mono text-slate-300 mt-1 truncate" title="{{ $venta->origen_archivo }}">{{ $venta->origen_archivo ?? 'N/A' }}</p>
                    </div>
                    {{-- Total consolidado facturado --}}
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Total Facturado</p>
                        <p class="text-xl font-bold text-emerald-400 mt-1">${{ number_format($venta->total, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 TABLA: PRODUCTOS VENDIDOS Y DEDUCIDOS POR FEFO
                 ========================================================================= --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl shadow-black/50 overflow-hidden">
                <div class="p-6 border-b border-slate-800">
                    <h3 class="text-base font-bold text-slate-200">Productos Vendidos y Deducidos por FEFO</h3>
                </div>
                
                {{-- Vista de tabla para pantallas de escritorio (Desktop) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-400 whitespace-nowrap">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-950 border-b border-slate-800">
                            <tr>
                                <th scope="col" class="px-6 py-3">SKU</th>
                                <th scope="col" class="px-6 py-3">Producto</th>
                                <th scope="col" class="px-6 py-3 text-right">Cant. Vendida</th>
                                <th scope="col" class="px-6 py-3 text-right">Precio Unit. Ref</th>
                                <th scope="col" class="px-6 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            {{-- Itera sobre las líneas de producto vendidas --}}
                            @foreach ($venta->detalles as $detalle)
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    {{-- Código SKU del producto --}}
                                    <td class="px-6 py-4 font-mono font-bold text-emerald-400 text-xs">{{ $detalle->producto->sku }}</td>
                                    {{-- Nombre del producto deducido --}}
                                    <td class="px-6 py-4 text-slate-200">{{ $detalle->producto->nombre }}</td>
                                    {{-- Cantidad descontada del stock --}}
                                    <td class="px-6 py-4 text-right font-bold text-white">{{ number_format($detalle->cantidad, 0, ',', '.') }}</td>
                                    {{-- Precio unitario de referencia --}}
                                    <td class="px-6 py-4 text-right text-slate-400">${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                                    {{-- Subtotal monetario de la línea --}}
                                    <td class="px-6 py-4 text-right font-bold text-emerald-400">${{ number_format($detalle->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        {{-- Pie de tabla con el total general monetario --}}
                        <tfoot>
                            <tr class="bg-slate-950 font-bold border-t border-slate-800">
                                <td colspan="4" class="px-6 py-4 text-right text-slate-300 uppercase text-xs">Total:</td>
                                <td class="px-6 py-4 text-right text-emerald-400 text-base">${{ number_format($venta->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Vista en tarjetas para pantallas móviles (Smartphones) --}}
                <div class="md:hidden divide-y divide-slate-800">
                    @foreach ($venta->detalles as $detalle)
                        <div class="p-4 space-y-1">
                            <div class="flex justify-between items-start">
                                <span class="font-mono text-xs font-bold text-emerald-400">{{ $detalle->producto->sku }}</span>
                                <span class="font-bold text-emerald-400 text-sm">${{ number_format($detalle->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-sm font-semibold text-slate-200">{{ $detalle->producto->nombre }}</div>
                            <div class="flex justify-between items-center text-xs text-slate-400 pt-1">
                                <span>Cant: <strong class="text-white">{{ number_format($detalle->cantidad, 0, ',', '.') }}</strong></span>
                                <span>Unit: ${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                    {{-- Total final visible en móviles --}}
                    <div class="p-4 bg-slate-950 flex justify-between items-center font-bold">
                        <span class="text-xs uppercase text-slate-400">Total Venta:</span>
                        <span class="text-emerald-400 text-lg">${{ number_format($venta->total, 0, ',', '.') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
