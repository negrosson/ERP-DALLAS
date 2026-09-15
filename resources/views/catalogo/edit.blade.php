<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Editar Producto:') }} <span class="text-indigo-600">{{ $catalogo->nombre }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-8 text-slate-900">
                    
                    <form id="edit-catalog-form" method="POST" action="{{ route('catalogo.update', $catalogo) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- SKU -->
                            <div>
                                <x-input-label for="sku" :value="__('SKU (Código Interno)')" class="text-slate-700 font-medium" />
                                <x-text-input id="sku" class="block w-full mt-1 bg-slate-50 border-slate-300 text-slate-500 cursor-not-allowed shadow-sm" type="text" name="sku" :value="old('sku', $catalogo->sku)" required readonly maxlength="50" />
                                <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                            </div>

                            <!-- Unidad de Medida -->
                            <div>
                                <x-input-label for="unidad_medida" :value="__('Unidad de Medida')" class="text-slate-700 font-medium" />
                                <select id="unidad_medida" name="unidad_medida" class="block w-full mt-1 border-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors" required>
                                    <option value="UN" {{ old('unidad_medida', $catalogo->unidad_medida) == 'UN' ? 'selected' : '' }}>Unidad (UN)</option>
                                    <option value="CJ" {{ old('unidad_medida', $catalogo->unidad_medida) == 'CJ' ? 'selected' : '' }}>Caja (CJ)</option>
                                    <option value="KG" {{ old('unidad_medida', $catalogo->unidad_medida) == 'KG' ? 'selected' : '' }}>Kilogramo (KG)</option>
                                    <option value="LT" {{ old('unidad_medida', $catalogo->unidad_medida) == 'LT' ? 'selected' : '' }}>Litro (LT)</option>
                                </select>
                                <x-input-error :messages="$errors->get('unidad_medida')" class="mt-2" />
                            </div>

                            <!-- Nombre -->
                            <div class="md:col-span-2">
                                <x-input-label for="nombre" :value="__('Nombre del Producto')" class="text-slate-700 font-medium" />
                                <x-text-input id="nombre" class="block w-full mt-1 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm transition-colors" type="text" name="nombre" :value="old('nombre', $catalogo->nombre)" required maxlength="200" />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>

                            <!-- Formato -->
                            <div x-data="{ formatoSel: '{{ in_array(old('formato', $catalogo->formato), ['', 'BOTELLA', 'LATA', 'DISPLAY', 'CAJA', 'BARRIL', 'PACK', 'BIDON']) ? old('formato', $catalogo->formato) : (old('formato', $catalogo->formato) ? 'OTRO' : '') }}' }">
                                <x-input-label for="formato_select" :value="__('Formato')" class="text-slate-700 font-medium" />
                                <select id="formato_select" x-model="formatoSel" class="block w-full mt-1 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm transition-colors" required>
                                    <option value="">Seleccione un formato...</option>
                                    <option value="BOTELLA">BOTELLA</option>
                                    <option value="LATA">LATA</option>
                                    <option value="DISPLAY">DISPLAY</option>
                                    <option value="CAJA">CAJA</option>
                                    <option value="BARRIL">BARRIL</option>
                                    <option value="PACK">PACK</option>
                                    <option value="BIDON">BIDON</option>
                                    <option value="OTRO">OTRO (Especificar)</option>
                                </select>
                                <input type="hidden" name="formato" :value="formatoSel" x-bind:disabled="formatoSel === 'OTRO' || formatoSel === ''">
                                <input x-show="formatoSel === 'OTRO'" type="text" name="formato" value="{{ old('formato', $catalogo->formato) }}" x-bind:disabled="formatoSel !== 'OTRO'" required maxlength="50" class="block w-full mt-2 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm transition-colors" placeholder="Especifique formato..." style="display: none;">
                                <x-input-error :messages="$errors->get('formato')" class="mt-2" />
                            </div>

                            <!-- Capacidad -->
                            <div x-data="{ capSel: '{{ in_array(old('capacidad', $catalogo->capacidad), ['', '250ML', '330ML', '350ML', '355ML', '473ML', '500ML', '600ML', '1L', '1.25L', '1.5L', '2L', '2.5L', '3L']) ? old('capacidad', $catalogo->capacidad) : (old('capacidad', $catalogo->capacidad) ? 'OTRO' : '') }}' }">
                                <x-input-label for="capacidad_select" :value="__('Capacidad')" class="text-slate-700 font-medium" />
                                <select id="capacidad_select" x-model="capSel" class="block w-full mt-1 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm transition-colors" required>
                                    <option value="">Seleccione capacidad...</option>
                                    <option value="250ML">250 ML</option>
                                    <option value="330ML">330 ML</option>
                                    <option value="350ML">350 ML</option>
                                    <option value="355ML">355 ML</option>
                                    <option value="473ML">473 ML</option>
                                    <option value="500ML">500 ML</option>
                                    <option value="600ML">600 ML</option>
                                    <option value="1L">1 L</option>
                                    <option value="1.25L">1.25 L</option>
                                    <option value="1.5L">1.5 L</option>
                                    <option value="2L">2 L</option>
                                    <option value="2.5L">2.5 L</option>
                                    <option value="3L">3 L</option>
                                    <option value="OTRO">OTRO (Especificar)</option>
                                </select>
                                <input type="hidden" name="capacidad" :value="capSel" x-bind:disabled="capSel === 'OTRO' || capSel === ''">
                                <input x-show="capSel === 'OTRO'" type="text" name="capacidad" value="{{ old('capacidad', $catalogo->capacidad) }}" x-bind:disabled="capSel !== 'OTRO'" required maxlength="50" class="block w-full mt-2 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm transition-colors" placeholder="Especifique capacidad..." style="display: none;">
                                <x-input-error :messages="$errors->get('capacidad')" class="mt-2" />
                            </div>

                            <!-- Precios -->
                            <div>
                                <x-input-label for="precio_compra_ref" :value="__('Precio Compra Ref. ($)')" class="text-slate-700 font-medium" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="text-slate-500 sm:text-sm">$</span>
                                    </div>
                                    <x-text-input id="precio_compra_ref" class="block w-full pl-7 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm transition-colors" type="number" step="0.01" min="0" name="precio_compra_ref" :value="old('precio_compra_ref', $catalogo->precio_compra_ref)" required />
                                </div>
                                <x-input-error :messages="$errors->get('precio_compra_ref')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="precio_venta" :value="__('Precio Venta ($)')" class="text-slate-700 font-medium" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="text-slate-500 sm:text-sm">$</span>
                                    </div>
                                    <x-text-input id="precio_venta" class="block w-full pl-7 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm transition-colors" type="number" step="0.01" min="0" name="precio_venta" :value="old('precio_venta', $catalogo->precio_venta)" required />
                                </div>
                                <x-input-error :messages="$errors->get('precio_venta')" class="mt-2" />
                            </div>

                            <!-- Configuración del Motor FEFO (Lógica de Vencimientos) -->
                            <div class="md:col-span-2" 
                                 x-data="{ 
                                    fefoMode: '{{ old('fefo_mode', $catalogo->constante_vencimiento_meses > 0 ? 'auto' : 'manual') }}',
                                    mesesVidaUtil: {{ old('constante_vencimiento_meses', $catalogo->constante_vencimiento_meses ?? 'null') }},
                                    diasAviso: {{ old('dias_alerta_vencimiento', $catalogo->dias_alerta_vencimiento ?? '45') }},
                                    nombreProd: '{{ addslashes($catalogo->nombre) }}',
                                    calcularProyeccion() {
                                        if (!this.mesesVidaUtil) return '';
                                        let d = new Date();
                                        d.setMonth(d.getMonth() + parseInt(this.mesesVidaUtil));
                                        return d.toLocaleDateString('es-CL', {day: '2-digit', month: '2-digit', year: 'numeric'});
                                    }
                                 }">
                                
                                <div class="border border-slate-200 rounded-xl p-6 bg-white shadow-sm relative overflow-hidden">
                                    <!-- Header -->
                                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-slate-800">Motor FEFO y Lógica de Vencimiento</h3>
                                            <p class="text-sm text-slate-500">Configura la regla de caducidad para la recepción en bodega.</p>
                                        </div>
                                    </div>

                                    @if(isset($proximoLote))
                                        @php
                                            $diasRestantes = \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($proximoLote->fecha_vencimiento)->startOfDay(), false);
                                            $estado = $diasRestantes <= 0 ? 'bg-red-50 text-red-700 border-red-200' : 
                                                     ($diasRestantes <= ($catalogo->dias_alerta_vencimiento ?? 30) ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200');
                                            $icono = $diasRestantes <= 0 ? 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' : 
                                                    'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
                                        @endphp
                                        <div class="mb-6 p-4 rounded-lg border {{ $estado }} flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icono }}"></path></svg>
                                                <div>
                                                    <p class="font-bold text-sm">Estado de Inventario Físico (Lote más crítico)</p>
                                                    <p class="text-xs opacity-90 mt-0.5">El próximo lote vence el <strong>{{ \Carbon\Carbon::parse($proximoLote->fecha_vencimiento)->format('d-m-Y') }}</strong> 
                                                        @if($diasRestantes < 0) (Venció hace {{ abs(intval($diasRestantes)) }} días) 
                                                        @elseif($diasRestantes == 0) (Vence hoy) 
                                                        @else (En {{ intval($diasRestantes) }} días) @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <a href="{{ route('lotes.index', ['search' => $catalogo->sku]) }}" class="text-xs font-bold underline hover:opacity-80 px-3 py-1 bg-white/50 rounded-md">Ver lote</a>
                                        </div>
                                    @endif

                                    <!-- Opciones de Modo (Radio Group Visual) -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                        <!-- Opción A -->
                                        <label class="relative flex flex-col p-4 cursor-pointer border rounded-xl transition-all duration-200 focus-within:ring-2 focus-within:ring-indigo-500"
                                               :class="fefoMode === 'auto' ? 'border-indigo-600 bg-indigo-50/50 shadow-sm' : 'border-slate-200 hover:border-indigo-300 hover:bg-slate-50'">
                                            <input type="radio" name="fefo_mode" value="auto" x-model="fefoMode" class="sr-only">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-bold text-slate-800" :class="fefoMode === 'auto' ? 'text-indigo-900' : ''">Autocalcular (Elaboración)</span>
                                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                                     :class="fefoMode === 'auto' ? 'border-indigo-600' : 'border-slate-300'">
                                                    <div class="w-2.5 h-2.5 rounded-full bg-indigo-600 transition-transform scale-0"
                                                         :class="fefoMode === 'auto' ? 'scale-100' : ''"></div>
                                                </div>
                                            </div>
                                            <p class="text-xs text-slate-500">Ideal para abarrotes. Se exige solo la fecha de elaboración al operario.</p>
                                        </label>

                                        <!-- Opción B -->
                                        <label class="relative flex flex-col p-4 cursor-pointer border rounded-xl transition-all duration-200 focus-within:ring-2 focus-within:ring-indigo-500"
                                               :class="fefoMode === 'manual' ? 'border-amber-500 bg-amber-50/30 shadow-sm' : 'border-slate-200 hover:border-amber-300 hover:bg-slate-50'">
                                            <input type="radio" name="fefo_mode" value="manual" x-model="fefoMode" class="sr-only" @change="mesesVidaUtil = null">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-bold text-slate-800" :class="fefoMode === 'manual' ? 'text-amber-900' : ''">Ingreso Manual (Directo)</span>
                                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                                     :class="fefoMode === 'manual' ? 'border-amber-500' : 'border-slate-300'">
                                                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500 transition-transform scale-0"
                                                         :class="fefoMode === 'manual' ? 'scale-100' : ''"></div>
                                                </div>
                                            </div>
                                            <p class="text-xs text-slate-500">Reglas de fábrica desconocidas. Exige fecha de vencimiento impresa.</p>
                                        </label>

                                        <!-- Opción C -->
                                        <label class="relative flex flex-col p-4 cursor-pointer border rounded-xl transition-all duration-200 focus-within:ring-2 focus-within:ring-indigo-500"
                                               :class="fefoMode === 'none' ? 'border-gray-500 bg-gray-50 shadow-sm' : 'border-slate-200 hover:border-gray-400 hover:bg-slate-50'">
                                            <input type="radio" name="fefo_mode" value="none" x-model="fefoMode" class="sr-only" @change="mesesVidaUtil = null; diasAviso = null">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-bold text-slate-800" :class="fefoMode === 'none' ? 'text-gray-900' : ''">Sin Fecha (Licores)</span>
                                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                                     :class="fefoMode === 'none' ? 'border-gray-500' : 'border-slate-300'">
                                                    <div class="w-2.5 h-2.5 rounded-full bg-gray-600 transition-transform scale-0"
                                                         :class="fefoMode === 'none' ? 'scale-100' : ''"></div>
                                                </div>
                                            </div>
                                            <p class="text-xs text-slate-500">Destilados y licores exentos de caducidad por normativa legal.</p>
                                        </label>
                                    </div>

                                    <!-- Paneles Dinámicos -->
                                    <div class="relative overflow-hidden">
                                        <!-- Panel A: Autocalcular -->
                                        <div x-show="fefoMode === 'auto'" 
                                             x-transition:enter="transition ease-out duration-300"
                                             x-transition:enter-start="opacity-0 -translate-y-2"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             class="flex flex-col md:flex-row gap-6 p-5 bg-slate-50 border border-slate-200 rounded-xl" style="display: none;">
                                            
                                            <div class="w-full md:w-1/3 space-y-4">
                                                <div>
                                                    <x-input-label for="constante_vencimiento_meses" :value="__('Meses de Vida Útil')" class="text-slate-700 font-bold text-xs uppercase tracking-wider mb-2" />
                                                    <x-text-input id="constante_vencimiento_meses" name="constante_vencimiento_meses" type="number" step="1" min="1" max="120" x-model="mesesVidaUtil" x-bind:required="fefoMode === 'auto'" class="block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-slate-900 font-bold" />
                                                    
                                                    <!-- Alerta Visual Inteligente para línea Zero -->
                                                    <div x-show="nombreProd.toLowerCase().includes('zero') && mesesVidaUtil != 4 && mesesVidaUtil != null" style="display: none;" class="mt-3 p-3 bg-red-100 border-l-4 border-red-500 text-red-700 text-sm rounded shadow-sm">
                                                        <strong class="block font-bold mb-0.5">¡ATENCIÓN! Posible Error</strong>
                                                        Detectamos que es un producto línea <strong>Zero</strong>. Según catálogo oficial, su vida útil debe ser de <strong>4 meses</strong>.
                                                    </div>

                                                    <x-input-error :messages="$errors->get('constante_vencimiento_meses')" class="mt-2" />
                                                </div>

                                                <div class="pt-4 border-t border-slate-200">
                                                    <x-input-label for="dias_alerta_vencimiento" :value="__('Aviso Previo (Días)')" class="text-slate-700 font-bold text-xs uppercase tracking-wider mb-2" />
                                                    <x-text-input id="dias_alerta_vencimiento" name="dias_alerta_vencimiento" type="number" step="1" min="1" x-model="diasAviso" class="block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-slate-900" placeholder="Ej. 30" />
                                                    <p class="mt-1.5 text-xs text-slate-500">Días antes de expirar para activar alerta.</p>
                                                    <x-input-error :messages="$errors->get('dias_alerta_vencimiento')" class="mt-2" />
                                                </div>
                                            </div>
                                            
                                            <div class="flex-1 flex items-start mt-2">
                                                <div x-show="mesesVidaUtil > 0" class="w-full bg-white border border-slate-200 rounded-lg p-5 shadow-sm flex items-start gap-4">
                                                    <div class="p-2 bg-slate-100 rounded-lg flex-shrink-0">
                                                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Proyección FEFO Activa</h4>
                                                        <p class="text-sm text-slate-500 mt-1">Si este producto se elaboró el día de hoy, su lote vencerá exactamente el:</p>
                                                        <div class="mt-3 inline-flex items-center px-4 py-1.5 rounded-md bg-slate-100 border border-slate-200">
                                                            <span class="font-mono font-bold text-lg text-slate-700 tracking-tight" x-text="calcularProyeccion()"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Panel B: Manual -->
                                        <div x-show="fefoMode === 'manual'" 
                                             x-transition:enter="transition ease-out duration-300"
                                             x-transition:enter-start="opacity-0 -translate-y-2"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             class="bg-amber-50 border-l-4 border-amber-500 p-5 rounded-r-xl" style="display: none;">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 mt-0.5">
                                                    <svg class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wide">Atención Requerida en Recepción</h3>
                                                    <p class="text-sm text-amber-700 mt-1.5 leading-relaxed">
                                                        Al recepcionar facturas de este producto, el sistema exigirá al operario digitar la <strong>FECHA DE VENCIMIENTO exacta</strong> impresa en el envase de forma obligatoria.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Panel C: Sin Vencimiento -->
                                        <div x-show="fefoMode === 'none'" 
                                             x-transition:enter="transition ease-out duration-300"
                                             x-transition:enter-start="opacity-0 -translate-y-2"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             class="bg-slate-100 border border-slate-200 p-5 rounded-xl" style="display: none;">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 mt-0.5">
                                                    <svg class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Producto Exento de Caducidad</h3>
                                                    <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">
                                                        Este producto no requiere control FEFO. Durante la recepción pasará directo a stock activo sin solicitar fechas de elaboración ni de vencimiento.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="md:col-span-2">
                                <x-input-label for="descripcion" :value="__('Descripción del Producto')" class="text-slate-700 font-medium" />
                                <textarea id="descripcion" name="descripcion" class="block w-full mt-1 border-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors" rows="3">{{ old('descripcion', $catalogo->descripcion) }}</textarea>
                                <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                            </div>

                            <!-- Activo -->
                            <div class="block mt-2 md:col-span-2">
                                <label for="activo" class="inline-flex items-center p-4 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors w-full sm:w-auto">
                                    <input id="activo" type="checkbox" class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500" name="activo" value="1" {{ old('activo', $catalogo->activo) ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <span class="block text-sm font-medium text-slate-900">Producto Activo</span>
                                        <span class="block text-xs text-slate-500">Permitir recepciones y ventas de este SKU.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-6 border-t border-slate-200">
                            <span id="save-loader" class="hidden mr-4 text-sm font-medium text-slate-500 items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Guardando...
                            </span>
                            <a href="{{ route('catalogo.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold tracking-wide text-slate-700 transition duration-150 ease-in-out bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Volver al Catálogo
                            </a>
                            <x-primary-button id="submit-btn" class="ml-3 px-6 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg">
                                {{ __('Guardar Cambios') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <!-- Nueva secciÃ³n: GestiÃ³n de Lotes -->
                    <div class="mt-12 pt-8 border-t border-slate-200">
                        <div class="mb-6 flex justify-between items-center">
                            <div>
                                <h3 class="text-xl font-bold text-slate-800">GestiÃ³n de Lotes y Stock</h3>
                                <p class="text-sm text-slate-500 mt-1">Modifica fÃ­sicamente el stock o las fechas de los lotes de este producto.</p>
                            </div>
                            <button x-data @click="$dispatch('open-create-lote-modal')" type="button" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                                + Crear Nuevo Lote
                            </button>
                        </div>

                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                            <table class="w-full text-sm text-left text-slate-600">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">Bodega</th>
                                        <th class="px-6 py-3 font-semibold">Cant. Disp.</th>
                                        <th class="px-6 py-3 font-semibold">F. ElaboraciÃ³n</th>
                                        <th class="px-6 py-3 font-semibold">F. Vencimiento</th>
                                        <th class="px-6 py-3 font-semibold text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($catalogo->lotesStock as $lote)
                                        <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-3">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-medium text-xs border border-slate-200 shadow-sm">
                                                    {{ $lote->bodega->nombre ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-3 font-bold text-slate-800">
                                                {{ number_format($lote->cantidad_disponible, 0) }}
                                            </td>
                                            <td class="px-6 py-3 text-slate-500">
                                                {{ $lote->fecha_elaboracion ? \Carbon\Carbon::parse($lote->fecha_elaboracion)->format('d/m/Y') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-3">
                                                @if($lote->fecha_vencimiento)
                                                    <span class="{{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->isPast() ? 'text-red-500 font-bold' : 'text-emerald-600 font-medium' }}">
                                                        {{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}
                                                    </span>
                                                @else
                                                    <span class="text-orange-400">Sin Fecha</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-3 text-right">
                                                <button x-data @click="$dispatch('open-edit-lote-modal', { 
                                                            id: {{ $lote->id }}, 
                                                            bodega: '{{ addslashes($lote->bodega->nombre) }}', 
                                                            cantidad: {{ $lote->cantidad_disponible }},
                                                            elaboracion: '{{ $lote->fecha_elaboracion ? \Carbon\Carbon::parse($lote->fecha_elaboracion)->format('Y-m-d') : '' }}',
                                                            vencimiento: '{{ $lote->fecha_vencimiento ? \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('Y-m-d') : '' }}'
                                                        })" 
                                                        type="button" 
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-md transition-colors border border-indigo-200 mb-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                    Editar
                                                </button>
                                                <button x-data @click="$dispatch('open-transfer-lote-modal', { 
                                                            id: {{ $lote->id }}, 
                                                            bodegaId: {{ $lote->bodega_id }},
                                                            bodegaNombre: '{{ addslashes($lote->bodega->nombre) }}'
                                                        })" 
                                                        type="button" 
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-md transition-colors border border-amber-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                                    Mover
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 bg-slate-50/50">
                                                Este producto no tiene lotes registrados en ninguna bodega actualmente.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Lote -->
    <div x-data="{ 
            open: false, 
            lote: {},
            isDisplay: false,
            displaySize: '6',
            otherSize: '',
            displayCount: 1,
            get calculatedCantidad() {
                if (!this.isDisplay) return this.lote.cantidad;
                let size = this.displaySize === 'otro' ? parseInt(this.otherSize || 0) : parseInt(this.displaySize);
                return size * parseInt(this.displayCount || 0);
            }
         }" 
         @open-edit-lote-modal.window="
            open = true; 
            lote = $event.detail;
            isDisplay = false;
         "
         x-show="open" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div @click.outside="open = false" class="bg-white border border-slate-200 p-6 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden relative" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <button @click="open = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-full p-1.5 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <h3 class="text-xl font-bold text-slate-800 mb-1">Editar Lote</h3>
            <p class="text-sm text-slate-500 mb-6">Bodega: <span class="font-semibold text-indigo-600" x-text="lote.bodega"></span></p>
            
            <form :action="'{{ url('lotes') }}/' + lote.id + '/full'" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Fechas -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Elaboración</label>
                        <input type="date" name="fecha_elaboracion" x-model="lote.elaboracion" class="bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" x-model="lote.vencimiento" class="bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                    </div>
                </div>
                
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6">
                    <h4 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" /></svg>
                        Ajuste de Inventario Físico
                    </h4>
                    
                    <div class="mb-4 flex items-center gap-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="isDisplay" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ml-3 text-sm font-medium text-slate-700">Ingresar como Displays / Cajas</span>
                        </label>
                    </div>

                    <div x-show="isDisplay" class="grid grid-cols-2 gap-4 mb-4 p-3 bg-indigo-50 rounded-lg border border-indigo-100" style="display: none;">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Unidades por Display</label>
                            <select x-model="displaySize" class="bg-white border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2">
                                <option value="4">4 Unidades</option>
                                <option value="6">6 Unidades</option>
                                <option value="12">12 Unidades</option>
                                <option value="24">24 Unidades</option>
                                <option value="otro">Otro tamaño...</option>
                            </select>
                            <input type="number" x-show="displaySize === 'otro'" x-model="otherSize" placeholder="Especifique..." min="1" class="mt-2 bg-white border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Cantidad de Displays</label>
                            <input type="number" x-model="displayCount" min="0" step="1" class="bg-white border border-slate-300 text-slate-800 text-sm font-bold rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Nueva Cantidad Real (Unidades)</label>
                            <input type="hidden" name="cantidad_nueva" :value="calculatedCantidad">
                            <input type="number" :value="calculatedCantidad" :disabled="isDisplay" x-model="lote.cantidad" min="0" step="0.01" required class="bg-white border border-slate-300 text-slate-800 text-sm font-bold rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 disabled:bg-slate-100 disabled:text-slate-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Motivo del Ajuste <span class="text-red-500">*</span></label>
                            <input type="text" name="motivo" required placeholder="Ej. Corrección de stock" class="bg-white border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="open = false" class="text-slate-600 bg-white hover:bg-slate-50 border border-slate-300 focus:ring-4 focus:outline-none focus:ring-slate-100 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors">Cancelar</button>
                    <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-200 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors shadow-sm">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Transferir Lote -->
    <div x-data="{ open: false, lote: {} }" 
         @open-transfer-lote-modal.window="open = true; lote = $event.detail;"
         x-show="open" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div @click.outside="open = false" class="bg-white border border-slate-200 p-6 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden relative" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <button @click="open = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-full p-1.5 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <h3 class="text-xl font-bold text-slate-800 mb-1">Transferir Lote</h3>
            <p class="text-sm text-slate-500 mb-6">Bodega Actual: <span class="font-semibold text-amber-600" x-text="lote.bodegaNombre"></span></p>
            
            <form :action="'{{ url('lotes') }}/' + lote.id + '/transferir'" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Bodega Destino</label>
                    <select name="bodega_destino_id" required class="bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5">
                        <option value="">Seleccione una bodega...</option>
                        @foreach(\App\Models\Bodega::where('activa', true)->get() as $b)
                            <option value="{{ $b->id }}">{{ $b->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Motivo / Observaciones</label>
                    <input type="text" name="motivo" required placeholder="Ej. Reordenamiento de inventario" class="bg-white border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="open = false" class="text-slate-600 bg-white hover:bg-slate-50 border border-slate-300 focus:ring-4 focus:outline-none focus:ring-slate-100 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors">Cancelar</button>
                    <button type="submit" class="text-white bg-amber-600 hover:bg-amber-700 focus:ring-4 focus:outline-none focus:ring-amber-200 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors shadow-sm">Transferir</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Crear Lote -->
    <div x-data="{ 
            open: false, 
            isDisplay: false,
            displaySize: '6',
            otherSize: '',
            displayCount: 1,
            cantidadManual: 0,
            get calculatedCantidad() {
                if (!this.isDisplay) return this.cantidadManual;
                let size = this.displaySize === 'otro' ? parseInt(this.otherSize || 0) : parseInt(this.displaySize);
                return size * parseInt(this.displayCount || 0);
            }
         }" 
         @open-create-lote-modal.window="
            open = true; 
            isDisplay = false;
            cantidadManual = 0;
            displayCount = 1;
         "
         x-show="open" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div @click.outside="open = false" class="bg-white border border-slate-200 p-6 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden relative" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <button @click="open = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-full p-1.5 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <h3 class="text-xl font-bold text-slate-800 mb-1">Crear Nuevo Lote</h3>
            <p class="text-sm text-slate-500 mb-6">Agrega stock directamente al producto <strong>{{ $catalogo->nombre }}</strong>.</p>
            
            <form action="{{ route('catalogo.lotes.store', $catalogo) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Bodega Destino <span class="text-red-500">*</span></label>
                    <select name="bodega_id" required class="bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5">
                        <option value="">Seleccione una bodega...</option>
                        @foreach(\App\Models\Bodega::where('activa', true)->get() as $b)
                            <option value="{{ $b->id }}">{{ $b->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Fechas -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Elaboración</label>
                        <input type="date" name="fecha_elaboracion" class="bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" class="bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5">
                    </div>
                </div>
                
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6">
                    
                    <div class="mb-4 flex items-center gap-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="es_display" value="0">
                            <input type="checkbox" name="es_display" value="1" x-model="isDisplay" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            <span class="ml-3 text-sm font-medium text-slate-700">Ingresar como Displays / Cajas</span>
                        </label>
                    </div>

                    <div x-show="isDisplay" class="grid grid-cols-2 gap-4 mb-4 p-3 bg-emerald-50 rounded-lg border border-emerald-100" style="display: none;">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Unidades por Display</label>
                            <input type="hidden" name="unidades_display" :value="displaySize === 'otro' ? otherSize : displaySize" :disabled="!isDisplay">
                            <select x-model="displaySize" class="bg-white border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2">
                                <option value="4">4 Unidades</option>
                                <option value="6">6 Unidades</option>
                                <option value="12">12 Unidades</option>
                                <option value="24">24 Unidades</option>
                                <option value="otro">Otro tamaño...</option>
                            </select>
                            <input type="number" x-show="displaySize === 'otro'" x-model="otherSize" placeholder="Especifique..." min="1" class="mt-2 bg-white border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Cantidad de Displays</label>
                            <input type="number" x-model="displayCount" min="1" step="1" class="bg-white border border-slate-300 text-slate-800 text-sm font-bold rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Cantidad Real (Unidades) <span class="text-red-500">*</span></label>
                            <input type="hidden" name="cantidad_disponible" :value="calculatedCantidad">
                            <input type="number" :value="calculatedCantidad" :disabled="isDisplay" x-model="cantidadManual" min="0.01" step="0.01" required class="bg-white border border-slate-300 text-slate-800 text-sm font-bold rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5 disabled:bg-slate-100 disabled:text-slate-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Motivo de Creación <span class="text-red-500">*</span></label>
                            <input type="text" name="motivo" required placeholder="Ej. Inventario inicial" class="bg-white border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="open = false" class="text-slate-600 bg-white hover:bg-slate-50 border border-slate-300 focus:ring-4 focus:outline-none focus:ring-slate-100 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors">Cancelar</button>
                    <button type="submit" class="text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-200 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors shadow-sm">Crear Lote</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Contenedor del Toast (Fijo Arriba Derecha) -->
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {


            // 2. Sistema de Toasts
            function showToast(message, type = 'success') {
                const toast = document.createElement('div');
                const isSuccess = type === 'success';
                
                toast.className = `transform transition-all duration-300 translate-x-full opacity-0 max-w-sm w-full bg-white shadow-lg rounded-xl pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden border-l-4 ${isSuccess ? 'border-emerald-500' : 'border-red-500'}`;
                
                toast.innerHTML = `
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                ${isSuccess 
                                    ? '<svg class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                                    : '<svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                                }
                            </div>
                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="text-sm font-medium text-slate-900">${isSuccess ? 'Guardado Exitoso' : 'Error'}</p>
                                <p class="mt-1 text-sm text-slate-500">${message}</p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button class="bg-white rounded-md inline-flex text-slate-400 hover:text-slate-500 focus:outline-none" onclick="this.closest('.max-w-sm').remove()">
                                    <span class="sr-only">Cerrar</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('toast-container').appendChild(toast);
                
                // Animar entrada
                setTimeout(() => {
                    toast.classList.remove('translate-x-full', 'opacity-0');
                    toast.classList.add('translate-x-0', 'opacity-100');
                }, 10);
                
                // Remover después de 4 segundos
                setTimeout(() => {
                    toast.classList.remove('translate-x-0', 'opacity-100');
                    toast.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            }

            // 3. Persistencia Asíncrona (AJAX Form Submit)
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50');
                saveLoader.classList.remove('hidden');
                
                // Si el checkbox de activo no está marcado, FormData no lo incluye. 
                // Lo forzamos inyectando 0 para que Laravel lo valide bien si es necesario.
                const formData = new FormData(form);
                if(!formData.has('activo')) formData.append('activo', '0');

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok && response.status === 422) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json(); // Laravel Controller normally redirects, we need to handle JSON if we modify Controller, OR we just accept the redirect via Fetch.
                    // Wait, standard Laravel Controller redirects on save. If it redirects, fetch follows it or returns opaque. 
                    // Let's modify the CatalogoController temporarily to return JSON for AJAX, or handle it via a generic response check.
                })
                .then(data => {
                    // Redirigir rápidamente a la vista principal
                    window.location.href = "{{ route('catalogo.index') }}";
                })
                .catch(error => {
                    if(error.errors) {
                        const firstError = Object.values(error.errors)[0][0];
                        showToast(firstError, 'error');
                        // Solo rehabilitamos el botón si hubo un error de validación
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50');
                        saveLoader.classList.add('hidden');
                    } else {
                        window.location.href = "{{ route('catalogo.index') }}";
                    }
                });
            });
        });
    </script>
</x-app-layout>
