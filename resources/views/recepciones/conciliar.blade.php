<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-gray-900 tracking-tight">
                    {{ __('Asignar Fechas') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Factura/Recepción: <span class="font-mono font-semibold text-indigo-600">{{ $recepcion->documento_referencia ?? 'N/A' }}</span></p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800 self-start md:self-auto">
                <svg class="mr-1.5 h-4 w-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                Pendiente de Fechas
            </span>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            
            <div class="mb-8 px-4 sm:px-0">
                <p class="text-gray-600">Revisa físicamente los envases en bodega e ingresa la <strong class="text-gray-900">fecha de vencimiento</strong> para cada lote recibido. Al confirmar, el stock se ingresará automáticamente.</p>
            </div>

            <form method="POST" action="{{ route('recepciones.conciliar.store', $recepcion) }}" id="conciliarForm">
                @csrf
                
                <div class="grid grid-cols-1 gap-6 px-4 sm:px-0">
                    @foreach($recepcion->detalles as $detalle)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-5 sm:p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 leading-snug">{{ $detalle->producto->nombre }}</h3>
                                    <p class="text-sm text-gray-500 mt-1 font-mono">SKU: {{ $detalle->producto->sku }}</p>
                                </div>
                                <div class="bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 flex flex-col items-center justify-center min-w-[4rem]">
                                    <span class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-0.5">Cant.</span>
                                    <span class="text-xl font-bold text-indigo-900">{{ number_format($detalle->cantidad, 0) }}</span>
                                </div>
                            </div>
                            
                            @php
                                $tieneConstante = !empty($detalle->producto->constante_vencimiento_meses);
                                $meses = $detalle->producto->constante_vencimiento_meses ?? 0;
                            @endphp
                            
                            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <input type="hidden" id="constante_{{ $detalle->id }}" value="{{ $meses }}">
                                
                                @if($tieneConstante)
                                    <!-- Modo Auto-calculado: Pide Elaboración -->
                                    <div>
                                        <label for="elab_{{ $detalle->id }}" class="block text-sm font-bold text-gray-800 mb-2">Fecha Elaboración <span class="text-red-500">*</span> <span class="text-xs font-normal text-gray-500 ml-1">(Suma {{ $meses }} meses)</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            </div>
                                            <input type="date" 
                                                   name="detalles[{{ $detalle->id }}][fecha_elaboracion]" 
                                                   id="elab_{{ $detalle->id }}"
                                                   class="pl-12 block w-full border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-lg py-4 font-semibold text-gray-900 shadow-sm transition-all hover:border-indigo-400" 
                                                   value="{{ old('detalles.'.$detalle->id.'.fecha_elaboracion', $detalle->fecha_elaboracion) }}"
                                                   onchange="calcularVencimiento({{ $detalle->id }})" required>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="venc_{{ $detalle->id }}" class="block text-sm font-bold text-gray-500 mb-2">Fecha Vencimiento <span class="text-xs font-normal text-indigo-600 ml-1">(Calculada automáticamente)</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </div>
                                            <input type="date" 
                                                   name="detalles[{{ $detalle->id }}][fecha_vencimiento]" 
                                                   id="venc_{{ $detalle->id }}"
                                                   class="pl-12 block w-full border-gray-200 bg-gray-50 text-gray-500 rounded-xl focus:ring-0 text-lg py-4 font-semibold shadow-inner" 
                                                   value="{{ old('detalles.'.$detalle->id.'.fecha_vencimiento', $detalle->fecha_vencimiento) }}" required readonly>
                                        </div>
                                    </div>
                                @else
                                    <!-- Modo Manual: Pide Vencimiento directo -->
                                    <div class="hidden">
                                        <input type="date" name="detalles[{{ $detalle->id }}][fecha_elaboracion]" id="elab_{{ $detalle->id }}" value="">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="venc_{{ $detalle->id }}" class="block text-sm font-bold text-gray-800 mb-2">Fecha de Vencimiento <span class="text-red-500">*</span> <span class="text-xs font-normal text-orange-600 ml-1">(Mirar envase)</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <svg class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </div>
                                            <input type="date" 
                                                   name="detalles[{{ $detalle->id }}][fecha_vencimiento]" 
                                                   id="venc_{{ $detalle->id }}"
                                                   class="pl-12 block w-full border-orange-200 bg-orange-50 text-gray-900 rounded-xl focus:ring-orange-500 focus:border-orange-500 text-lg py-4 font-semibold shadow-sm transition-all hover:border-orange-400" 
                                                   value="{{ old('detalles.'.$detalle->id.'.fecha_vencimiento', $detalle->fecha_vencimiento) }}" required>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Floating/Sticky Action Bar -->
                <div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] sm:static sm:bg-transparent sm:border-0 sm:shadow-none sm:mt-8 sm:p-0 flex flex-col sm:flex-row items-center justify-between gap-4 z-50">
                    <a href="{{ route('recepciones.index') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-4 border border-transparent text-lg font-bold rounded-xl shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:-translate-y-0.5">
                        <svg class="mr-2 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Confirmar e Ingresar Stock
                    </button>
                </div>
                <!-- Spacing for fixed bottom bar on mobile -->
                <div class="h-24 sm:hidden"></div>
            </form>

        </div>
    </div>

    <script>
        function calcularVencimiento(detalleId) {
            const constanteInput = document.getElementById('constante_' + detalleId);
            const elabInput = document.getElementById('elab_' + detalleId);
            const vencInput = document.getElementById('venc_' + detalleId);

            const meses = parseInt(constanteInput.value);
            
            if (meses > 0 && elabInput.value) {
                const fechaElab = new Date(elabInput.value);
                const fechaBase = new Date(fechaElab.getTime() + Math.abs(fechaElab.getTimezoneOffset() * 60000));
                
                fechaBase.setMonth(fechaBase.getMonth() + meses);
                
                const yyyy = fechaBase.getFullYear();
                const mm = String(fechaBase.getMonth() + 1).padStart(2, '0');
                const dd = String(fechaBase.getDate()).padStart(2, '0');
                
                vencInput.value = `${yyyy}-${mm}-${dd}`;
                
                vencInput.classList.add('bg-green-50', 'border-green-400');
                setTimeout(() => {
                    vencInput.classList.remove('bg-green-50', 'border-green-400');
                }, 1500);
            }
        }
    </script>
</x-app-layout>
