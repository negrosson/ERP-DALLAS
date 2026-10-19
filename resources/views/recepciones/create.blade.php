{{-- 
========================================================================================
VISTA: CREACIÓN DE NUEVA RECEPCIÓN (BORRADOR)
RUTA: /recepciones/create
CONTROLADOR: RecepcionController@create -> RecepcionController@store
PROPÓSITO:
- Crear una cabecera de recepción en estado inicial 'BORRADOR'.
- Registrar proveedor, bodega de destino, documento de respaldo (factura/guía) y notas.
- Una vez creado el borrador, el sistema redirige al usuario para agregar líneas de productos.
========================================================================================
--}}

<x-app-layout>
    {{-- Encabezado de la página: Título de la acción --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Crear Nueva Recepción (Borrador)') }}
        </h2>
    </x-slot>

    {{-- Contenedor principal con espaciado vertical --}}
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            {{-- Tarjeta contenedor con fondo blanco y sombra suave --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    {{-- Formulario POST hacia el endpoint store del controlador de recepciones --}}
                    <form method="POST" action="{{ route('recepciones.store') }}">
                        {{-- Token de seguridad contra ataques CSRF --}}
                        @csrf

                        {{-- Grilla responsive de dos columnas para los campos de datos --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            
                            {{-- Campo: Proveedor con soporte para Datalist HTML5 (autocompletado rápido por texto o RUT) --}}
                            <div>
                                <x-input-label for="proveedor_id" :value="__('Proveedor')" />
                                <input type="text" id="proveedor_id" name="proveedor_id" list="proveedores_list" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Escriba o seleccione un proveedor..." value="{{ old('proveedor_id') }}" required autofocus autocomplete="off">
                                {{-- Lista de opciones provista por el servidor para el buscador nativo --}}
                                <datalist id="proveedores_list">
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->nombre }}">{{ $prov->rut }}</option>
                                    @endforeach
                                </datalist>
                                {{-- Mensaje de validación de error para el campo de proveedor --}}
                                <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
                            </div>

                            {{-- Campo: Selector desplegable de Bodega de Destino --}}
                            <div>
                                <x-input-label for="bodega_id" :value="__('Bodega de Destino')" />
                                <select id="bodega_id" name="bodega_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleccione la Bodega...</option>
                                    {{-- Itera sobre las bodegas habilitadas en el sistema --}}
                                    @foreach($bodegas as $bodega)
                                        <option value="{{ $bodega->id }}" {{ old('bodega_id') == $bodega->id ? 'selected' : '' }}>
                                            [{{ $bodega->codigo }}] {{ $bodega->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- Mensaje de error para validación de bodega --}}
                                <x-input-error :messages="$errors->get('bodega_id')" class="mt-2" />
                            </div>

                            {{-- Campo: Documento de Referencia (Número de factura electrónica, guía de despacho o remito) --}}
                            <div>
                                <x-input-label for="documento_referencia" :value="__('Nº Documento (Factura, Guía, etc.)')" />
                                <x-text-input id="documento_referencia" class="block w-full mt-1" type="text" name="documento_referencia" :value="old('documento_referencia')" maxlength="50" placeholder="Opcional" />
                                <x-input-error :messages="$errors->get('documento_referencia')" class="mt-2" />
                            </div>

                            {{-- Campo: Observaciones y notas logísticas del ingreso (ocupa 2 columnas en pantallas medianas) --}}
                            <div class="md:col-span-2">
                                <x-input-label for="observaciones" :value="__('Observaciones')" />
                                <textarea id="observaciones" name="observaciones" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3">{{ old('observaciones') }}</textarea>
                                <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Botonera de control inferior: Cancelar y Crear Borrador --}}
                        <div class="flex items-center justify-end mt-6">
                            {{-- Enlace para abortar y volver a la tabla general de recepciones --}}
                            <a href="{{ route('recepciones.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                                Cancelar
                            </a>
                            {{-- Botón de envío para persistir el nuevo registro borrador --}}
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
