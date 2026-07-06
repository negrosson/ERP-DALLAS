<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold leading-tight text-indigo-400 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                {{ __('Escaneo de Facturas (OCR con IA)') }}
            </h2>
            <a href="{{ route('recepciones.index') }}" class="px-4 py-2 text-sm font-bold text-slate-300 transition-all bg-slate-800 hover:bg-slate-700 rounded-xl border border-slate-700">
                Volver a Recepciones
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-950 min-h-screen font-sans">
        <div class="mx-auto max-w-5xl w-full px-4 sm:px-6 lg:px-8 flex flex-col gap-6">
            
            <!-- Instructions and Form -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg shadow-black/50">
                <div class="mb-6 flex items-start">
                    <div class="p-3 bg-indigo-500/10 rounded-lg text-indigo-400 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-200">Automatización de Recepción</h3>
                        <p class="text-sm text-slate-400 mt-1">Sube una fotografía o PDF de la factura del proveedor. Nuestro sistema procesará el documento extrayendo los SKUs, descripciones y cantidades automáticamente usando Inteligencia Artificial.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Proveedor Origen (Para validación cruzada)</label>
                        <select id="proveedor_id" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Seleccionar...</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Bodega Destino</label>
                        <select id="bodega_id" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Seleccionar...</option>
                            @foreach($bodegas as $bodega)
                                <option value="{{ $bodega->id }}">[{{ $bodega->codigo }}] {{ $bodega->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="flex items-center justify-center w-full">
                        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-slate-700 border-dashed rounded-xl cursor-pointer bg-slate-900 hover:bg-slate-800 hover:border-indigo-500 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <p class="mb-2 text-sm text-slate-400"><span class="font-semibold text-indigo-400">Haz clic para subir</span> o arrastra y suelta tu factura</p>
                                <p class="text-xs text-slate-500">PDF, JPG, PNG (MAX. 5MB)</p>
                            </div>
                            <input id="dropzone-file" type="file" class="hidden" accept=".pdf,image/*" />
                        </label>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button id="btnProcessOCR" class="px-6 py-3 text-sm font-bold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-[0_0_15px_rgba(79,70,229,0.4)] disabled:opacity-50 flex items-center" disabled>
                        <svg class="w-5 h-5 mr-2 animate-spin hidden" id="spinner" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        PROCESAR FACTURA
                    </button>
                </div>
            </div>

            <!-- Resultados (Oculto Inicialmente) -->
            <div id="ocrResults" class="hidden bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg shadow-black/50">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold tracking-widest text-emerald-400 uppercase">Datos Extraídos Exitosamente</h3>
                    <span class="text-xs text-slate-400 font-mono">Confianza ML: 98.4%</span>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-800">
                    <table class="w-full text-sm text-left text-slate-400">
                        <thead class="text-xs text-slate-300 uppercase bg-slate-800">
                            <tr>
                                <th class="px-4 py-3">Código Detectado</th>
                                <th class="px-4 py-3">Descripción en Factura</th>
                                <th class="px-4 py-3 text-center">Cant.</th>
                                <th class="px-4 py-3 text-center">Mapeo Sistema</th>
                            </tr>
                        </thead>
                        <tbody id="resultTableBody" class="divide-y divide-slate-800/50 bg-slate-900/50">
                            <!-- JS will populate -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <button onclick="alert('Funcionalidad simulada. Aquí se enviaría el POST a recepciones.store')" class="px-6 py-3 text-sm font-bold text-slate-900 transition-all bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-[0_0_15px_rgba(16,185,129,0.4)]">
                        CONFIRMAR E INGRESAR AL INVENTARIO
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('dropzone-file');
            const btnProcess = document.getElementById('btnProcessOCR');
            const spinner = document.getElementById('spinner');
            const resultsDiv = document.getElementById('ocrResults');
            const resultBody = document.getElementById('resultTableBody');
            
            // Requerir proveedor para habilitar botón
            const provSelect = document.getElementById('proveedor_id');
            const bodSelect = document.getElementById('bodega_id');
            
            function checkReady() {
                if(fileInput.files.length > 0 && provSelect.value && bodSelect.value) {
                    btnProcess.disabled = false;
                } else {
                    btnProcess.disabled = true;
                }
            }

            fileInput.addEventListener('change', checkReady);
            provSelect.addEventListener('change', checkReady);
            bodSelect.addEventListener('change', checkReady);

            btnProcess.addEventListener('click', function() {
                // Simulación de OCR
                btnProcess.disabled = true;
                spinner.classList.remove('hidden');
                resultsDiv.classList.add('hidden');
                
                // Simular llamada a API de ML de 2 segundos
                setTimeout(() => {
                    spinner.classList.add('hidden');
                    btnProcess.innerHTML = 'FACTURA PROCESADA';
                    resultsDiv.classList.remove('hidden');
                    
                    const mockData = [
                        { codigo: '780123456789', desc: 'Bebida Energética 250ml', qty: 24, match: true },
                        { codigo: '780987654321', desc: 'Galletas de Chocolate', qty: 10, match: true },
                        { codigo: 'PROV-001', desc: 'Caja Cartón Embalaje', qty: 50, match: false },
                    ];

                    resultBody.innerHTML = '';
                    mockData.forEach(item => {
                        const matchBadge = item.match 
                            ? '<span class="bg-emerald-500/10 text-emerald-400 text-xs font-bold px-2 py-1 rounded border border-emerald-500/20"><i class="fa fa-check mr-1"></i> Match Exacto</span>' 
                            : '<span class="bg-amber-500/10 text-amber-400 text-xs font-bold px-2 py-1 rounded border border-amber-500/20">Mapeo Manual Requerido</span>';
                            
                        resultBody.innerHTML += `
                            <tr>
                                <td class="px-4 py-3 font-mono">${item.codigo}</td>
                                <td class="px-4 py-3">${item.desc}</td>
                                <td class="px-4 py-3 text-center font-bold text-white">${item.qty}</td>
                                <td class="px-4 py-3 text-center">${matchBadge}</td>
                            </tr>
                        `;
                    });
                    
                }, 2000);
            });
        });
    </script>
</x-app-layout>
