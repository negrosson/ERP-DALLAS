{{-- Componente Principal de Layout de la Aplicación --}}
<x-app-layout>
    {{-- Encabezado de la Vista: Información de la recepción, proveedor, bodega y estado --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                {{-- Título con ícono de calendario / asignación de fechas --}}
                <h2 class="text-xl font-bold leading-tight text-slate-100 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ __('Asignación de Fechas y Lotes') }}
                </h2>
                {{-- Metadatos resumidos: Folio de Factura, Proveedor y Bodega Destino --}}
                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-400">
                    <span>Factura: <strong class="font-mono text-indigo-300">#{{ $recepcion->numero_factura ?? $recepcion->documento_referencia ?? 'S/N' }}</strong></span>
                    <span>•</span>
                    <span>Proveedor: <strong class="text-slate-200">{{ $recepcion->proveedor->nombre }}</strong></span>
                    <span>•</span>
                    <span>Bodega Destino: <strong class="text-emerald-400">[{{ $recepcion->bodega->codigo }}] {{ $recepcion->bodega->nombre }}</strong></span>
                </div>
            </div>
            {{-- Badge de estado: Pendiente de Fechas / Lotes con punto animado ámbar --}}
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse mr-2"></span>
                    Pendiente de Fechas / Lotes
                </span>
            </div>
        </div>
    </x-slot>

    {{-- Contenedor Principal de la Página con Espaciado Inferior para la Barra Fija (Sticky Bar) --}}
    <div class="py-6 min-h-screen bg-slate-950 text-slate-200 font-sans pb-32">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">
            
            {{-- Mensaje de Error de Sesión Flash --}}
            @if(session('error'))
                <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-300 text-sm flex items-start gap-3 shadow-lg" role="alert">
                    <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>
                        <strong class="font-bold text-rose-200">Error:</strong>
                        <p class="mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Banner Guía Operativa para Control Físico de Mercadería y Lotes --}}
            <div class="bg-gradient-to-r from-slate-900 to-indigo-950/40 border border-slate-800 rounded-2xl p-5 shadow-lg">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 bg-indigo-500/10 rounded-xl text-indigo-400 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <div class="text-xs text-slate-300 space-y-1">
                        <p class="font-bold text-sm text-slate-100">Control de Empaque, Envases y Vencimiento</p>
                        <p class="text-slate-400 leading-relaxed">
                            El sistema detecta automáticamente los productos del catálogo por código de proveedor o similitud. Si algún ítem físico <strong>no existe en tu catálogo</strong>, puedes darlo de alta inmediatamente con el botón <em>"+ Crear en Catálogo"</em>. Selecciona el empaque (<strong>1x, x6, x12, x24</strong>) y asigna las fechas de vencimiento. Si un producto llegó con dos fechas distintas, usa <em>"+ Dividir Lote"</em>.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Formulario Principal de Conciliación y Entrada a Inventario FEFO --}}
            <form method="POST" action="{{ route('recepciones.conciliar.store', $recepcion) }}" id="conciliarForm">
                @csrf

                {{-- Contenedor dinámico de Cards de Productos --}}
                <div id="contenedor-cards" class="space-y-6">
                    @foreach($recepcion->detalles as $index => $detalle)
                        @php
                            $producto = $detalle->catalogoProducto;
                            $parsed = $detalle->parsed_info ?? [];
                            $esNuevo = $detalle->es_nuevo ?? (!$producto || !$producto->activo);
                            $meses = $producto->constante_vencimiento_meses ?? 6;
                            $formatoActual = old("detalles.{$index}.formato", $producto->formato ?? ($parsed['formato'] ?? 'Lata'));
                            $capacidadActual = old("detalles.{$index}.capacidad", $producto->capacidad ?? ($parsed['capacidad'] ?? '350 cc'));
                            $empaqueFactor = $parsed['empaque_factor'] ?? 1;
                            $nombreItem = $detalle->descripcion_proveedor ?: ($producto?->nombre ?: ($parsed['nombre_sugerido'] ?? 'Producto Desconocido'));
                            $precioRefUnitario = $detalle->precio_unitario > 0 ? round((float)$detalle->precio_unitario / max(1, $empaqueFactor), 2) : ($producto?->precio_compra_ref ?? 0);
                        @endphp
                        
                        {{-- Tarjeta Individual de Producto/Lote --}}
                        <div class="producto-card bg-slate-900 border {{ $esNuevo ? 'border-amber-500/50' : 'border-slate-800' }} rounded-2xl shadow-xl shadow-black/40 overflow-hidden transition-all duration-200" data-card-index="{{ $index }}" data-empaque-sugerido="{{ $empaqueFactor }}">
                            
                            {{-- Cabecera del Card: Numeración, Nombre de Producto, SKU y Botones de Acción --}}
                            <div class="px-5 py-4 bg-slate-950/70 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    {{-- Badge con el número de fila / ítem --}}
                                    <span class="w-7 h-7 rounded-lg {{ $esNuevo ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' }} font-bold text-xs flex items-center justify-center shrink-0">
                                        #{{ $loop->iteration }}
                                    </span>
                                    <div>
                                        {{-- Nombre del Producto --}}
                                        <h3 class="text-sm font-bold text-slate-100 font-mono tracking-tight" id="titulo-prod-{{ $index }}">
                                            {{ $producto ? $producto->nombre : $nombreItem }}
                                        </h3>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            {{-- SKU Maestro asignado o estado pendiente --}}
                                            <span class="text-[11px] text-slate-400 font-mono" id="sku-prod-{{ $index }}">
                                                SKU: {{ $producto ? $producto->sku : 'PENDIENTE' }}
                                            </span>
                                            @if($detalle->codigo_proveedor_usado)
                                                <span class="text-[10px] bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded font-mono border border-slate-700">
                                                    Cód. Factura: {{ $detalle->codigo_proveedor_usado }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Botones de Acción: Dividir Lote y Eliminar Ítem --}}
                                <div class="flex items-center gap-2 self-end sm:self-auto">
                                    {{-- Botón Dividir Lote (para mercadería con dos fechas de vencimiento diferentes) --}}
                                    <button type="button" onclick="duplicarLote({{ $index }})" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-cyan-300 bg-cyan-950/50 hover:bg-cyan-900/60 border border-cyan-700/50 rounded-lg transition-all" title="Si este producto viene con dos fechas de vencimiento distintas">
                                        <svg class="w-3.5 h-3.5 mr-1 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        + Dividir Lote
                                    </button>
                                    {{-- Botón para remover la tarjeta de la recepción actual --}}
                                    <button type="button" onclick="eliminarCard(this)" class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold text-rose-400 hover:text-rose-200 bg-rose-950/40 hover:bg-rose-900/60 border border-rose-800/40 rounded-lg transition-all" title="Eliminar ítem de la recepción">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Campos Ocultos: ID del detalle y constante de vencimiento en meses del producto --}}
                            <input type="hidden" name="detalles[{{ $index }}][id]" value="{{ $detalle->id }}">
                            <input type="hidden" id="constante_{{ $index }}" value="{{ $meses }}">

                            {{-- Cuerpo del Card: Formularios de Vinculación, Empaque, Cantidades y Fechas --}}
                            <div class="p-5 space-y-5">
                                
                                {{-- Banner de Advertencia Si el producto no está en el catálogo oficial --}}
                                <div id="banner-nuevo-{{ $index }}" class="{{ $esNuevo ? 'block' : 'hidden' }} bg-gradient-to-r from-amber-950/50 to-slate-900 border border-amber-500/40 rounded-xl p-4 shadow-md">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 uppercase tracking-wide">
                                                    ⚠️ Producto No Registrado en Catálogo
                                                </span>
                                                @if($detalle->codigo_proveedor_usado)
                                                    <span class="text-xs font-mono text-slate-400">Cód: {{ $detalle->codigo_proveedor_usado }}</span>
                                                @endif
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-100 mt-1">
                                                "{{ $nombreItem }}"
                                            </h4>
                                            <p class="text-xs text-slate-400 mt-0.5">
                                                Este producto de la factura no existe en tu inventario. ¿Deseas darlo de alta ahora mismo con sus atributos detectados o vincularlo a uno existente?
                                            </p>
                                        </div>
                                        {{-- Botón para abrir modal de creación rápida de producto --}}
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" 
                                                    onclick="abrirModalCrearProducto({{ $index }}, '{{ addslashes($detalle->codigo_proveedor_usado) }}', '{{ addslashes($parsed['nombre_sugerido'] ?? $nombreItem) }}', '{{ addslashes($formatoActual) }}', '{{ addslashes($capacidadActual) }}', {{ (float)$precioRefUnitario }})" 
                                                    class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-[0_0_15px_rgba(16,185,129,0.3)] transition-all flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                <span>✨ Crear en Catálogo</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Cuadrícula de 3 Columnas: Catálogo Maestro, Formato y Capacidad --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    {{-- Selector de Producto Maestro del Catálogo --}}
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                            Vincular Producto Catálogo <span class="text-rose-400">*</span>
                                        </label>
                                        <select name="detalles[{{ $index }}][catalogo_producto_id]" 
                                                id="select-prod-{{ $index }}" 
                                                onchange="cambiarProductoCatalogo({{ $index }}, this)" 
                                                class="block w-full bg-slate-950 border-slate-800 text-slate-200 text-xs rounded-xl focus:border-indigo-500 focus:ring-indigo-500 font-sans" 
                                                required>
                                            @if(!$producto || $esNuevo)
                                                <option value="" selected disabled>-- ⚠️ Selecciona o pulsa "Crear en Catálogo" --</option>
                                            @endif
                                            @foreach($productosCatalogo as $prodCat)
                                                <option value="{{ $prodCat->id }}" 
                                                    data-nombre="{{ $prodCat->nombre }}" 
                                                    data-sku="{{ $prodCat->sku }}" 
                                                    data-meses="{{ $prodCat->constante_vencimiento_meses ?? 0 }}"
                                                    data-formato="{{ $prodCat->formato }}"
                                                    data-capacidad="{{ $prodCat->capacidad }}"
                                                    {{ ($producto && $prodCat->id == $producto->id) ? 'selected' : '' }}>
                                                    {{ $prodCat->nombre }} ({{ $prodCat->sku }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Selector de Tipo de Envase / Formato --}}
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                            Tipo de Envase
                                        </label>
                                        <select name="detalles[{{ $index }}][formato]" id="formato_{{ $index }}" class="block w-full bg-slate-950 border-slate-800 text-slate-200 text-xs rounded-xl focus:border-indigo-500 focus:ring-indigo-500 font-sans">
                                            <option value="Lata" {{ $formatoActual == 'Lata' ? 'selected' : '' }}>🥫 Lata</option>
                                            <option value="Botella Vidrio" {{ $formatoActual == 'Botella Vidrio' ? 'selected' : '' }}>🍾 Botella Vidrio</option>
                                            <option value="Botella PET" {{ $formatoActual == 'Botella PET' || $formatoActual == 'Botella Desechable (PET)' ? 'selected' : '' }}>🧴 Botella Desechable (PET)</option>
                                            <option value="Botella Retornable" {{ $formatoActual == 'Botella Retornable' ? 'selected' : '' }}>♻️ Botella Retornable</option>
                                            <option value="Tetrapak" {{ $formatoActual == 'Tetrapak' ? 'selected' : '' }}>📦 Tetrapak</option>
                                            <option value="Barril / Keg" {{ $formatoActual == 'Barril / Keg' ? 'selected' : '' }}>🛢️ Barril / Keg</option>
                                        </select>
                                    </div>

                                    {{-- Campo de Capacidad en volumen --}}
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                            Capacidad
                                        </label>
                                        <input type="text" name="detalles[{{ $index }}][capacidad]" id="capacidad_{{ $index }}" value="{{ $capacidadActual }}" placeholder="Ej: 237 cc, 350 cc, 1.5 L" class="block w-full bg-slate-950 border-slate-800 text-slate-200 text-xs rounded-xl focus:border-indigo-500 focus:ring-indigo-500 font-sans">
                                    </div>
                                </div>

                                {{-- Sección de Selección de Empaque y Factor Multiplicador (x1, x6, x12, x24) --}}
                                <div class="bg-slate-950/60 border border-slate-800/80 rounded-xl p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                                        <div>
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Unidades a Ingresar en Stock</span>
                                            <p class="text-[11px] text-slate-500">Selecciona el multiplicador si llegó en cajas o packs, o ingresa las unidades sueltas directamente.</p>
                                        </div>
                                        
                                        {{-- Selector de Empaque Rápido con Píldoras de Factor --}}
                                        <div class="flex items-center gap-1.5 self-start sm:self-auto bg-slate-900 p-1 rounded-xl border border-slate-800">
                                            <button type="button" onclick="setMultiplicador({{ $index }}, 1)" id="btn-mult-{{ $index }}-1" class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white">
                                                1x (Unidad)
                                            </button>
                                            <button type="button" onclick="setMultiplicador({{ $index }}, 6)" id="btn-mult-{{ $index }}-6" class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white">
                                                x6 (Six-Pack)
                                            </button>
                                            <button type="button" onclick="setMultiplicador({{ $index }}, 12)" id="btn-mult-{{ $index }}-12" class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white">
                                                x12 (Caja 12)
                                            </button>
                                            <button type="button" onclick="setMultiplicador({{ $index }}, 24)" id="btn-mult-{{ $index }}-24" class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white">
                                                x24 (Caja 24)
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                                        {{-- Control auxiliar de cálculo por número de cajas / packs --}}
                                        <div id="caja-packs-wrapper-{{ $index }}" class="hidden">
                                            <label class="block text-[11px] font-semibold text-slate-400 mb-1">
                                                N° de Cajas / Packs <span id="label-empaque-{{ $index }}" class="text-indigo-400"></span>
                                            </label>
                                            <div class="flex items-center gap-2">
                                                <input type="number" id="cajas_{{ $index }}" min="1" step="1" value="{{ (float)$detalle->cantidad > 0 ? (float)$detalle->cantidad : 1 }}" oninput="calcularDesdeCajas({{ $index }})" class="block w-full bg-slate-900 border-slate-700 text-slate-100 text-sm font-bold rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                                                <span class="text-xs text-slate-400 font-bold" id="formula-{{ $index }}">&times; 1</span>
                                            </div>
                                        </div>

                                        {{-- Cantidad Total Final de Unidades Físicas (Almacenada en base de datos) --}}
                                        <div class="sm:col-span-1">
                                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                                Total Unidades Físicas <span class="text-emerald-400">*</span>
                                            </label>
                                            <div class="relative">
                                                <input type="number" 
                                                       name="detalles[{{ $index }}][cantidad]" 
                                                       id="cant_{{ $index }}" 
                                                       value="{{ old('detalles.'.$index.'.cantidad', (float)$detalle->cantidad > 0 ? (float)$detalle->cantidad : 1) }}" 
                                                       min="0.1" 
                                                       step="any"
                                                       oninput="recalcularTotalGeneral()"
                                                       class="block w-full bg-slate-900 border-emerald-500/50 text-emerald-300 text-base font-bold rounded-xl focus:border-emerald-400 focus:ring-emerald-400 pl-4 pr-12 py-2.5 shadow-[inset_0_1px_3px_rgba(0,0,0,0.5)]" 
                                                       required>
                                                <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs font-bold text-slate-500 pointer-events-none">
                                                    UN
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Sección de Fechas: Elaboración y Vencimiento --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                    {{-- Fecha de Elaboración (Calcula automáticamente la fecha de vencimiento con la constante) --}}
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                            Fecha Elaboración
                                            <span class="text-[10px] text-indigo-400 font-normal ml-1">(Calcula vencimiento autom.)</span>
                                        </label>
                                        <div class="relative">
                                            <input type="date" 
                                                   name="detalles[{{ $index }}][fecha_elaboracion]" 
                                                   id="elab_{{ $index }}" 
                                                   value="{{ old('detalles.'.$index.'.fecha_elaboracion', $detalle->fecha_elaboracion ? \Carbon\Carbon::parse($detalle->fecha_elaboracion)->format('Y-m-d') : '') }}"
                                                   onchange="calcularVencimientoPorElab({{ $index }})"
                                                   class="block w-full bg-slate-950 border-slate-800 text-slate-200 text-sm rounded-xl focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                                        </div>
                                    </div>

                                    {{-- Fecha de Vencimiento con badge de días restantes --}}
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 flex items-center justify-between">
                                            <span>Fecha de Vencimiento <span class="text-rose-400">*</span></span>
                                            <span id="badge-dias-{{ $index }}" class="text-[10px] text-slate-500 font-normal"></span>
                                        </label>
                                        <div class="relative">
                                            <input type="date" 
                                                   name="detalles[{{ $index }}][fecha_vencimiento]" 
                                                   id="venc_{{ $index }}" 
                                                   value="{{ old('detalles.'.$index.'.fecha_vencimiento', $detalle->fecha_vencimiento ? \Carbon\Carbon::parse($detalle->fecha_vencimiento)->format('Y-m-d') : '') }}"
                                                   onchange="actualizarBadgeDias({{ $index }})"
                                                   class="block w-full bg-slate-950 border-indigo-500/50 text-indigo-200 text-sm font-bold rounded-xl focus:border-indigo-400 focus:ring-indigo-400 py-2.5 shadow-sm" 
                                                   required>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Barra Fija Inferior (Sticky Bar Responsiva para confirmación de recepción) --}}
                <div class="fixed bottom-0 left-0 right-0 z-40 bg-slate-900/90 backdrop-blur-md border-t border-slate-800 shadow-[0_-10px_25px_rgba(0,0,0,0.5)] p-4">
                    <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                        {{-- Contadores totales en vivo: Cantidad de productos y suma de unidades físicas --}}
                        <div class="flex items-center gap-4 text-xs text-slate-400 self-start sm:self-auto">
                            <div>
                                <span class="block text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Productos</span>
                                <span class="text-sm font-bold text-slate-200" id="total-productos-counter">{{ count($recepcion->detalles) }}</span>
                            </div>
                            <div class="h-6 w-px bg-slate-800"></div>
                            <div>
                                <span class="block text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Total Unidades</span>
                                <span class="text-base font-bold text-emerald-400" id="total-unidades-counter">0</span>
                            </div>
                        </div>

                        {{-- Botones de Navegación: Cancelar e Ingresar Stock FEFO --}}
                        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                            <a href="{{ route('recepciones.index') }}" class="w-full sm:w-auto text-center px-5 py-2.5 text-xs font-bold text-slate-400 hover:text-white transition-colors bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700">
                                Cancelar
                            </a>
                            <button type="submit" id="btn-confirmar-stock" class="w-full sm:w-auto px-6 py-2.5 text-xs font-bold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-[0_0_15px_rgba(79,70,229,0.4)] flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Confirmar e Ingresar Stock FEFO</span>
                            </button>
                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>

    {{-- Modal Emergente para Crear Nuevo Producto en Catálogo Maestro --}}
    <div id="modal-crear-producto" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl shadow-black relative">
            {{-- Encabezado del Modal con botón de cerrar --}}
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="p-2 bg-emerald-500/10 text-emerald-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-slate-100">Alta de Nuevo Producto en Catálogo</h3>
                        <p class="text-xs text-slate-400">Se registrará oficialmente en el catálogo maestro y quedará vinculado.</p>
                    </div>
                </div>
                <button type="button" onclick="cerrarModalCrearProducto()" class="text-slate-400 hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Formulario de Registro Express de Producto en Catálogo --}}
            <form id="form-quick-producto" class="mt-4 space-y-4" onsubmit="enviarQuickProducto(event)">
                <input type="hidden" id="modal-card-idx" value="">
                <input type="hidden" id="modal-cod-prov" value="">
                <input type="hidden" id="modal-prov-id" value="{{ $recepcion->proveedor_id }}">

                {{-- Campo: Nombre Oficial --}}
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Nombre Oficial en Catálogo *</label>
                    <input type="text" id="modal-nombre" required class="block w-full bg-slate-950 border-slate-800 text-slate-100 text-sm rounded-xl focus:border-emerald-500 focus:ring-emerald-500 py-2.5">
                </div>

                {{-- Cuadrícula 2 Columnas: SKU y Formato --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">SKU Maestro</label>
                        <input type="text" id="modal-sku" placeholder="Ej: COC-VID-237ML" class="block w-full bg-slate-950 border-slate-800 text-slate-100 text-xs font-mono rounded-xl focus:border-emerald-500 focus:ring-emerald-500 py-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Tipo de Envase</label>
                        <select id="modal-formato" class="block w-full bg-slate-950 border-slate-800 text-slate-200 text-xs rounded-xl focus:border-emerald-500 focus:ring-emerald-500 py-2.5">
                            <option value="Lata">🥫 Lata</option>
                            <option value="Botella Vidrio">🍾 Botella Vidrio</option>
                            <option value="Botella PET">🧴 Botella Desechable (PET)</option>
                            <option value="Botella Retornable">♻️ Botella Retornable</option>
                            <option value="Tetrapak">📦 Tetrapak</option>
                            <option value="Barril / Keg">🛢️ Barril / Keg</option>
                        </select>
                    </div>
                </div>

                {{-- Cuadrícula 2 Columnas: Capacidad y Meses de Vida Útil --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Capacidad</label>
                        <input type="text" id="modal-capacidad" placeholder="Ej: 237 cc, 350 cc, 1.5 L" class="block w-full bg-slate-950 border-slate-800 text-slate-100 text-xs rounded-xl focus:border-emerald-500 focus:ring-emerald-500 py-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Vida Útil (Meses)</label>
                        <input type="number" id="modal-meses" value="6" min="1" max="60" class="block w-full bg-slate-950 border-slate-800 text-slate-100 text-xs rounded-xl focus:border-emerald-500 focus:ring-emerald-500 py-2.5">
                    </div>
                </div>

                {{-- Cuadrícula 2 Columnas: Precios de Referencia (Compra y Venta) --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Precio Compra Ref (Un.)</label>
                        <input type="number" id="modal-precio-compra" step="any" min="0" class="block w-full bg-slate-950 border-slate-800 text-slate-100 text-xs rounded-xl focus:border-emerald-500 focus:ring-emerald-500 py-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Precio Venta (PVP)</label>
                        <input type="number" id="modal-precio-venta" step="any" min="0" placeholder="Ej: 1000" class="block w-full bg-slate-950 border-slate-800 text-slate-100 text-xs rounded-xl focus:border-emerald-500 focus:ring-emerald-500 py-2.5">
                    </div>
                </div>

                {{-- Acciones del Modal: Cancelar o Guardar y Vincular --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" onclick="cerrarModalCrearProducto()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white rounded-xl bg-slate-800">
                        Cancelar
                    </button>
                    <button type="submit" id="btn-modal-guardar" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-lg transition-all flex items-center gap-2">
                        <span>✨ Guardar y Vincular</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- PLANTILLA OCULTA: Utilizada para clonar Cards al presionar "+ Dividir Lote"  --}}
    {{-- ========================================================================= --}}
    <template id="card-template">
        <div class="producto-card bg-slate-900 border border-slate-800 rounded-2xl shadow-xl shadow-black/40 overflow-hidden transition-all duration-200" data-card-index="{INDEX}">
            {{-- Cabecera del Card Clonado --}}
            <div class="px-5 py-4 bg-slate-950/70 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 font-bold text-xs flex items-center justify-center shrink-0">
                        +Lote
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-100 font-mono tracking-tight" id="titulo-prod-{INDEX}">
                            {NOMBRE}
                        </h3>
                        <span class="text-[11px] text-slate-400 font-mono" id="sku-prod-{INDEX}">SKU: {SKU}</span>
                    </div>
                </div>
                {{-- Botón para eliminar el sublote clonado --}}
                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <button type="button" onclick="eliminarCard(this)" class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold text-rose-400 hover:text-rose-200 bg-rose-950/40 hover:bg-rose-900/60 border border-rose-800/40 rounded-lg transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>

            {{-- Campos ocultos del sublote --}}
            <input type="hidden" name="detalles[{INDEX}][id]" value="">
            <input type="hidden" id="constante_{INDEX}" value="{MESES}">

            {{-- Formulario del sublote clonado --}}
            <div class="p-5 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Selector de Producto del Catálogo --}}
                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                            Vincular Producto Catálogo <span class="text-rose-400">*</span>
                        </label>
                        <select name="detalles[{INDEX}][catalogo_producto_id]" id="select-prod-{INDEX}" onchange="cambiarProductoCatalogo({INDEX}, this)" class="block w-full bg-slate-950 border-slate-800 text-slate-200 text-xs rounded-xl focus:border-indigo-500 focus:ring-indigo-500 font-sans" required>
                            @foreach($productosCatalogo as $prodCat)
                                <option value="{{ $prodCat->id }}" 
                                    data-nombre="{{ $prodCat->nombre }}" 
                                    data-sku="{{ $prodCat->sku }}" 
                                    data-meses="{{ $prodCat->constante_vencimiento_meses ?? 0 }}"
                                    data-formato="{{ $prodCat->formato }}"
                                    data-capacidad="{{ $prodCat->capacidad }}">
                                    {{ $prodCat->nombre }} ({{ $prodCat->sku }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tipo de Envase --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                            Tipo de Envase
                        </label>
                        <select name="detalles[{INDEX}][formato]" id="formato_{INDEX}" class="block w-full bg-slate-950 border-slate-800 text-slate-200 text-xs rounded-xl focus:border-indigo-500 focus:ring-indigo-500 font-sans">
                            <option value="Lata">🥫 Lata</option>
                            <option value="Botella Vidrio">🍾 Botella Vidrio</option>
                            <option value="Botella PET">🧴 Botella Desechable (PET)</option>
                            <option value="Botella Retornable">♻️ Botella Retornable</option>
                            <option value="Tetrapak">📦 Tetrapak</option>
                            <option value="Barril / Keg">🛢️ Barril / Keg</option>
                        </select>
                    </div>

                    {{-- Capacidad --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                            Capacidad
                        </label>
                        <input type="text" name="detalles[{INDEX}][capacidad]" id="capacidad_{INDEX}" value="{CAPACIDAD}" class="block w-full bg-slate-950 border-slate-800 text-slate-200 text-xs rounded-xl focus:border-indigo-500 focus:ring-indigo-500 font-sans">
                    </div>
                </div>

                {{-- Empaque y Unidades --}}
                <div class="bg-slate-950/60 border border-slate-800/80 rounded-xl p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Unidades a Ingresar en Stock</span>
                        </div>
                        <div class="flex items-center gap-1.5 self-start sm:self-auto bg-slate-900 p-1 rounded-xl border border-slate-800">
                            <button type="button" onclick="setMultiplicador({INDEX}, 1)" id="btn-mult-{INDEX}-1" class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white">
                                1x
                            </button>
                            <button type="button" onclick="setMultiplicador({INDEX}, 6)" id="btn-mult-{INDEX}-6" class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white">
                                x6
                            </button>
                            <button type="button" onclick="setMultiplicador({INDEX}, 12)" id="btn-mult-{INDEX}-12" class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white">
                                x12
                            </button>
                            <button type="button" onclick="setMultiplicador({INDEX}, 24)" id="btn-mult-{INDEX}-24" class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white">
                                x24
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                        <div id="caja-packs-wrapper-{INDEX}" class="hidden">
                            <label class="block text-[11px] font-semibold text-slate-400 mb-1">
                                N° Cajas / Packs <span id="label-empaque-{INDEX}" class="text-indigo-400"></span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="cajas_{INDEX}" min="1" step="1" value="1" oninput="calcularDesdeCajas({INDEX})" class="block w-full bg-slate-900 border-slate-700 text-slate-100 text-sm font-bold rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="text-xs text-slate-400 font-bold" id="formula-{INDEX}">&times; 1</span>
                            </div>
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Total Unidades Físicas <span class="text-emerald-400">*</span>
                            </label>
                            <input type="number" 
                                   name="detalles[{INDEX}][cantidad]" 
                                   id="cant_{INDEX}" 
                                   value="{CANTIDAD}" 
                                   min="0.1" 
                                   step="any"
                                   oninput="recalcularTotalGeneral()"
                                   class="block w-full bg-slate-900 border-emerald-500/50 text-emerald-300 text-base font-bold rounded-xl focus:border-emerald-400 focus:ring-emerald-400 px-4 py-2.5" 
                                   required>
                        </div>
                    </div>
                </div>

                {{-- Fechas de Elaboración y Vencimiento del Sublote --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                            Fecha Elaboración
                        </label>
                        <input type="date" 
                               name="detalles[{INDEX}][fecha_elaboracion]" 
                               id="elab_{INDEX}" 
                               onchange="calcularVencimientoPorElab({INDEX})"
                               class="block w-full bg-slate-950 border-slate-800 text-slate-200 text-sm rounded-xl focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 flex items-center justify-between">
                            <span>Fecha de Vencimiento <span class="text-rose-400">*</span></span>
                            <span id="badge-dias-{INDEX}" class="text-[10px] text-slate-500 font-normal"></span>
                        </label>
                        <input type="date" 
                               name="detalles[{INDEX}][fecha_vencimiento]" 
                               id="venc_{INDEX}" 
                               onchange="actualizarBadgeDias({INDEX})"
                               class="block w-full bg-slate-950 border-indigo-500/50 text-indigo-200 text-sm font-bold rounded-xl focus:border-indigo-400 focus:ring-indigo-400 py-2.5 shadow-sm" 
                               required>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ========================================================================= --}}
    {{-- SCRIPTS JAVASCRIPT: Lógica de Empaque, Multiplicadores, Fechas y Envío     --}}
    {{-- ========================================================================= --}}
    <script>
        // Contador global incremental para los índices de tarjetas duplicadas
        let indexContador = {{ count($recepcion->detalles) }};
        // Diccionario reactivo de multiplicadores activos por índice de card
        const multiplicadoresActivos = {};

        // Inicialización tras cargar el DOM
        document.addEventListener('DOMContentLoaded', () => {
            // Inicializar multiplicadores sugeridos por empaque en cada card
            document.querySelectorAll('.producto-card').forEach((card) => {
                const idx = card.dataset.cardIndex;
                const empaqueSugerido = parseInt(card.dataset.empaqueSugerido || 1);
                
                // Si el empaque detectado por OCR es un pack (x6, x12, x24), activarlo
                if ([6, 12, 24].includes(empaqueSugerido)) {
                    setMultiplicador(idx, empaqueSugerido);
                    // Si la cantidad original era 1 caja, calcular total en unidades
                    const cantInput = document.getElementById(`cant_${idx}`);
                    const cajasInput = document.getElementById(`cajas_${idx}`);
                    if (cantInput && cajasInput) {
                        const cantVal = parseFloat(cantInput.value) || 1;
                        if (cantVal <= 10 && cantVal % empaqueSugerido !== 0) {
                            // Era probablemente 1 o 2 cajas
                            cajasInput.value = cantVal;
                            cantInput.value = cantVal * empaqueSugerido;
                        }
                    }
                } else {
                    setMultiplicador(idx, 1);
                }

                // Actualizar badge de cuenta regresiva
                actualizarBadgeDias(idx);
            });

            // Calcular unidades físicas totales de la recepción
            recalcularTotalGeneral();
        });

        /**
         * Configura el factor multiplicador de empaque (1x, 6x, 12x, 24x) y actualiza estilos
         */
        function setMultiplicador(idx, mult) {
            multiplicadoresActivos[idx] = mult;
            
            // Actualizar estilo visual activo de las píldoras de empaque
            [1, 6, 12, 24].forEach(m => {
                const btn = document.getElementById(`btn-mult-${idx}-${m}`);
                if (btn) {
                    if (m === mult) {
                        btn.className = 'px-2.5 py-1 text-xs font-bold rounded-lg transition-all bg-indigo-600 text-white shadow-sm';
                    } else {
                        btn.className = 'px-2.5 py-1 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white';
                    }
                }
            });

            const wrapper = document.getElementById(`caja-packs-wrapper-${idx}`);
            const labelEmpaque = document.getElementById(`label-empaque-${idx}`);
            const formula = document.getElementById(`formula-${idx}`);
            const cantInput = document.getElementById(`cant_${idx}`);
            const cajasInput = document.getElementById(`cajas_${idx}`);

            if (mult === 1) {
                // Ocultar sección auxiliar de cajas para modo unidades sueltas
                if (wrapper) wrapper.classList.add('hidden');
            } else {
                // Mostrar sección de cajas/packs con la fórmula correspondiente
                if (wrapper) wrapper.classList.remove('hidden');
                let nombreEmpaque = mult === 6 ? '(Six-Packs)' : (mult === 12 ? '(Cajas x12)' : '(Cajas x24)');
                if (labelEmpaque) labelEmpaque.textContent = nombreEmpaque;
                if (formula) formula.innerHTML = `&times; ${mult}`;

                const cantActual = parseFloat(cantInput.value) || 0;
                if (cajasInput && cantActual > 0) {
                    const cajasEstimadas = Math.round(cantActual / mult) || 1;
                    cajasInput.value = cajasEstimadas;
                }
            }
        }

        /**
         * Calcula el total de unidades físicas a partir del número de cajas ingresadas
         */
        function calcularDesdeCajas(idx) {
            const mult = multiplicadoresActivos[idx] || 1;
            const cajasInput = document.getElementById(`cajas_${idx}`);
            const cantInput = document.getElementById(`cant_${idx}`);

            if (cajasInput && cantInput && mult > 1) {
                const numCajas = parseFloat(cajasInput.value) || 0;
                cantInput.value = Math.max(0, numCajas * mult);
                recalcularTotalGeneral();
            }
        }

        /**
         * Calcula automáticamente la fecha de vencimiento sumando la constante de meses
         * a la fecha de elaboración seleccionada por el usuario.
         */
        function calcularVencimientoPorElab(idx) {
            const elabInput = document.getElementById(`elab_${idx}`);
            const vencInput = document.getElementById(`venc_${idx}`);
            const constInput = document.getElementById(`constante_${idx}`);

            const meses = parseInt(constInput?.value || 0);

            if (meses > 0 && elabInput?.value) {
                const fechaElab = new Date(elabInput.value + 'T00:00:00');
                fechaElab.setMonth(fechaElab.getMonth() + meses);

                const yyyy = fechaElab.getFullYear();
                const mm = String(fechaElab.getMonth() + 1).padStart(2, '0');
                const dd = String(fechaElab.getDate()).padStart(2, '0');

                if (vencInput) {
                    vencInput.value = `${yyyy}-${mm}-${dd}`;
                    // Efecto visual de resalte esmeralda al autocalcular
                    vencInput.classList.add('ring-2', 'ring-emerald-500');
                    setTimeout(() => vencInput.classList.remove('ring-2', 'ring-emerald-500'), 1000);
                    actualizarBadgeDias(idx);
                }
            }
        }

        /**
         * Calcula la diferencia de días entre la fecha de vencimiento y hoy,
         * mostrando un badge con semáforo de urgencia (rojo, ámbar, verde).
         */
        function actualizarBadgeDias(idx) {
            const vencInput = document.getElementById(`venc_${idx}`);
            const badge = document.getElementById(`badge-dias-${idx}`);
            if (!vencInput || !badge || !vencInput.value) return;

            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            const venc = new Date(vencInput.value + 'T00:00:00');

            const diffTime = venc - hoy;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays < 0) {
                badge.className = 'text-[10px] text-rose-400 font-bold';
                badge.textContent = `⚠️ Vencido hace ${Math.abs(diffDays)} días`;
            } else if (diffDays <= 30) {
                badge.className = 'text-[10px] text-amber-400 font-bold';
                badge.textContent = `⚡ Vence en ${diffDays} días`;
            } else {
                badge.className = 'text-[10px] text-emerald-400 font-medium';
                badge.textContent = `✓ ${diffDays} días de vida útil`;
            }
        }

        /**
         * Actualiza los datos asociados de la tarjeta cuando el usuario cambia el producto en el select
         */
        function cambiarProductoCatalogo(idx, selectEl) {
            const opt = selectEl.options[selectEl.selectedIndex];
            if (!opt || !opt.value) return;

            const nombre = opt.dataset.nombre;
            const sku = opt.dataset.sku;
            const meses = opt.dataset.meses || 6;
            const formato = opt.dataset.formato;
            const capacidad = opt.dataset.capacidad;

            // Actualizar títulos y SKU en cabecera
            const titulo = document.getElementById(`titulo-prod-${idx}`);
            if (titulo) titulo.textContent = nombre;

            const skuLabel = document.getElementById(`sku-prod-${idx}`);
            if (skuLabel) skuLabel.textContent = `SKU: ${sku}`;

            // Actualizar constante de meses
            const constInput = document.getElementById(`constante_${idx}`);
            if (constInput) constInput.value = meses;

            // Actualizar selects de formato y capacidad
            if (formato) {
                const formatoSelect = document.getElementById(`formato_${idx}`);
                if (formatoSelect) formatoSelect.value = formato;
            }
            if (capacidad) {
                const capInput = document.getElementById(`capacidad_${idx}`);
                if (capInput) capInput.value = capacidad;
            }

            // Ocultar banner de producto nuevo si existía
            const banner = document.getElementById(`banner-nuevo-${idx}`);
            if (banner) banner.classList.add('hidden');

            // Recalcular vencimiento con la nueva constante
            calcularVencimientoPorElab(idx);
        }

        /**
         * Divide el lote de un producto en dos tarjetas cuando llegaron unidades con fechas distintas
         */
        function duplicarLote(idxOriginal) {
            const cardOriginal = document.querySelector(`[data-card-index="${idxOriginal}"]`);
            if (!cardOriginal) return;

            const selectOriginal = cardOriginal.querySelector(`select[name="detalles[${idxOriginal}][catalogo_producto_id]"]`);
            const optOriginal = selectOriginal.options[selectOriginal.selectedIndex];
            const cantOriginalInput = document.getElementById(`cant_${idxOriginal}`);
            
            // Repartir la cantidad entre ambas mitades
            const cantTotal = parseFloat(cantOriginalInput.value) || 0;
            const mitad1 = Math.ceil(cantTotal / 2);
            const mitad2 = Math.floor(cantTotal / 2);

            cantOriginalInput.value = mitad1;

            // Instanciar template de tarjeta clonada
            const nuevoIndex = indexContador++;
            const templateHtml = document.getElementById('card-template').innerHTML
                .replaceAll('{INDEX}', nuevoIndex)
                .replaceAll('{NOMBRE}', optOriginal ? optOriginal.dataset.nombre : 'Producto')
                .replaceAll('{SKU}', optOriginal ? optOriginal.dataset.sku : '')
                .replaceAll('{MESES}', optOriginal ? optOriginal.dataset.meses : 6)
                .replaceAll('{CAPACIDAD}', document.getElementById(`capacidad_${idxOriginal}`)?.value || '350 cc')
                .replaceAll('{CANTIDAD}', mitad2 > 0 ? mitad2 : 1);

            const div = document.createElement('div');
            div.innerHTML = templateHtml.trim();
            const nuevoCard = div.firstChild;

            // Insertar después de la tarjeta original
            cardOriginal.after(nuevoCard);

            // Seleccionar el mismo producto en el nuevo select
            const nuevoSelect = nuevoCard.querySelector(`select[name="detalles[${nuevoIndex}][catalogo_producto_id]"]`);
            if (nuevoSelect && selectOriginal.value) nuevoSelect.value = selectOriginal.value;

            // Clonar formato del envase
            const formatoOriginal = cardOriginal.querySelector(`select[name="detalles[${idxOriginal}][formato]"]`);
            const nuevoFormato = nuevoCard.querySelector(`select[name="detalles[${nuevoIndex}][formato]"]`);
            if (formatoOriginal && nuevoFormato) nuevoFormato.value = formatoOriginal.value;

            recalcularTotalGeneral();
        }

        /**
         * Elimina una tarjeta de la recepción con validación de no dejar la lista vacía
         */
        function eliminarCard(btn) {
            const cards = document.querySelectorAll('.producto-card');
            if (cards.length <= 1) {
                alert('La recepción debe contener al menos un producto.');
                return;
            }

            const card = btn.closest('.producto-card');
            if (card) {
                card.remove();
                recalcularTotalGeneral();
            }
        }

        /**
         * Recalcula la suma de unidades físicas de todos los lotes y actualiza los badges de la barra fija
         */
        function recalcularTotalGeneral() {
            let totalUnidades = 0;
            const cantInputs = document.querySelectorAll('input[name^="detalles"][name$="[cantidad]"]');
            
            cantInputs.forEach(input => {
                totalUnidades += parseFloat(input.value) || 0;
            });

            const cards = document.querySelectorAll('.producto-card');
            
            const prodCounter = document.getElementById('total-productos-counter');
            if (prodCounter) prodCounter.textContent = cards.length;

            const unitCounter = document.getElementById('total-unidades-counter');
            if (unitCounter) unitCounter.textContent = new Intl.NumberFormat('es-CL').format(totalUnidades) + ' un.';
        }

        /**
         * Abre el modal de creación express de producto rellenando campos sugeridos detectados
         */
        function abrirModalCrearProducto(idx, codProv, nombreSugerido, formato, capacidad, precioCompra) {
            document.getElementById('modal-card-idx').value = idx;
            document.getElementById('modal-cod-prov').value = codProv;
            document.getElementById('modal-nombre').value = nombreSugerido || '';
            
            // Generar SKU sugerido a partir de iniciales y capacidad
            const cleanStr = (nombreSugerido || 'PRD').replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            const skuSug = cleanStr.substring(0, 3) + '-' + (capacidad || 'UN').replace(/[^a-zA-Z0-9]/g, '').toUpperCase() + '-' + Math.floor(100 + Math.random() * 900);
            document.getElementById('modal-sku').value = skuSug;

            document.getElementById('modal-formato').value = formato || 'Lata';
            document.getElementById('modal-capacidad').value = capacidad || '350 cc';
            document.getElementById('modal-precio-compra').value = precioCompra || 0;
            document.getElementById('modal-precio-venta').value = precioCompra > 0 ? Math.round(precioCompra * 1.5) : 0;
            document.getElementById('modal-meses').value = 6;

            document.getElementById('modal-crear-producto').classList.remove('hidden');
        }

        /**
         * Cierra el modal de creación express de producto
         */
        function cerrarModalCrearProducto() {
            document.getElementById('modal-crear-producto').classList.add('hidden');
        }

        /**
         * Envía la solicitud AJAX para dar de alta el producto en el catálogo maestro
         * e inyecta la nueva opción en todos los selectores de la página sin recargar.
         */
        async function enviarQuickProducto(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-modal-guardar');
            btn.disabled = true;
            btn.innerHTML = `<span>Guardando...</span>`;

            const idx = document.getElementById('modal-card-idx').value;
            const codProv = document.getElementById('modal-cod-prov').value;
            const provId = document.getElementById('modal-prov-id').value;

            const payload = {
                nombre: document.getElementById('modal-nombre').value,
                sku: document.getElementById('modal-sku').value,
                formato: document.getElementById('modal-formato').value,
                capacidad: document.getElementById('modal-capacidad').value,
                precio_compra_ref: document.getElementById('modal-precio-compra').value,
                precio_venta: document.getElementById('modal-precio-venta').value,
                constante_vencimiento_meses: document.getElementById('modal-meses').value,
                proveedor_id: provId,
                codigo_proveedor: codProv,
            };

            try {
                const res = await fetch("{{ route('catalogo.quick-store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    alert('Error creando producto: ' + (data.message || JSON.stringify(data.errors || 'Error desconocido')));
                    btn.disabled = false;
                    btn.innerHTML = `<span>✨ Guardar y Vincular</span>`;
                    return;
                }

                const nuevoProd = data.producto;

                // Añadir la nueva opción a todos los selects de la página
                document.querySelectorAll('select[name^="detalles"][name$="[catalogo_producto_id]"]').forEach(select => {
                    const opt = document.createElement('option');
                    opt.value = nuevoProd.id;
                    opt.textContent = `${nuevoProd.nombre} (${nuevoProd.sku})`;
                    opt.dataset.nombre = nuevoProd.nombre;
                    opt.dataset.sku = nuevoProd.sku;
                    opt.dataset.meses = nuevoProd.constante_vencimiento_meses || 6;
                    opt.dataset.formato = nuevoProd.formato;
                    opt.dataset.capacidad = nuevoProd.capacidad;
                    select.appendChild(opt);
                });

                // Seleccionar inmediatamente en el card actual
                const selectCard = document.getElementById(`select-prod-${idx}`);
                if (selectCard) {
                    selectCard.value = nuevoProd.id;
                    cambiarProductoCatalogo(idx, selectCard);
                }

                // Ocultar banner de producto nuevo y mostrar confirmación verde
                const banner = document.getElementById(`banner-nuevo-${idx}`);
                if (banner) {
                    banner.className = 'bg-emerald-950/40 border border-emerald-500/40 rounded-xl p-3 text-emerald-300 text-xs font-semibold flex items-center gap-2 mb-3 shadow';
                    banner.innerHTML = `<svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> <span>✓ Producto creado oficialmente en catálogo y vinculado con éxito: <strong>${nuevoProd.nombre}</strong></span>`;
                }

                cerrarModalCrearProducto();

            } catch (err) {
                alert('Ocurrió un error al guardar: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<span>✨ Guardar y Vincular</span>`;
            }
        }

        /**
         * Validación estricta previa al envío del formulario completo:
         * - Verifica que todos los productos estén vinculados al catálogo
         * - Verifica que todas las fechas de vencimiento estén presentes
         * - Verifica que todas las cantidades sean mayores a 0
         * - Deshabilita el botón con un spinner para prevenir doble sumisión (idempotencia)
         */
        document.getElementById('conciliarForm')?.addEventListener('submit', function(e) {
            const cards = document.querySelectorAll('.producto-card');
            if (cards.length === 0) {
                e.preventDefault();
                alert('No hay productos en esta recepción.');
                return false;
            }

            for (let card of cards) {
                const prodSelect = card.querySelector('select[name^="detalles"][name$="[catalogo_producto_id]"]');
                const vencInput = card.querySelector('input[name^="detalles"][name$="[fecha_vencimiento]"]');
                const cantInput = card.querySelector('input[name^="detalles"][name$="[cantidad]"]');

                if (!prodSelect || !prodSelect.value) {
                    e.preventDefault();
                    alert('⚠️ Hay productos pendientes de vincular o crear en el catálogo. Pulsa "+ Crear en Catálogo" o selecciona un producto existente.');
                    prodSelect?.focus();
                    return false;
                }

                if (!vencInput || !vencInput.value) {
                    e.preventDefault();
                    alert('⚠️ Por favor ingresa la fecha de vencimiento para todos los productos y lotes.');
                    vencInput?.focus();
                    return false;
                }

                if (!cantInput || parseFloat(cantInput.value) <= 0) {
                    e.preventDefault();
                    alert('⚠️ La cantidad física debe ser mayor a 0.');
                    cantInput?.focus();
                    return false;
                }
            }

            // Deshabilitar botón para evitar doble submit
            const btn = document.getElementById('btn-confirmar-stock');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Ingresando Stock a Bodega...</span>
                `;
            }
        });
    </script>
</x-app-layout>
