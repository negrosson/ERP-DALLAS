<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Crear Nuevo Producto Maestro') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('catalogo.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- SKU -->
                            <div>
                                <x-input-label for="sku" :value="__('SKU (Código Interno)')" />
                                <x-text-input id="sku" class="block w-full mt-1" type="text" name="sku" :value="old('sku')" required autofocus maxlength="50" />
                                <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                            </div>

                            <!-- Unidad de Medida -->
                            <div>
                                <x-input-label for="unidad_medida" :value="__('Unidad de Medida')" />
                                <select id="unidad_medida" name="unidad_medida" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="UN" {{ old('unidad_medida') == 'UN' ? 'selected' : '' }}>Unidad (UN)</option>
                                    <option value="CJ" {{ old('unidad_medida') == 'CJ' ? 'selected' : '' }}>Caja (CJ)</option>
                                    <option value="KG" {{ old('unidad_medida') == 'KG' ? 'selected' : '' }}>Kilogramo (KG)</option>
                                    <option value="LT" {{ old('unidad_medida') == 'LT' ? 'selected' : '' }}>Litro (LT)</option>
                                </select>
                                <x-input-error :messages="$errors->get('unidad_medida')" class="mt-2" />
                            </div>

                            <!-- Nombre -->
                            <div class="md:col-span-2">
                                <x-input-label for="nombre" :value="__('Nombre del Producto')" />
                                <x-text-input id="nombre" class="block w-full mt-1" type="text" name="nombre" :value="old('nombre')" required maxlength="200" />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>

                            <!-- Precios -->
                            <div>
                                <x-input-label for="precio_compra_ref" :value="__('Precio Compra Ref. ($)')" />
                                <x-text-input id="precio_compra_ref" class="block w-full mt-1" type="number" step="0.01" min="0" name="precio_compra_ref" :value="old('precio_compra_ref', 0)" required />
                                <x-input-error :messages="$errors->get('precio_compra_ref')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="precio_venta" :value="__('Precio Venta ($)')" />
                                <x-text-input id="precio_venta" class="block w-full mt-1" type="number" step="0.01" min="0" name="precio_venta" :value="old('precio_venta', 0)" required />
                                <x-input-error :messages="$errors->get('precio_venta')" class="mt-2" />
                            </div>

                            <!-- Autocalculo de Vencimiento -->
                            <div class="md:col-span-2">
                                <x-input-label for="constante_vencimiento_meses" :value="__('Constante de Vencimiento (Meses)')" />
                                <p class="text-xs text-gray-500">Dejar en blanco si la fecha de vencimiento se ingresará manualmente en cada recepción.</p>
                                <x-text-input id="constante_vencimiento_meses" class="block w-full mt-1 sm:w-1/2" type="number" step="1" min="1" max="120" name="constante_vencimiento_meses" :value="old('constante_vencimiento_meses')" />
                                <x-input-error :messages="$errors->get('constante_vencimiento_meses')" class="mt-2" />
                            </div>

                            <!-- Descripción -->
                            <div class="md:col-span-2">
                                <x-input-label for="descripcion" :value="__('Descripción')" />
                                <textarea id="descripcion" name="descripcion" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3">{{ old('descripcion') }}</textarea>
                                <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                            </div>

                            <!-- Activo -->
                            <div class="block mt-4 md:col-span-2">
                                <label for="activo" class="inline-flex items-center">
                                    <input id="activo" type="checkbox" class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Producto Activo') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('catalogo.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                                Cancelar
                            </a>
                            <x-primary-button class="ml-3">
                                {{ __('Guardar Producto') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
