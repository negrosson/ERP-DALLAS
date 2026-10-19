{{-- Componente Principal de Layout de la Aplicación --}}
<x-app-layout>
    {{-- Inyección en el <head> de la biblioteca LocalForage para almacenamiento offline en IndexedDB --}}
    @push('head')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.10.0/localforage.min.js"></script>
    @endpush

    {{-- Encabezado de la Vista: Título del módulo y botón de navegación de retorno --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            {{-- Título con ícono de documento / factura --}}
            <h2 class="text-xl font-bold leading-tight text-slate-200 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                {{ __('Escaneo de Facturas') }}
            </h2>
            {{-- Enlace para regresar al listado general de recepciones --}}
            <a href="{{ route('recepciones.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-slate-300 transition-all bg-slate-800 hover:bg-slate-700 rounded-xl border border-slate-700">
                &larr; Volver
            </a>
        </div>
    </x-slot>

    {{-- Contenedor Principal de la Página con Fondo Oscuro y Tipografía Sans-serif --}}
    <div class="py-6 bg-slate-950 min-h-screen font-sans">
        <div class="mx-auto max-w-5xl w-full px-4 sm:px-6 lg:px-8 flex flex-col gap-6">
            
            {{-- Banner Offline: Notificación visual cuando se pierde la conexión a Internet --}}
            <div id="offline-banner" class="hidden p-4 mb-4 text-sm text-yellow-800 bg-yellow-100 rounded-lg shadow-sm flex items-center" role="alert">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <span class="font-bold">Modo Offline Activo:</span> Sin conexión a Internet. Las facturas que proceses se guardarán en tu celular.
                </div>
            </div>

            {{-- Banner de Sincronización: Alerta cuando se recupera la red y hay facturas pendientes en cola --}}
            <div id="sync-banner" class="hidden p-4 mb-4 text-sm text-emerald-800 bg-emerald-100 rounded-lg shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <div>
                        <span class="font-bold">¡Conexión Recuperada!</span> Tienes una factura guardada lista para sincronizar.
                    </div>
                </div>
                {{-- Botón para disparar la sincronización inmediata del documento en cola --}}
                <button onclick="syncOfflineInvoice()" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow transition-colors w-full sm:w-auto text-center">
                    Sincronizar Ahora
                </button>
            </div>
            
            {{-- Contenedor de Alertas de Error y Discrepancias de Marca / OCR --}}
            <div id="alert-container" class="hidden p-5 mb-6 rounded-2xl border border-rose-500/40 bg-gradient-to-r from-rose-950/80 to-slate-900 text-rose-200 shadow-2xl shadow-rose-950/50 flex items-start gap-4 transition-all" role="alert">
                <div class="p-2.5 bg-rose-500/20 text-rose-400 rounded-xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div class="flex-1">
                    <h4 id="alert-title" class="font-bold text-sm text-rose-100 uppercase tracking-wide">Error en el Documento</h4>
                    <p id="alert-message" class="text-xs text-rose-300 mt-1.5 leading-relaxed font-medium"></p>
                </div>
            </div>

            {{-- Tarjeta Principal del Formulario de Carga y Escaneo OCR --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg shadow-black/50">
                {{-- Encabezado descriptivo de la sección --}}
                <div class="mb-6 flex items-start">
                    <div class="p-3 bg-indigo-500/10 rounded-lg text-indigo-400 mr-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-200">Cargar Factura de Proveedor</h3>
                        <p class="text-xs text-slate-400 mt-1">Sube la foto o PDF de la factura para extraer automáticamente el folio, fecha y productos recibidos.</p>
                    </div>
                </div>

                {{-- Formulario de carga con prevención de submit por defecto para delegar a AJAX --}}
                <form id="ocr-form" onsubmit="event.preventDefault(); processOCR();" enctype="multipart/form-data">
                    @csrf
                    {{-- Cuadrícula de 2 columnas para selección de Proveedor y Bodega --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Selector de Proveedor Origen --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Proveedor (Origen)</label>
                            <select id="proveedor_id" name="proveedor_id" onchange="autoAlinearBodegaConProveedor()" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Seleccionar...</option>
                                @foreach($proveedores as $prov)
                                    <option value="{{ $prov->id }}" data-codigo="{{ $prov->codigo }}" data-nombre="{{ $prov->nombre }}">{{ $prov->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Selector de Bodega Destino --}}
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

                    {{-- Contenedor de Aviso de Incompatibilidad de Marcas (CCU vs Coca-Cola) --}}
                    <div id="advertencia-bodega" class="hidden mt-4 p-3 bg-amber-500/10 border border-amber-500/30 rounded-xl text-xs text-amber-300 flex items-center gap-2"></div>

                    {{-- Zona Dropzone para Arrastrar / Cargar Archivo o Capturar con la Cámara Móvil --}}
                    <div class="mt-6">
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" id="dropzone-label" class="flex flex-col items-center justify-center w-full h-48 border-2 border-slate-700 border-dashed rounded-xl cursor-pointer bg-slate-900 hover:bg-slate-800 hover:border-indigo-500 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4" id="upload-content">
                                    <svg class="w-10 h-10 mb-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    <p class="mb-2 text-sm text-slate-400"><span class="font-semibold text-indigo-400">Toca para abrir la cámara</span> o sube una imagen</p>
                                    <p class="text-xs text-slate-500">Foto o PDF (Recomendado: Factura extendida y legible)</p>
                                </div>
                                {{-- Input de archivo con capture="environment" para abrir la cámara trasera en smartphones --}}
                                <input id="dropzone-file" name="factura" type="file" class="hidden" accept=".pdf,image/jpeg,image/png,image/jpg" capture="environment" required onchange="updateFileName(this)" />
                            </label>
                        </div>
                    </div>
                    
                    {{-- Botón de Procesar Factura con Estado Deshabilitado por Defecto --}}
                    <div class="mt-6 flex justify-end">
                        <button type="submit" id="btnProcessOCR" class="w-full md:w-auto px-8 py-4 text-base font-bold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-[0_0_15px_rgba(79,70,229,0.4)] disabled:opacity-50 flex items-center justify-center" disabled>
                            {{-- Spinner de carga rotatorio para feedback visual --}}
                            <svg class="w-5 h-5 mr-2 animate-spin hidden" id="spinner" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span id="btn-text">PROCESAR FACTURA</span>
                        </button>
                    </div>
                </form>
            </div>
            
            {{-- Estado Visual de Procesamiento OCR con Animación de Escaneo Láser --}}
            <div id="ocr-loading-state" class="hidden bg-slate-900 border border-indigo-500/30 rounded-2xl p-8 shadow-lg text-center flex flex-col items-center justify-center relative overflow-hidden">
                {{-- Barra superior de escaneo con animación CSS continua --}}
                <div class="absolute top-0 left-0 w-full h-1 bg-slate-800">
                    <div class="h-1 bg-indigo-500 w-1/3 animate-[scan_2s_ease-in-out_infinite]"></div>
                </div>
                <svg class="w-16 h-16 text-indigo-500 animate-pulse mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                <h3 class="text-xl font-bold text-slate-200">Procesando y leyendo factura...</h3>
                <p class="text-slate-400 mt-2 max-w-md mx-auto">Detectando códigos, descripciones y cantidades. Esto tomará unos segundos.</p>
            </div>
        </div>
    </div>

    {{-- Estilos de animación personalizados para el efecto de barrido láser del escáner --}}
    <style>
        @keyframes scan {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(300%); }
        }
    </style>

    {{-- Lógica JavaScript del Módulo de Escaneo OCR y Resiliencia Offline --}}
    <script>
        // Referencias a los elementos del Document Object Model (DOM)
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

        // Configuración inicial de observadores de red al cargar la página
        window.addEventListener('load', () => {
            updateNetworkStatus();
            checkPendingSync();
        });
        
        // Detección automática de reconexión a Internet
        window.addEventListener('online', () => {
            updateNetworkStatus();
            checkPendingSync();
        });
        
        // Detección automática de pérdida de conexión a Internet
        window.addEventListener('offline', () => {
            updateNetworkStatus();
            syncBanner.classList.add('hidden');
        });

        /**
         * Actualiza la visibilidad del banner de advertencia según el estado navigator.onLine.
         */
        function updateNetworkStatus() {
            if (navigator.onLine) {
                offlineBanner.classList.add('hidden');
            } else {
                offlineBanner.classList.remove('hidden');
            }
        }

        /**
         * Consulta en LocalForage (IndexedDB) si existe una factura almacenada pendiente de sincronizar.
         */
        function checkPendingSync() {
            if (navigator.onLine && window.localforage) {
                localforage.getItem('offline_invoice').then(data => {
                    if (data) {
                        syncBanner.classList.remove('hidden');
                    }
                }).catch(err => console.error(err));
            }
        }

        /**
         * Actualiza el contenido visual de la dropzone al seleccionar un archivo o tomar una foto.
         * Muestra el nombre del archivo y un checkmark en color esmeralda.
         */
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

        /**
         * Valida que los tres campos obligatorios (archivo, proveedor y bodega) estén completos
         * para habilitar o deshabilitar el botón de procesar.
         */
        function checkReady() {
            if(fileInput.files.length > 0 && provSelect.value && bodSelect.value) {
                btnProcess.disabled = false;
            } else {
                btnProcess.disabled = true;
            }
        }

        // Suscripción a los cambios en los selectores para validar el estado de habilitación
        provSelect.addEventListener('change', checkReady);
        bodSelect.addEventListener('change', checkReady);

        /**
         * Muestra el estado de carga visual: desactiva el botón, enciende el spinner,
         * atenúa el formulario y despliega la animación de escaneo.
         */
        function showLoading() {
            btnProcess.disabled = true;
            document.getElementById('spinner').classList.remove('hidden');
            document.getElementById('btn-text').innerText = 'PROCESANDO...';
            alertContainer.classList.add('hidden');
            form.classList.add('opacity-50', 'pointer-events-none');
            loadingState.classList.remove('hidden');
            syncBanner.classList.add('hidden');
        }

        /**
         * Oculta el estado de carga y restablece la interactividad del formulario.
         */
        function hideLoading() {
            form.classList.remove('opacity-50', 'pointer-events-none');
            loadingState.classList.add('hidden');
            btnProcess.disabled = false;
            document.getElementById('spinner').classList.add('hidden');
            document.getElementById('btn-text').innerText = 'PROCESAR FACTURA';
        }

        /**
         * Regla de Negocio Front-End:
         * Alinea automáticamente la bodega sugerida según el proveedor seleccionado:
         * Si el proveedor contiene 'CCU' o 'CERVECER', selecciona la bodega CCU.
         * Si el proveedor contiene 'COCA', 'EMB' o 'ANDINA', selecciona la bodega Coca-Cola.
         */
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

        /**
         * Verifica si existe una discrepancia evidente entre el proveedor y la bodega elegida.
         * (Por ejemplo: Proveedor CCU en bodega Coca-Cola o viceversa).
         * Muestra una advertencia preventiva de color ámbar sin bloquear el flujo.
         */
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

        /**
         * Envía la factura a procesamiento OCR:
         * - Si no hay conexión, almacena el archivo en IndexedDB (localForage).
         * - Si hay conexión, realiza una petición POST multipart/form-data al backend Laravel.
         * - Si la respuesta es exitosa, redirige automáticamente a la pantalla de conciliación.
         */
        function processOCR() {
            const formData = new FormData(form);
            alertContainer.classList.add('hidden');
            
            // Bifurcación Modo Offline
            if (!navigator.onLine && window.localforage) {
                const file = fileInput.files[0];
                if (!file) return;

                const dataToSave = {
                    proveedor_id: provSelect.value,
                    bodega_id: bodSelect.value,
                    file: file,
                    token: formData.get('_token')
                };

                // Guardado en IndexedDB con localForage
                localforage.setItem('offline_invoice', dataToSave).then(() => {
                    alertContainer.classList.remove('hidden');
                    document.getElementById('alert-title').textContent = "Guardado Exitoso";
                    document.getElementById('alert-message').textContent = "Factura guardada en la memoria del dispositivo. Se sincronizará cuando recuperes conexión.";
                    
                    // Reiniciar el formulario
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

            // Modo Online: Envío HTTP mediante Fetch API
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
                    // Redirección al paso de conciliación asistida
                    window.location.href = data.redirect_url;
                }
            })
            .catch(error => {
                // Notificación visual de error o discrepancia detectada por el servicio OCR
                alertContainer.classList.remove('hidden');
                document.getElementById('alert-title').textContent = "Discrepancia / Error";
                document.getElementById('alert-message').textContent = error.message;
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                hideLoading();
            });
        }

        /**
         * Sincroniza la factura guardada en IndexedDB hacia el servidor tan pronto como
         * la conectividad a Internet se reanuda.
         */
        function syncOfflineInvoice() {
            if (!window.localforage) return;

            showLoading();

            localforage.getItem('offline_invoice').then(data => {
                if (!data) {
                    hideLoading();
                    syncBanner.classList.add('hidden');
                    return;
                }

                // Reconstrucción del FormData con los datos persistidos
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
                        // Limpieza de IndexedDB tras sincronización exitosa
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
                    syncBanner.classList.remove('hidden'); // Mostrar nuevamente para reintentar
                });

            }).catch(err => {
                hideLoading();
                console.error(err);
            });
        }
    </script>
</x-app-layout>
