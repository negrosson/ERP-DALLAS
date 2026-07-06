<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Crear Nueva Recepción (Borrador)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('recepciones.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Proveedor -->
                            <div>
                                <x-input-label for="proveedor_id" :value="__('Proveedor')" />
                                <select id="proveedor_id" name="proveedor_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required autofocus>
                                    <option value="">Seleccione un Proveedor...</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id }}" {{ old('proveedor_id') == $prov->id ? 'selected' : '' }}>
                                            {{ $prov->nombre }} ({{ $prov->rut }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
                            </div>

                            <!-- Bodega -->
                            <div>
                                <x-input-label for="bodega_id" :value="__('Bodega de Destino')" />
                                <select id="bodega_id" name="bodega_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleccione la Bodega...</option>
                                    @foreach($bodegas as $bodega)
                                        <option value="{{ $bodega->id }}" {{ old('bodega_id') == $bodega->id ? 'selected' : '' }}>
                                            [{{ $bodega->codigo }}] {{ $bodega->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('bodega_id')" class="mt-2" />
                            </div>

                            <!-- Documento Referencia -->
                            <div>
                                <x-input-label for="documento_referencia" :value="__('Nº Documento (Factura, Guía, etc.)')" />
                                <x-text-input id="documento_referencia" class="block w-full mt-1" type="text" name="documento_referencia" :value="old('documento_referencia')" maxlength="50" placeholder="Opcional" />
                                <x-input-error :messages="$errors->get('documento_referencia')" class="mt-2" />
                            </div>

                            <!-- Observaciones -->
                            <div class="md:col-span-2">
                                <x-input-label for="observaciones" :value="__('Observaciones')" />
                                <textarea id="observaciones" name="observaciones" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3">{{ old('observaciones') }}</textarea>
                                <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('recepciones.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                                Cancelar
                            </a>
                            <x-primary-button class="ml-3">
                                {{ __('Crear Borrador') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
