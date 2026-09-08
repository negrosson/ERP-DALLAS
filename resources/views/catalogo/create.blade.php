<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Crear Nuevo Producto Maestro') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('catalogo.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- SKU -->
                            <div>
                                <x-input-label for="sku" :value="__('SKU (Código Interno)')" />
                                <x-text-input id="sku" class="block w-full mt-1" type="text" name="sku" :value="old('sku')" required autofocus maxlength="50" />
                                <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                            </div>

                            <!-- Unidad de Medida -->
                            <div>
                                <x-input-label for="unidad_medida" :value="__('Unidad de Medida')" />
                                <select id="unidad_medida" name="unidad_medida" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="UN" {{ old('unidad_medida') == 'UN' ? 'selected' : '' }}>Unidad (UN)</option>
                                    <option value="CJ" {{ old('unidad_medida') == 'CJ' ? 'selected' : '' }}>Caja (CJ)</option>
                                    <option value="KG" {{ old('unidad_medida') == 'KG' ? 'selected' : '' }}>Kilogramo (KG)</option>
                                    <option value="LT" {{ old('unidad_medida') == 'LT' ? 'selected' : '' }}>Litro (LT)</option>
                                </select>
                                <x-input-error :messages="$errors->get('unidad_medida')" class="mt-2" />
                            </div>

                            <!-- Nombre -->
                            <div class="md:col-span-2">
                                <x-input-label for="nombre" :value="__('Nombre del Producto')" />
                                <x-text-input id="nombre" class="block w-full mt-1" type="text" name="nombre" :value="old('nombre')" required maxlength="200" />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>

                            <!-- Formato -->
                            <div x-data="{ formatoSel: '{{ in_array(old('formato'), ['', 'BOTELLA', 'LATA', 'DISPLAY', 'CAJA', 'BARRIL', 'PACK', 'BIDON']) ? old('formato') : (old('formato') ? 'OTRO' : '') }}' }">
                                <x-input-label for="formato_select" :value="__('Formato')" />
                                <select id="formato_select" x-model="formatoSel" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
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
                                <input x-show="formatoSel === 'OTRO'" type="text" name="formato" value="{{ old('formato') }}" x-bind:disabled="formatoSel !== 'OTRO'" required maxlength="50" class="block w-full mt-2 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Especifique formato..." style="display: none;">
                                <x-input-error :messages="$errors->get('formato')" class="mt-2" />
                            </div>

                            <!-- Capacidad -->
                            <div x-data="{ capSel: '{{ in_array(old('capacidad'), ['', '250ML', '330ML', '350ML', '355ML', '473ML', '500ML', '600ML', '1L', '1.25L', '1.5L', '2L', '2.5L', '3L']) ? old('capacidad') : (old('capacidad') ? 'OTRO' : '') }}' }">
                                <x-input-label for="capacidad_select" :value="__('Capacidad')" />
                                <select id="capacidad_select" x-model="capSel" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
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
                                <input x-show="capSel === 'OTRO'" type="text" name="capacidad" value="{{ old('capacidad') }}" x-bind:disabled="capSel !== 'OTRO'" required maxlength="50" class="block w-full mt-2 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Especifique capacidad..." style="display: none;">
                                <x-input-error :messages="$errors->get('capacidad')" class="mt-2" />
                            </div>

                            <!-- Precios -->
                            <div>
                                <x-input-label for="precio_compra_ref" :value="__('Precio Compra Ref. ($)')" />
                                <x-text-input id="precio_compra_ref" class="block w-full mt-1" type="number" step="0.01" min="0" name="precio_compra_ref" :value="old('precio_compra_ref', 0)" required />
                                <x-input-error :messages="$errors->get('precio_compra_ref')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="precio_venta" :value="__('Precio Venta ($)')" />
                                <x-text-input id="precio_venta" class="block w-full mt-1" type="number" step="0.01" min="0" name="precio_venta" :value="old('precio_venta', 0)" required />
                                <x-input-error :messages="$errors->get('precio_venta')" class="mt-2" />
                            </div>

                            <!-- Configuración del Motor FEFO (Lógica de Vencimientos) -->
                            <div class="md:col-span-2" 
                                 x-data="{ 
                                    fefoMode: '{{ old('fefo_mode', 'auto') }}',
                                    mesesVidaUtil: {{ old('constante_vencimiento_meses', 'null') }},
                                    diasAviso: {{ old('dias_alerta_vencimiento', '45') }},
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
                                <x-input-label for="descripcion" :value="__('Descripción')" />
                                <textarea id="descripcion" name="descripcion" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3">{{ old('descripcion') }}</textarea>
                                <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                            </div>

                            <!-- Activo -->
                            <div class="block mt-4 md:col-span-2">
                                <label for="activo" class="inline-flex items-center">
                                    <input id="activo" type="checkbox" class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Producto Activo') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('catalogo.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                                Cancelar
                            </a>
                            <x-primary-button class="ml-3">
                                {{ __('Guardar Producto') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
