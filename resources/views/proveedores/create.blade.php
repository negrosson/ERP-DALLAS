<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Crear Nuevo Proveedor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('proveedores.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Código -->
                            <div>
                                <x-input-label for="codigo" :value="__('Código (Ej: CCU, EMB)')" />
                                <x-text-input id="codigo" class="block w-full mt-1" type="text" name="codigo" :value="old('codigo')" required autofocus maxlength="20" />
                                <x-input-error :messages="$errors->get('codigo')" class="mt-2" />
                            </div>

                            <!-- RUT -->
                            <div>
                                <x-input-label for="rut" :value="__('RUT Empresa')" />
                                <x-text-input id="rut" class="block w-full mt-1" type="text" name="rut" :value="old('rut')" required maxlength="12" placeholder="12.345.678-9" />
                                <x-input-error :messages="$errors->get('rut')" class="mt-2" />
                            </div>

                            <!-- Nombre -->
                            <div class="md:col-span-2">
                                <x-input-label for="nombre" :value="__('Razón Social')" />
                                <x-text-input id="nombre" class="block w-full mt-1" type="text" name="nombre" :value="old('nombre')" required maxlength="150" />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>

                            <!-- Contacto Nombre -->
                            <div>
                                <x-input-label for="contacto_nombre" :value="__('Nombre de Contacto')" />
                                <x-text-input id="contacto_nombre" class="block w-full mt-1" type="text" name="contacto_nombre" :value="old('contacto_nombre')" maxlength="100" />
                                <x-input-error :messages="$errors->get('contacto_nombre')" class="mt-2" />
                            </div>

                            <!-- Contacto Teléfono -->
                            <div>
                                <x-input-label for="contacto_telefono" :value="__('Teléfono de Contacto')" />
                                <x-text-input id="contacto_telefono" class="block w-full mt-1" type="text" name="contacto_telefono" :value="old('contacto_telefono')" maxlength="20" />
                                <x-input-error :messages="$errors->get('contacto_telefono')" class="mt-2" />
                            </div>

                            <!-- Contacto Email -->
                            <div class="md:col-span-2">
                                <x-input-label for="contacto_email" :value="__('Email de Contacto')" />
                                <x-text-input id="contacto_email" class="block w-full mt-1" type="email" name="contacto_email" :value="old('contacto_email')" maxlength="100" />
                                <x-input-error :messages="$errors->get('contacto_email')" class="mt-2" />
                            </div>

                            <!-- Dirección -->
                            <div class="md:col-span-2">
                                <x-input-label for="direccion" :value="__('Dirección')" />
                                <textarea id="direccion" name="direccion" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3">{{ old('direccion') }}</textarea>
                                <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                            </div>

                            <!-- Activo -->
                            <div class="block mt-4 md:col-span-2">
                                <label for="activo" class="inline-flex items-center">
                                    <input id="activo" type="checkbox" class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Proveedor Activo') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('proveedores.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                                Cancelar
                            </a>
                            <x-primary-button class="ml-3">
                                {{ __('Guardar Proveedor') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
