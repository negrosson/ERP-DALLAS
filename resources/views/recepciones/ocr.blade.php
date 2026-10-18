<x-app-layout>
    @push('head')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.10.0/localforage.min.js"></script>
    @endpush
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="text-xl font-bold leading-tight text-slate-200 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                {{ __('Escaneo de Facturas') }}
            </h2>
            <a href="{{ route('recepciones.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-slate-300 transition-all bg-slate-800 hover:bg-slate-700 rounded-xl border border-slate-700">
                &larr; Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-950 min-h-screen font-sans">
        <div class="mx-auto max-w-5xl w-full px-4 sm:px-6 lg:px-8 flex flex-col gap-6">
            
            <!-- Banners Offline / Sync -->
            <div id="offline-banner" class="hidden p-4 mb-4 text-sm text-yellow-800 bg-yellow-100 rounded-lg shadow-sm flex items-center" role="alert">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <span class="font-bold">Modo Offline Activo:</span> Sin conexión a Internet. Las facturas que proceses se guardarán en tu celular.
                </div>
            </div>

            <div id="sync-banner" class="hidden p-4 mb-4 text-sm text-emerald-800 bg-emerald-100 rounded-lg shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <div>
                        <span class="font-bold">¡Conexión Recuperada!</span> Tienes una factura guardada lista para sincronizar.
                    </div>
                </div>
                <button onclick="syncOfflineInvoice()" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow transition-colors w-full sm:w-auto text-center">
                    Sincronizar Ahora
                </button>
            </div>
            
            <div id="alert-container" class="hidden p-5 mb-6 rounded-2xl border border-rose-500/40 bg-gradient-to-r from-rose-950/80 to-slate-900 text-rose-200 shadow-2xl shadow-rose-950/50 flex items-start gap-4 transition-all" role="alert">
                <div class="p-2.5 bg-rose-500/20 text-rose-400 rounded-xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div class="flex-1">
                    <h4 id="alert-title" class="font-bold text-sm text-rose-100 uppercase tracking-wide">Error en el Documento</h4>
                    <p id="alert-message" class="text-xs text-rose-300 mt-1.5 leading-relaxed font-medium"></p>
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg shadow-black/50">
                <div class="mb-6 flex items-start">
                    <div class="p-3 bg-indigo-500/10 rounded-lg text-indigo-400 mr-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-200">Cargar Factura de Proveedor</h3>
                        <p class="text-xs text-slate-400 mt-1">Sube la foto o PDF de la factura para extraer automáticamente el folio, fecha y productos recibidos.</p>
                    </div>
                </div>

                <form id="ocr-form" onsubmit="event.preventDefault(); processOCR();" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Proveedor (Origen)</label>
                            <select id="proveedor_id" name="proveedor_id" onchange="autoAlinearBodegaConProveedor()" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Seleccionar...</option>
                                @foreach($proveedores as $prov)
                                    <option value="{{ $prov->id }}" data-codigo="{{ $prov->codigo }}" data-nombre="{{ $prov->nombre }}">{{ $prov->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Bodega (Destino)</label>
                            <select id="bodega_id" name="bodega_id" onchange="verificarIncompatibilidadFront()" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Seleccionar...</option>
                                @foreach($bodegas as $bodega)
                                    <option value="{{ $bodega->id }}" data-codigo="{{ $bodega->codigo }}" data-nombre="{{ $bodega->nombre }}">[{{ $bodega->codigo }}] {{ $bodega->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Aviso de Incompatibilidad Front-end -->
                    <div id="advertencia-bodega" class="hidden mt-4 p-3 bg-amber-500/10 border border-amber-500/30 rounded-xl text-xs text-amber-300 flex items-center gap-2"></div>

                    <div class="mt-6">
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" id="dropzone-label" class="flex flex-col items-center justify-center w-full h-48 border-2 border-slate-700 border-dashed rounded-xl cursor-pointer bg-slate-900 hover:bg-slate-800 hover:border-indigo-500 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4" id="upload-content">
                                    <svg class="w-10 h-10 mb-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    <p class="mb-2 text-sm text-slate-400"><span class="font-semibold text-indigo-400">Toca para abrir la cámara</span> o sube una imagen</p>
                                    <p class="text-xs text-slate-500">Foto o PDF (Recomendado: Factura extendida y legible)</p>
                                </div>
                                <input id="dropzone-file" name="factura" type="file" class="hidden" accept=".pdf,image/jpeg,image/png,image/jpg" capture="environment" required onchange="updateFileName(this)" />
                            </label>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="submit" id="btnProcessOCR" class="w-full md:w-auto px-8 py-4 text-base font-bold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-[0_0_15px_rgba(79,70,229,0.4)] disabled:opacity-50 flex items-center justify-center" disabled>
                            <svg class="w-5 h-5 mr-2 animate-spin hidden" id="spinner" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span id="btn-text">PROCESAR FACTURA</span>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Estado Visual OCR -->
            <div id="ocr-loading-state" class="hidden bg-slate-900 border border-indigo-500/30 rounded-2xl p-8 shadow-lg text-center flex flex-col items-center justify-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-slate-800">
                    <div class="h-1 bg-indigo-500 w-1/3 animate-[scan_2s_ease-in-out_infinite]"></div>
                </div>
                <svg class="w-16 h-16 text-indigo-500 animate-pulse mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                <h3 class="text-xl font-bold text-slate-200">Procesando y leyendo factura...</h3>
                <p class="text-slate-400 mt-2 max-w-md mx-auto">Detectando códigos, descripciones y cantidades. Esto tomará unos segundos.</p>
            </div>
        </div>
    </div>

    <style>
        @keyframes scan {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(300%); }
        }
    </style>

    <script>
        // DOM Elements
        const fileInput = document.getElementById('dropzone-file');
        const btnProcess = document.getElementById('btnProcessOCR');
        const provSelect = document.getElementById('proveedor_id');
        const bodSelect = document.getElementById('bodega_id');
        
        const alertContainer = document.getElementById('alert-container');
        const alertMessage = document.getElementById('alert-message');
        const loadingState = document.getElementById('ocr-loading-state');
        const form = document.getElementById('ocr-form');

        const offlineBanner = document.getElementById('offline-banner');
        const syncBanner = document.getElementById('sync-banner');

        // Initial setup
        window.addEventListener('load', () => {
            updateNetworkStatus();
            checkPendingSync();
        });
        
        window.addEventListener('online', () => {
            updateNetworkStatus();
            checkPendingSync();
        });
        
        window.addEventListener('offline', () => {
            updateNetworkStatus();
            syncBanner.classList.add('hidden');
        });

        function updateNetworkStatus() {
            if (navigator.onLine) {
                offlineBanner.classList.add('hidden');
            } else {
                offlineBanner.classList.remove('hidden');
            }
        }

        function checkPendingSync() {
            if (navigator.onLine && window.localforage) {
                localforage.getItem('offline_invoice').then(data => {
                    if (data) {
                        syncBanner.classList.remove('hidden');
                    }
                }).catch(err => console.error(err));
            }
        }

        function updateFileName(input) {
            const uploadContent = document.getElementById('upload-content');
            if (input.files && input.files.length > 0) {
                const fileName = input.files[0].name;
                uploadContent.innerHTML = `
                    <svg class="w-12 h-12 mb-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="mb-1 text-sm font-bold text-emerald-400">¡Imagen Lista!</p>
                    <p class="text-xs text-slate-400">${fileName}</p>
                `;
            }
            checkReady();
        }

        function checkReady() {
            if(fileInput.files.length > 0 && provSelect.value && bodSelect.value) {
                btnProcess.disabled = false;
            } else {
                btnProcess.disabled = true;
            }
        }

        provSelect.addEventListener('change', checkReady);
        bodSelect.addEventListener('change', checkReady);

        function showLoading() {
            btnProcess.disabled = true;
            document.getElementById('spinner').classList.remove('hidden');
            document.getElementById('btn-text').innerText = 'PROCESANDO...';
            alertContainer.classList.add('hidden');
            form.classList.add('opacity-50', 'pointer-events-none');
            loadingState.classList.remove('hidden');
            syncBanner.classList.add('hidden');
        }

        function hideLoading() {
            form.classList.remove('opacity-50', 'pointer-events-none');
            loadingState.classList.add('hidden');
            btnProcess.disabled = false;
            document.getElementById('spinner').classList.add('hidden');
            document.getElementById('btn-text').innerText = 'PROCESAR FACTURA';
        }

        function autoAlinearBodegaConProveedor() {
            const provOption = provSelect.options[provSelect.selectedIndex];
            if (!provOption || !provOption.value) {
                verificarIncompatibilidadFront();
                return;
            }

            const pTexto = ((provOption.dataset.codigo || '') + ' ' + (provOption.dataset.nombre || provOption.text)).toUpperCase();
            
            // Si es CCU, buscar bodega CCU
            if (pTexto.includes('CCU') || pTexto.includes('CERVECER')) {
                for (let i = 0; i < bodSelect.options.length; i++) {
                    const bOpt = bodSelect.options[i];
                    const bTexto = ((bOpt.dataset.codigo || '') + ' ' + (bOpt.dataset.nombre || bOpt.text)).toUpperCase();
                    if (bTexto.includes('CCU')) {
                        bodSelect.selectedIndex = i;
                        break;
                    }
                }
            } 
            // Si es Coca-Cola, buscar bodega Coca-Cola
            else if (pTexto.includes('COCA') || pTexto.includes('EMB') || pTexto.includes('ANDINA')) {
                for (let i = 0; i < bodSelect.options.length; i++) {
                    const bOpt = bodSelect.options[i];
                    const bTexto = ((bOpt.dataset.codigo || '') + ' ' + (bOpt.dataset.nombre || bOpt.text)).toUpperCase();
                    if (bTexto.includes('COCA')) {
                        bodSelect.selectedIndex = i;
                        break;
                    }
                }
            }

            verificarIncompatibilidadFront();
        }

        function verificarIncompatibilidadFront() {
            const advertenciaEl = document.getElementById('advertencia-bodega');
            if (!advertenciaEl) return;

            const provOption = provSelect.options[provSelect.selectedIndex];
            const bodOption = bodSelect.options[bodSelect.selectedIndex];

            if (!provOption?.value || !bodOption?.value) {
                advertenciaEl.classList.add('hidden');
                return;
            }

            const pTexto = ((provOption.dataset.codigo || '') + ' ' + (provOption.dataset.nombre || provOption.text)).toUpperCase();
            const bTexto = ((bodOption.dataset.codigo || '') + ' ' + (bodOption.dataset.nombre || bodOption.text)).toUpperCase();

            const isProvCCU = pTexto.includes('CCU') || pTexto.includes('CERVECER');
            const isProvCoca = pTexto.includes('COCA') || pTexto.includes('EMB') || pTexto.includes('ANDINA');

            const isBodCCU = bTexto.includes('CCU');
            const isBodCoca = bTexto.includes('COCA');

            if ((isProvCCU && isBodCoca) || (isProvCoca && isBodCCU)) {
                advertenciaEl.classList.remove('hidden');
                advertenciaEl.innerHTML = `<svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span><strong>Atención:</strong> Has seleccionado proveedor <strong>${isProvCCU ? 'CCU' : 'Coca-Cola'}</strong> con la bodega <strong>${bodOption.text}</strong>. El sistema verificará la factura escaneada para evitar discrepancias.</span>`;
            } else {
                advertenciaEl.classList.add('hidden');
            }
        }

        function processOCR() {
            const formData = new FormData(form);
            alertContainer.classList.add('hidden');
            
            if (!navigator.onLine && window.localforage) {
                // Modo Offline: Guardar en IndexedDB
                const file = fileInput.files[0];
                if (!file) return;

                const dataToSave = {
                    proveedor_id: provSelect.value,
                    bodega_id: bodSelect.value,
                    file: file,
                    token: formData.get('_token')
                };

                localforage.setItem('offline_invoice', dataToSave).then(() => {
                    alertContainer.classList.remove('hidden');
                    document.getElementById('alert-title').textContent = "Guardado Exitoso";
                    document.getElementById('alert-message').textContent = "Factura guardada en la memoria del dispositivo. Se sincronizará cuando recuperes conexión.";
                    
                    // Reset Form
                    form.reset();
                    btnProcess.disabled = true;
                    document.getElementById('upload-content').innerHTML = `
                        <svg class="w-10 h-10 mb-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <p class="mb-2 text-sm text-slate-400"><span class="font-semibold text-indigo-400">Toca para abrir la cámara</span> o sube una imagen</p>
                    `;
                }).catch(err => {
                    alertContainer.classList.remove('hidden');
                    document.getElementById('alert-message').textContent = "Error al guardar en el dispositivo: " + err.message;
                });
                return;
            }

            // Modo Online
            showLoading();

            fetch('{{ route('recepciones.ocr.process') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Error desconocido al procesar la factura.');
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect_url;
                }
            })
            .catch(error => {
                alertContainer.classList.remove('hidden');
                document.getElementById('alert-title').textContent = "Discrepancia / Error";
                document.getElementById('alert-message').textContent = error.message;
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                hideLoading();
            });
        }

        function syncOfflineInvoice() {
            if (!window.localforage) return;

            showLoading();

            localforage.getItem('offline_invoice').then(data => {
                if (!data) {
                    hideLoading();
                    syncBanner.classList.add('hidden');
                    return;
                }

                const formData = new FormData();
                formData.append('proveedor_id', data.proveedor_id);
                formData.append('bodega_id', data.bodega_id);
                formData.append('factura', data.file);
                formData.append('_token', data.token);

                fetch('{{ route('recepciones.ocr.process') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        localforage.removeItem('offline_invoice').then(() => {
                            window.location.href = res.redirect_url;
                        });
                    } else {
                        throw new Error(res.message || 'Error al sincronizar.');
                    }
                })
                .catch(error => {
                    alertContainer.classList.remove('hidden', 'bg-yellow-100', 'text-yellow-800');
                    alertContainer.classList.add('bg-red-100', 'text-red-700');
                    alertContainer.innerHTML = `<span class="font-bold">Error de Sincronización:</span> <span id="alert-message">${error.message}</span>`;
                    hideLoading();
                    syncBanner.classList.remove('hidden'); // Show sync banner again so they can retry
                });

            }).catch(err => {
                hideLoading();
                console.error(err);
            });
        }
    </script>
</x-app-layout>
