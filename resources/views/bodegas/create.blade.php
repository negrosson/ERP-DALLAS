{{-- 
========================================================================================
VISTA: CREACIÓN DE NUEVA BODEGA
RUTA: /bodegas/create
CONTROLADOR: BodegaController@create -> BodegaController@store
PROPÓSITO:
- Proveer el formulario administrativo para dar de alta una nueva bodega física o cámara fría.
- Configurar el código único interno, nombre descriptivo, notas de ubicación y banderas operativas:
  * es_refrigerada: Habilita el control de cadena de frío y alertas específicas.
  * activa: Determina si la bodega está habilitada para recepcionar y despachar mercadería.
========================================================================================
--}}

<x-app-layout>
    {{-- Slot del encabezado: Título de la vista --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Crear Nueva Bodega') }}
        </h2>
    </x-slot>

    {{-- Contenedor del formulario centrado con espaciado vertical --}}
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    {{-- Formulario POST para almacenar la bodega en la base de datos --}}
                    <form method="POST" action="{{ route('bodegas.store') }}">
                        @csrf

                        {{-- Grilla responsive de dos columnas para los datos de la bodega --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            
                            {{-- Campo: Código de Bodega (se fuerza visualmente a mayúsculas, max 20 chars) --}}
                            <div>
                                <x-input-label for="codigo" :value="__('Código de Bodega (Ej: BGEN)')" />
                                <x-text-input id="codigo" class="block w-full mt-1 uppercase" type="text" name="codigo" :value="old('codigo')" required autofocus maxlength="20" />
                                <x-input-error :messages="$errors->get('codigo')" class="mt-2" />
                            </div>

                            {{-- Campo: Nombre descriptivo de la bodega --}}
                            <div>
                                <x-input-label for="nombre" :value="__('Nombre de la Bodega')" />
                                <x-text-input id="nombre" class="block w-full mt-1" type="text" name="nombre" :value="old('nombre')" required maxlength="100" />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>

                            {{-- Campo: Descripción detallada o ubicación física dentro del recinto --}}
                            <div class="md:col-span-2">
                                <x-input-label for="descripcion" :value="__('Descripción / Ubicación (Opcional)')" />
                                <textarea id="descripcion" name="descripcion" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3">{{ old('descripcion') }}</textarea>
                                <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                            </div>

                            {{-- Checkbox: Es Refrigerada (Cadena de frío para lácteos, bebidas o perecibles) --}}
                            <div class="block mt-4 md:col-span-2">
                                <label for="es_refrigerada" class="inline-flex items-center">
                                    <input id="es_refrigerada" type="checkbox" class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500" name="es_refrigerada" value="1" {{ old('es_refrigerada') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Esta bodega es refrigerada (Cadena de frío)') }}</span>
                                </label>
                            </div>
                            
                            {{-- Checkbox: Bodega Activa (por defecto activada) --}}
                            <div class="block mt-2 md:col-span-2">
                                <label for="activa" class="inline-flex items-center">
                                    <input id="activa" type="checkbox" class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500" name="activa" value="1" {{ old('activa', true) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Bodega Activa') }}</span>
                                </label>
                            </div>
                        </div>

                        {{-- Botonera de control inferior: Cancelar y Guardar --}}
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('bodegas.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                                Cancelar
                            </a>
                            <x-primary-button class="ml-3">
                                {{ __('Guardar Bodega') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
