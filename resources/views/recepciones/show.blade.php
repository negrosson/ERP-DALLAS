<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Recepción #') }}{{ str_pad($recepcion->id, 5, '0', STR_PAD_LEFT) }}
                @if($recepcion->estado === \App\Enums\EstadoRecepcion::CONFIRMADO)
                    <span class="ml-2 bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded">Confirmada</span>
                @else
                    <span class="ml-2 bg-yellow-100 text-yellow-800 text-sm font-medium px-2.5 py-0.5 rounded">Borrador</span>
                @endif
            </h2>
            <a href="{{ route('recepciones.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                &larr; Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    <span class="font-medium">Éxito!</span> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                    <span class="font-medium">Error!</span> {{ session('error') }}
                </div>
            @endif

            <!-- Cabecera -->
            <div class="p-6 mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-medium text-gray-900 border-b pb-2">Información General</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Proveedor</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ optional($recepcion->proveedor)->nombre }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Bodega Destino</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ optional($recepcion->bodega)->nombre }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Fecha de Recepción</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ \Carbon\Carbon::parse($recepcion->fecha_recepcion)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Documento Referencia</p>
                        <p class="mt-1 font-semibold text-gray-900">{{ $recepcion->documento_referencia ?? 'N/A' }}</p>
                    </div>
                    <div class="lg:col-span-4">
                        <p class="text-sm font-medium text-gray-500">Observaciones</p>
                        <p class="mt-1 text-gray-900">{{ $recepcion->observaciones ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Agregar Producto (Solo si es BORRADOR) -->
            @if($recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR)
            <div class="p-6 mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg border-l-4 border-indigo-500">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Agregar Producto</h3>
                
                <form method="POST" action="{{ route('recepciones.detalles.store', $recepcion) }}">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4 items-end">
                        <div class="md:col-span-2">
                            <x-input-label for="catalogo_producto_id" :value="__('Producto')" />
                            <select id="catalogo_producto_id" name="catalogo_producto_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Seleccione...</option>
                                @foreach($productos as $prod)
                                    <option value="{{ $prod->id }}" {{ old('catalogo_producto_id') == $prod->id ? 'selected' : '' }}>
                                        [{{ $prod->sku }}] {{ $prod->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('catalogo_producto_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="cantidad_recibida" :value="__('Cantidad')" />
                            <x-text-input id="cantidad_recibida" class="block w-full mt-1" type="number" step="0.01" min="0.01" name="cantidad_recibida" :value="old('cantidad_recibida', 1)" required />
                            <x-input-error :messages="$errors->get('cantidad_recibida')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="precio_unitario" :value="__('Costo Unitario ($)')" />
                            <x-text-input id="precio_unitario" class="block w-full mt-1" type="number" step="0.01" min="0" name="precio_unitario" :value="old('precio_unitario', 0)" required />
                            <x-input-error :messages="$errors->get('precio_unitario')" class="mt-2" />
                        </div>
                        
                        <div class="md:col-span-2">
                            <x-input-label for="fecha_vencimiento" :value="__('Fecha Vencimiento (Opcional si tiene autocalculo)')" />
                            <x-text-input id="fecha_vencimiento" class="block w-full mt-1" type="date" name="fecha_vencimiento" :value="old('fecha_vencimiento')" />
                            <x-input-error :messages="$errors->get('fecha_vencimiento')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2 flex justify-end">
                            <x-primary-button>
                                {{ __('Agregar a la Recepción') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            <!-- Listado de Productos -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Detalle de Recepción</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 whitespace-nowrap">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">SKU</th>
                                    <th scope="col" class="px-6 py-3">Producto</th>
                                    <th scope="col" class="px-6 py-3 text-right">Cantidad</th>
                                    <th scope="col" class="px-6 py-3 text-right">Costo Unit.</th>
                                    <th scope="col" class="px-6 py-3 text-right">Subtotal</th>
                                    <th scope="col" class="px-6 py-3">Vencimiento</th>
                                    @if($recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR)
                                        <th scope="col" class="px-6 py-3">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php $total = 0; @endphp
                                @forelse ($recepcion->detalles as $detalle)
                                    @php $total += $detalle->subtotal; @endphp
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-mono font-medium text-gray-900">
                                            {{ optional($detalle->producto)->sku }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ optional($detalle->producto)->nombre }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold">
                                            {{ number_format($detalle->cantidad_recibida, 2, ',', '.') }} {{ optional($detalle->producto)->unidad_medida }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            ${{ number_format($detalle->precio_unitario, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                            ${{ number_format($detalle->subtotal, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($detalle->fecha_vencimiento)
                                                {{ \Carbon\Carbon::parse($detalle->fecha_vencimiento)->format('d/m/Y') }}
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        @if($recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR)
                                            <td class="px-6 py-4">
                                                <form action="{{ route('recepciones.detalles.destroy', [$recepcion, $detalle]) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Quitar producto?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-medium text-red-600 hover:underline">Quitar</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR ? 7 : 6 }}" class="px-6 py-4 text-center text-gray-500">
                                            No hay productos en esta recepción.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($recepcion->detalles->count() > 0)
                                <tfoot>
                                    <tr class="bg-gray-50 font-bold text-gray-900">
                                        <td colspan="4" class="px-6 py-4 text-right uppercase">Total Recepción:</td>
                                        <td class="px-6 py-4 text-right text-lg text-blue-700">${{ number_format($total, 2, ',', '.') }}</td>
                                        <td colspan="{{ $recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR ? 2 : 1 }}"></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                    
                </div>
            </div>

            <!-- Confirmación -->
            @if($recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR && $recepcion->detalles->count() > 0)
            <div class="mt-6 flex justify-end">
                <form action="{{ route('recepciones.confirm', $recepcion) }}" method="POST" onsubmit="return confirm('¿Está seguro de CONFIRMAR esta recepción? Se ingresará el stock a la bodega y no podrá deshacer esta acción.');">
                    @csrf
                    <button type="submit" class="px-6 py-3 text-base font-medium text-white transition-colors bg-green-600 rounded-md hover:bg-green-700 shadow-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Confirmar e Ingresar Stock
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
