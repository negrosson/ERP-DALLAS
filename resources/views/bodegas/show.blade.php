{{-- 
========================================================================================
VISTA: CONSOLA DE GESTIÓN Y AUDITORÍA DE INVENTARIO POR BODEGA
RUTA: /bodegas/{bodega}
CONTROLADOR: BodegaController@show
PROPÓSITO:
- Visualizar el stock físico consolidado de productos y el desglose de lotes por fecha de vencimiento (FEFO).
- Filtrado reactivo en tiempo real vía AJAX (sin recarga completa):
  * Buscador por nombre o SKU.
  * Filtro jerárquico por marca (CCU vs Coca-Cola).
  * Filtro por formato y capacidad de envasado.
  * Filtro por urgencia de vencimiento (vencidos, <7 días, <14 días, <30 días).
- Historial de auditoría de ajustes de inventario con capacidad de reversión (Deshacer ajuste).
- Modal interactivo de "Ingreso Rápido de Stock" con multiplicadores de empaque (Displays/Cajas).
- Modal de edición rápida de fechas de lote con calculadora de vida útil en meses.
- Integración de modales de traslado entre bodegas y ajuste masivo.
========================================================================================
--}}

<x-app-layout>
    {{-- Slot del encabezado superior: Título de la bodega, código identificador y botón de retorno --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-200">
                    {{ __('Inventario de Bodega:') }} {{ $bodega->nombre }}
                </h2>
                <p class="text-sm text-slate-400 mt-1 font-mono">Código: {{ $bodega->codigo }}</p>
            </div>
            {{-- Enlace para regresar a la lista de bodegas --}}
            <a href="{{ route('bodegas.index') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-700 transition-all bg-white border border-gray-300 hover:bg-gray-50 rounded-xl shadow-sm">
                &larr; Volver a Bodegas
            </a>
        </div>
    </x-slot>

    {{-- Contenedor del contenido principal --}}
    <div class="py-4">
        {{-- Contenedor flotante para notificaciones Toast ultra-rápidas en móvil y desktop --}}
        <div id="toast-container" class="fixed top-4 left-1/2 -translate-x-1/2 sm:left-auto sm:right-6 sm:translate-x-0 z-[150] flex flex-col gap-2 pointer-events-none w-11/12 max-w-sm"></div>

        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            
            {{-- Barra superior de estado de la bodega y botón de acción rápida --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                {{-- Badges de tipo de bodega y operatividad --}}
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

                {{-- Si la bodega está activa, permite el ingreso rápido directo de unidades --}}
                @if($bodega->activa)
                    <button type="button" @click="$dispatch('open-quick-add')" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white transition-all bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-lg shadow-emerald-900/20 border border-emerald-500/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Ingreso Rápido de Stock
                    </button>
                @endif
            </div>

            {{-- =========================================================================
                 BARRA DE FILTROS REACTIVOS (Búsqueda, Marca, Formato, Capacidad, Vencimiento)
                 Cada cambio dispara automáticamente una consulta asíncrona fetch vía AJAX
                 ========================================================================= --}}
            <div class="mb-6 bg-slate-900/50 backdrop-blur-md shadow-lg sm:rounded-xl border border-slate-800/60 p-4">
                <form id="filter-form" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7 gap-4">
                    {{-- Buscador de texto: Búsqueda por Nombre o Código SKU --}}
                    <div class="relative xl:col-span-2">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="search" id="search" name="search" value="{{ request('search') }}" class="block w-full p-2.5 pl-10 text-sm bg-slate-800 border-slate-700 placeholder-slate-500 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Buscar por Nombre o SKU...">
                    </div>

                    {{-- Selector de Marca comercial adaptado dinámicamente al tipo de bodega --}}
                    <div>
                        <select name="marca" id="marca" class="block w-full p-2.5 text-sm bg-slate-800 border-slate-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">{{ $placeholderMarca ?? 'Cualquier Marca/Bebida' }}</option>
                            @if(isset($gruposMarcas))
                                @foreach($gruposMarcas as $grupoLabel => $marcas)
                                    @if(count($gruposMarcas) > 1)
                                        <optgroup label="{{ $grupoLabel }}">
                                            @foreach($marcas as $val => $lbl)
                                                <option value="{{ $val }}" {{ request('marca') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                            @endforeach
                                        </optgroup>
                                    @else
                                        @foreach($marcas as $val => $lbl)
                                            <option value="{{ $val }}" {{ request('marca') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                    
                    {{-- Selector de Formato de empaque (Lata, Botella, Vidrio, etc.) --}}
                    <div>
                        <select id="formato" name="formato" class="block w-full p-2.5 text-sm bg-slate-800 border-slate-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Cualquier Formato</option>
                            @foreach($formatos as $fmt)
                                <option value="{{ $fmt }}" {{ request('formato') == $fmt ? 'selected' : '' }}>{{ $fmt }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selector de Capacidad de volumen (cc / ml / Litros) --}}
                    <div>
                        <select id="capacidad" name="capacidad" class="block w-full p-2.5 text-sm bg-slate-800 border-slate-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Cualquier Capacidad</option>
                            @foreach($capacidades as $cap)
                                <option value="{{ $cap }}" {{ request('capacidad') == $cap ? 'selected' : '' }}>{{ $cap }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selector de Ventana de Vencimiento FEFO para priorización de mermas --}}
                    <div>
                        <select id="vencimiento" name="vencimiento" class="block w-full p-2.5 text-sm bg-slate-800 border-slate-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Cualquier Vencimiento</option>
                            <option value="vencidos" {{ request('vencimiento') == 'vencidos' ? 'selected' : '' }}>🚨 Vencidos (&lt; 0 días)</option>
                            <option value="1_mes" {{ in_array(request('vencimiento'), ['1_mes', '30_dias']) ? 'selected' : '' }}>⚡ Hasta 1 Mes (30 días)</option>
                            <option value="2_meses" {{ request('vencimiento') == '2_meses' ? 'selected' : '' }}>📅 Hasta 2 Meses (60 días)</option>
                            <option value="3_meses" {{ request('vencimiento') == '3_meses' ? 'selected' : '' }}>📆 Hasta 3 Meses (90 días)</option>
                            <option value="6_meses" {{ request('vencimiento') == '6_meses' ? 'selected' : '' }}>📦 Hasta 6 Meses (180 días)</option>
                            <option value="12_meses" {{ request('vencimiento') == '12_meses' ? 'selected' : '' }}>🎯 Hasta 12 Meses (365 días)</option>
                            <option value="optimos" {{ in_array(request('vencimiento'), ['optimos', 'buen_estado']) ? 'selected' : '' }}>🛡️ Óptimos (+12 meses)</option>
                        </select>
                    </div>
                    
                    {{-- Botón para resetear todos los campos de búsqueda y restablecer la consulta base --}}
                    <div>
                        <button type="button" onclick="window.resetFilters()" class="w-full px-4 py-2.5 text-sm font-medium text-slate-300 bg-slate-800 border border-slate-700 rounded-lg hover:bg-slate-700 focus:ring-4 focus:outline-none focus:ring-slate-800 transition-colors">
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            {{-- =========================================================================
                 CONTENEDOR DE LA TABLA DE STOCK CON CARGA DINÁMICA
                 ========================================================================= --}}
            <div class="bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative">
                
                {{-- Overlay de carga animado (Spinner) que se activa durante las peticiones AJAX --}}
                <div id="table-loader" class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px] z-10 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-200">
                    <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div class="p-6 text-slate-300 border-b border-slate-800/50">
                    
                    <h3 class="text-lg font-semibold text-white mb-4">Stock Consolidado</h3>
                    
                    {{-- Contenedor con scroll personalizado donde se inyecta el HTML de la tabla parcial --}}
                    <div class="overflow-x-auto overflow-y-auto max-h-[65vh] custom-scrollbar rounded-xl">
                        <div id="table-container">
                            {{-- Incluye la vista parcial con las filas y acordeones de productos --}}
                            @include('bodegas.partials.table', ['productos' => $productos])
                        </div>
                    </div>
                    {{-- Contenedor dinámico de enlaces de paginación sincronizado por JavaScript --}}
                    <div id="pagination-container" class="px-6 py-4 border-t border-slate-800/50 bg-slate-900/30"></div>
                </div>
            </div>
            
            {{-- =========================================================================
                 HISTORIAL DE AJUSTES (AUDITORÍA INMUTABLE)
                 Registro de todas las variaciones manuales de stock con opción a Deshacer
                 ========================================================================= --}}
            <div class="mt-8 bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative">
                <div class="p-6 text-slate-300">
                    <h3 class="text-lg font-semibold text-white mb-4">Historial de Ajustes (Auditoría)</h3>
                    
                    {{-- Vista de auditoría en tabla para pantallas de escritorio --}}
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
                                {{-- Itera sobre el historial de movimientos de ajuste --}}
                                @forelse($historial as $ajuste)
                                <tr class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors {{ $ajuste->es_reversion ? 'bg-orange-900/10' : '' }}">
                                    {{-- Fecha exacta de la auditoría --}}
                                    <td class="px-4 py-3">{{ $ajuste->created_at->format('d/m/Y H:i') }}</td>
                                    {{-- Lote y denominación del producto afectado --}}
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs text-slate-500">#{{ $ajuste->lote_stock_id }}</span><br>
                                        {{ $ajuste->lote->producto->nombre ?? 'N/A' }}
                                    </td>
                                    {{-- Nombre del operador responsable del movimiento --}}
                                    <td class="px-4 py-3">{{ $ajuste->usuario?->name ?? 'Sistema' }}</td>
                                    {{-- Motivo o justificación documentada --}}
                                    <td class="px-4 py-3">
                                        {{ $ajuste->motivo }}
                                        @if($ajuste->es_reversion)
                                            <span class="ml-2 text-xs text-orange-400 border border-orange-500/30 px-1 rounded">Reversión</span>
                                        @endif
                                    </td>
                                    {{-- Cantidad ajustada (+ verde o - roja) --}}
                                    <td class="px-4 py-3 text-right font-bold {{ $ajuste->diferencia > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $ajuste->diferencia > 0 ? '+' : '' }}{{ number_format($ajuste->diferencia, 0) }}
                                    </td>
                                    {{-- Botón para revertir el ajuste creando una contra-partida contable --}}
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

                    {{-- Vista de auditoría en tarjetas móviles para smartphones --}}
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
                                
                                {{-- Acción móvil para deshacer ajuste --}}
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

    {{-- =========================================================================
         MODAL: TRANSFERENCIA DE LOTE A OTRA BODEGA
         ========================================================================= --}}
    <x-transfer-lote-modal :bodegas="\App\Models\Bodega::where('activa', true)->orderBy('nombre')->get()" />
    
    {{-- =========================================================================
         MODAL: EDICIÓN DE FECHAS DE ELABORACIÓN Y VENCIMIENTO DEL LOTE
         Incluye calculadora reactiva de fecha de caducidad por meses de vida útil
         ========================================================================= --}}
    <div x-data="{
        open: false,
        loteId: null,
        loteFElab: '',
        loteFVenc: '',
        mesesVidaUtil: '',
        actionUrl: '',
        // Abre el modal y formatea las fechas iniciales en formato estándar AAAA-MM-DD
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
            
            // Si tiene fecha de elaboración y meses de vida útil definidos pero sin vencimiento, calcula automáticamente
            if (this.loteFElab && this.mesesVidaUtil && !this.loteFVenc) {
                this.calcularVencimiento();
            }
        },
        // Calcula la fecha de caducidad sumando los meses configurados a la fecha de elaboración
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
            {{-- Fondo oscurecido con desenfoque --}}
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
                {{-- Panel del modal --}}
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
                            
                            {{-- Input de Fecha de Elaboración --}}
                            <div>
                                <label for="fecha_elaboracion" class="block text-sm font-medium text-slate-300 mb-1">Fecha de Elaboración</label>
                                <input type="date" id="fecha_elaboracion" name="fecha_elaboracion" x-model="loteFElab" @change="calcularVencimiento" class="block w-full bg-slate-800 border-slate-700 text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                            </div>

                            {{-- Calculadora de vida útil en meses --}}
                            <div class="p-3 bg-indigo-900/20 border border-indigo-500/20 rounded-lg">
                                <label for="meses_vida_util" class="block text-xs font-medium text-indigo-300 mb-1">Calculadora: Vida Útil (Meses)</label>
                                <div class="flex gap-2">
                                    <input type="number" id="meses_vida_util" x-model="mesesVidaUtil" @input="calcularVencimiento" min="1" step="1" class="block w-full bg-slate-800 border-slate-700 text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm" placeholder="Ej. 6" />
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1">Si el envase dice "Consumir antes de 6 meses", ingresa 6 aquí y se calculará automáticamente.</p>
                            </div>

                            {{-- Input de Fecha de Vencimiento Definitiva --}}
                            <div>
                                <label for="fecha_vencimiento" class="block text-sm font-medium text-slate-300 mb-1">Fecha de Vencimiento Final <span class="text-red-400">*</span></label>
                                <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" x-model="loteFVenc" required class="block w-full bg-slate-800 border-slate-700 text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
                            </div>
                        </div>
                        
                        {{-- Botonera del modal de fechas --}}
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

    {{-- =========================================================================
         SLOT DE SCRIPTS: FILTRADO REACTIVO ASÍNCRONO (AJAX LIVE SEARCH)
         ========================================================================= --}}
    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Referencias a los elementos del DOM de los filtros
                const searchInput = document.getElementById('search');
                const marcaInput = document.getElementById('marca');
                const formatoSelect = document.getElementById('formato');
                const capacidadSelect = document.getElementById('capacidad');
                const vencimientoSelect = document.getElementById('vencimiento');
                const tableLoader = document.getElementById('table-loader');
                const tableContainer = document.getElementById('table-container');
                const paginationContainer = document.getElementById('pagination-container');
                
                let debounceTimer;

                /**
                 * Realiza la petición asíncrona hacia el controlador para actualizar el inventario.
                 * Construye los query parameters, actualiza el historial del navegador mediante pushState
                 * e inyecta la respuesta HTML en el contenedor de tabla y de enlaces de paginación.
                 */
                const fetchProductos = (urlStr) => {
                    // Muestra el indicador de carga visual
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
                    
                    // Actualiza la URL en la barra del navegador sin refrescar la página
                    window.history.pushState({}, '', url);

                    // Petición HTTP AJAX con cabecera XMLHttpRequest
                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                        
                        // Reactivar los componentes reactivos de Alpine.js en la tabla inyectada
                        if (window.Alpine) {
                            window.Alpine.initTree(tableContainer);
                        }
                        
                        // Extrae y sincroniza la barra de paginación del HTML devuelto
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
                        // Oculta el spinner de carga al terminar
                        tableLoader.classList.remove('opacity-100');
                        tableLoader.classList.add('opacity-0', 'pointer-events-none');
                    });
                };

                // Función con debounce de 300ms para evitar sobrecargar el servidor en cada pulsación del teclado
                const handleInput = () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => fetchProductos(), 300);
                };

                // Asignación de event listeners para los controles del formulario
                searchInput.addEventListener('input', handleInput);
                marcaInput.addEventListener('change', () => fetchProductos());
                formatoSelect.addEventListener('change', () => fetchProductos());
                capacidadSelect.addEventListener('change', () => fetchProductos());
                vencimientoSelect.addEventListener('change', () => fetchProductos());
                
                // Intercepta los clicks de paginación para navegar asíncronamente
                paginationContainer.addEventListener('click', (e) => {
                    const link = e.target.closest('a');
                    if (link) {
                        e.preventDefault();
                        fetchProductos(link.href);
                    }
                });
                
                // Exponer actualización asíncrona de tabla sin recarga de página
                window.refreshBodegaTable = () => fetchProductos();

                // Sistema de notificaciones Toast flotantes y ultra-rápidas para móvil y desktop
                window.showToast = (message, type = 'success') => {
                    const container = document.getElementById('toast-container');
                    if (!container) return;
                    const isSuccess = type === 'success';
                    const toast = document.createElement('div');
                    toast.className = `transform transition-all duration-300 -translate-y-2 opacity-0 max-w-sm w-full bg-slate-900/95 border ${isSuccess ? 'border-emerald-500/70 text-emerald-300' : 'border-rose-500/70 text-rose-300'} shadow-2xl rounded-xl p-3.5 text-sm flex items-center gap-3 pointer-events-auto backdrop-blur-md`;
                    toast.innerHTML = `
                        <div class="w-7 h-7 rounded-lg ${isSuccess ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'} flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                ${isSuccess ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>' : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>'}
                            </svg>
                        </div>
                        <span class="font-medium text-xs sm:text-sm flex-1 leading-tight">${message}</span>
                    `;
                    container.appendChild(toast);
                    requestAnimationFrame(() => {
                        toast.classList.remove('-translate-y-2', 'opacity-0');
                        toast.classList.add('translate-y-0', 'opacity-100');
                    });
                    setTimeout(() => {
                        toast.classList.remove('translate-y-0', 'opacity-100');
                        toast.classList.add('-translate-y-2', 'opacity-0');
                        setTimeout(() => toast.remove(), 300);
                    }, 3500);
                };

                // Interceptar eliminaciones y reversiones para ejecutarlas vía AJAX instantáneo
                document.addEventListener('submit', function(e) {
                    const form = e.target;
                    if (form && form.action && (form.action.includes('/eliminar') || form.action.includes('/revertir'))) {
                        e.preventDefault();
                        const formData = new FormData(form);
                        fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            window.showToast(data.message || 'Operación completada con éxito', data.success ? 'success' : 'error');
                            window.refreshBodegaTable();
                        })
                        .catch(() => {
                            // Fallback clásico
                            form.submit();
                        });
                    }
                });

                // Función global para limpiar filtros y volver al listado completo
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

    {{-- =========================================================================
         MODAL: INGRESO RÁPIDO DE STOCK
         Permite cargar existencias directamente en la bodega sin requerir recepción formal previa
         ========================================================================= --}}
    <div x-data="{
            open: false,
            searchQuery: '',
            searchResults: [],
            isLoading: false,
            
            // Detalles del producto seleccionado o creado en caliente
            productoId: '',
            nombre: '',
            sku: '',
            marca: '',
            formato: 'Botella',
            capacidad: '',
            formatoEmpaqueDefault: 'Display',
            unidadesPorEmpaqueDefault: 6,
            
            // Lotes existentes cargados desde la base de datos
            lotesExistentes: [],
            isLoadingLotes: false,
            
            // Colección dinámica de filas de lotes a ingresar (permite ingresar varios lotes con distintas fechas)
            lotesFilas: [
                {
                    modo: 'nuevo',
                    selectedLoteId: '',
                    formato: 'Display',
                    unidadesPorEmpaque: 24,
                    cantidadEmpaques: 1,
                    fechaElaboracion: '',
                    fechaVencimiento: ''
                }
            ],
            
            isSubmitting: false,

            // Calcula el total de unidades para una fila específica
            getFilaUnidades(fila) {
                const qty = parseFloat(fila.cantidadEmpaques) || 0;
                const mult = fila.formato === 'Unidad' ? 1 : (parseInt(fila.unidadesPorEmpaque) || 1);
                return qty * mult;
            },

            // Calcula automáticamente el total consolidado de todas las filas
            get totalUnidades() {
                return this.lotesFilas.reduce((sum, f) => sum + this.getFilaUnidades(f), 0);
            },

            // Agrega una nueva fila de lote con diferente fecha/cantidad
            addLoteFila() {
                this.lotesFilas.push({
                    modo: 'nuevo',
                    selectedLoteId: '',
                    formato: this.formatoEmpaqueDefault || 'Display',
                    unidadesPorEmpaque: this.unidadesPorEmpaqueDefault || 24,
                    cantidadEmpaques: 1,
                    fechaElaboracion: '',
                    fechaVencimiento: ''
                });
            },

            // Elimina una fila de lote
            removeLoteFila(index) {
                if (this.lotesFilas.length > 1) {
                    this.lotesFilas.splice(index, 1);
                }
            },

            // Atajo rápido para establecer vencimiento en una fila
            setFilaVencimientoMeses(index, meses) {
                const d = new Date();
                d.setMonth(d.getMonth() + meses);
                this.lotesFilas[index].fechaVencimiento = d.toISOString().split('T')[0];
            },

            // Al seleccionar un lote existente en una fila
            onSelectExistingLote(index) {
                const fila = this.lotesFilas[index];
                const found = this.lotesExistentes.find(l => l.id == fila.selectedLoteId);
                if (found) {
                    fila.fechaVencimiento = found.fecha_vencimiento ? found.fecha_vencimiento.substring(0, 10) : '';
                    fila.fechaElaboracion = found.fecha_elaboracion ? found.fecha_elaboracion.substring(0, 10) : '';
                }
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
                this.sku = '';
                this.marca = '';
                this.formato = 'Botella';
                this.capacidad = '';
                this.lotesExistentes = [];
                this.isLoadingLotes = false;
                this.lotesFilas = [
                    {
                        modo: 'nuevo',
                        selectedLoteId: '',
                        formato: 'Display',
                        unidadesPorEmpaque: 24,
                        cantidadEmpaques: 1,
                        fechaElaboracion: '',
                        fechaVencimiento: ''
                    }
                ];
            },

            // Búsqueda en el catálogo de productos con debounce
            searchProducts() {
                if (this.searchQuery.trim().length < 2) {
                    this.searchResults = [];
                    return;
                }
                this.isLoading = true;
                fetch('{{ route('catalogo.index') }}?search=' + encodeURIComponent(this.searchQuery), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    this.searchResults = data.data || data;
                })
                .finally(() => this.isLoading = false);
            },

            // Selecciona un producto sugerido, infiere formato/capacidad y carga sus lotes existentes
            selectProduct(prod) {
                this.productoId = prod.id;
                this.nombre = prod.nombre;
                this.sku = prod.sku || '';
                this.marca = prod.marca || '';
                this.formato = prod.formato || 'Botella';
                this.capacidad = prod.capacidad || '';
                
                // Formato por defecto
                let fmt = 'Display';
                let mult = 24;
                if (prod.formato) {
                    fmt = prod.formato;
                    const match = String(prod.formato).match(/\b(?:pack\s*)?(?:x\s*)?(\d+)\b/i);
                    if (match && parseInt(match[1]) > 1) {
                        mult = parseInt(match[1]);
                    }
                }
                this.formatoEmpaqueDefault = fmt;
                this.unidadesPorEmpaqueDefault = mult;

                // Reinicia las filas con el formato detectado
                this.lotesFilas = [
                    {
                        modo: 'nuevo',
                        selectedLoteId: '',
                        formato: fmt,
                        unidadesPorEmpaque: mult,
                        cantidadEmpaques: 1,
                        fechaElaboracion: '',
                        fechaVencimiento: ''
                    }
                ];

                // Cargar lotes físicos existentes de este producto en ESTA bodega
                this.isLoadingLotes = true;
                fetch('/catalogo/' + prod.id + '/lotes', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(lotes => {
                    this.lotesExistentes = Array.isArray(lotes) ? lotes.filter(l => l.bodega_id == {{ $bodega->id }}) : [];
                    if (this.lotesExistentes.length > 0) {
                        this.lotesFilas[0].modo = 'existente';
                        this.lotesFilas[0].selectedLoteId = this.lotesExistentes[0].id;
                        this.onSelectExistingLote(0);
                    }
                })
                .catch(() => {
                    this.lotesExistentes = [];
                })
                .finally(() => this.isLoadingLotes = false);

                this.searchQuery = '';
                this.searchResults = [];
            },

            // Permite registrar un producto nuevo sobre la marcha si no existe en el catálogo
            useNewProduct() {
                this.productoId = '';
                this.nombre = (this.searchQuery || '').trim().toUpperCase();
                this.sku = '';
                this.marca = '';
                this.formato = 'Botella';
                this.capacidad = '';
                this.lotesExistentes = [];
                this.lotesFilas = [
                    {
                        modo: 'nuevo',
                        selectedLoteId: '',
                        formato: 'Display',
                        unidadesPorEmpaque: 24,
                        cantidadEmpaques: 1,
                        fechaElaboracion: '',
                        fechaVencimiento: ''
                    }
                ];
                this.searchQuery = '';
                this.searchResults = [];
            },

            // Valida y envía el formulario de ingreso rápido mediante AJAX ultra-rápido
            submitForm() {
                if(this.totalUnidades <= 0) {
                    alert('La cantidad total de unidades debe ser mayor a 0');
                    return;
                }
                if(!this.nombre) {
                    alert('Debes indicar un nombre de producto');
                    return;
                }
                // Validar que cada nuevo lote tenga fecha de vencimiento
                for (let i = 0; i < this.lotesFilas.length; i++) {
                    const f = this.lotesFilas[i];
                    if (f.modo === 'nuevo' && !f.fechaVencimiento) {
                        alert('Debes ingresar la fecha de vencimiento para el Lote #' + (i + 1));
                        return;
                    }
                }
                this.isSubmitting = true;
                const form = document.getElementById('quick-add-form');
                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => { throw new Error(err.message || 'Error al procesar ingreso'); });
                    }
                    return res.json();
                })
                .then(data => {
                    this.closeModal();
                    if (window.showToast) {
                        window.showToast(data.message || 'Ingreso de stock guardado correctamente', 'success');
                    }
                    if (window.refreshBodegaTable) {
                        window.refreshBodegaTable();
                    }
                })
                .catch(err => {
                    alert(err.message || 'Ocurrió un error al guardar el ingreso');
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
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
         
         <div class="bg-slate-900 border border-slate-700 w-full max-w-2xl rounded-2xl shadow-2xl flex flex-col overflow-hidden max-h-[90vh]" @click.outside="closeModal()">
            
            {{-- Encabezado del modal de ingreso rápido --}}
            <div class="p-4 sm:p-5 border-b border-slate-800 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>Ingreso Rápido de Stock</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-normal border border-slate-700">{{ $bodega->nombre }}</span>
                </h3>
                <button @click="closeModal()" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-4 sm:p-6 overflow-y-auto custom-scrollbar flex-1 relative space-y-5">
                
                {{-- PASO 1: Buscar o Escribir Nombre de Producto --}}
                <div x-show="!nombre" class="space-y-4 relative">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Producto (Escribe para buscar o crear)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input x-ref="searchBox" type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()" @keydown.enter.prevent="useNewProduct()" placeholder="Ej: Cerveza Escudo, Cristal, Coca-Cola..." 
                            class="block w-full pl-10 p-3 bg-slate-800 border-slate-700 text-white rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    </div>

                    {{-- Lista desplegable de autocompletado y opción de creación directa --}}
                    <div x-show="searchQuery.length > 0" class="mt-2 bg-slate-800 border border-slate-700 rounded-lg overflow-hidden max-h-60 overflow-y-auto custom-scrollbar shadow-inner">
                        <div x-show="isLoading" class="p-4 text-center text-slate-400 text-sm">Buscando en catálogo...</div>
                        
                        <button type="button" @click="useNewProduct()" class="w-full text-left p-3 border-b border-emerald-700/50 bg-emerald-900/20 hover:bg-emerald-800/40 transition-colors flex flex-col gap-1">
                            <span class="text-emerald-400 font-bold">Crear: "<span x-text="searchQuery"></span>"</span>
                            <span class="text-xs text-slate-400">Presiona ENTER o haz clic aquí para dar de alta este producto</span>
                        </button>

                        <template x-for="prod in searchResults" :key="prod.id">
                            <button type="button" @click="selectProduct(prod)" class="w-full text-left p-3 border-b border-slate-700/50 hover:bg-slate-700 transition-colors flex flex-col gap-1">
                                <span class="text-white font-medium" x-text="prod.nombre"></span>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-indigo-400 font-mono font-bold" x-show="prod.sku">SKU: <span x-text="prod.sku"></span></span>
                                    <span class="text-slate-400" x-show="prod.capacidad" x-text="'• ' + prod.capacidad"></span>
                                    <span class="text-slate-400" x-show="prod.formato" x-text="'• ' + prod.formato"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- PASO 2: Configuración de Empaque, Capacidad y Lotes Múltiples --}}
                <div x-show="nombre" class="space-y-5" style="display: none;">
                    <form id="quick-add-form" action="{{ route('bodegas.quick-lote', $bodega) }}" method="POST">
                        @csrf
                        <input type="hidden" name="catalogo_producto_id" :value="productoId">
                        
                        {{-- Tarjeta con el producto seleccionado --}}
                        <div class="bg-slate-800/60 p-4 rounded-xl border border-slate-700/60 relative">
                            <button type="button" @click="resetState(); setTimeout(() => $refs.searchBox.focus(), 100)" class="absolute top-2 right-2 text-slate-400 hover:text-rose-400 p-1" title="Cambiar producto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-500/20 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0 pr-6">
                                    <h4 class="text-white font-bold text-base truncate mb-1">
                                        <input type="text" name="nombre" x-model="nombre" class="bg-transparent border-0 p-0 text-white font-bold text-base focus:ring-0 w-full" readonly>
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[11px] font-mono text-indigo-400 font-bold bg-indigo-950/70 border border-indigo-800/50 px-2 py-0.5 rounded" x-show="sku" x-text="sku"></span>
                                        <span class="text-xs text-slate-400">Bodega: <strong class="text-slate-200">{{ $bodega->nombre }}</strong></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Configuración de Formato y Capacidad del Producto --}}
                            <div class="mt-3 pt-3 border-t border-slate-700/60 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Formato de Envase --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                            Formato de Envase
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-normal">Tipo envase</span>
                                    </label>
                                    <select name="formato" x-model="formato"
                                            class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                                        <option value="Botella">Botella</option>
                                        <option value="Lata">Lata</option>
                                        <option value="Display">Display</option>
                                        <option value="Caja">Caja</option>
                                        <option value="Pack">Pack</option>
                                        <option value="Barril">Barril</option>
                                        <option value="Bidón">Bidón</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        <template x-for="f in ['Botella', 'Lata', 'Display', 'Pack']" :key="f">
                                            <button type="button" @click="formato = f" 
                                                    :class="formato === f ? 'bg-indigo-600 text-white border-indigo-400 shadow-sm' : 'bg-slate-800 text-slate-400 hover:text-white border-slate-700'"
                                                    class="text-[10px] font-bold px-2 py-0.5 rounded border transition-colors"
                                                    x-text="f"></button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Capacidad / Volumen --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                            Capacidad / Tamaño (Volumen)
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-normal">ej: 350 cc, 1.5 L</span>
                                    </label>
                                    <input type="text" name="capacidad" x-model="capacidad" list="capacidades-sugeridas"
                                           placeholder="Ej: 350 cc, 600 ml, 1.5 L, 1.6 L..."
                                           class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                                    <datalist id="capacidades-sugeridas">
                                        <option value="220 ml">
                                        <option value="237 cc">
                                        <option value="250 ml">
                                        <option value="330 cc">
                                        <option value="350 cc">
                                        <option value="355 ml">
                                        <option value="400 cc">
                                        <option value="470 cc">
                                        <option value="473 cc">
                                        <option value="500 cc">
                                        <option value="590 ml">
                                        <option value="600 ml">
                                        <option value="600 cc">
                                        <option value="650 cc">
                                        <option value="710 cc">
                                        <option value="1 L">
                                        <option value="1.5 L">
                                        <option value="1.6 L">
                                        <option value="1.75 L">
                                        <option value="2 L">
                                        <option value="2.5 L">
                                        <option value="3 L">
                                    </datalist>
                                    {{-- Botones rápidos de capacidad para un solo clic --}}
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        <template x-for="c in ['350 cc', '590 ml', '600 ml', '1.5 L', '1.6 L', '3 L']" :key="c">
                                            <button type="button" @click="capacidad = c" 
                                                    :class="capacidad === c ? 'bg-indigo-600 text-white border-indigo-400 shadow-sm' : 'bg-slate-800 text-slate-400 hover:text-white border-slate-700'"
                                                    class="text-[10px] font-bold px-2 py-0.5 rounded border transition-colors"
                                                    x-text="c"></button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN DE LOTES Y FECHAS DINÁMICAS (Soporte de uno o múltiples lotes) --}}
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        Lotes y Fechas de Vencimiento
                                    </h4>
                                    <p class="text-[11px] text-slate-400">Si la mercadería tiene distintas fechas de vencimiento, agrega cada lote por separado.</p>
                                </div>
                                
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-800 text-emerald-400 border border-slate-700" x-text="lotesFilas.length + ' Lote(s)'"></span>
                            </div>

                            {{-- Renderizado de cada fila de lote --}}
                            <div class="space-y-4">
                                <template x-for="(fila, index) in lotesFilas" :key="index">
                                    <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800 relative space-y-3 shadow-md">
                                        
                                        {{-- Cabecera de la fila del lote --}}
                                        <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold flex items-center justify-center border border-emerald-500/30" x-text="index + 1"></span>
                                                <span class="text-xs font-bold text-white">Lote #<span x-text="index + 1"></span></span>
                                                <span class="text-[11px] text-indigo-400 font-mono font-bold" x-text="'(' + getFilaUnidades(fila) + ' UN)'"></span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                {{-- Selector Lote Existente / Nuevo si hay disponibles --}}
                                                <template x-if="lotesExistentes.length > 0">
                                                    <div class="inline-flex rounded-lg bg-slate-800 p-0.5 border border-slate-700 text-[10px]">
                                                        <button type="button" @click="fila.modo = 'existente'; onSelectExistingLote(index)" 
                                                                :class="fila.modo === 'existente' ? 'bg-emerald-600 text-white font-bold' : 'text-slate-400 hover:text-white'"
                                                                class="px-2 py-0.5 rounded transition-colors">
                                                            Existente (<span x-text="lotesExistentes.length"></span>)
                                                        </button>
                                                        <button type="button" @click="fila.modo = 'nuevo'; fila.selectedLoteId = ''" 
                                                                :class="fila.modo === 'nuevo' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'"
                                                                class="px-2 py-0.5 rounded transition-colors">
                                                            + Nuevo
                                                        </button>
                                                    </div>
                                                </template>

                                                {{-- Botón para eliminar esta fila si hay más de 1 --}}
                                                <button type="button" x-show="lotesFilas.length > 1" @click="removeLoteFila(index)" class="text-slate-500 hover:text-rose-400 p-1 text-xs" title="Eliminar este lote">
                                                    ✕
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Inputs ocultos para esta fila en el envío del formulario --}}
                                        <input type="hidden" :name="'lotes[' + index + '][lote_stock_id]'" :value="fila.modo === 'existente' ? fila.selectedLoteId : ''">
                                        <input type="hidden" :name="'lotes[' + index + '][cantidad]'" :value="getFilaUnidades(fila)">
                                        <input type="hidden" :name="'lotes[' + index + '][fecha_elaboracion]'" :value="fila.fechaElaboracion">
                                        <input type="hidden" :name="'lotes[' + index + '][fecha_vencimiento]'" :value="fila.fechaVencimiento">

                                        {{-- Configuración de Empaque y Cantidad para este lote --}}
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div>
                                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">Empaque</label>
                                                <select x-model="fila.formato" class="w-full bg-slate-900 border-slate-700 text-white text-xs rounded-lg py-1.5 px-2.5">
                                                    <option value="Display">Display</option>
                                                    <option value="Caja">Caja</option>
                                                    <option value="Pack">Pack</option>
                                                    <option value="Unidad">Unidad / Suelto</option>
                                                </select>
                                            </div>

                                            <div x-show="fila.formato !== 'Unidad'">
                                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">Unidades x Empaque</label>
                                                <select x-model="fila.unidadesPorEmpaque" class="w-full bg-slate-900 border-slate-700 text-white text-xs rounded-lg py-1.5 px-2.5">
                                                    <option value="6">6 unidades</option>
                                                    <option value="12">12 unidades</option>
                                                    <option value="24">24 unidades</option>
                                                    <option value="32">32 unidades</option>
                                                    <option value="1">1 (Unidad)</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">Cantidad Empaques</label>
                                                <input type="number" min="1" step="1" x-model="fila.cantidadEmpaques" class="w-full bg-slate-900 border-slate-700 text-white text-xs rounded-lg py-1.5 px-2.5">
                                            </div>
                                        </div>

                                        {{-- Caso A: Seleccionar lote existente en esta bodega --}}
                                        <div x-show="fila.modo === 'existente' && lotesExistentes.length > 0" class="space-y-2">
                                            <label class="block text-[11px] text-slate-400">Sumar a lote físico existente:</label>
                                            <select x-model="fila.selectedLoteId" @change="onSelectExistingLote(index)" class="w-full bg-slate-900 border-slate-700 text-white text-xs rounded-lg p-2 focus:ring-emerald-500">
                                                <template x-for="l in lotesExistentes" :key="l.id">
                                                    <option :value="l.id" x-text="'Lote #' + l.id + ' — Vence: ' + (l.fecha_vencimiento ? l.fecha_vencimiento.substring(0, 10) : 'Sin Fecha') + ' (Saldo: ' + Math.round(l.cantidad_disponible) + ' UN)'"></option>
                                                </template>
                                            </select>
                                        </div>

                                        {{-- Caso B: Nuevo Lote con fechas independientes --}}
                                        <div x-show="fila.modo === 'nuevo' || lotesExistentes.length === 0" class="space-y-2">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">Fecha Elaboración</label>
                                                    <input type="date" x-model="fila.fechaElaboracion" class="w-full bg-slate-900 border-slate-700 rounded-lg text-xs text-white p-2 focus:ring-indigo-500">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] text-slate-400 uppercase font-semibold mb-1">
                                                        Fecha Vencimiento <span class="text-rose-400">*</span>
                                                    </label>
                                                    <input type="date" x-model="fila.fechaVencimiento" class="w-full bg-slate-900 border-slate-700 rounded-lg text-xs text-white p-2 focus:ring-emerald-500">
                                                </div>
                                            </div>
                                            {{-- Atajos rápidos de vencimiento para esta fila --}}
                                            <div class="flex items-center gap-1.5 pt-0.5">
                                                <span class="text-[10px] text-slate-500 font-medium">Vence en:</span>
                                                <button type="button" @click="setFilaVencimientoMeses(index, 3)" class="text-[10px] px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700">+3m</button>
                                                <button type="button" @click="setFilaVencimientoMeses(index, 6)" class="text-[10px] px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700">+6m</button>
                                                <button type="button" @click="setFilaVencimientoMeses(index, 12)" class="text-[10px] px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700">+1 año</button>
                                            </div>
                                        </div>

                                    </div>
                                </template>
                            </div>

                            {{-- Botón para agregar otro lote con distinta fecha --}}
                            <button type="button" @click="addLoteFila()" class="w-full py-2.5 px-3 rounded-xl border border-dashed border-indigo-500/40 hover:border-indigo-400 bg-indigo-950/20 hover:bg-indigo-900/30 text-indigo-300 hover:text-white text-xs font-semibold flex items-center justify-center gap-2 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>+ Agregar otro lote con distinta fecha de vencimiento</span>
                            </button>

                            {{-- Resumen Total Consolidado --}}
                            <div class="bg-gradient-to-r from-emerald-950/40 via-indigo-950/30 to-slate-900 p-4 rounded-xl border border-emerald-500/30 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] text-slate-400 block font-medium">Total Consolidado a Ingresar</span>
                                    <span class="text-xs text-emerald-400 font-semibold" x-text="lotesFilas.length + ' lote(s) en esta operación'"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-black text-white" x-text="new Intl.NumberFormat('es-CL').format(totalUnidades)"></span>
                                    <span class="text-xs text-emerald-400 font-bold ml-1">UN</span>
                                </div>
                            </div>
                        </div>

                        {{-- Botonera inferior del modal rápido --}}
                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-800">
                            <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-slate-300 bg-slate-800 border border-slate-600 rounded-lg hover:bg-slate-700 transition-colors">Cancelar</button>
                            <button type="button" @click="submitForm()" :disabled="isSubmitting" class="px-5 py-2 text-sm font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-500 shadow-lg shadow-emerald-500/20 transition-all disabled:opacity-50 flex items-center gap-2">
                                <span>Ingresar Stock</span>
                                <span class="text-xs opacity-80" x-text="'(' + totalUnidades + ' UN)'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para el ajuste masivo de existencias de la bodega --}}
    <x-ajuste-masivo-modal :bodega="$bodega" />
</x-app-layout>
