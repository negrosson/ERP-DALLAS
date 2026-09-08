<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Catálogo Maestro de Productos') }}
            </h2>
            <a href="{{ route('catalogo.create') }}" class="px-5 py-2.5 text-sm font-semibold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-lg shadow-indigo-600/30">
                + Nuevo Producto
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                    <span class="font-medium">Éxito!</span> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                    <span class="font-medium">Error!</span> {{ session('error') }}
                </div>
            @endif

            <!-- Filtros -->
            <div class="p-6 mb-6 bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60">
                <form id="filter-form" action="{{ route('catalogo.index') }}" method="GET" class="flex flex-col md:flex-row md:items-end gap-4 w-full">
                    <div class="flex-1 min-w-[200px]">
                        <label for="search" class="block text-sm font-medium text-slate-400 mb-1">Buscar Producto o SKU</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" class="bg-slate-800 border border-slate-700 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5 placeholder-slate-500" placeholder="Ej. Cachantun, CCU-001...">
                        </div>
                    </div>
                    
                    <div class="w-full md:w-40">
                        <label for="bodega_id" class="block text-sm font-medium text-slate-400 mb-1">Bodega FÃ­sica</label>
                        <select name="bodega_id" id="bodega_id" class="bg-slate-800 border border-slate-700 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                            <option value="">Todas</option>
                            @foreach($bodegas as $bodega)
                                <option value="{{ $bodega->id }}" {{ request('bodega_id') == $bodega->id ? 'selected' : '' }}>
                                    {{ $bodega->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="w-full md:w-32">
                        <label for="estado" class="block text-sm font-medium text-slate-400 mb-1">Estado</label>
                        <select name="estado" id="estado" class="bg-slate-800 border border-slate-700 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                            <option value="">Todos</option>
                            <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Activos</option>
                            <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Inactivos</option>
                        </select>
                    </div>

                    <div class="w-full md:w-48">
                        <label for="vencimiento" class="block text-sm font-medium text-slate-400 mb-1">Control Vencimiento</label>
                        <select name="vencimiento" id="vencimiento" class="bg-slate-800 border border-slate-700 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                            <option value="">Todos</option>
                            <option value="vencidos" {{ request('vencimiento') === 'vencidos' ? 'selected' : '' }}>🔴 Vencidos</option>
                            <option value="7_dias" {{ request('vencimiento') === '7_dias' ? 'selected' : '' }}>🟠 Vencen en 7 días</option>
                            <option value="14_dias" {{ request('vencimiento') === '14_dias' ? 'selected' : '' }}>🟡 Vencen en 14 días</option>
                            <option value="20_dias" {{ request('vencimiento') === '20_dias' ? 'selected' : '' }}>🟡 Vencen en 20 días</option>
                            <option value="30_dias" {{ request('vencimiento') === '30_dias' ? 'selected' : '' }}>🟢 Vencen en 30 días</option>
                            <option value="buen_estado" {{ request('vencimiento') === 'buen_estado' ? 'selected' : '' }}>🟢 Buen Estado (+30d)</option>
                        </select>
                    </div>

                    <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                        @if(request()->hasAny(['search', 'bodega_id', 'marca', 'estado', 'vencimiento']))
                            <a href="{{ route('catalogo.index') }}" class="flex-1 md:flex-none px-5 py-2.5 text-sm font-medium text-slate-300 bg-slate-700 hover:bg-slate-600 rounded-lg transition-colors border border-slate-600 text-center">
                                Limpiar Filtros
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative">
                <div class="p-0 text-slate-300">
                    
                    <div class="overflow-x-auto">
                        <div id="table-container">
                            @include('catalogo.partials.table', ['productos' => $productos])
                        </div>
                    </div>
                    
                    <div id="pagination-container" class="px-6 py-4 border-t border-slate-800/50 bg-slate-900/30">
                        {{ $productos->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div x-data="{ 
        open: false,
        productoId: null,
        productoNombre: '',
        lotes: [],
        cargando: false,
        error: '',
        abrirModal(e) {
            this.open = true;
            this.productoId = e.detail.id;
            this.productoNombre = e.detail.nombre;
            this.cargarLotes();
        },
        cargarLotes() {
            this.cargando = true;
            this.error = '';
            this.lotes = [];
            fetch(`/catalogo/${this.productoId}/lotes`)
                .then(res => res.json())
                .then(data => {
                    this.lotes = data.map(l => ({...l, nuevaCantidad: l.cantidad_disponible, motivo: '', enviando: false}));
                    this.cargando = false;
                })
                .catch(err => {
                    this.error = 'Error al cargar los lotes.';
                    this.cargando = false;
                });
        },
        ajustarStock(lote) {
            if(lote.nuevaCantidad === '' || lote.nuevaCantidad == lote.cantidad_disponible) return;
            if(!lote.motivo) { alert('Debe ingresar un motivo para el ajuste.'); return; }
            
            lote.enviando = true;
            
            fetch(`/bodegas/ajustar/${lote.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    cantidad_nueva: lote.nuevaCantidad,
                    motivo: lote.motivo
                })
            }).then(res => {
                if(res.ok) {
                    this.cargarLotes();
                    if(typeof window.fetchCatalogo === 'function') window.fetchCatalogo();
                    else window.location.reload();
                } else {
                    alert('Error al guardar el ajuste.');
                    lote.enviando = false;
                }
            }).catch(err => {
                alert('Error de conexión.');
                lote.enviando = false;
            });
        }
    }" 
    @open-ajuste-modal.window="abrirModal($event)"
    x-show="open" 
    style="display: none;" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
        
        <div @click.outside="open = false" x-show="open" x-transition class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Header del Modal -->
            <div class="px-6 py-4 border-b border-slate-700 flex items-center gap-4 bg-slate-800/50">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500/10 border border-amber-500/20">
                    <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white" id="modal-title">Ajuste Rápido de Stock</h3>
                    <p class="text-sm text-slate-400 font-medium" x-text="productoNombre"></p>
                </div>
                <button @click="open = false" class="ml-auto text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg p-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Cuerpo Scrollable -->
            <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                <template x-if="cargando">
                    <div class="flex items-center justify-center py-8">
                        <svg class="animate-spin -ml-1 mr-3 h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span class="text-slate-400 font-medium">Cargando lotes...</span>
                    </div>
                </template>
                <template x-if="error">
                    <div class="text-center py-6 text-rose-400 font-medium bg-rose-500/10 rounded-lg border border-rose-500/20" x-text="error"></div>
                </template>
                <template x-if="!cargando && lotes.length === 0 && !error">
                    <div class="text-center py-10 text-slate-400 font-medium bg-slate-800/30 rounded-lg border border-slate-700 border-dashed">
                        Este producto no tiene ningún lote registrado en el sistema.
                    </div>
                </template>
                
                <div class="space-y-4">
                    <template x-for="lote in lotes" :key="lote.id">
                        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4 transition-all" :class="{'opacity-50 pointer-events-none': lote.enviando, 'ring-1 ring-amber-500/50 bg-slate-800': lote.nuevaCantidad !== '' && lote.nuevaCantidad != lote.cantidad_disponible}">
                            
                            <!-- Cabecera del Lote -->
                            <div class="flex justify-between items-start mb-3 pb-3 border-b border-slate-700/50">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-bold text-slate-300 bg-slate-700 px-2 py-0.5 rounded uppercase tracking-wider">Lote #<span x-text="lote.id"></span></span>
                                        <span class="text-sm font-bold text-white" x-text="lote.bodega?.nombre || 'Bodega General'"></span>
                                    </div>
                                    <div class="text-xs text-slate-400 flex flex-col gap-0.5 mt-1">
                                        <span>Vence: <span class="text-slate-200 font-medium" x-text="lote.fecha_vencimiento ? new Date(lote.fecha_vencimiento).toLocaleDateString() : 'Sin fecha'"></span></span>
                                        <span>Recepción: <span class="text-slate-300" x-text="lote.recepcion_detalle?.recepcion?.numero_factura || 'N/A'"></span></span>
                                    </div>
                                </div>
                                <div class="text-right bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-1.5">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Stock Actual</div>
                                    <div class="text-xl font-black" :class="lote.cantidad_disponible > 0 ? 'text-emerald-400' : 'text-rose-400'" x-text="lote.cantidad_disponible"></div>
                                </div>
                            </div>
                            
                            <!-- Controles de Ajuste -->
                            <div class="flex flex-col sm:flex-row gap-3 items-end">
                                <div class="w-full sm:w-32 shrink-0">
                                    <label class="block text-xs font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Nuevo Stock</label>
                                    <input type="number" min="0" step="0.01" x-model="lote.nuevaCantidad" class="w-full bg-slate-900 border border-slate-600 text-white font-bold rounded-lg focus:ring-amber-500 focus:border-amber-500 p-2.5 text-center">
                                </div>
                                <div class="flex-1 w-full">
                                    <label class="block text-xs font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Motivo del Ajuste</label>
                                    <input type="text" x-model="lote.motivo" placeholder="Ej. Merma, conteo físico..." class="w-full bg-slate-900 border border-slate-600 text-white rounded-lg focus:ring-amber-500 focus:border-amber-500 p-2.5">
                                </div>
                                <div class="w-full sm:w-auto shrink-0">
                                    <button @click="ajustarStock(lote)" :disabled="lote.nuevaCantidad === '' || lote.nuevaCantidad == lote.cantidad_disponible || !lote.motivo" class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:bg-slate-800 disabled:text-slate-600 disabled:border disabled:border-slate-700 text-white font-bold px-5 py-2.5 rounded-lg transition-all shadow-lg shadow-indigo-500/20 disabled:shadow-none whitespace-nowrap flex items-center justify-center gap-2">
                                        <template x-if="lote.enviando">
                                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </template>
                                        <template x-if="!lote.enviando">
                                            <span>Aplicar</span>
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 bg-slate-800/80 border-t border-slate-700 flex justify-end">
                <button type="button" @click="open = false" class="bg-slate-700 hover:bg-slate-600 text-white font-medium px-6 py-2.5 rounded-lg transition-colors shadow-sm">
                    Cerrar Ventana
                </button>
            </div>
        </div>
    </div>

    <!-- Script de Reactividad Avanzada -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('filter-form');
            const searchInput = document.getElementById('search');
            const bodegaSelect = document.getElementById('bodega_id');
            const estadoSelect = document.getElementById('estado');
            const vencimientoSelect = document.getElementById('vencimiento');
            
            const tableContainer = document.getElementById('table-container');
            const paginationContainer = document.getElementById('pagination-container');
            
            let debounceTimer;

            const loadUrl = (url) => {
                window.history.pushState({}, '', url);
                tableContainer.style.opacity = '0.5';

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    
                    tableContainer.style.opacity = '1';
                    
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const linksRow = tempDiv.querySelector('#pagination-links');
                    if (linksRow) {
                        paginationContainer.innerHTML = linksRow.innerHTML;
                    } else {
                        paginationContainer.innerHTML = '';
                    }
                });
            };

            const fetchCatalogo = () => {
                const params = new URLSearchParams();
                if (searchInput.value) params.append('search', searchInput.value);
                if (bodegaSelect.value) params.append('bodega_id', bodegaSelect.value);
                if (estadoSelect.value) params.append('estado', estadoSelect.value);
                if (vencimientoSelect.value) params.append('vencimiento', vencimientoSelect.value);

                const url = `{{ route('catalogo.index', [], false) }}?${params.toString()}`;
                loadUrl(url);
            };

            paginationContainer.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (link) {
                    e.preventDefault();
                    loadUrl(link.href);
                }
            });

            // Event Listeners
            [bodegaSelect, estadoSelect, vencimientoSelect].forEach(select => {
                if(select) select.addEventListener('change', fetchCatalogo);
            });

            if(searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(fetchCatalogo, 300);
                });
            }
            
            // Fix form submission
            if(form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    fetchCatalogo();
                });
            }
        });
    </script>
</x-app-layout>
