<x-app-layout>
    @push('head')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.10.0/localforage.min.js"></script>
    @endpush
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="text-xl font-bold leading-tight text-emerald-400 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                {{ __('Modo Escáner: Ingreso Rápido') }}
            </h2>
            <a href="{{ route('recepciones.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-slate-300 transition-all bg-slate-800 hover:bg-slate-700 rounded-xl border border-slate-700">
                Salir del Modo Escáner
            </a>
        </div>
    </x-slot>

    <!-- Html5Qrcode Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div class="py-4 bg-slate-950 min-h-screen font-sans">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 h-full flex flex-col lg:flex-row gap-6">
            
            <!-- Banner Offline -->
            <div id="offline-banner" class="hidden absolute top-0 left-0 w-full bg-yellow-500/90 text-yellow-950 font-bold px-4 py-2 text-center z-50 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>ESTÁS SIN CONEXIÓN - Modo Offline Activo. Tus escaneos se guardarán en el celular.</span>
            </div>
            
            <div id="online-sync-banner" class="hidden absolute top-0 left-0 w-full bg-emerald-500/90 text-emerald-950 font-bold px-4 py-2 text-center z-50 flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>CONEXIÓN RECUPERADA - Tienes datos pendientes de sincronizar.</span>
                </div>
                <button type="button" id="btnSync" class="px-4 py-1 bg-emerald-950 text-emerald-400 rounded-lg text-sm font-bold shadow-lg hover:bg-emerald-900">Sincronizar Ahora</button>
            </div>

            <!-- Panel Izquierdo: Configuración y Scanner -->
            <div class="w-full lg:w-1/3 flex flex-col gap-6">
                
                <!-- Setup Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg shadow-black/50">
                    <h3 class="text-sm font-bold tracking-widest text-slate-400 uppercase mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        1. Configuración de Entrada
                    </h3>
                    <form id="setupForm" onsubmit="event.preventDefault();">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Proveedor Origen <span class="text-rose-500">*</span></label>
                                <select id="proveedor_id" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-emerald-500 focus:ring-emerald-500 text-sm" required>
                                    <option value="">Seleccionar Proveedor...</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Bodega Destino <span class="text-rose-500">*</span></label>
                                <select id="bodega_id" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-emerald-500 focus:ring-emerald-500 text-sm" required>
                                    <option value="">Seleccionar Bodega...</option>
                                    @foreach($bodegas as $bodega)
                                        <option value="{{ $bodega->id }}">[{{ $bodega->codigo }}] {{ $bodega->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">N° Factura / Guía (Validación Anti-Duplicado)</label>
                                <input type="text" id="documento_referencia" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-emerald-500 focus:ring-emerald-500 uppercase font-mono text-sm" placeholder="Ej: 849302">
                                <div id="factura-feedback" class="mt-1 text-xs hidden"></div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Scanner Active Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg shadow-black/50 flex flex-col relative overflow-hidden group">
                    <div class="absolute inset-0 bg-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none indicator-pulse"></div>
                    
                    <h3 class="text-sm font-bold tracking-widest text-slate-400 uppercase mb-4 relative z-10 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                        2. Captura de Código
                    </h3>
                    
                    <div class="flex flex-col items-center justify-center space-y-4 relative z-10">
                        <div class="p-5 bg-slate-950 rounded-full border-2 border-dashed border-emerald-500/50 shadow-[0_0_30px_rgba(16,185,129,0.1)] relative">
                            <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        </div>
                        <div class="text-center">
                            <p class="font-mono text-emerald-400 font-bold tracking-widest text-sm" id="scannerStatus">ESPERANDO CÓDIGO...</p>
                            <p class="text-[11px] text-slate-500 mt-1">Usa la pistola USB o escribe el SKU. El sistema suma unidades automáticamente sin duplicar filas.</p>
                        </div>

                        <!-- Ultimo scan / Input manual -->
                        <div class="w-full mt-2">
                            <input type="text" id="manual_barcode" class="block w-full bg-slate-950 border-slate-700 text-emerald-400 font-mono text-center text-lg rounded-lg focus:border-emerald-500 focus:ring-emerald-500 tracking-[0.15em] py-2" placeholder="SCAN o SKU + ENTER" autocomplete="off" autofocus>
                        </div>

                        <!-- Scan Notification Toast -->
                        <div id="scan-toast" class="w-full text-center text-xs py-2 px-3 rounded-lg hidden transition-all"></div>

                        <div class="w-full flex items-center justify-center gap-2 mt-2">
                            <div class="h-px bg-slate-800 flex-1"></div>
                            <span class="text-xs text-slate-600 font-bold">O</span>
                            <div class="h-px bg-slate-800 flex-1"></div>
                        </div>

                        <button type="button" id="startMobileScanner" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-bold flex items-center justify-center transition-colors border border-slate-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Usar Cámara del Móvil
                        </button>
                    </div>

                    <!-- Div for camera -->
                    <div id="reader" class="hidden w-full mt-4 rounded-lg overflow-hidden border-2 border-emerald-500/50"></div>

                </div>
            </div>

            <!-- Panel Derecho: Lista de Escaneados -->
            <div class="w-full lg:w-2/3 flex flex-col">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-lg shadow-black/50 flex flex-col h-auto min-h-[500px] lg:h-[calc(100vh-10rem)]">
                    <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-900/80 rounded-t-2xl">
                        <h3 class="text-sm font-bold tracking-widest text-slate-200 uppercase flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            3. Mercadería Escaneada (Consolidada)
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400">Total Unidades:</span>
                            <span class="text-xs font-mono font-bold text-emerald-400 bg-emerald-950/70 px-3 py-1 rounded-full border border-emerald-500/30" id="totalItems">0</span>
                        </div>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-slate-400">
                                <thead class="text-xs text-slate-400 uppercase bg-slate-950 sticky top-0 z-10 shadow-sm border-b border-slate-800">
                                    <tr>
                                        <th class="px-4 py-3">CÓDIGO (SKU)</th>
                                        <th class="px-4 py-3">PRODUCTO</th>
                                        <th class="px-4 py-3 text-center">CANTIDAD</th>
                                        <th class="px-4 py-3 text-right">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody id="scannedList" class="divide-y divide-slate-800/50">
                                    <!-- Filas Dinámicas de JS -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div id="emptyState" class="flex flex-col items-center justify-center py-16 text-slate-600 space-y-4">
                            <svg class="w-16 h-16 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <p class="font-mono text-sm tracking-widest uppercase">Escanea un producto para comenzar</p>
                            <p class="text-xs text-slate-500">Si escaneas el mismo producto repetidas veces, se sumará a la cantidad existente.</p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 border-t border-slate-800 bg-slate-950/70 rounded-b-2xl">
                        <button type="button" id="btnGuardarRecepcion" class="w-full py-4 text-sm font-bold text-slate-900 transition-all bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-[0_0_20px_rgba(16,185,129,0.3)] disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2" disabled>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span id="btnGuardarText">FINALIZAR Y CREAR RECEPCIÓN</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Sonidos de Confirmación y Error -->
    <audio id="beepOk" src="https://www.soundjay.com/buttons/sounds/button-09.mp3" preload="auto"></audio>
    <audio id="beepError" src="https://www.soundjay.com/buttons/sounds/button-10.mp3" preload="auto"></audio>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar de LocalStorage
            const storedSession = localStorage.getItem('scanner_session');
            let scannedItems = storedSession ? JSON.parse(storedSession) : [];
            
            const beepOk = document.getElementById('beepOk');
            const beepError = document.getElementById('beepError');
            const manualInput = document.getElementById('manual_barcode');
            const emptyState = document.getElementById('emptyState');
            const scannedList = document.getElementById('scannedList');
            const totalItemsEl = document.getElementById('totalItems');
            const btnGuardar = document.getElementById('btnGuardarRecepcion');
            const btnGuardarText = document.getElementById('btnGuardarText');
            const provSelect = document.getElementById('proveedor_id');
            const bodSelect = document.getElementById('bodega_id');
            const docInput = document.getElementById('documento_referencia');
            const facturaFeedback = document.getElementById('factura-feedback');
            const scanToast = document.getElementById('scan-toast');

            let isFacturaDuplicada = false;
            let isSearching = false;

            // Mantener focus en input manual para pistolas USB lectoras de código de barras
            document.addEventListener('click', (e) => {
                if(e.target.tagName !== 'INPUT' && e.target.tagName !== 'SELECT' && e.target.tagName !== 'BUTTON') {
                    manualInput.focus();
                }
            });

            // Capturar enter en input manual (pistola USB)
            manualInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const code = this.value.trim();
                    this.value = '';
                    if (code) {
                        processBarcode(code);
                    }
                }
            });

            /**
             * Procesa el código escaneado consultando el catálogo y aplicando la regla anti-duplicados
             */
            function processBarcode(barcode) {
                if(!barcode || isSearching) return;
                
                // Sanitizar caracteres básicos
                if(/[^a-zA-Z0-9\-\_\.\/]/.test(barcode)) {
                    showToast("Código con caracteres no permitidos: " + barcode, false);
                    playSound(false);
                    return;
                }

                // =========================================================================
                // REGLA ANTI-DUPLICADOS EN FRONTEND:
                // Si el código ya fue escaneado, incrementamos la cantidad en la fila existente
                // =========================================================================
                let existingItem = scannedItems.find(i => i.barcode === barcode || i.sku === barcode);
                if (existingItem) {
                    existingItem.qty++;
                    playSound(true);
                    showToast(`+1 "${existingItem.name}" (Total: ${existingItem.qty})`, true);
                    updateUI(existingItem.barcode);
                    return;
                }

                // Si no está en memoria, consultar a la base de datos
                isSearching = true;
                const provId = provSelect.value;
                const url = `{{ route('recepciones.scanner.buscar') }}?codigo=${encodeURIComponent(barcode)}&proveedor_id=${provId}`;

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        isSearching = false;
                        if(data.success && data.producto) {
                            const prod = data.producto;

                            // Verificar nuevamente si el SKU maestro ya existe en la lista
                            let existingByProdId = scannedItems.find(i => i.producto_id === prod.id);
                            if (existingByProdId) {
                                existingByProdId.qty++;
                                playSound(true);
                                showToast(`+1 "${existingByProdId.name}" (Total: ${existingByProdId.qty})`, true);
                                updateUI(existingByProdId.barcode);
                            } else {
                                scannedItems.unshift({
                                    producto_id: prod.id,
                                    barcode: barcode,
                                    sku: prod.sku,
                                    name: prod.nombre + (prod.formato ? ' (' + prod.formato + ')' : ''),
                                    precio: prod.precio_compra_ref || 0,
                                    qty: 1
                                });
                                playSound(true);
                                showToast(`Producto agregado: ${prod.nombre}`, true);
                                updateUI(barcode);
                            }
                        } else {
                            playSound(false);
                            showToast(data.message || `Código ${barcode} no encontrado en catálogo.`, false);
                        }
                    })
                    .catch(err => {
                        isSearching = false;
                        playSound(false);
                        showToast("Error de conexión al buscar producto.", false);
                    });
            }

            /**
             * Muestra notificación visual momentánea
             */
            function showToast(msg, isSuccess) {
                scanToast.textContent = msg;
                scanToast.className = `w-full text-center text-xs py-2 px-3 rounded-lg transition-all ${isSuccess ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40'}`;
                scanToast.classList.remove('hidden');
                setTimeout(() => {
                    scanToast.classList.add('hidden');
                }, 3000);
            }

            /**
             * Renderiza la tabla de escaneados y persiste en LocalStorage
             */
            function updateUI(highlightBarcode = null) {
                localStorage.setItem('scanner_session', JSON.stringify(scannedItems));

                if (scannedItems.length > 0) {
                    emptyState.classList.add('hidden');
                    btnGuardar.disabled = isFacturaDuplicada;
                } else {
                    emptyState.classList.remove('hidden');
                    btnGuardar.disabled = true;
                }

                scannedList.innerHTML = '';
                let total = 0;
                
                scannedItems.forEach((item, index) => {
                    total += item.qty;
                    const isHighlighted = (item.barcode === highlightBarcode || item.sku === highlightBarcode);
                    const tr = document.createElement('tr');
                    tr.className = `border-b border-slate-800 transition-colors ${isHighlighted ? 'bg-emerald-500/20' : 'bg-slate-900/50 hover:bg-slate-800/40'}`;
                    tr.innerHTML = `
                        <td class="px-4 py-3 font-mono text-emerald-400 font-bold">${item.sku || item.barcode}</td>
                        <td class="px-4 py-3 text-slate-200">
                            <div>${item.name}</div>
                            <div class="text-[10px] text-slate-500 font-mono">Scan: ${item.barcode}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button" class="text-slate-400 hover:text-white px-2.5 py-1 bg-slate-800 hover:bg-slate-700 rounded-lg text-base font-bold transition-colors" onclick="changeQty(${index}, -1)">-</button>
                                <span class="font-mono text-lg font-bold text-white w-10 text-center">${item.qty}</span>
                                <button type="button" class="text-slate-400 hover:text-white px-2.5 py-1 bg-slate-800 hover:bg-slate-700 rounded-lg text-base font-bold transition-colors" onclick="changeQty(${index}, 1)">+</button>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" class="p-1 text-rose-500 hover:text-rose-400 transition-colors" onclick="removeItem(${index})" title="Eliminar fila">
                                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </td>
                    `;
                    scannedList.appendChild(tr);
                });

                totalItemsEl.textContent = total;
            }

            window.changeQty = function(index, delta) {
                if (scannedItems[index].qty + delta > 0) {
                    scannedItems[index].qty += delta;
                    updateUI();
                } else if (scannedItems[index].qty + delta === 0) {
                    removeItem(index);
                }
            };

            window.removeItem = function(index) {
                scannedItems.splice(index, 1);
                updateUI();
            };

            function playSound(success) {
                if(success) {
                    beepOk.currentTime = 0;
                    beepOk.play().catch(e => {});
                    const pulseDiv = document.querySelector('.indicator-pulse');
                    if (pulseDiv) {
                        pulseDiv.classList.add('bg-emerald-500/20');
                        setTimeout(() => pulseDiv.classList.remove('bg-emerald-500/20'), 200);
                    }
                } else {
                    beepError.currentTime = 0;
                    beepError.play().catch(e => {});
                }
            }

            // =========================================================================
            // VALIDACIÓN EN TIEMPO REAL: FACTURA DUPLICADA
            // =========================================================================
            function verificarFacturaDuplicada() {
                const provId = provSelect.value;
                const factura = docInput.value.trim();

                if (!provId || !factura || factura.toUpperCase() === 'S/N') {
                    isFacturaDuplicada = false;
                    facturaFeedback.classList.add('hidden');
                    btnGuardar.disabled = (scannedItems.length === 0);
                    return;
                }

                fetch(`{{ route('recepciones.check-factura') }}?proveedor_id=${provId}&numero_factura=${encodeURIComponent(factura)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.exists) {
                            isFacturaDuplicada = true;
                            facturaFeedback.textContent = '⚠️ ' + data.message;
                            facturaFeedback.className = 'mt-1 text-xs text-rose-400 font-semibold block';
                            btnGuardar.disabled = true;
                            playSound(false);
                        } else {
                            isFacturaDuplicada = false;
                            facturaFeedback.textContent = '✓ Factura disponible (no duplicada)';
                            facturaFeedback.className = 'mt-1 text-xs text-emerald-400 block';
                            btnGuardar.disabled = (scannedItems.length === 0);
                        }
                    })
                    .catch(() => {
                        isFacturaDuplicada = false;
                    });
            }

            docInput.addEventListener('blur', verificarFacturaDuplicada);
            docInput.addEventListener('input', () => {
                // Si el usuario cambia el texto, limpiar advertencia previa hasta el próximo blur o debounce
                if (isFacturaDuplicada) {
                    isFacturaDuplicada = false;
                    btnGuardar.disabled = (scannedItems.length === 0);
                    facturaFeedback.classList.add('hidden');
                }
            });
            provSelect.addEventListener('change', verificarFacturaDuplicada);

            // --- HTML5 QR CODE SCANNER (CÁMARA MÓVIL) ---
            const btnMobile = document.getElementById('startMobileScanner');
            const readerDiv = document.getElementById('reader');
            let html5QrcodeScanner = null;

            btnMobile.addEventListener('click', () => {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                    readerDiv.classList.add('hidden');
                    btnMobile.innerHTML = `
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Usar Cámara del Móvil
                    `;
                } else {
                    readerDiv.classList.remove('hidden');
                    btnMobile.innerHTML = 'Detener Cámara';
                    
                    html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: {width: 250, height: 150} }, false);
                    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                }
            });

            function onScanSuccess(decodedText) {
                processBarcode(decodedText);
            }
            function onScanFailure() {}

            // =========================================================================
            // GUARDAR RECEPCIÓN EN BACKEND
            // =========================================================================
            btnGuardar.addEventListener('click', function() {
                const prov = provSelect.value;
                const bod = bodSelect.value;
                const docRef = docInput.value.trim();

                if(!prov || !bod) {
                    alert("Debe seleccionar Proveedor y Bodega antes de finalizar la recepción.");
                    return;
                }

                if (isFacturaDuplicada) {
                    alert("No se puede guardar: La factura ingresada ya existe en el sistema.");
                    return;
                }

                if (scannedItems.length === 0) {
                    alert("No hay productos escaneados.");
                    return;
                }

                // Guardar config en LocalStorage
                localStorage.setItem('scanner_config', JSON.stringify({prov, bod}));
                
                if (!navigator.onLine) {
                    alert("Sin conexión a internet. Los datos quedaron guardados en tu celular. Presiona 'Sincronizar' cuando vuelva la señal.");
                    return;
                }
                
                enviarAlServidor();
            });

            function enviarAlServidor() {
                btnGuardar.disabled = true;
                btnGuardarText.textContent = "GUARDANDO RECEPCIÓN...";

                const payload = {
                    _token: '{{ csrf_token() }}',
                    proveedor_id: provSelect.value,
                    bodega_id: bodSelect.value,
                    documento_referencia: docInput.value.trim(),
                    items: scannedItems.map(item => ({
                        catalogo_producto_id: item.producto_id,
                        cantidad: item.qty,
                        precio_unitario: item.precio || 0,
                    }))
                };

                fetch('{{ route('recepciones.scanner.guardar') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        localStorage.removeItem('scanner_session');
                        localStorage.removeItem('scanner_config');
                        window.location.href = data.redirect_url;
                    } else {
                        btnGuardar.disabled = false;
                        btnGuardarText.textContent = "FINALIZAR Y CREAR RECEPCIÓN";
                        playSound(false);
                        alert(data.message || "Error al procesar la recepción.");
                    }
                })
                .catch(err => {
                    btnGuardar.disabled = false;
                    btnGuardarText.textContent = "FINALIZAR Y CREAR RECEPCIÓN";
                    playSound(false);
                    alert("Error de conexión con el servidor. Intente de nuevo.");
                });
            }

            // Manejo de Estado de Red
            const offlineBanner = document.getElementById('offline-banner');
            const onlineSyncBanner = document.getElementById('online-sync-banner');
            const btnSync = document.getElementById('btnSync');

            function updateOnlineStatus() {
                if (!navigator.onLine) {
                    offlineBanner.classList.remove('hidden');
                    onlineSyncBanner.classList.add('hidden');
                } else {
                    offlineBanner.classList.add('hidden');
                    if (scannedItems.length > 0) {
                        onlineSyncBanner.classList.remove('hidden');
                    } else {
                        onlineSyncBanner.classList.add('hidden');
                    }
                }
            }

            window.addEventListener('online', updateOnlineStatus);
            window.addEventListener('offline', updateOnlineStatus);
            updateOnlineStatus();

            // Restaurar selects si existían
            const storedConfig = localStorage.getItem('scanner_config');
            if (storedConfig) {
                const conf = JSON.parse(storedConfig);
                if (conf.prov) provSelect.value = conf.prov;
                if (conf.bod) bodSelect.value = conf.bod;
            }

            // Render inicial si había sesión
            if (scannedItems.length > 0) {
                updateUI();
            }

            btnSync.addEventListener('click', function() {
                enviarAlServidor();
            });
        });
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</x-app-layout>
