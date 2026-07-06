<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold leading-tight text-emerald-400 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                {{ __('Modo Escáner: Ingreso Rápido') }}
            </h2>
            <a href="{{ route('recepciones.index') }}" class="px-4 py-2 text-sm font-bold text-slate-300 transition-all bg-slate-800 hover:bg-slate-700 rounded-xl border border-slate-700">
                Salir del Modo Escáner
            </a>
        </div>
    </x-slot>

    <!-- Html5Qrcode Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div class="py-4 bg-slate-950 min-h-screen font-sans">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 h-full flex flex-col lg:flex-row gap-6">
            
            <!-- Panel Izquierdo: Configuración y Scanner -->
            <div class="w-full lg:w-1/3 flex flex-col gap-6">
                
                <!-- Setup Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg shadow-black/50">
                    <h3 class="text-sm font-bold tracking-widest text-slate-400 uppercase mb-4">1. Configuración de Entrada</h3>
                    <form id="setupForm">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Proveedor Origen</label>
                                <select id="proveedor_id" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-emerald-500 focus:ring-emerald-500" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Bodega Destino</label>
                                <select id="bodega_id" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-emerald-500 focus:ring-emerald-500" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($bodegas as $bodega)
                                        <option value="{{ $bodega->id }}">[{{ $bodega->codigo }}] {{ $bodega->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Factura / Guía (Opcional)</label>
                                <input type="text" id="documento_referencia" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-emerald-500 focus:ring-emerald-500 uppercase font-mono" placeholder="Nº Documento">
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Scanner Active Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg shadow-black/50 flex-1 flex flex-col relative overflow-hidden group">
                    <div class="absolute inset-0 bg-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none indicator-pulse"></div>
                    
                    <h3 class="text-sm font-bold tracking-widest text-slate-400 uppercase mb-4 relative z-10">2. Captura de Código</h3>
                    
                    <div class="flex-1 flex flex-col items-center justify-center space-y-4 relative z-10">
                        <div class="p-6 bg-slate-950 rounded-full border-2 border-dashed border-emerald-500/50 shadow-[0_0_30px_rgba(16,185,129,0.1)] relative">
                            <svg class="w-12 h-12 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        </div>
                        <div class="text-center">
                            <p class="font-mono text-emerald-400 font-bold tracking-widest" id="scannerStatus">ESPERANDO CÓDIGO...</p>
                            <p class="text-[10px] text-slate-500 mt-1">Usa la pistola USB. El sistema está escuchando automáticamente.</p>
                        </div>

                        <!-- Ultimo scan -->
                        <div class="w-full mt-4">
                            <input type="text" id="manual_barcode" class="block w-full bg-slate-950 border-slate-700 text-emerald-400 font-mono text-center text-xl rounded-lg focus:border-emerald-500 focus:ring-emerald-500 tracking-[0.2em]" placeholder="SCAN" autocomplete="off" autofocus>
                        </div>

                        <div class="w-full flex items-center justify-center gap-2 mt-4">
                            <div class="h-px bg-slate-800 flex-1"></div>
                            <span class="text-xs text-slate-600 font-bold">O</span>
                            <div class="h-px bg-slate-800 flex-1"></div>
                        </div>

                        <button type="button" id="startMobileScanner" class="w-full py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-bold flex items-center justify-center transition-colors">
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
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-lg shadow-black/50 flex flex-col h-[calc(100vh-10rem)]">
                    <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-900/80 rounded-t-2xl">
                        <h3 class="text-sm font-bold tracking-widest text-slate-200 uppercase">3. Lista de Mercadería Escaneada</h3>
                        <div class="text-xs font-mono text-emerald-400 bg-emerald-950/50 px-3 py-1 rounded-full border border-emerald-500/30">
                            Total Items: <span id="totalItems">0</span>
                        </div>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                        <table class="w-full text-sm text-left text-slate-400">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-950 sticky top-0 z-10 shadow-sm border-b border-slate-800">
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
                        
                        <div id="emptyState" class="flex flex-col items-center justify-center h-full text-slate-600 space-y-4">
                            <svg class="w-16 h-16 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <p class="font-mono text-sm tracking-widest uppercase">Escanea un producto para comenzar</p>
                        </div>
                    </div>

                    <div class="p-6 border-t border-slate-800 bg-slate-950/50 rounded-b-2xl">
                        <button type="button" id="btnGuardarRecepcion" class="w-full py-4 text-sm font-bold text-slate-900 transition-all bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-[0_0_15px_rgba(16,185,129,0.4)] disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            FINALIZAR Y CREAR RECEPCIÓN
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Sonidos -->
    <audio id="beepOk" src="https://www.soundjay.com/buttons/sounds/button-09.mp3" preload="auto"></audio>
    <audio id="beepError" src="https://www.soundjay.com/buttons/sounds/button-10.mp3" preload="auto"></audio>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let scannedItems = [];
            const beepOk = document.getElementById('beepOk');
            const beepError = document.getElementById('beepError');
            const manualInput = document.getElementById('manual_barcode');
            const emptyState = document.getElementById('emptyState');
            const scannedList = document.getElementById('scannedList');
            const totalItemsEl = document.getElementById('totalItems');
            const btnGuardar = document.getElementById('btnGuardarRecepcion');
            
            // Focus global para USB Scanner
            document.addEventListener('click', () => {
                if(document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'SELECT') {
                    manualInput.focus();
                }
            });

            // Capturar enter en input manual (simulando pistola)
            manualInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    processBarcode(this.value.trim());
                    this.value = '';
                }
            });

            function processBarcode(barcode) {
                if(!barcode) return;
                
                // Validar seguridad XSS basico
                if(/[^a-zA-Z0-9\-\_]/.test(barcode)) {
                    showError("Código inválido. Caracteres no permitidos.");
                    return;
                }

                // Aquí idealmente haríamos un fetch a la API para buscar el producto por SKU o Mapeo
                // Como es una demostración, agregaremos el producto simulado a la lista
                
                let existingItem = scannedItems.find(i => i.barcode === barcode);
                if (existingItem) {
                    existingItem.qty++;
                } else {
                    scannedItems.push({
                        barcode: barcode,
                        name: 'Producto Simulado (' + barcode + ')', // Simulado
                        qty: 1
                    });
                }
                
                playSound(true);
                updateUI();
            }

            function updateUI() {
                if (scannedItems.length > 0) {
                    emptyState.classList.add('hidden');
                    btnGuardar.disabled = false;
                } else {
                    emptyState.classList.remove('hidden');
                    btnGuardar.disabled = true;
                }

                scannedList.innerHTML = '';
                let total = 0;
                
                scannedItems.forEach((item, index) => {
                    total += item.qty;
                    const tr = document.createElement('tr');
                    tr.className = 'border-b border-slate-800 bg-slate-900/50 group';
                    tr.innerHTML = `
                        <td class="px-4 py-3 font-mono text-emerald-400 font-bold">${item.barcode}</td>
                        <td class="px-4 py-3 text-slate-200">${item.name}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button" class="text-slate-500 hover:text-white px-2 py-1 bg-slate-800 rounded" onclick="changeQty(${index}, -1)">-</button>
                                <span class="font-mono text-lg font-bold text-white w-8">${item.qty}</span>
                                <button type="button" class="text-slate-500 hover:text-white px-2 py-1 bg-slate-800 rounded" onclick="changeQty(${index}, 1)">+</button>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" class="text-red-500 hover:text-red-400" onclick="removeItem(${index})">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </td>
                    `;
                    scannedList.prepend(tr); // Añadir al principio
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
                    beepOk.play().catch(e => console.log('Audio error:', e));
                    // Visual feedback
                    const inputDiv = document.querySelector('.indicator-pulse');
                    inputDiv.classList.add('bg-emerald-500/20');
                    setTimeout(() => inputDiv.classList.remove('bg-emerald-500/20'), 200);
                } else {
                    beepError.currentTime = 0;
                    beepError.play().catch(e => console.log('Audio error:', e));
                }
            }

            function showError(msg) {
                playSound(false);
                alert(msg);
            }

            // --- HTML5 QR CODE SCANNER (CÁMARA MÓVIL) ---
            const btnMobile = document.getElementById('startMobileScanner');
            const readerDiv = document.getElementById('reader');
            let html5QrcodeScanner = null;

            btnMobile.addEventListener('click', () => {
                if (html5QrcodeScanner) {
                    // Stop
                    html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                    readerDiv.classList.add('hidden');
                    btnMobile.innerHTML = 'Usar Cámara del Móvil';
                } else {
                    // Start
                    readerDiv.classList.remove('hidden');
                    btnMobile.innerHTML = 'Detener Cámara';
                    
                    html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: {width: 250, height: 150} }, false);
                    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                }
            });

            function onScanSuccess(decodedText, decodedResult) {
                processBarcode(decodedText);
            }
            function onScanFailure(error) {
                // handle scan failure, usually better to ignore and keep scanning
            }

            // -- Guardar Recepción (Mockup form submit)
            btnGuardar.addEventListener('click', function() {
                const prov = document.getElementById('proveedor_id').value;
                const bod = document.getElementById('bodega_id').value;
                if(!prov || !bod) {
                    alert("Debe seleccionar Proveedor y Bodega antes de guardar.");
                    return;
                }
                
                alert("Simulando guardado de " + scannedItems.length + " SKUs. En un entorno real esto enviaría un POST al servidor.");
                window.location.href = "{{ route('recepciones.index') }}";
            });
        });
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</x-app-layout>
