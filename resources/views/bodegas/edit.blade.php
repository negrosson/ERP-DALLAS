<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Editar Bodega:') }} {{ $bodega->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('bodegas.update', $bodega) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Código -->
                            <div>
                                <x-input-label for="codigo" :value="__('Código de Bodega (Ej: BGEN)')" />
                                <x-text-input id="codigo" class="block w-full mt-1 uppercase" type="text" name="codigo" :value="old('codigo', $bodega->codigo)" required autofocus maxlength="20" />
                                <x-input-error :messages="$errors->get('codigo')" class="mt-2" />
                            </div>

                            <!-- Nombre -->
                            <div>
                                <x-input-label for="nombre" :value="__('Nombre de la Bodega')" />
                                <x-text-input id="nombre" class="block w-full mt-1" type="text" name="nombre" :value="old('nombre', $bodega->nombre)" required maxlength="100" />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>

                            <!-- Descripción -->
                            <div class="md:col-span-2">
                                <x-input-label for="descripcion" :value="__('Descripción / Ubicación (Opcional)')" />
                                <textarea id="descripcion" name="descripcion" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3">{{ old('descripcion', $bodega->descripcion) }}</textarea>
                                <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                            </div>

                            <!-- Es Refrigerada -->
                            <div class="block mt-4 md:col-span-2">
                                <label for="es_refrigerada" class="inline-flex items-center">
                                    <input id="es_refrigerada" type="checkbox" class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500" name="es_refrigerada" value="1" {{ old('es_refrigerada', $bodega->es_refrigerada) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Esta bodega es refrigerada (Cadena de frío)') }}</span>
                                </label>
                            </div>
                            
                            <!-- Activa -->
                            <div class="block mt-2 md:col-span-2">
                                <label for="activa" class="inline-flex items-center">
                                    <input id="activa" type="checkbox" class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500" name="activa" value="1" {{ old('activa', $bodega->activa) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Bodega Activa') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('bodegas.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                                Cancelar
                            </a>
                            <x-primary-button class="ml-3">
                                {{ __('Actualizar Bodega') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
