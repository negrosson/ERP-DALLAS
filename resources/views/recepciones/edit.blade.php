{{-- 
========================================================================================
VISTA: EDICIÓN DE CABECERA DE RECEPCIÓN
RUTA: /recepciones/{recepcion}/edit
CONTROLADOR: RecepcionController@edit -> RecepcionController@update
PROPÓSITO:
- Modificar los datos generales de una recepción existente en estado no-confirmado.
- Actualizar el proveedor, bodega receptora, número de documento tributario o notas.
- Validación de formulario con valores antiguos (old) o existentes del modelo.
========================================================================================
--}}

<x-app-layout>
    {{-- Encabezado: Título dinámico con el número identificador formateado a 5 dígitos --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Editar Recepción:') }} #{{ str_pad($recepcion->id, 5, '0', STR_PAD_LEFT) }}
        </h2>
    </x-slot>

    {{-- Contenedor del formulario con márgenes responsivos --}}
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            {{-- Tarjeta contenedora blanca con esquinas redondeadas --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    {{-- Formulario para actualización con método spoofing PUT --}}
                    <form method="POST" action="{{ route('recepciones.update', $recepcion) }}">
                        {{-- Token CSRF de seguridad Laravel --}}
                        @csrf
                        {{-- Spoofing de verbo HTTP PUT requerido por la ruta de actualización REST --}}
                        @method('PUT')

                        {{-- Grilla de dos columnas adaptativa --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            
                            {{-- Selector de Proveedor: Selección vinculada al ID del proveedor --}}
                            <div>
                                <x-input-label for="proveedor_id" :value="__('Proveedor')" />
                                <select id="proveedor_id" name="proveedor_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required autofocus>
                                    <option value="">Seleccione un Proveedor...</option>
                                    {{-- Itera sobre la lista de proveedores preseleccionando el asignado a la recepción --}}
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id }}" {{ old('proveedor_id', $recepcion->proveedor_id) == $prov->id ? 'selected' : '' }}>
                                            {{ $prov->nombre }} ({{ $prov->rut }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
                            </div>

                            {{-- Selector de Bodega Destino: Establece el destino físico del inventario --}}
                            <div>
                                <x-input-label for="bodega_id" :value="__('Bodega de Destino')" />
                                <select id="bodega_id" name="bodega_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleccione la Bodega...</option>
                                    {{-- Itera sobre las bodegas del sistema preseleccionando la bodega actual --}}
                                    @foreach($bodegas as $bodega)
                                        <option value="{{ $bodega->id }}" {{ old('bodega_id', $recepcion->bodega_id) == $bodega->id ? 'selected' : '' }}>
                                            [{{ $bodega->codigo }}] {{ $bodega->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('bodega_id')" class="mt-2" />
                            </div>

                            {{-- Campo de Documento de Referencia: Factura o guía asociada --}}
                            <div>
                                <x-input-label for="documento_referencia" :value="__('Nº Documento (Factura, Guía, etc.)')" />
                                <x-text-input id="documento_referencia" class="block w-full mt-1" type="text" name="documento_referencia" :value="old('documento_referencia', $recepcion->documento_referencia)" maxlength="50" placeholder="Opcional" />
                                <x-input-error :messages="$errors->get('documento_referencia')" class="mt-2" />
                            </div>

                            {{-- Campo de Observaciones: Notas o incidencias detectadas al recibir el flete --}}
                            <div class="md:col-span-2">
                                <x-input-label for="observaciones" :value="__('Observaciones')" />
                                <textarea id="observaciones" name="observaciones" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3">{{ old('observaciones', $recepcion->observaciones) }}</textarea>
                                <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Botones de pie de formulario --}}
                        <div class="flex items-center justify-end mt-6">
                            {{-- Botón para descartar cambios y retornar a la vista principal --}}
                            <a href="{{ route('recepciones.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                                Cancelar
                            </a>
                            {{-- Botón de persistencia de modificaciones --}}
                            <x-primary-button class="ml-3">
                                {{ __('Guardar Cambios') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
