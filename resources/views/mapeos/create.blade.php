{{-- 
========================================================================================
VISTA: CREACIÓN DE NUEVO MAPEO DE CÓDIGO
RUTA: /mapeos/create
CONTROLADOR: MapeoProductoController@create -> MapeoProductoController@store
PROPÓSITO:
- Crear una regla de equivalencia entre el SKU/código de un proveedor y un producto del catálogo interno.
- Configurar el factor de multiplicación para transformar automáticamente unidades proveedor a unidades base.
========================================================================================
--}}

<x-app-layout>
    {{-- Slot del encabezado: Título de la acción --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Crear Nuevo Mapeo de Código') }}
        </h2>
    </x-slot>

    {{-- Contenedor del formulario --}}
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    {{-- Formulario POST para almacenar nuevo mapeo --}}
                    <form method="POST" action="{{ route('mapeos.store') }}">
                        @csrf

                        {{-- Grilla de dos columnas adaptativa --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            {{-- Selector: Proveedor emisor del código --}}
                            <div>
                                <x-input-label for="proveedor_id" :value="__('Proveedor')" />
                                <select id="proveedor_id" name="proveedor_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required autofocus>
                                    <option value="">Seleccione un Proveedor...</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id }}" {{ old('proveedor_id') == $prov->id ? 'selected' : '' }}>
                                            {{ $prov->nombre }} ({{ $prov->codigo }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
                            </div>

                            {{-- Selector: Producto Maestro del catálogo interno --}}
                            <div>
                                <x-input-label for="catalogo_producto_id" :value="__('Producto Maestro')" />
                                <select id="catalogo_producto_id" name="catalogo_producto_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleccione un Producto...</option>
                                    @foreach($productos as $prod)
                                        <option value="{{ $prod->id }}" {{ old('catalogo_producto_id') == $prod->id ? 'selected' : '' }}>
                                            [{{ $prod->sku }}] {{ $prod->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('catalogo_producto_id')" class="mt-2" />
                            </div>

                            {{-- Campo: Código impreso por el proveedor en su factura o guía --}}
                            <div>
                                <x-input-label for="codigo_proveedor" :value="__('Código usado por el Proveedor')" />
                                <x-text-input id="codigo_proveedor" class="block w-full mt-1" type="text" name="codigo_proveedor" :value="old('codigo_proveedor')" required maxlength="50" />
                                <x-input-error :messages="$errors->get('codigo_proveedor')" class="mt-2" />
                            </div>

                            {{-- Campo: Factor de conversión multiplicativo --}}
                            <div>
                                <x-input-label for="factor_conversion" :value="__('Factor de Conversión')" />
                                <p class="text-xs text-gray-500">¿Cuántas unidades del maestro equivalen a 1 unidad del proveedor? (Ej: 1 si es lo mismo, 6 si el proveedor vende pack de 6)</p>
                                <x-text-input id="factor_conversion" class="block w-full mt-1" type="number" step="0.01" min="0.01" name="factor_conversion" :value="old('factor_conversion', 1)" required />
                                <x-input-error :messages="$errors->get('factor_conversion')" class="mt-2" />
                            </div>

                            {{-- Campo: Glosa o descripción textual usada por el proveedor --}}
                            <div class="md:col-span-2">
                                <x-input-label for="descripcion_proveedor" :value="__('Descripción en sistema del Proveedor (Opcional)')" />
                                <x-text-input id="descripcion_proveedor" class="block w-full mt-1" type="text" name="descripcion_proveedor" :value="old('descripcion_proveedor')" maxlength="200" />
                                <x-input-error :messages="$errors->get('descripcion_proveedor')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Botones de pie de formulario --}}
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('mapeos.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                                Cancelar
                            </a>
                            <x-primary-button class="ml-3">
                                {{ __('Guardar Mapeo') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
