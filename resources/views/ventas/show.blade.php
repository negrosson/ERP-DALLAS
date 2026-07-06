<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle de Importación de Ventas #') . $venta->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 uppercase tracking-wide font-semibold">Bodega Origen (Descuento FEFO)</p>
                            <p class="text-lg font-medium">{{ $venta->bodega->nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 uppercase tracking-wide font-semibold">Fecha de Registro</p>
                            <p class="text-lg font-medium">{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 uppercase tracking-wide font-semibold">Archivo Origen</p>
                            <p class="text-lg font-mono text-gray-700">{{ $venta->origen_archivo ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 uppercase tracking-wide font-semibold">Total Venta</p>
                            <p class="text-xl font-bold text-green-600">${{ number_format($venta->total, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <h3 class="text-lg font-medium mb-4">Productos Vendidos</h3>
                    <table class="w-full text-sm text-left text-gray-500 whitespace-nowrap">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                            <tr>
                                <th scope="col" class="px-6 py-3">SKU</th>
                                <th scope="col" class="px-6 py-3">Producto</th>
                                <th scope="col" class="px-6 py-3 text-right">Cant. Vendida</th>
                                <th scope="col" class="px-6 py-3 text-right">Precio Unit. (Ref)</th>
                                <th scope="col" class="px-6 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($venta->detalles as $detalle)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-mono">{{ $detalle->producto->sku }}</td>
                                    <td class="px-6 py-4">{{ $detalle->producto->nombre }}</td>
                                    <td class="px-6 py-4 text-right font-medium text-gray-900">{{ number_format($detalle->cantidad, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right">${{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900">${{ number_format($detalle->subtotal, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-bold">
                                <td colspan="4" class="px-6 py-4 text-right">Total:</td>
                                <td class="px-6 py-4 text-right">${{ number_format($venta->total, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="mt-6">
                        <a href="{{ route('ventas.index') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">&larr; Volver a Ventas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
