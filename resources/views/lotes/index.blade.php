{{-- 
========================================================================================
VISTA: CONSOLA GLOBAL DE LOTES DISPONIBLES Y RADAR FEFO
RUTA: /lotes
CONTROLADOR: LoteStockController@index
PROPÓSITO:
- Monitorear de forma transversal todas las existencias por lotes en el ERP a través de todas las bodegas.
- Filtros reactivos con búsqueda en vivo (AJAX) sin refrescar la página:
  * Texto: Búsqueda por Nombre o SKU.
  * Selector de Bodega física o vitrina.
  * Selector de Proveedor despachador.
  * Selector de Estado de Vencimiento / Ventana de Riesgo FEFO:
    - Vencido (<0 días)
    - Crítico (0 a 6 días)
    - Alerta (7 a 14 días)
    - Preventivo (15 a 21 días)
    - Atención (22 a 30 días)
- Modal para editar fechas de lote en caliente con calculadora por meses de vida útil.
- Modales para traslados entre bodegas y ajuste masivo.
========================================================================================
--}}

<x-app-layout>
    {{-- Slot del encabezado: Título principal del módulo --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Lotes Disponibles (Stock)') }}
        </h2>
    </x-slot>

    {{-- Contenedor del contenido principal --}}
    <div class="py-6">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            
            {{-- Notificación Flash de estado del sistema --}}
            @if (session('status'))
                <div class="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('status') }}
                </div>
            @endif

            {{-- Alerta de errores de validación devueltos por el servidor --}}
            @if ($errors->any())
                <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 px-4 py-3 rounded-xl">
                    <div class="flex items-center gap-3 mb-2 font-bold">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Hubo problemas con tu solicitud:
                    </div>
                    <ul class="list-disc list-inside text-sm pl-2 space-y-1 text-rose-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            {{-- =========================================================================
                 BARRA DE FILTROS REACTIVOS CON BUSCADOR ASÍNCRONO
                 ========================================================================= --}}
            <div class="p-6 mb-6 bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60">
                <form id="filter-form" onsubmit="event.preventDefault(); fetchLotes();" class="flex flex-col md:flex-row md:items-end gap-5">
                    
                    {{-- Buscador por texto: SKU o nombre del producto --}}
                    <div class="flex-1 relative">
                        <x-input-label for="search" :value="__('Buscar Producto (Nombre o SKU)')" class="text-slate-300 font-medium mb-1 block" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input id="search" name="search" type="text" class="block w-full pl-10 bg-slate-900 border-slate-700 text-slate-200 placeholder-slate-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm" value="{{ request('search') }}" placeholder="Ej: Arroz, Bebida, CCU..." autocomplete="off" />
                        </div>
                    </div>
                    
                    {{-- Filtro de Bodega receptora o exhibidor --}}
                    <div class="flex-1">
                        <x-input-label for="bodega_id" :value="__('Bodega')" class="text-slate-300 font-medium mb-1 block" />
                        <select id="bodega_id" name="bodega_id" class="block w-full bg-slate-900 border-slate-700 text-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todas las Bodegas</option>
                            @foreach($bodegas as $bodega)
                                <option value="{{ $bodega->id }}" {{ request('bodega_id') == $bodega->id ? 'selected' : '' }}>
                                    {{ $bodega->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro de Proveedor de origen --}}
                    <div class="flex-1">
                        <x-input-label for="proveedor_id" :value="__('Proveedor')" class="text-slate-300 font-medium mb-1 block" />
                        <select id="proveedor_id" name="proveedor_id" class="block w-full bg-slate-900 border-slate-700 text-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos los Proveedores</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" {{ request('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                    {{ $proveedor->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro por Nivel de Riesgo y Alerta de Vencimiento FEFO --}}
                    <div class="flex-1">
                        <x-input-label for="alerta" :value="__('Estado Vencimiento')" class="text-slate-300 font-medium mb-1 block" />
                        <select id="alerta" name="alerta" class="block w-full bg-slate-900 border-slate-700 text-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos los Estados</option>
                            <option value="vencido" {{ request('alerta') == 'vencido' ? 'selected' : '' }}>🚨 Vencido (&lt; 0 días)</option>
                            <option value="critico" {{ request('alerta') == 'critico' ? 'selected' : '' }}>⚡ Crítico (&lt; 7 días)</option>
                            <option value="1mes" {{ in_array(request('alerta'), ['1mes', 'bajo']) ? 'selected' : '' }}>⚠️ Hasta 1 Mes (30 días)</option>
                            <option value="2meses" {{ request('alerta') == '2meses' ? 'selected' : '' }}>📅 Hasta 2 Meses (60 días)</option>
                            <option value="3meses" {{ request('alerta') == '3meses' ? 'selected' : '' }}>📆 Hasta 3 Meses (90 días)</option>
                            <option value="6meses" {{ request('alerta') == '6meses' ? 'selected' : '' }}>📦 Hasta 6 Meses (180 días)</option>
                            <option value="12meses" {{ request('alerta') == '12meses' ? 'selected' : '' }}>🎯 Hasta 12 Meses (365 días)</option>
                            <option value="optimo" {{ in_array(request('alerta'), ['optimo', 'mas12meses']) ? 'selected' : '' }}>🛡️ +12 Meses (Óptimos)</option>
                        </select>
                    </div>
                    
                    {{-- Botón para resetear filtros a valores predeterminados --}}
                    <div>
                        <button type="button" onclick="resetFilters()" class="w-full md:w-auto px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-lg border border-slate-700 shadow-sm transition-colors duration-200">
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            {{-- =========================================================================
                 CONTENEDOR DE LA TABLA DE LOTES CON CARGA ASÍNCRONA
                 ========================================================================= --}}
            <div class="bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative">
                {{-- Spinner de carga activo durante las llamadas fetch --}}
                <div id="table-loader" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm z-10 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
                    <div class="flex flex-col items-center">
                        <svg class="animate-spin h-10 w-10 text-indigo-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <p class="text-slate-300 font-medium">Actualizando datos...</p>
                    </div>
                </div>

                <div class="p-0 text-slate-300">
                    {{-- Contenedor donde se inserta el HTML de lotes.partials.table --}}
                    <div class="overflow-x-auto">
                        <div id="table-container">
                            @include('lotes.partials.table', ['productos' => $productos])
                        </div>
                    </div>
                    
                    {{-- Paginación sincronizada por AJAX --}}
                    <div id="pagination-container" class="px-6 py-4 border-t border-slate-800/50 bg-slate-900/30">
                        {{ $productos->links() }}
                    </div>
                </div>
            </div>

            {{-- =========================================================================
                 MODAL: EDICIÓN DE FECHAS DE ELABORACIÓN Y VENCIMIENTO DEL LOTE
                 ========================================================================= --}}
            <div x-data="{
                open: false,
                loteId: null,
                loteFElab: '',
                loteFVenc: '',
                mesesVidaUtil: '',
                actionUrl: '',
                abrirModal(id, elab, venc, url, vidaUtil = '') {
                    this.loteId = id;
                    // Convierte fechas del formato local DD/MM/AAAA a formato HTML5 AAAA-MM-DD
                    const parseDate = (d) => {
                        if(!d || d === 'N/A' || d === 'S/F') return '';
                        let parts = d.split('/');
                        if (parts.length === 3) return `${parts[2]}-${parts[1]}-${parts[0]}`;
                        return '';
                    };
                    this.loteFElab = parseDate(elab);
                    this.loteFVenc = parseDate(venc);
                    this.mesesVidaUtil = vidaUtil;
                    this.actionUrl = url;
                    this.open = true;
                    
                    // Si tiene fecha de elaboración y meses de vida útil definidos pero sin vencimiento, calcula automáticamente
                    if (this.loteFElab && this.mesesVidaUtil && !this.loteFVenc) {
                        this.calcularVencimiento();
                    }
                },
                // Calcula el vencimiento sumando la vida útil a la fecha de elaboración
                calcularVencimiento() {
                    if (this.loteFElab && this.mesesVidaUtil) {
                        let fecha = new Date(this.loteFElab + 'T12:00:00');
                        fecha.setMonth(fecha.getMonth() + parseInt(this.mesesVidaUtil));
                        this.loteFVenc = fecha.toISOString().split('T')[0];
                    }
                }
            }" 
            @open-fechas-modal.window="abrirModal($event.detail.id, $event.detail.elab, $event.detail.venc, $event.detail.url, $event.detail.vida_util)">
                <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    {{-- Capa de fondo oscurecida --}}
                    <div x-show="open"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" 
                        aria-hidden="true"></div>

                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        {{-- Panel central del modal --}}
                        <div x-show="open" @click.outside="open = false"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="relative inline-block align-bottom bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl shadow-black/50 transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border border-slate-700">
                            
                            {{-- Formulario para actualizar fechas del lote con método PUT --}}
                            <form :action="actionUrl" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="px-6 py-5 border-b border-slate-800 bg-slate-900/50">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2" id="modal-title">
                                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Editar Fechas Lote #<span x-text="loteId"></span>
                                        </h3>
                                        <button type="button" @click="open = false" class="text-slate-400 hover:text-white focus:outline-none">
                                            <span class="sr-only">Cerrar</span>
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="p-6 space-y-4">
                                    <p class="text-sm text-slate-400">Puedes ingresar la fecha de vencimiento manualmente, o calcularla en base a los meses de vida útil del producto (Consumir antes de).</p>
                                    
                                    {{-- Input: Fecha de elaboración --}}
                                    <div>
                                        <label for="fecha_elaboracion" class="block text-sm font-medium text-slate-300 mb-1">Fecha de Elaboración</label>
                                        <input type="date" id="fecha_elaboracion" name="fecha_elaboracion" x-model="loteFElab" @change="calcularVencimiento" class="block w-full bg-slate-800 border-slate-700 text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                                    </div>
                                    
                                    {{-- Calculadora: Vida útil en meses --}}
                                    <div class="p-3 bg-indigo-900/20 border border-indigo-500/20 rounded-lg">
                                        <label for="meses_vida_util" class="block text-xs font-medium text-indigo-300 mb-1">Calculadora: Vida Útil (Meses)</label>
                                        <div class="flex gap-2">
                                            <input type="number" id="meses_vida_util" x-model="mesesVidaUtil" @input="calcularVencimiento" min="1" step="1" class="block w-full bg-slate-800 border-slate-700 text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm" placeholder="Ej. 6" />
                                        </div>
                                        <p class="text-[10px] text-slate-400 mt-1">Si el envase dice "Consumir antes de 6 meses", ingresa 6 aquí y se calculará automáticamente.</p>
                                    </div>

                                    {{-- Input: Fecha de vencimiento definitiva --}}
                                    <div>
                                        <label for="fecha_vencimiento" class="block text-sm font-medium text-slate-300 mb-1">Fecha de Vencimiento Final <span class="text-red-400">*</span></label>
                                        <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" x-model="loteFVenc" required class="block w-full bg-slate-800 border-slate-700 text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                                    </div>
                                </div>
                                
                                {{-- Botonera de pie de modal --}}
                                <div class="px-6 py-4 bg-slate-900/80 border-t border-slate-800 flex items-center justify-end gap-3">
                                    <button type="button" @click="open = false" class="px-4 py-2 bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg font-medium transition-colors border border-slate-700">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-500 rounded-lg font-bold shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2">
                                        Guardar Cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

    {{-- =========================================================================
         SCRIPT: MANEJO DE PETICIONES AJAX REACTIVAS CON DEBOUNCE
         ========================================================================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('search');
            const bodegaSelect = document.getElementById('bodega_id');
            const proveedorSelect = document.getElementById('proveedor_id');
            const alertaSelect = document.getElementById('alerta');
            const tableBody = document.getElementById('table-body');
            const tableLoader = document.getElementById('table-loader');
            const paginationContainer = document.getElementById('pagination-container');
            
            let debounceTimer;

            /**
             * Consulta asíncrona hacia /lotes para refrescar el contenido sin recargar la página.
             */
            const fetchLotes = () => {
                tableLoader.classList.remove('opacity-0', 'pointer-events-none');
                tableLoader.classList.add('opacity-100');
                
                const params = new URLSearchParams();
                if (searchInput.value) params.append('search', searchInput.value);
                if (bodegaSelect.value) params.append('bodega_id', bodegaSelect.value);
                if (proveedorSelect.value) params.append('proveedor_id', proveedorSelect.value);
                if (alertaSelect.value) params.append('alerta', alertaSelect.value);

                const url = `{{ route('lotes.index', [], false) }}?${params.toString()}`;
                
                // Actualiza la barra del navegador sin refrescar
                window.history.pushState({}, '', url);

                const tableContainer = document.getElementById('table-container');

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    // Reemplaza todo el contenido del contenedor con el nuevo HTML recibido
                    tableContainer.innerHTML = html;
                    
                    // Extrae los links de paginación del fragmento HTML
                    const tempDiv = document.createElement('table');
                    tempDiv.innerHTML = html;
                    const linksRow = tempDiv.querySelector('#pagination-links td');
                    if (linksRow && linksRow.innerHTML.trim() !== '') {
                        paginationContainer.innerHTML = linksRow.innerHTML;
                        paginationContainer.classList.remove('hidden');
                    } else {
                        paginationContainer.innerHTML = '';
                        paginationContainer.classList.add('hidden');
                    }
                })
                .finally(() => {
                    tableLoader.classList.remove('opacity-100');
                    tableLoader.classList.add('opacity-0', 'pointer-events-none');
                });
            };

            // Event Listeners con Debounce de 300ms para el campo de búsqueda de texto
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchLotes, 300);
            });

            bodegaSelect.addEventListener('change', fetchLotes);
            proveedorSelect.addEventListener('change', fetchLotes);
            alertaSelect.addEventListener('change', fetchLotes);
            
            // Intercepta clicks en los botones de paginación para navegar asíncronamente
            paginationContainer.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (link) {
                    e.preventDefault();
                    const url = new URL(link.href);
                    
                    tableLoader.classList.remove('opacity-0', 'pointer-events-none');
                    tableLoader.classList.add('opacity-100');
                    window.history.pushState({}, '', url);
                    
                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                        
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;
                        const linksRow = tempDiv.querySelector('#pagination-links');
                        if (linksRow) paginationContainer.innerHTML = linksRow.innerHTML;
                    })
                    .finally(() => {
                        tableLoader.classList.remove('opacity-100');
                        tableLoader.classList.add('opacity-0', 'pointer-events-none');
                    });
                }
            });
            
            // Función Reset global para limpiar todos los controles de filtro
            window.resetFilters = () => {
                searchInput.value = '';
                bodegaSelect.value = '';
                proveedorSelect.value = '';
                alertaSelect.value = '';
                fetchLotes();
            };
        });
    </script>
    
    {{-- Componentes modales reutilizables: Ajuste masivo y Traslado de lotes --}}
    <x-ajuste-masivo-modal :bodegas="$bodegas" />
    <x-transfer-lote-modal :bodegas="$bodegas" />
</x-app-layout>
