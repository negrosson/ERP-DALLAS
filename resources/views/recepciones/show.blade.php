<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Recepción #') }}{{ str_pad($recepcion->id, 5, '0', STR_PAD_LEFT) }}
                @if($recepcion->estado === \App\Enums\EstadoRecepcion::CONFIRMADO)
                    <span class="ml-2 bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded">Confirmada</span>
                @else
                    <span class="ml-2 bg-yellow-100 text-yellow-800 text-sm font-medium px-2.5 py-0.5 rounded">Borrador</span>
                @endif
            </h2>
            <a href="{{ route('recepciones.index') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-700 transition-all bg-white border border-gray-300 hover:bg-gray-50 rounded-xl shadow-sm">
                &larr; Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    <span class="font-medium">Éxito!</span> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                    <span class="font-medium">Error!</span> {{ session('error') }}
                </div>
            @endif

            <!-- Cabecera -->
            <div class="bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative mb-6">
                <div class="p-6 text-slate-300 border-b border-slate-800/50">
                    <h3 class="mb-6 text-lg font-semibold text-white border-b border-slate-700/50 pb-2">Información General del Ingreso</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div class="bg-slate-800/40 p-4 rounded-lg border border-slate-700/30">
                            <p class="text-sm font-medium text-slate-400">Proveedor</p>
                            <p class="mt-1 text-lg font-bold text-white">{{ optional($recepcion->proveedor)->nombre }}</p>
                        </div>
                        <div class="bg-slate-800/40 p-4 rounded-lg border border-slate-700/30">
                            <p class="text-sm font-medium text-slate-400">Bodega Destino</p>
                            <p class="mt-1 text-lg font-bold text-indigo-400">{{ optional($recepcion->bodega)->nombre }}</p>
                        </div>
                        <div class="bg-slate-800/40 p-4 rounded-lg border border-slate-700/30">
                            <p class="text-sm font-medium text-slate-400">Fecha de Recepción</p>
                            <p class="mt-1 text-lg font-bold text-white">{{ \Carbon\Carbon::parse($recepcion->fecha_recepcion)->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="bg-slate-800/40 p-4 rounded-lg border border-slate-700/30">
                            <p class="text-sm font-medium text-slate-400">Documento Referencia</p>
                            <p class="mt-1 text-lg font-bold text-emerald-400">{{ $recepcion->documento_referencia ?? 'N/A' }}</p>
                        </div>
                        <div class="lg:col-span-4 bg-slate-800/40 p-4 rounded-lg border border-slate-700/30">
                            <p class="text-sm font-medium text-slate-400">Observaciones</p>
                            <p class="mt-1 text-white font-medium">{{ $recepcion->observaciones ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agregar Producto (Solo si es BORRADOR) -->
            @if($recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR)
            <div class="bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative mb-6">
                <div class="p-6 border-l-4 border-indigo-500">
                    <h3 class="mb-4 text-lg font-semibold text-white">Agregar Producto</h3>
                    
                    <form method="POST" action="{{ route('recepciones.detalles.store', $recepcion) }}">
                        @csrf
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 items-end">
                            <div class="sm:col-span-2 flex items-end gap-2" x-data='{ 
                                searchText: @json(old("producto_search")), 
                                productos: {!! $productosJson !!},
                                openDropdown: false,
                                get selectedProduct() {
                                    return this.productos.find(p => p.text === this.searchText);
                                },
                                get filteredProductos() {
                                    if (!this.searchText) {
                                        return this.productos.slice(0, 50);
                                    }
                                    
                                    const results = [];
                                    for (const p of this.productos) {
                                        const score = window.fuzzyProductMatch(this.searchText, p.text);
                                        if (score < 999) {
                                            results.push({ item: p, score: score });
                                        }
                                    }
                                    
                                    // Sort by best match (lowest score)
                                    results.sort((a, b) => a.score - b.score);
                                    
                                    return results.slice(0, 50).map(r => r.item);
                                }
                            }' x-init="
                                $watch('searchText', value => {
                                    let p = selectedProduct;
                                    if(p) {
                                        document.getElementById('fefo_mode_hidden').value = p.fefo;
                                        toggleDateFieldsAlt(p.fefo);
                                    } else {
                                        document.getElementById('fefo_mode_hidden').value = '';
                                        toggleDateFieldsAlt('');
                                    }
                                });
                                if(searchText) {
                                    setTimeout(() => {
                                        let p = selectedProduct;
                                        if(p) {
                                            document.getElementById('fefo_mode_hidden').value = p.fefo;
                                            toggleDateFieldsAlt(p.fefo);
                                        }
                                    }, 100);
                                }
                            }">
                                <div class="flex-1 relative" @click.away="openDropdown = false">
                                    <x-input-label for="producto_search" :value="__('Producto')" class="text-slate-300" />
                                    <input type="text" id="producto_search" x-model="searchText" @focus="openDropdown = true" @input="openDropdown = true" @keydown.escape="openDropdown = false" class="block w-full mt-1 bg-slate-800 border-slate-700 text-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Escriba para buscar o seleccione..." required autocomplete="off">
                                    
                                    <!-- Dropdown Autocomplete -->
                                    <div x-show="openDropdown" class="absolute z-50 w-full mt-1 bg-slate-800 border border-slate-700 rounded-md shadow-2xl max-h-60 overflow-y-auto" style="display: none;" x-transition>
                                        <template x-for="p in filteredProductos" :key="p.id">
                                            <button type="button" @click="searchText = p.text; openDropdown = false; $refs.cantidadInput.focus();" class="w-full text-left px-4 py-2.5 text-sm text-slate-300 hover:bg-indigo-600 hover:text-white focus:bg-indigo-600 focus:text-white transition-colors border-b border-slate-700/50 last:border-0">
                                                <span x-text="p.text" class="block truncate"></span>
                                            </button>
                                        </template>
                                        <div x-show="filteredProductos.length === 0" class="px-4 py-3 text-sm text-slate-500 italic text-center">
                                            No se encontraron productos.
                                        </div>
                                    </div>
                                    <input type="hidden" name="catalogo_producto_id" :value="selectedProduct ? selectedProduct.id : ''">
                                    <input type="hidden" name="producto_search" :value="searchText">
                                    <input type="hidden" id="fefo_mode_hidden" value="">
                                    <x-input-error :messages="$errors->get('catalogo_producto_id')" class="mt-2" />
                                </div>
                                <button type="button" @click="if(selectedProduct) { $dispatch('open-ajuste-masivo', { producto: selectedProduct, lotes: selectedProduct.lotes }) }" :disabled="!selectedProduct" class="shrink-0 mb-[1px] px-3 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-md text-white font-bold text-sm flex items-center shadow-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed h-[42px]" title="Ajuste Masivo de Inventario">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                </button>
                            </div>

                            <div>
                                <x-input-label for="cantidad" :value="__('Cantidad')" class="text-slate-300" />
                                <x-text-input id="cantidad" x-ref="cantidadInput" class="block w-full mt-1 bg-slate-800 border-slate-700 text-slate-300" type="number" step="0.01" min="0.01" name="cantidad" :value="old('cantidad', 1)" required />
                                <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="precio_unitario" :value="__('Costo Unitario ($)')" class="text-slate-300" />
                                <x-text-input id="precio_unitario" class="block w-full mt-1 bg-slate-800 border-slate-700 text-slate-300" type="number" step="0.01" min="0" name="precio_unitario" :value="old('precio_unitario', 0)" required />
                                <x-input-error :messages="$errors->get('precio_unitario')" class="mt-2" />
                            </div>
                            
                            <!-- Fechas (Se muestran u ocultan dinámicamente) -->
                            <div class="md:col-span-1" id="container_fecha_elaboracion" style="display: none;">
                                <x-input-label for="fecha_elaboracion" :value="__('Fecha de Elaboración')" class="font-bold text-indigo-400" />
                                <x-text-input id="fecha_elaboracion" class="block w-full mt-1 bg-slate-800 border-indigo-500/50 text-slate-300 focus:ring-indigo-500" type="date" name="fecha_elaboracion" :value="old('fecha_elaboracion')" />
                                <x-input-error :messages="$errors->get('fecha_elaboracion')" class="mt-2" />
                                <p class="text-xs text-slate-400 mt-1">El vcto. se calculará solo.</p>
                            </div>

                            <div class="md:col-span-1" id="container_fecha_vencimiento" style="display: none;">
                                <x-input-label for="fecha_vencimiento" :value="__('Fecha Vencimiento (Explícita)')" class="font-bold text-rose-400" />
                                <x-text-input id="fecha_vencimiento" class="block w-full mt-1 bg-slate-800 border-rose-500/50 text-slate-300 focus:ring-rose-500" type="date" name="fecha_vencimiento" :value="old('fecha_vencimiento')" />
                                <x-input-error :messages="$errors->get('fecha_vencimiento')" class="mt-2" />
                                <p class="text-xs text-slate-400 mt-1">Ingresa el vcto. impreso.</p>
                            </div>

                            <div class="sm:col-span-2 flex flex-col sm:flex-row gap-2 justify-end items-stretch sm:items-end">
                                <button type="submit" class="w-full sm:w-auto px-5 py-3 sm:py-2.5 text-sm font-semibold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-[0_0_15px_rgba(79,70,229,0.4)]">
                                    {{ __('Agregar a la Recepción') }}
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <script>
                        function toggleDateFieldsAlt(fefoMode) {
                            const elabContainer = document.getElementById('container_fecha_elaboracion');
                            const vencContainer = document.getElementById('container_fecha_vencimiento');
                            const elabInput = document.getElementById('fecha_elaboracion');
                            const vencInput = document.getElementById('fecha_vencimiento');
                            
                            // Reset required attributes and hide both initially
                            elabInput.required = false;
                            vencInput.required = false;
                            elabContainer.style.display = 'none';
                            vencContainer.style.display = 'none';
                            
                            if (fefoMode === 'auto') {
                                elabContainer.style.display = 'block';
                                elabInput.required = true;
                                vencInput.value = ''; // Limpiar vencimiento si no se usa
                            } else if (fefoMode === 'manual') {
                                vencContainer.style.display = 'block';
                                vencInput.required = true;
                                elabInput.value = ''; // Limpiar elaboracion si no se usa
                            }
                        }
                    </script>
                </div>
            </div>
            @endif

                    <!-- Tabla en Desktop / Cards en Móvil -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-400 whitespace-nowrap">
                            <thead class="text-xs text-slate-300 uppercase bg-slate-800/80 sticky top-0 z-0 shadow-sm border-b border-slate-700 backdrop-blur-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-3">SKU</th>
                                    <th scope="col" class="px-6 py-3">Producto</th>
                                    <th scope="col" class="px-6 py-3 text-right">Cantidad</th>
                                    <th scope="col" class="px-6 py-3 text-right">Costo Unit.</th>
                                    <th scope="col" class="px-6 py-3 text-right">Subtotal</th>
                                    <th scope="col" class="px-6 py-3">Vencimiento</th>
                                    @if($recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR)
                                        <th scope="col" class="px-6 py-3">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php $total = 0; @endphp
                                @forelse ($recepcion->detalles as $detalle)
                                    @php $total += $detalle->subtotal; @endphp
                                    <tr class="border-b border-slate-800/50 bg-slate-900/30 hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 font-mono font-medium text-slate-200">{{ optional($detalle->producto)->sku }}</td>
                                        <td class="px-6 py-4 font-bold text-white">{{ optional($detalle->producto)->nombre }}</td>
                                        <td class="px-6 py-4 text-right"><span class="bg-indigo-900/50 text-indigo-300 border border-indigo-700 px-3 py-1 rounded-lg font-bold text-base">{{ number_format($detalle->cantidad, 0, ',', '.') }} {{ optional($detalle->producto)->unidad_medida }}</span></td>
                                        <td class="px-6 py-4 text-right text-slate-300">${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-emerald-400">${{ number_format($detalle->subtotal, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4">
                                            @if($detalle->fecha_vencimiento)
                                                <span class="text-slate-200">{{ \Carbon\Carbon::parse($detalle->fecha_vencimiento)->format('d/m/Y') }}</span>
                                            @else
                                                <span class="flex items-center gap-1 bg-orange-500/10 border border-orange-500/20 px-2 py-1 rounded w-max text-orange-400 font-medium"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>Falta Fecha</span>
                                            @endif
                                        </td>
                                        @if($recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR)
                                            <td class="px-6 py-4">
                                                <form action="{{ route('recepciones.detalles.destroy', [$recepcion, $detalle]) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Quitar producto?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-medium text-red-500 hover:text-red-400 transition-colors">Quitar</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR ? 7 : 6 }}" class="px-6 py-12 text-center text-slate-500 bg-slate-900/30">
                                            No hay productos en esta recepción.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($recepcion->detalles->count() > 0)
                                <tfoot>
                                    <tr class="bg-slate-950/50 font-bold border-t border-slate-700/50">
                                        <td colspan="4" class="px-6 py-4 text-right uppercase text-slate-300">Total Recepción:</td>
                                        <td class="px-6 py-4 text-right text-xl text-emerald-400">${{ number_format($total, 0, ',', '.') }}</td>
                                        <td colspan="{{ $recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR ? 2 : 1 }}"></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    <!-- Vista Móvil: Cards por producto -->
                    <div class="block sm:hidden space-y-3">
                        @php $total = 0; @endphp
                        @forelse ($recepcion->detalles as $detalle)
                            @php $total += $detalle->subtotal; @endphp
                            <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4 space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-bold text-white text-base">{{ optional($detalle->producto)->nombre }}</p>
                                        <p class="text-xs font-mono text-slate-400">{{ optional($detalle->producto)->sku }}</p>
                                    </div>
                                    @if($recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR)
                                        <form action="{{ route('recepciones.detalles.destroy', [$recepcion, $detalle]) }}" method="POST" onsubmit="return confirm('¿Quitar producto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="shrink-0 px-3 py-1.5 text-xs font-bold bg-rose-900/40 text-rose-400 border border-rose-700/50 rounded-lg hover:bg-rose-800/50 transition-colors">
                                                ✕ Quitar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-700/30">
                                    <div class="text-center">
                                        <p class="text-[10px] uppercase text-slate-500 font-medium">Cantidad</p>
                                        <p class="text-sm font-bold text-indigo-300">{{ number_format($detalle->cantidad, 0, ',', '.') }} {{ optional($detalle->producto)->unidad_medida }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-[10px] uppercase text-slate-500 font-medium">Costo U.</p>
                                        <p class="text-sm font-bold text-slate-300">${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-[10px] uppercase text-slate-500 font-medium">Subtotal</p>
                                        <p class="text-sm font-bold text-emerald-400">${{ number_format($detalle->subtotal, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 pt-1">
                                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @if($detalle->fecha_vencimiento)
                                        <span class="text-sm text-slate-200">Vcto: {{ \Carbon\Carbon::parse($detalle->fecha_vencimiento)->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-sm text-orange-400 font-medium">⚠ Falta fecha de vencimiento</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center text-slate-500">No hay productos en esta recepción.</div>
                        @endforelse
                        @if($recepcion->detalles->count() > 0)
                            <div class="flex justify-between items-center bg-slate-950/50 border border-slate-700/50 rounded-xl px-4 py-3 font-bold">
                                <span class="text-slate-300 uppercase text-sm">Total Recepción</span>
                                <span class="text-xl text-emerald-400">${{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                    
                </div>
            </div>

            <!-- Confirmación -->
            @if($recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR && $recepcion->detalles->count() > 0)
            <div class="mt-6 flex justify-end">
                <form action="{{ route('recepciones.confirm', $recepcion) }}" method="POST" onsubmit="return confirm('¿Está seguro de CONFIRMAR esta recepción? Se ingresará el stock a la bodega y no podrá deshacer esta acción.');">
                    @csrf
                    <button type="submit" class="px-6 py-3 text-base font-medium text-white transition-colors bg-green-600 rounded-md hover:bg-green-700 shadow-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Confirmar e Ingresar Stock
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
    
    <x-ajuste-masivo-modal :bodega="$recepcion->bodega" />

    <script>
        // Funciones auxiliares para búsqueda tolerante a errores ortográficos (Fuzzy Match)
        window.levenshtein = function(a, b) {
            if (a.length === 0) return b.length;
            if (b.length === 0) return a.length;
            const matrix = [];
            for (let i = 0; i <= b.length; i++) {
                matrix[i] = [i];
            }
            for (let j = 0; j <= a.length; j++) {
                matrix[0][j] = j;
            }
            for (let i = 1; i <= b.length; i++) {
                for (let j = 1; j <= a.length; j++) {
                    if (b.charAt(i - 1) === a.charAt(j - 1)) {
                        matrix[i][j] = matrix[i - 1][j - 1];
                    } else {
                        matrix[i][j] = Math.min(
                            matrix[i - 1][j - 1] + 1, // substitution
                            matrix[i][j - 1] + 1,     // insertion
                            matrix[i - 1][j] + 1      // deletion
                        );
                    }
                }
            }
            return matrix[b.length][a.length];
        };

        window.fuzzyProductMatch = function(search, text) {
            search = search.toLowerCase().trim();
            text = text.toLowerCase();
            
            if (text.includes(search)) return 0; // Match exacto
            
            const searchWords = search.split(/\s+/);
            const textWords = text.split(/[\s,\[\]\-]+/);
            
            let totalScore = 0;
            
            for (const sw of searchWords) {
                if (sw.length === 0) continue;
                
                let bestWordScore = 999;
                
                if (text.includes(sw)) {
                    bestWordScore = 0;
                } else {
                    for (const tw of textWords) {
                        if (tw.length === 0) continue;
                        
                        if (tw.includes(sw)) {
                            bestWordScore = 0;
                            break;
                        }
                        
                        const dist = window.levenshtein(sw, tw);
                        // Tolerancia de errores basada en el tamaño de la palabra
                        const maxTypos = sw.length <= 3 ? 1 : (sw.length <= 5 ? 2 : 3);
                        
                        if (dist <= maxTypos && dist < bestWordScore) {
                            bestWordScore = dist;
                        }
                    }
                }
                
                if (bestWordScore > 3) {
                    return 999; // Rechaza si una palabra no se parece a nada
                }
                
                totalScore += bestWordScore;
            }
            
            return totalScore;
        };
    </script>
</x-app-layout>
