<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Importar Ventas POS') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Formato del Archivo</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>El sistema requiere un archivo <strong>.csv</strong> con las siguientes columnas en la primera fila (encabezado):</p>
                                    <ul class="list-disc list-inside mt-1">
                                        <li><code>sku</code> (Código del producto en el catálogo)</li>
                                        <li><code>cantidad</code> (Cantidad vendida)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(session('errores_csv'))
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Se encontraron errores en el archivo:</strong>
                            <ul class="mt-2 text-sm list-disc list-inside">
                                @foreach(session('errores_csv') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('ventas.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-6">
                            <x-input-label for="bodega_id" :value="__('Bodega (De dónde se descontará el stock)')" />
                            <select id="bodega_id" name="bodega_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach($bodegas as $bodega)
                                    <option value="{{ $bodega->id }}" {{ $bodega->codigo === 'B-SALA' ? 'selected' : '' }}>
                                        {{ $bodega->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('bodega_id')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="archivo_ventas" :value="__('Archivo CSV de Ventas')" />
                            <input type="file" id="archivo_ventas" name="archivo_ventas" accept=".csv,.txt" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required />
                            <x-input-error :messages="$errors->get('archivo_ventas')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('ventas.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900 mr-4">
                                Cancelar
                            </a>
                            <x-primary-button>
                                {{ __('Procesar y Deducir Stock') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
