<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Conciliación de Fechas: ') }} {{ $recepcion->numero_factura }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Instrucciones de Conciliación</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>Ingresa las fechas correspondientes a cada lote físico. Si el producto tiene una constante de vencimiento configurada, el sistema pre-calculará la fecha de vencimiento automáticamente en cuanto ingreses la fecha de elaboración.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('recepciones.conciliar.store', $recepcion) }}">
                        @csrf
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Producto</th>
                                        <th scope="col" class="px-6 py-3">Cant.</th>
                                        <th scope="col" class="px-6 py-3">Fecha Elaboración</th>
                                        <th scope="col" class="px-6 py-3">Fecha Vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recepcion->detalles as $detalle)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            {{ $detalle->producto->nombre }}
                                            <div class="text-xs text-gray-500">SKU: {{ $detalle->producto->sku }}</div>
                                            <!-- Guardamos la constante para usarla en JS -->
                                            <input type="hidden" id="constante_{{ $detalle->id }}" value="{{ $detalle->producto->constante_vencimiento_meses ?? 0 }}">
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ number_format($detalle->cantidad) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="date" 
                                                   name="detalles[{{ $detalle->id }}][fecha_elaboracion]" 
                                                   id="elab_{{ $detalle->id }}"
                                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" 
                                                   value="{{ old('detalles.'.$detalle->id.'.fecha_elaboracion', $detalle->fecha_elaboracion) }}"
                                                   onchange="calcularVencimiento({{ $detalle->id }})">
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="date" 
                                                   name="detalles[{{ $detalle->id }}][fecha_vencimiento]" 
                                                   id="venc_{{ $detalle->id }}"
                                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" 
                                                   value="{{ old('detalles.'.$detalle->id.'.fecha_vencimiento', $detalle->fecha_vencimiento) }}" required>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('recepciones.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                                Volver
                            </a>
                            <x-primary-button class="ml-3 bg-indigo-600 hover:bg-indigo-700">
                                {{ __('Confirmar Conciliación') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Lógica de pre-cálculo FEFO -->
    <script>
        function calcularVencimiento(detalleId) {
            const constanteInput = document.getElementById('constante_' + detalleId);
            const elabInput = document.getElementById('elab_' + detalleId);
            const vencInput = document.getElementById('venc_' + detalleId);

            const meses = parseInt(constanteInput.value);
            
            if (meses > 0 && elabInput.value) {
                const fechaElab = new Date(elabInput.value);
                // Evitar desfase de zona horaria al parsear
                const fechaBase = new Date(fechaElab.getTime() + Math.abs(fechaElab.getTimezoneOffset() * 60000));
                
                fechaBase.setMonth(fechaBase.getMonth() + meses);
                
                // Formatear AAAA-MM-DD para el input type="date"
                const yyyy = fechaBase.getFullYear();
                const mm = String(fechaBase.getMonth() + 1).padStart(2, '0');
                const dd = String(fechaBase.getDate()).padStart(2, '0');
                
                vencInput.value = `${yyyy}-${mm}-${dd}`;
                
                // Highlight field to show it was auto-calculated
                vencInput.classList.add('bg-green-50', 'border-green-400');
                setTimeout(() => {
                    vencInput.classList.remove('bg-green-50', 'border-green-400');
                }, 1500);
            }
        }
    </script>
</x-app-layout>
