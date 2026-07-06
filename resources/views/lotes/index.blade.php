<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Lotes Disponibles (Stock)') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            
            <!-- Filtros Modernos con Reactividad -->
            <div class="p-6 mb-6 bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60">
                <form id="filter-form" onsubmit="event.preventDefault(); fetchLotes();" class="flex flex-col md:flex-row md:items-end gap-5">
                    
                    <div class="flex-1 relative">
                        <x-input-label for="search" :value="__('Buscar Producto (Nombre o SKU)')" class="text-slate-300 font-medium mb-1 block" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <x-text-input id="search" name="search" type="text" class="block w-full pl-10 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" :value="request('search')" placeholder="Ej: Arroz, Bebida..." autocomplete="off" />
                        </div>
                    </div>
                    
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

                    <div class="flex-1">
                        <x-input-label for="proveedor_id" :value="__('Proveedor (Marca)')" class="text-slate-300 font-medium mb-1 block" />
                        <select id="proveedor_id" name="proveedor_id" class="block w-full bg-slate-900 border-slate-700 text-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos los Proveedores</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" {{ request('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                    {{ $proveedor->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1">
                        <x-input-label for="alerta" :value="__('Estado Vencimiento')" class="text-slate-300 font-medium mb-1 block" />
                        <select id="alerta" name="alerta" class="block w-full bg-slate-900 border-slate-700 text-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos los Estados</option>
                            <option value="vencido" {{ request('alerta') == 'vencido' ? 'selected' : '' }}>Vencido (<0 días)</option>
                            <option value="critico" {{ request('alerta') == 'critico' ? 'selected' : '' }}>Crítico (0-6 días)</option>
                            <option value="alto" {{ request('alerta') == 'alto' ? 'selected' : '' }}>Alerta (7-14 días)</option>
                            <option value="medio" {{ request('alerta') == 'medio' ? 'selected' : '' }}>Preventivo (15-21 días)</option>
                            <option value="bajo" {{ request('alerta') == 'bajo' ? 'selected' : '' }}>Atención (22-30 días)</option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="button" onclick="resetFilters()" class="w-full md:w-auto px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-lg border border-slate-700 shadow-sm transition-colors duration-200">
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla de Lotes con Sticky Header -->
            <div class="bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative">
                <!-- Loader Overlay -->
                <div id="table-loader" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm z-10 hidden flex items-center justify-center">
                    <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>

                <div class="overflow-x-auto max-h-[600px]">
                    <table class="w-full text-sm text-left text-slate-600 whitespace-nowrap">
                        <thead class="text-xs text-slate-300 uppercase bg-slate-800/80 sticky top-0 z-0 shadow-sm border-b border-slate-700 backdrop-blur-sm">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">ID Lote</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Bodega</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">SKU</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Producto</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Cant. Actual</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Costo Unit.</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Vencimiento (FEFO)</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="divide-y divide-slate-800/50">
                            @include('lotes.partials.table', ['lotes' => $lotes])
                        </tbody>
                    </table>
                </div>
                
                <div id="pagination-container" class="p-4 border-t border-slate-700 bg-slate-800/50 text-slate-300">
                    {{ $lotes->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Script de Reactividad Avanzada -->
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

            const fetchLotes = () => {
                tableLoader.classList.remove('hidden');
                
                const params = new URLSearchParams();
                if (searchInput.value) params.append('search', searchInput.value);
                if (bodegaSelect.value) params.append('bodega_id', bodegaSelect.value);
                if (proveedorSelect.value) params.append('proveedor_id', proveedorSelect.value);
                if (alertaSelect.value) params.append('alerta', alertaSelect.value);

                const url = `{{ route('lotes.index') }}?${params.toString()}`;
                
                // Actualizar la URL sin recargar para que se pueda compartir
                window.history.pushState({}, '', url);

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    tableBody.innerHTML = html;
                    
                    // Extraer los links de paginación
                    const tempDiv = document.createElement('div');
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
                    tableLoader.classList.add('hidden');
                });
            };

            // Event Listeners con Debounce
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchLotes, 300); // 300ms debounce
            });

            bodegaSelect.addEventListener('change', fetchLotes);
            proveedorSelect.addEventListener('change', fetchLotes);
            alertaSelect.addEventListener('change', fetchLotes);
            
            // Interceptar clicks de paginación para hacerlos via AJAX
            paginationContainer.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (link) {
                    e.preventDefault();
                    const url = new URL(link.href);
                    
                    tableLoader.classList.remove('hidden');
                    window.history.pushState({}, '', url);
                    
                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        tableBody.innerHTML = html;
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;
                        const linksRow = tempDiv.querySelector('#pagination-links td');
                        if (linksRow) paginationContainer.innerHTML = linksRow.innerHTML;
                    })
                    .finally(() => tableLoader.classList.add('hidden'));
                }
            });
            
            // Función Reset exportada globalmente
            window.resetFilters = () => {
                searchInput.value = '';
                bodegaSelect.value = '';
                proveedorSelect.value = '';
                alertaSelect.value = '';
                fetchLotes();
            };
        });
    </script>
</x-app-layout>
