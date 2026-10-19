{{-- Componente Principal de Layout de la Aplicación --}}
<x-app-layout>
    {{-- Encabezado de la Vista: Título del Módulo de Ingreso Manual Rápido --}}
    <x-slot name="header">
        <h2 class="text-xl font-bold leading-tight text-emerald-400 flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
            {{ __('Ingreso Manual Rápido') }}
        </h2>
    </x-slot>

    {{-- Contenedor Principal con Estado Reactivo de Alpine.js: ingresoManualData() --}}
    <div class="py-6 sm:py-12" x-data="ingresoManualData()">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-slate-900/50 backdrop-blur-md shadow-lg sm:rounded-xl border border-slate-800/60 p-4 sm:p-6 text-slate-300">
                
                {{-- Formulario de Ingreso Directo a Bodega --}}
                <form id="ingreso-manual-form" action="{{ route('recepciones.manual.store') }}" method="POST">
                    @csrf
                    
                    {{-- Configuración General: Bodega, Proveedor y Documento de Referencia --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        {{-- Selector de Bodega Destino --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Bodega Destino <span class="text-rose-500">*</span></label>
                            <select name="bodega_id" required class="w-full bg-slate-950 border border-slate-800 rounded-lg text-slate-300 focus:ring-emerald-500 focus:border-emerald-500 text-base sm:text-sm">
                                <option value="">Seleccione Bodega</option>
                                @foreach($bodegas as $bodega)
                                    <option value="{{ $bodega->id }}" {{ old('bodega_id') == $bodega->id ? 'selected' : '' }}>{{ $bodega->nombre }}</option>
                                @endforeach
                            </select>
                            @error('bodega_id') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Selector de Proveedor con Botón Rápido "+ Nuevo" --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1 flex justify-between">
                                <span>Proveedor <span class="text-rose-500">*</span></span>
                                <button type="button" @click="openNuevoProveedor = true" class="text-indigo-400 hover:text-indigo-300 text-xs underline focus:outline-none">+ Nuevo</button>
                            </label>
                            <select name="proveedor_id" id="proveedor_id" x-model="proveedorId" required class="w-full bg-slate-950 border border-slate-800 rounded-lg text-slate-300 focus:ring-emerald-500 focus:border-emerald-500 text-base sm:text-sm">
                                <option value="">Seleccione Proveedor</option>
                                <template x-for="prov in proveedores" :key="prov.id">
                                    <option :value="prov.id" x-text="prov.nombre"></option>
                                </template>
                            </select>
                            @error('proveedor_id') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Input de Documento de Referencia (Factura / Guía) con validación anti-duplicados al perder el foco --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Documento Ref (N° Factura / Guía)</label>
                            <input type="text" name="documento_referencia" x-model="documentoReferencia" @blur="checkDocDuplicado()" value="{{ old('documento_referencia') }}" placeholder="Ej: Factura 1234" class="w-full bg-slate-950 border border-slate-800 rounded-lg text-slate-300 focus:ring-emerald-500 focus:border-emerald-500 text-base sm:text-sm font-mono uppercase">
                            <div x-show="docError" class="text-rose-400 text-xs mt-1 font-semibold" x-text="docError"></div>
                            @error('documento_referencia') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <hr class="border-slate-800 my-6">

                    {{-- Encabezado de la Tabla Dinámica de Productos y Botón de Agregar Fila --}}
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-emerald-400">Productos</h3>
                        <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-2 text-sm font-medium text-indigo-300 bg-indigo-900/30 rounded-lg border border-indigo-700/50 hover:bg-indigo-900/50 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Agregar Fila
                        </button>
                    </div>

                    {{-- Tabla de Ingreso Directo de Ítems --}}
                    <div class="overflow-x-auto bg-slate-950/50 rounded-lg border border-slate-800">
                        <table class="w-full text-left text-slate-400 whitespace-nowrap">
                            <thead class="text-xs uppercase bg-slate-900/80 border-b border-slate-800 text-slate-300">
                                <tr>
                                    <th class="px-3 py-3 min-w-[250px] w-full">Producto <span class="text-rose-500">*</span></th>
                                    <th class="px-3 py-3 w-24">Cant. <span class="text-rose-500">*</span></th>
                                    <th class="px-3 py-3 w-28">Precio</th>
                                    <th class="px-3 py-3 w-36">Elaboración</th>
                                    <th class="px-3 py-3 w-36">Vencimiento <span class="text-rose-500">*</span></th>
                                    <th class="px-3 py-3 w-16 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Renderizado Reactivo de Filas Alpine x-for sobre 'items' --}}
                                <template x-for="(item, index) in items" :key="item.id">
                                    <tr class="border-b border-slate-800/50 hover:bg-slate-900/50 transition-colors">
                                        {{-- Columna 1: Selector de Producto del Catálogo con Detalle de Envase --}}
                                        <td class="px-3 py-2 align-top">
                                            <div class="relative">
                                                <select :name="`items[${index}][catalogo_producto_id]`" x-model="item.producto_id" @change="onProductSelect(item)" required class="w-full bg-slate-950 border border-slate-800 rounded text-slate-300 focus:ring-emerald-500 focus:border-emerald-500 text-base sm:text-xs py-2 px-2">
                                                    <option value="">Buscar o seleccionar...</option>
                                                    @foreach($productos as $p)
                                                        <option value="{{ $p->id }}">{{ $p->sku }} - {{ $p->nombre }} {{ $p->formato ? '('.$p->formato.')' : '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div x-show="item.producto_id" class="text-[10px] text-slate-500 mt-1" x-text="getProductDetail(item.producto_id)"></div>
                                        </td>
                                        {{-- Columna 2: Cantidad Física con Unidad de Medida --}}
                                        <td class="px-3 py-2 align-top">
                                            <input type="number" :name="`items[${index}][cantidad]`" x-model="item.cantidad" min="0.01" step="0.01" required class="w-full bg-slate-950 border border-slate-800 rounded text-slate-300 focus:ring-emerald-500 focus:border-emerald-500 text-base sm:text-xs py-2 px-2">
                                            <div x-show="item.producto_id" class="text-[10px] text-slate-500 mt-1" x-text="getUM(item.producto_id)"></div>
                                        </td>
                                        {{-- Columna 3: Precio Unitario Referencial --}}
                                        <td class="px-3 py-2 align-top">
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none text-slate-500 text-xs">$</div>
                                                <input type="number" :name="`items[${index}][precio_unitario]`" x-model="item.precio" min="0" step="0.01" placeholder="0" class="w-full bg-slate-950 border border-slate-800 rounded text-slate-300 focus:ring-emerald-500 focus:border-emerald-500 text-base sm:text-xs py-2 pl-6 px-2">
                                            </div>
                                        </td>
                                        {{-- Columna 4: Fecha de Elaboración (Habilitada según meses de vencimiento) --}}
                                        <td class="px-3 py-2 align-top">
                                            <input type="date" :name="`items[${index}][fecha_elaboracion]`" x-model="item.fecha_elaboracion" @change="calcVencimiento(item)" :disabled="!needsElaboracion(item.producto_id)" :class="{'opacity-50 cursor-not-allowed bg-slate-900': !needsElaboracion(item.producto_id)}" class="w-full bg-slate-950 border border-slate-800 rounded text-slate-300 focus:ring-emerald-500 focus:border-emerald-500 text-base sm:text-xs py-2 px-2">
                                        </td>
                                        {{-- Columna 5: Fecha de Vencimiento (Obligatoria para control FEFO) --}}
                                        <td class="px-3 py-2 align-top">
                                            <input type="date" :name="`items[${index}][fecha_vencimiento]`" x-model="item.fecha_vencimiento" :readonly="needsElaboracion(item.producto_id)" :class="{'bg-slate-900 text-emerald-400 font-bold': needsElaboracion(item.producto_id) && item.fecha_vencimiento, 'bg-slate-950': !needsElaboracion(item.producto_id)}" required class="w-full border border-slate-800 rounded text-slate-300 focus:ring-emerald-500 focus:border-emerald-500 text-base sm:text-xs py-2 px-2">
                                        </td>
                                        {{-- Columna 6: Botón de Eliminación de la Fila --}}
                                        <td class="px-3 py-2 align-top text-center">
                                            <button type="button" @click="removeItem(index)" class="p-2 text-rose-400 hover:text-rose-300 hover:bg-rose-900/30 rounded-lg transition-colors" title="Quitar fila">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        {{-- Mensaje de Estado Vacío si la lista de filas no tiene elementos --}}
                        <div x-show="items.length === 0" class="p-8 text-center text-slate-500">
                            Presiona "Agregar Fila" para empezar a ingresar productos.
                        </div>
                    </div>

                    {{-- Botón de Confirmación con Protección Contra Doble Envío --}}
                    <div class="mt-8 flex justify-end">
                        <button type="submit" :disabled="items.length === 0 || isSubmitting" class="w-full sm:w-auto px-6 py-4 sm:py-3 text-lg sm:text-base font-bold text-white transition-all bg-emerald-600 rounded-xl hover:bg-emerald-500 shadow-lg shadow-emerald-900/20 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting"><svg class="w-6 h-6 sm:w-5 sm:h-5 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Confirmar Ingreso Directo</span>
                            <span x-show="isSubmitting"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal de Alta Rápida de Nuevo Proveedor (AJAX) --}}
        <div x-show="openNuevoProveedor" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="openNuevoProveedor = false" class="relative w-full max-w-md p-4 sm:p-6 mx-4 bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-xl font-bold text-slate-200">Nuevo Proveedor Rápido</h3>
                    <button @click="openNuevoProveedor = false" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                {{-- Campos del Modal de Proveedor --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Nombre/Razón Social <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="nuevoProv.nombre" class="w-full bg-slate-950 border border-slate-800 rounded-lg text-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-base sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">RUT</label>
                        <input type="text" x-model="nuevoProv.rut" class="w-full bg-slate-950 border border-slate-800 rounded-lg text-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-base sm:text-sm">
                    </div>
                    {{-- Acciones del Modal --}}
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="openNuevoProveedor = false" class="px-4 py-3 sm:py-2 text-base sm:text-sm font-medium text-slate-300 hover:text-white bg-slate-800 rounded-lg">Cancelar</button>
                        <button type="button" @click="saveProveedor()" :disabled="isSavingProv || !nuevoProv.nombre" class="px-4 py-3 sm:py-2 text-base sm:text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:opacity-50">
                            <span x-text="isSavingProv ? 'Guardando...' : 'Guardar y Usar'"></span>
                        </button>
                    </div>
                    <div x-show="provError" class="text-rose-500 text-sm mt-2" x-text="provError"></div>
                </div>
            </div>
        </div>

    </div>

    {{-- Scripts de Inicialización del Componente Reactivo Alpine.js --}}
    @push('head')
    <script>
        document.addEventListener('alpine:init', () => {
            /**
             * Componente Reactivo: ingresoManualData()
             * Maneja la interacción en memoria de las filas de productos, cálculo automático
             * de vencimiento y alta rápida de proveedores vía AJAX.
             */
            Alpine.data('ingresoManualData', () => ({
                proveedores: @json($proveedores),
                productosData: @json($productos->keyBy('id')),
                proveedorId: "{{ old('proveedor_id') }}",
                documentoReferencia: "{{ old('documento_referencia') }}",
                docError: '',
                items: [],
                nextId: 1,
                isSubmitting: false,
                
                // Estado del Modal de Alta de Proveedor
                openNuevoProveedor: false,
                isSavingProv: false,
                nuevoProv: { nombre: '', rut: '' },
                provError: '',

                /**
                 * Inicialización del componente al montar
                 */
                init() {
                    // Rehidratar desde old('items') si ocurrió un error de validación en el servidor
                    const oldItems = @json(old('items', []));
                    if(oldItems && oldItems.length > 0) {
                        Object.values(oldItems).forEach(item => {
                            this.items.push({
                                id: this.nextId++,
                                producto_id: item.catalogo_producto_id,
                                cantidad: item.cantidad,
                                precio: item.precio_unitario,
                                fecha_elaboracion: item.fecha_elaboracion,
                                fecha_vencimiento: item.fecha_vencimiento
                            });
                        });
                    } else {
                        // Comenzar con 1 fila vacía por defecto
                        this.addItem();
                    }

                    // Manejador del submit del formulario con validaciones previas
                    document.getElementById('ingreso-manual-form').addEventListener('submit', (e) => {
                        if(this.items.length === 0) {
                            e.preventDefault();
                            alert("Debe agregar al menos un producto.");
                            return;
                        }
                        if(this.docError) {
                            e.preventDefault();
                            alert("Corrija el error del documento/factura antes de continuar.");
                            return;
                        }
                        this.isSubmitting = true;
                    });
                },

                /**
                 * Valida en tiempo real que el número de factura no esté duplicado
                 */
                checkDocDuplicado() {
                    if(!this.proveedorId || !this.documentoReferencia || this.documentoReferencia.toUpperCase() === 'S/N') {
                        this.docError = '';
                        return;
                    }
                    fetch(`{{ route('recepciones.check-factura') }}?proveedor_id=${this.proveedorId}&numero_factura=${encodeURIComponent(this.documentoReferencia.trim())}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.exists) {
                                this.docError = '⚠️ ' + data.message;
                            } else {
                                this.docError = '';
                            }
                        })
                        .catch(() => { this.docError = ''; });
                },

                /**
                 * Añade una nueva fila al listado de productos
                 */
                addItem() {
                    this.items.push({
                        id: this.nextId++,
                        producto_id: '',
                        cantidad: 1,
                        precio: '',
                        fecha_elaboracion: '',
                        fecha_vencimiento: ''
                    });
                },

                /**
                 * Remueve una fila por su índice
                 */
                removeItem(index) {
                    this.items.splice(index, 1);
                },

                /**
                 * Obtiene los metadatos de un producto a partir de su ID
                 */
                getProductInfo(id) {
                    return id ? this.productosData[id] : null;
                },

                /**
                 * Gestiona el cambio de producto en una fila y alerta en caso de duplicidad
                 */
                onProductSelect(item) {
                    // Reiniciar fechas al cambiar el producto
                    item.fecha_elaboracion = '';
                    item.fecha_vencimiento = '';

                    // Comprobar si este producto ya fue seleccionado en otra fila para alertar al usuario
                    if(item.producto_id) {
                        const duplicates = this.items.filter(i => i.id !== item.id && String(i.producto_id) === String(item.producto_id));
                        if(duplicates.length > 0) {
                            alert("⚠️ Atención: Este producto ya se encuentra en otra fila. Si corresponde al mismo vencimiento, es recomendable incrementar la cantidad en la fila existente en lugar de duplicar productos.");
                        }
                    }
                },

                /**
                 * Retorna una cadena con el formato, capacidad y vida útil del producto
                 */
                getProductDetail(id) {
                    const p = this.getProductInfo(id);
                    if(!p) return '';
                    let details = [];
                    if(p.formato) details.push(p.formato);
                    if(p.capacidad) details.push(p.capacidad);
                    if(p.constante_vencimiento_meses) details.push(`Venc: ${p.constante_vencimiento_meses} meses`);
                    return details.join(' | ');
                },

                /**
                 * Retorna la unidad de medida del producto (ej: UN, Display, etc.)
                 */
                getUM(id) {
                    const p = this.getProductInfo(id);
                    return p ? p.unidad_medida : '';
                },

                /**
                 * Determina si el producto requiere cálculo por fecha de elaboración
                 */
                needsElaboracion(id) {
                    const p = this.getProductInfo(id);
                    return p && p.constante_vencimiento_meses > 0;
                },

                /**
                 * Calcula la fecha de vencimiento a partir de la fecha de elaboración y la vida útil en meses
                 */
                calcVencimiento(item) {
                    const p = this.getProductInfo(item.producto_id);
                    if(p && p.constante_vencimiento_meses > 0 && item.fecha_elaboracion) {
                        const date = new Date(item.fecha_elaboracion);
                        // Asegurar el desfase horario adecuado
                        date.setMinutes(date.getMinutes() + date.getTimezoneOffset()); 
                        
                        date.setMonth(date.getMonth() + p.constante_vencimiento_meses);
                        
                        // Formatear a YYYY-MM-DD
                        const yyyy = date.getFullYear();
                        const mm = String(date.getMonth() + 1).padStart(2, '0');
                        const dd = String(date.getDate()).padStart(2, '0');
                        
                        item.fecha_vencimiento = `${yyyy}-${mm}-${dd}`;
                    }
                },

                /**
                 * Guarda un nuevo proveedor vía AJAX y lo selecciona automáticamente en el selector
                 */
                saveProveedor() {
                    if(!this.nuevoProv.nombre) return;
                    this.isSavingProv = true;
                    this.provError = '';

                    fetch('{{ route("proveedores.ajax-store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.nuevoProv)
                    })
                    .then(response => response.json().then(data => ({ status: response.status, body: data })))
                    .then(({ status, body }) => {
                        this.isSavingProv = false;
                        if(status === 200 && body.success) {
                            this.proveedores.push(body.proveedor);
                            // Ordenar alfabéticamente
                            this.proveedores.sort((a,b) => a.nombre.localeCompare(b.nombre));
                            
                            // Seleccionar automáticamente
                            this.proveedorId = body.proveedor.id;
                            
                            // Cerrar y resetear modal
                            this.openNuevoProveedor = false;
                            this.nuevoProv = { nombre: '', rut: '' };
                        } else {
                            this.provError = body.message || 'Error al guardar el proveedor. Verifique que el RUT no esté duplicado.';
                        }
                    })
                    .catch(error => {
                        this.isSavingProv = false;
                        this.provError = 'Error de conexión.';
                        console.error(error);
                    });
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
