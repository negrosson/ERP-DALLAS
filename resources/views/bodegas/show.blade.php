<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-200">
                    {{ __('Inventario de Bodega:') }} {{ $bodega->nombre }}
                </h2>
                <p class="text-sm text-slate-400 mt-1 font-mono">Código: {{ $bodega->codigo }}</p>
            </div>
            <a href="{{ route('bodegas.index') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-700 transition-all bg-white border border-gray-300 hover:bg-gray-50 rounded-xl shadow-sm">
                &larr; Volver a Bodegas
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex flex-wrap gap-4">
                    @if($bodega->es_refrigerada)
                        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-lg">❄️ Cámara de Frío</span>
                    @else
                        <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-lg">📦 Bodega Normal</span>
                    @endif
                    
                    @if($bodega->activa)
                        <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-lg">✅ Activa</span>
                    @else
                        <span class="bg-red-100 text-red-800 text-sm font-medium px-3 py-1 rounded-lg">❌ Inactiva</span>
                    @endif
                </div>

                @if($bodega->activa)
                    <button type="button" @click="$dispatch('open-quick-add')" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white transition-all bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-lg shadow-emerald-900/20 border border-emerald-500/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Ingreso Rápido de Stock
                    </button>
                @endif
            </div>

            <div class="mb-6 bg-slate-900/50 backdrop-blur-md shadow-lg sm:rounded-xl border border-slate-800/60 p-4">
                <form id="filter-form" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7 gap-4">
                    <!-- Buscador -->
                    <div class="relative xl:col-span-2">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="search" id="search" name="search" value="{{ request('search') }}" class="block w-full p-2.5 pl-10 text-sm bg-slate-800 border-slate-700 placeholder-slate-500 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Buscar por Nombre o SKU...">
                    </div>

                    <!-- Filtro Marca -->
                    <div>
                        <select name="marca" id="marca" class="block w-full p-2.5 text-sm bg-slate-800 border-slate-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Cualquier Marca/Bebida</option>
                            <optgroup label="CCU">
                                <option value="Cachantun" {{ request('marca') === 'Cachantun' ? 'selected' : '' }}>Cachantún</option>
                                <option value="Pepsi" {{ request('marca') === 'Pepsi' ? 'selected' : '' }}>Pepsi</option>
                                <option value="Bilz" {{ request('marca') === 'Bilz' ? 'selected' : '' }}>Bilz</option>
                                <option value="Pap" {{ request('marca') === 'Pap' ? 'selected' : '' }}>Pap</option>
                                <option value="Kem" {{ request('marca') === 'Kem' ? 'selected' : '' }}>Kem</option>
                                <option value="Limonsoda" {{ request('marca') === 'Limonsoda' ? 'selected' : '' }}>Limonsoda</option>
                                <option value="Crush" {{ request('marca') === 'Crush' ? 'selected' : '' }}>Crush</option>
                                <option value="Canada Dry" {{ request('marca') === 'Canada Dry' ? 'selected' : '' }}>Canada Dry</option>
                                <option value="Gatorade" {{ request('marca') === 'Gatorade' ? 'selected' : '' }}>Gatorade</option>
                            </optgroup>
                            <optgroup label="Coca-Cola">
                                <option value="Coca-Cola" {{ request('marca') === 'Coca-Cola' ? 'selected' : '' }}>Coca-Cola</option>
                                <option value="Fanta" {{ request('marca') === 'Fanta' ? 'selected' : '' }}>Fanta</option>
                                <option value="Sprite" {{ request('marca') === 'Sprite' ? 'selected' : '' }}>Sprite</option>
                                <option value="Monster" {{ request('marca') === 'Monster' ? 'selected' : '' }}>Monster</option>
                                <option value="Powerade" {{ request('marca') === 'Powerade' ? 'selected' : '' }}>Powerade</option>
                                <option value="Benedictino" {{ request('marca') === 'Benedictino' ? 'selected' : '' }}>Benedictino</option>
                                <option value="Aquarius" {{ request('marca') === 'Aquarius' ? 'selected' : '' }}>Aquarius</option>
                            </optgroup>
                        </select>
                    </div>
                    
                    <!-- Filtro Formato -->
                    <div>
                        <select id="formato" name="formato" class="block w-full p-2.5 text-sm bg-slate-800 border-slate-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Cualquier Formato</option>
                            @foreach($formatos as $fmt)
                                <option value="{{ $fmt }}" {{ request('formato') == $fmt ? 'selected' : '' }}>{{ $fmt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro Capacidad -->
                    <div>
                        <select id="capacidad" name="capacidad" class="block w-full p-2.5 text-sm bg-slate-800 border-slate-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Cualquier Capacidad</option>
                            @foreach($capacidades as $cap)
                                <option value="{{ $cap }}" {{ request('capacidad') == $cap ? 'selected' : '' }}>{{ $cap }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro Vencimiento -->
                    <div>
                        <select id="vencimiento" name="vencimiento" class="block w-full p-2.5 text-sm bg-slate-800 border-slate-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Cualquier Vencimiento</option>
                            <option value="vencidos" {{ request('vencimiento') == 'vencidos' ? 'selected' : '' }}>Vencidos (Rojo)</option>
                            <option value="7_dias" {{ request('vencimiento') == '7_dias' ? 'selected' : '' }}>Vence < 7 días</option>
                            <option value="14_dias" {{ request('vencimiento') == '14_dias' ? 'selected' : '' }}>Vence < 14 días</option>
                            <option value="30_dias" {{ request('vencimiento') == '30_dias' ? 'selected' : '' }}>Vence < 30 días</option>
                        </select>
                    </div>
                    
                    <!-- Limpiar -->
                    <div>
                        <button type="button" onclick="window.resetFilters()" class="w-full px-4 py-2.5 text-sm font-medium text-slate-300 bg-slate-800 border border-slate-700 rounded-lg hover:bg-slate-700 focus:ring-4 focus:outline-none focus:ring-slate-800 transition-colors">
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative">
                
                <!-- Loading overlay -->
                <div id="table-loader" class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px] z-10 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-200">
                    <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div class="p-6 text-slate-300 border-b border-slate-800/50">
                    
                    <h3 class="text-lg font-semibold text-white mb-4">Stock Consolidado</h3>
                    
                    <div class="overflow-x-auto overflow-y-auto max-h-[65vh] custom-scrollbar rounded-xl">
                        <div id="table-container">
                            @include('bodegas.partials.table', ['productos' => $productos])
                        </div>
                    </div>
                    <div id="pagination-container" class="px-6 py-4 border-t border-slate-800/50 bg-slate-900/30"></div>
                </div>
            </div>
            
            <div class="mt-8 bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative">
                <div class="p-6 text-slate-300">
                    <h3 class="text-lg font-semibold text-white mb-4">Historial de Ajustes (Auditoría)</h3>
                    
                    <div class="hidden md:block overflow-x-auto overflow-y-auto max-h-[40vh] custom-scrollbar rounded-xl border border-slate-700">
                        <table class="w-full text-sm text-left text-slate-400 whitespace-nowrap">
                            <thead class="text-xs text-slate-300 uppercase bg-slate-800/80 sticky top-0 z-0">
                                <tr>
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Lote & Producto</th>
                                    <th class="px-4 py-3">Usuario</th>
                                    <th class="px-4 py-3">Motivo</th>
                                    <th class="px-4 py-3 text-right">Variación</th>
                                    <th class="px-4 py-3 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historial as $ajuste)
                                <tr class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors {{ $ajuste->es_reversion ? 'bg-orange-900/10' : '' }}">
                                    <td class="px-4 py-3">{{ $ajuste->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs text-slate-500">#{{ $ajuste->lote_stock_id }}</span><br>
                                        {{ $ajuste->lote->producto->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3">{{ $ajuste->usuario?->name ?? 'Sistema' }}</td>
                                    <td class="px-4 py-3">
                                        {{ $ajuste->motivo }}
                                        @if($ajuste->es_reversion)
                                            <span class="ml-2 text-xs text-orange-400 border border-orange-500/30 px-1 rounded">Reversión</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold {{ $ajuste->diferencia > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $ajuste->diferencia > 0 ? '+' : '' }}{{ number_format($ajuste->diferencia, 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if(!$ajuste->es_reversion && !$ajuste->reversiones->count())
                                        <form action="{{ route('bodegas.revertir', $ajuste->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de revertir este ajuste? Se creará un nuevo registro de reversión.');">
                                            @csrf
                                            <button type="submit" class="text-xs bg-slate-700 hover:bg-slate-600 text-white px-2 py-1 rounded transition-colors">
                                                Deshacer
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-xs text-slate-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No hay ajustes registrados en esta bodega.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards Historial -->
                    <div class="md:hidden space-y-4 max-h-[50vh] overflow-y-auto custom-scrollbar">
                        @forelse($historial as $ajuste)
                            <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-800 {{ $ajuste->es_reversion ? 'border-orange-900/50 bg-orange-900/10' : '' }}">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="text-xs text-slate-400">{{ $ajuste->created_at->format('d/m/Y H:i') }}</div>
                                    <div class="font-bold {{ $ajuste->diferencia > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $ajuste->diferencia > 0 ? '+' : '' }}{{ number_format($ajuste->diferencia, 0) }}
                                    </div>
                                </div>
                                <div class="text-sm font-bold text-white mb-1">{{ $ajuste->lote->producto->nombre ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-400 mb-2 font-mono">Lote #{{ $ajuste->lote_stock_id }}</div>
                                <div class="text-sm text-slate-300 mb-1"><span class="text-slate-500">Motivo:</span> {{ $ajuste->motivo }}</div>
                                @if($ajuste->es_reversion)
                                    <div class="text-xs text-orange-400 mb-2">Reversión</div>
                                @endif
                                <div class="text-xs text-slate-500 mb-3"><span class="text-slate-600">Usuario:</span> {{ $ajuste->usuario?->name ?? 'Sistema' }}</div>
                                
                                @if(!$ajuste->es_reversion && !$ajuste->reversiones->count())
                                    <div class="pt-3 border-t border-slate-800/50">
                                        <form action="{{ route('bodegas.revertir', $ajuste->id) }}" method="POST" class="w-full" onsubmit="return confirm('¿Estás seguro de revertir este ajuste? Se creará un nuevo registro de reversión.');">
                                            @csrf
                                            <button type="submit" class="w-full text-center py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg font-medium border border-slate-700 transition-colors">
                                                Deshacer Ajuste
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-500 bg-slate-900/30 rounded-lg">
                                No hay ajustes registrados en esta bodega.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Transferir Lote -->
    <x-transfer-lote-modal :bodegas="\App\Models\Bodega::where('activa', true)->orderBy('nombre')->get()" />
    <!-- Modal para Editar Fechas del Lote -->
    <div x-data="{
        open: false,
        loteId: null,
        loteFElab: '',
        loteFVenc: '',
        mesesVidaUtil: '',
        actionUrl: '',
        abrirModal(id, elab, venc, url, vidaUtil = '') {
            this.loteId = id;
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
            
            if (this.loteFElab && this.mesesVidaUtil && !this.loteFVenc) {
                this.calcularVencimiento();
            }
        },
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
            <!-- Overlay -->
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
                <!-- Panel -->
                <div x-show="open" @click.outside="open = false"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block align-bottom bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl shadow-black/50 transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border border-slate-700">
                    
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
                            
                            <div>
                                <label for="fecha_elaboracion" class="block text-sm font-medium text-slate-300 mb-1">Fecha de Elaboración</label>
                                <input type="date" id="fecha_elaboracion" name="fecha_elaboracion" x-model="loteFElab" @change="calcularVencimiento" class="block w-full bg-slate-800 border-slate-700 text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                            </div>

                            <div class="p-3 bg-indigo-900/20 border border-indigo-500/20 rounded-lg">
                                <label for="meses_vida_util" class="block text-xs font-medium text-indigo-300 mb-1">Calculadora: Vida Útil (Meses)</label>
                                <div class="flex gap-2">
                                    <input type="number" id="meses_vida_util" x-model="mesesVidaUtil" @input="calcularVencimiento" min="1" step="1" class="block w-full bg-slate-800 border-slate-700 text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm" placeholder="Ej. 6" />
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1">Si el envase dice "Consumir antes de 6 meses", ingresa 6 aquí y se calculará automáticamente.</p>
                            </div>

                            <div>
                                <label for="fecha_vencimiento" class="block text-sm font-medium text-slate-300 mb-1">Fecha de Vencimiento Final <span class="text-red-400">*</span></label>
                                <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" x-model="loteFVenc" required class="block w-full bg-slate-800 border-slate-700 text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                            </div>
                        </div>
                        
                        <div class="px-6 py-4 bg-slate-900/80 border-t border-slate-800 flex items-center justify-end gap-3">
                            <button type="button" @click="open = false" class="px-4 py-2 bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg font-medium transition-colors border border-slate-700">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-500 rounded-lg font-bold shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2">
                                Guardar Fechas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('search');
                const marcaInput = document.getElementById('marca');
                const formatoSelect = document.getElementById('formato');
                const capacidadSelect = document.getElementById('capacidad');
                const vencimientoSelect = document.getElementById('vencimiento');
                const tableLoader = document.getElementById('table-loader');
                const tableContainer = document.getElementById('table-container');
                const paginationContainer = document.getElementById('pagination-container');
                
                let debounceTimer;

                const fetchProductos = (urlStr) => {
                    tableLoader.classList.remove('opacity-0', 'pointer-events-none');
                    tableLoader.classList.add('opacity-100');
                    
                    let url;
                    if (urlStr) {
                        url = new URL(urlStr);
                    } else {
                        const params = new URLSearchParams();
                        if (searchInput.value) params.append('search', searchInput.value);
                        if (marcaInput.value) params.append('marca', marcaInput.value);
                        if (formatoSelect.value) params.append('formato', formatoSelect.value);
                        if (capacidadSelect.value) params.append('capacidad', capacidadSelect.value);
                        if (vencimientoSelect.value) params.append('vencimiento', vencimientoSelect.value);
                        url = `{{ route('bodegas.show', $bodega, false) }}?${params.toString()}`;
                    }
                    
                    window.history.pushState({}, '', url);

                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                        
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;
                        const linksRow = tempDiv.querySelector('#pagination-links');
                        if (linksRow && linksRow.innerHTML.trim() !== '') {
                            paginationContainer.innerHTML = linksRow.innerHTML;
                        } else {
                            paginationContainer.innerHTML = '';
                        }
                    })
                    .finally(() => {
                        tableLoader.classList.remove('opacity-100');
                        tableLoader.classList.add('opacity-0', 'pointer-events-none');
                    });
                };

                const handleInput = () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => fetchProductos(), 300);
                };

                searchInput.addEventListener('input', handleInput);
                marcaInput.addEventListener('change', () => fetchProductos());
                formatoSelect.addEventListener('change', () => fetchProductos());
                capacidadSelect.addEventListener('change', () => fetchProductos());
                vencimientoSelect.addEventListener('change', () => fetchProductos());
                
                paginationContainer.addEventListener('click', (e) => {
                    const link = e.target.closest('a');
                    if (link) {
                        e.preventDefault();
                        fetchProductos(link.href);
                    }
                });
                
                window.resetFilters = () => {
                    searchInput.value = '';
                    marcaInput.value = '';
                    formatoSelect.value = '';
                    capacidadSelect.value = '';
                    vencimientoSelect.value = '';
                    fetchProductos();
                };
            });
        </script>
    </x-slot>

    <!-- Modal Ingreso Rápido -->
    <div x-data="{
            open: false,
            searchQuery: '',
            searchResults: [],
            isLoading: false,
            
            // Selected or new product details
            productoId: '',
            nombre: '',
            marca: '',
            
            // Ingreso details
            formatoEmpaquíe: 'Display',
            unidadesPorEmpaquíe: 24,
            cantidadEmpaquíes: 1,
            fechaElaboracion: '',
            fechaVencimiento: '',
            
            isSubmitting: false,

            get totalUnidades() {
                return (parseFloat(this.cantidadEmpaquíes) || 0) * (parseInt(this.unidadesPorEmpaquíe) || 0);
            },

            closeModal() {
                this.open = false;
                setTimeout(() => this.resetState(), 300);
            },
            
            resetState() {
                this.searchQuery = '';
                this.searchResults = [];
                this.productoId = '';
                this.nombre = '';
                this.marca = '';
                this.formatoEmpaquíe = 'Display';
                this.unidadesPorEmpaquíe = 24;
                this.cantidadEmpaquíes = 1;
                this.fechaElaboracion = '';
                this.fechaVencimiento = '';
            },

            searchProducts() {
                if (this.searchQuery.trim().length < 2) {
                    this.searchResults = [];
                    return;
                }
                this.isLoading = true;
                // Reuse existing endpoint just for suggestion
                fetch('{{ route('catalogo.index') }}?search=' + encodeURIComponent(this.searchQuery), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    this.searchResults = data.data || data;
                })
                .finally(() => this.isLoading = false);
            },

            selectProduct(prod) {
                this.productoId = prod.id;
                this.nombre = prod.nombre;
                this.marca = prod.marca || '';
                
                // Try to infer units from format
                if (prod.formato) {
                    const match = String(prod.formato).match(/\b(?:pack\s*)?(?:x\s*)?(\d+)\b/i);
                    if (match && parseInt(match[1]) > 1) {
                        this.unidadesPorEmpaque = parseInt(match[1]);
                    }
                }
                this.searchQuery = '';
                this.searchResults = [];
            },

            useNewProduct() {
                this.productoId = '';
                this.nombre = this.searchQuery;
                this.searchQuery = '';
                this.searchResults = [];
            },

            submitForm() {
                if(this.totalUnidades <= 0) {
                    alert('La cantidad de unidades debe ser mayor a 0');
                    return;
                }
                if(!this.nombre) {
                    alert('Debes indicar un nombre de producto');
                    return;
                }
                this.isSubmitting = true;
                document.getElementById('quick-add-form').submit();
            }
         }"
         @open-quick-add.window="open = true; setTimeout(() => $refs.searchBox.focus(), 100)"
         x-show="open" 
         class="fixed inset-0 z-[100] flex items-center justify-center pt-10 sm:pt-0 bg-black/60 backdrop-blur-sm p-4 sm:p-0"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
         
         <div class="bg-slate-900 border border-slate-700 w-full max-w-lg rounded-2xl shadow-2xl flex flex-col overflow-hidden max-h-[90vh]" @click.outside="closeModal()">
            
            <div class="p-4 sm:p-6 border-b border-slate-800 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Ingreso Rápido de Stock
                </h3>
                <button @click="closeModal()" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-4 sm:p-6 overflow-y-auto custom-scrollbar flex-1 relative">
                
                <!-- Step 1: Buscar o Escribir Producto -->
                <div x-show="!nombre" class="space-y-4 relative">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Producto (Escribe para buscar o crear)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input x-ref="searchBox" type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()" @keydown.enter.prevent="useNewProduct()" placeholder="Ej: Cerveza Escudo..." 
                            class="block w-full pl-10 p-3 bg-slate-800 border-slate-700 text-white rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <!-- Resultados / Autocompletado -->
                    <div x-show="searchQuery.length > 0" class="mt-2 bg-slate-800 border border-slate-700 rounded-lg overflow-hidden max-h-60 overflow-y-auto custom-scrollbar shadow-inner">
                        <div x-show="isLoading" class="p-4 text-center text-slate-400 text-sm">Buscando...</div>
                        
                        <button type="button" @click="useNewProduct()" class="w-full text-left p-3 border-b border-emerald-700/50 bg-emerald-900/20 hover:bg-emerald-800/40 transition-colors flex flex-col gap-1">
                            <span class="text-emerald-400 font-bold">Crear: "<span x-text="searchQuery"></span>"</span>
                            <span class="text-xs text-slate-400">Presiona ENTER o haz clic aquíí para usar este nombre</span>
                        </button>

                        <template x-for="prod in searchResults" :key="prod.id">
                            <button type="button" @click="selectProduct(prod)" class="w-full text-left p-3 border-b border-slate-700/50 hover:bg-slate-700 transition-colors flex flex-col gap-1">
                                <span class="text-white font-medium" x-text="prod.nombre"></span>
                                <span class="text-xs text-indigo-400 font-mono font-bold" x-show="prod.sku">SKU: <span x-text="prod.sku"></span></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Step 2: Ingresar Detalles -->
                <div x-show="nombre" class="space-y-5" style="display: none;">
                    <form id="quick-add-form" action="{{ route('bodegas.quick-lote', $bodega) }}" method="POST">
                        @csrf
                        <input type="hidden" name="catalogo_producto_id" :value="productoId">
                        
                        <!-- Auto-calculated total quantity -->
                        <input type="hidden" name="cantidad" :value="totalUnidades">
                        
                        <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700/50 mb-5 relative">
                            <button type="button" @click="resetState(); setTimeout(() => $refs.searchBox.focus(), 100)" class="absolute top-2 right-2 text-slate-400 hover:text-rose-400 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-500/20 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 pr-6">
                                    <h4 class="text-white font-bold text-lg truncate mb-1">
                                        <input type="text" name="nombre" x-model="nombre" class="bg-transparent border-0 p-0 text-white focus:ring-0 w-full" readonly>
                                    </h4>
                                    <div class="flex items-center gap-2 mt-2">
                                        <input type="text" name="marca" x-model="marca" placeholder="Marca (opcional)" class="bg-slate-900 border-slate-700 rounded-md text-xs text-slate-300 py-1 px-2 w-1/2">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Fila Empaquíe -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-400 mb-1">Tipo de Empaquíe</label>
                                    <select name="formato" x-model="formatoEmpaquíe" class="w-full bg-slate-800 border-slate-700 rounded-md text-sm text-slate-300">
                                        <option value="Display">Display</option>
                                        <option value="Caja">Caja</option>
                                        <option value="Pack">Pack</option>
                                        <option value="Unidad">Unidad / Suelto</option>
                                    </select>
                                </div>
                                <div x-show="formatoEmpaquíe !== 'Unidad'">
                                    <label class="block text-xs font-medium text-slate-400 mb-1">Unidades por <span x-text="formatoEmpaquíe"></span></label>
                                    <select x-model="unidadesPorEmpaquíe" class="w-full bg-slate-800 border-slate-700 rounded-md text-sm text-slate-300">
                                        <option value="6">6 unidades</option>
                                        <option value="12">12 unidades</option>
                                        <option value="24">24 unidades</option>
                                        <option value="32">32 unidades</option>
                                        <option value="1">1 (Unidad)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-400 mb-1">Cantidad de <span x-text="formatoEmpaquíe"></span>(s)</label>
                                    <input type="number" min="1" step="1" x-model="cantidadEmpaquíes" class="w-full bg-slate-800 border-slate-700 rounded-md text-sm text-slate-300">
                                </div>
                                <div class="bg-indigo-900/20 border border-indigo-700/30 rounded-md p-3 flex flex-col justify-center items-center">
                                    <span class="text-xs text-indigo-400 mb-1">Total a Ingresar</span>
                                    <span class="text-2xl font-black text-indigo-300" x-text="totalUnidades + ' UN'"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-400 mb-1">Elaboración</label>
                                    <input type="date" name="fecha_elaboracion" x-model="fechaElaboracion" class="w-full bg-slate-800 border-slate-700 rounded-md text-sm text-slate-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-400 mb-1">Vencimiento</label>
                                    <input type="date" name="fecha_vencimiento" x-model="fechaVencimiento" class="w-full bg-slate-800 border-slate-700 rounded-md text-sm text-slate-300">
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-800">
                            <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-slate-300 bg-slate-800 border border-slate-600 rounded-lg hover:bg-slate-700 transition-colors">Cancelar</button>
                            <button type="button" @click="submitForm()" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 shadow-lg shadow-emerald-500/20 transition-colors disabled:opacity-50">Ingresar Stock</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-ajuste-masivo-modal :bodega="$bodega" />
</x-app-layout>

