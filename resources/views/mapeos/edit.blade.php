<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Editar Mapeo de Código') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('mapeos.update', $mapeo) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Proveedor -->
                            <div>
                                <x-input-label for="proveedor_id" :value="__('Proveedor')" />
                                <select id="proveedor_id" name="proveedor_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id }}" {{ old('proveedor_id', $mapeo->proveedor_id) == $prov->id ? 'selected' : '' }}>
                                            {{ $prov->nombre }} ({{ $prov->codigo }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
                            </div>

                            <!-- Producto Maestro -->
                            <div>
                                <x-input-label for="catalogo_producto_id" :value="__('Producto Maestro')" />
                                <select id="catalogo_producto_id" name="catalogo_producto_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @foreach($productos as $prod)
                                        <option value="{{ $prod->id }}" {{ old('catalogo_producto_id', $mapeo->catalogo_producto_id) == $prod->id ? 'selected' : '' }}>
                                            [{{ $prod->sku }}] {{ $prod->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('catalogo_producto_id')" class="mt-2" />
                            </div>

                            <!-- Código Proveedor -->
                            <div>
                                <x-input-label for="codigo_proveedor" :value="__('Código usado por el Proveedor')" />
                                <x-text-input id="codigo_proveedor" class="block w-full mt-1" type="text" name="codigo_proveedor" :value="old('codigo_proveedor', $mapeo->codigo_proveedor)" required maxlength="50" autofocus />
                                <x-input-error :messages="$errors->get('codigo_proveedor')" class="mt-2" />
                            </div>

                            <!-- Factor Conversión -->
                            <div>
                                <x-input-label for="factor_conversion" :value="__('Factor de Conversión')" />
                                <p class="text-xs text-gray-500">¿Cuántas unidades del maestro equivalen a 1 unidad del proveedor? (Ej: 1 si es lo mismo, 6 si el proveedor vende pack de 6)</p>
                                <x-text-input id="factor_conversion" class="block w-full mt-1" type="number" step="0.01" min="0.01" name="factor_conversion" :value="old('factor_conversion', $mapeo->factor_conversion)" required />
                                <x-input-error :messages="$errors->get('factor_conversion')" class="mt-2" />
                            </div>

                            <!-- Descripción Proveedor -->
                            <div class="md:col-span-2">
                                <x-input-label for="descripcion_proveedor" :value="__('Descripción en sistema del Proveedor (Opcional)')" />
                                <x-text-input id="descripcion_proveedor" class="block w-full mt-1" type="text" name="descripcion_proveedor" :value="old('descripcion_proveedor', $mapeo->descripcion_proveedor)" maxlength="200" />
                                <x-input-error :messages="$errors->get('descripcion_proveedor')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('mapeos.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                                Cancelar
                            </a>
                            <x-primary-button class="ml-3">
                                {{ __('Actualizar Mapeo') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
