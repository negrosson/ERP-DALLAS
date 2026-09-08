@props(['bodega' => null, 'bodegas' => []])

<!-- Modal Ajuste Masivo -->
<div x-data="{
        open: false,
        producto: null,
        lotes: [],
        motivo: '',
        isSubmitting: false,
        mesesVidaUtil: '',
        productoFormato: '',
        bodegaIdProp: {{ $bodega ? $bodega->id : 'null' }},

            openModal(prodData, lotesData) {
                this.producto = prodData;
                this.mesesVidaUtil = prodData.constante_vencimiento_meses || '';
                this.productoFormato = prodData.formato || '';
                this.motivo = '';
                this.isSubmitting = false;
                
                // Parse format to find default units
                let defaultUnits = 1;
                if (prodData.formato) {
                    const match = String(prodData.formato).match(/\b(?:pack\s*)?(?:x\s*)?(\d+)\b/i);
                    if (match && parseInt(match[1]) > 1) {
                        defaultUnits = parseInt(match[1]);
                    }
                }

                this.lotes = lotesData.map(lote => ({
                    id: lote.id,
                    fecha_elaboracion: lote.fecha_elaboracion ? lote.fecha_elaboracion.split('T')[0] : '',
                    fecha_vencimiento: lote.fecha_vencimiento ? lote.fecha_vencimiento.split('T')[0] : '',
                    cantidad_original: lote.cantidad_disponible,
                    empaque: defaultUnits > 1 ? 'Display' : 'Unidad',
                    unidades_por_empaque: defaultUnits,
                    cantidad_empaques: Math.floor(lote.cantidad_disponible / defaultUnits) + (lote.cantidad_disponible % defaultUnits > 0 ? (lote.cantidad_disponible % defaultUnits) / defaultUnits : 0),
                    get cantidad_total() {
                        return (parseFloat(this.cantidad_empaques) || 0) * (parseInt(this.unidades_por_empaque) || 1);
                    }
                }));

                this.open = true;
            },

            getFormAction() {
                if (!this.producto) return '';
                return '{{ url('productos') }}/' + this.producto.id + '/ajuste-masivo';
            },

            addLote() {
                let defaultUnits = 1;
                if (this.producto && this.producto.formato) {
                    const match = String(this.producto.formato).match(/\b(?:pack\s*)?(?:x\s*)?(\d+)\b/i);
                    if (match && parseInt(match[1]) > 1) {
                        defaultUnits = parseInt(match[1]);
                    }
                }
                
                this.lotes.push({
                    id: null,
                    bodega_id: this.bodegaIdProp,
                    fecha_elaboracion: '',
                    fecha_vencimiento: '',
                    cantidad_original: 0,
                    empaque: defaultUnits > 1 ? 'Display' : 'Unidad',
                    unidades_por_empaque: defaultUnits,
                    cantidad_empaques: 0,
                    get cantidad_total() {
                        return (parseFloat(this.cantidad_empaques) || 0) * (parseInt(this.unidades_por_empaque) || 1);
                    }
                });
            },

            calcularVencimiento(lote) {
                if (lote.fecha_elaboracion && this.mesesVidaUtil) {
                    let fecha = new Date(lote.fecha_elaboracion + 'T12:00:00');
                    fecha.setMonth(fecha.getMonth() + parseInt(this.mesesVidaUtil));
                    lote.fecha_vencimiento = fecha.toISOString().split('T')[0];
                }
            },

            removeLote(index) {
                this.lotes.splice(index, 1);
            },

            submitForm() {
                // Verify required dates
                for (let i = 0; i < this.lotes.length; i++) {
                    if (this.lotes[i].cantidad_total > 0 && !this.lotes[i].fecha_vencimiento) {
                        alert('Debe especificar la fecha de vencimiento para todos los lotes con stock.');
                        return;
                    }
                }

                this.isSubmitting = true;
                document.getElementById('ajuste-masivo-form').submit();
            }
         }"
         @open-ajuste-masivo.window="openModal($event.detail.producto, $event.detail.lotes)"
         x-show="open" 
         class="fixed inset-0 z-[110] flex items-center justify-center pt-10 sm:pt-0 bg-black/60 backdrop-blur-sm p-4 sm:p-0"
         style="display: none;"
         x-transition>
         
         <div class="bg-slate-900 border border-slate-700 w-full max-w-4xl rounded-2xl shadow-2xl flex flex-col overflow-hidden max-h-[90vh]" @click.outside="open = false">
            
            <div class="p-4 sm:p-6 border-b border-slate-800 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Ajuste Avanzado de Inventario
                </h3>
                <button @click="open = false" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-4 sm:p-6 overflow-y-auto custom-scrollbar flex-1 relative">
                <div x-show="producto" class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-xl font-black text-white" x-text="producto?.nombre"></h4>
                        <div class="text-sm text-slate-400 mt-1 flex flex-wrap items-center gap-2">
                            <span class="font-mono">SKU: <span x-text="producto?.sku"></span></span> 
                            <span class="text-slate-600">|</span> 
                            <label for="formato_input" class="text-slate-400">Formato:</label>
                            <input list="formatos_datalist" id="formato_input" x-model="productoFormato" form="ajuste-masivo-form" name="formato" class="bg-slate-800 border-slate-700 text-slate-200 text-xs rounded px-2 py-1 w-32 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. Botella, Lata">
                            <datalist id="formatos_datalist">
                                <option value="Botella">Botella</option>
                                <option value="Vidrio">Vidrio</option>
                                <option value="Lata">Lata</option>
                            </datalist>
                        </div>
                    </div>
                    <div class="bg-indigo-900/30 border border-indigo-500/30 rounded-lg p-3">
                        <label for="vida_util_global" class="block text-xs font-medium text-indigo-300 mb-1">Calculadora: Vida Útil (Meses)</label>
                        <input type="number" id="vida_util_global" x-model="mesesVidaUtil" @change="lotes.forEach(l => calcularVencimiento(l))" min="1" step="1" class="w-32 bg-slate-800 border-slate-700 text-slate-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Ej. 6">
                    </div>
                </div>

                <form id="ajuste-masivo-form" :action="getFormAction()" method="POST">
                    @csrf
                    
                    <div class="space-y-4">
                        <template x-for="(lote, index) in lotes" :key="index">
                            <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-4 relative">
                                <input type="hidden" :name="'lotes['+index+'][id]'" :value="lote.id">
                                
                                <div class="absolute top-2 right-2 flex gap-2">
                                    <span x-show="lote.id" class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-indigo-900/50 text-indigo-400 border border-indigo-700/50 rounded">Lote Existente</span>
                                    <span x-show="!lote.id" class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-emerald-900/50 text-emerald-400 border border-emerald-700/50 rounded">Lote Nuevo</span>
                                    <button type="button" x-show="!lote.id || lotes.length > 1" @click="removeLote(index)" class="text-slate-500 hover:text-rose-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end mt-4 lg:mt-0">
                                    
                                    <!-- Bodega Destino (solo para lotes nuevos globales) -->
                                    <template x-if="!lote.id && !bodegaIdProp">
                                        <div class="col-span-1 lg:col-span-12">
                                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Bodega de Destino <span class="text-rose-500">*</span></label>
                                            <select :name="'lotes['+index+'][bodega_id]'" x-model="lote.bodega_id" required class="w-full bg-slate-900 border-slate-700 rounded-md text-sm text-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="">Seleccione una bodega...</option>
                                                @foreach($bodegas as $b)
                                                    <option value="{{ $b->id }}">{{ $b->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </template>
                                    <template x-if="lote.id || bodegaIdProp">
                                        <input type="hidden" :name="'lotes['+index+'][bodega_id]'" :value="lote.bodega_id">
                                    </template>
                                    
                                    <!-- Fechas -->
                                    <div class="col-span-1 lg:col-span-3">
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Vencimiento <span class="text-rose-500">*</span></label>
                                        <input type="date" :name="'lotes['+index+'][fecha_vencimiento]'" x-model="lote.fecha_vencimiento" required class="w-full bg-slate-900 border-slate-700 rounded-md text-sm text-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div class="col-span-1 lg:col-span-3">
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Elaboración</label>
                                        <input type="date" :name="'lotes['+index+'][fecha_elaboracion]'" x-model="lote.fecha_elaboracion" @change="calcularVencimiento(lote)" class="w-full bg-slate-900 border-slate-700 rounded-md text-sm text-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>

                                    <!-- Empaque -->
                                    <div class="col-span-1 lg:col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Empaque</label>
                                        <select x-model="lote.unidades_por_empaque" class="w-full bg-slate-900 border-slate-700 rounded-md text-sm text-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="6">Display (6)</option>
                                            <option value="12">Display (12)</option>
                                            <option value="24">Display (24)</option>
                                            <option value="32">Display (32)</option>
                                            <option value="1">Unidades</option>
                                        </select>
                                    </div>

                                    <!-- Conteo Físico -->
                                    <div class="col-span-1 lg:col-span-4 flex items-center gap-3">
                                        <div class="flex-1">
                                            <label class="block text-[10px] font-bold text-amber-400 uppercase tracking-wider mb-1">Conteo Físico</label>
                                            <input type="number" step="0.01" min="0" x-model="lote.cantidad_empaques" required class="w-full bg-amber-500/10 border-amber-500/50 rounded-md text-base font-bold text-white focus:ring-amber-500 focus:border-amber-500 text-center">
                                        </div>
                                        <div class="shrink-0 pt-4 text-center">
                                            <input type="hidden" :name="'lotes['+index+'][cantidad_nueva]'" :value="lote.cantidad_total">
                                            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Total Real</span>
                                            <span class="text-xl font-black text-indigo-400" x-text="lote.cantidad_total"></span> <span class="text-xs text-indigo-500 font-bold">UN</span>
                                            <div x-show="lote.id && lote.cantidad_total !== lote.cantidad_original" class="text-[10px] font-bold" :class="lote.cantidad_total > lote.cantidad_original ? 'text-emerald-400' : 'text-rose-400'">
                                                <span x-text="lote.cantidad_total > lote.cantidad_original ? '+' : ''"></span><span x-text="lote.cantidad_total - lote.cantidad_original"></span> dif
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="addLote()" class="w-full py-3 border-2 border-dashed border-slate-700 hover:border-indigo-500 hover:bg-indigo-900/10 rounded-xl text-slate-400 hover:text-indigo-400 transition-colors font-medium flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Añadir otro lote / fecha
                        </button>
                    </div>

                    <div class="mt-6 border-t border-slate-700/50 pt-4">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Motivo del Ajuste General</label>
                        <input type="text" name="motivo" x-model="motivo" class="w-full bg-slate-800 border-slate-700 rounded-lg text-white focus:ring-indigo-500 focus:border-indigo-500 p-3" placeholder="Ej: Conteo físico mensual, Merma, Corrección de fechas...">
                    </div>

                </form>
            </div>

            <div class="p-4 sm:p-6 border-t border-slate-800 bg-slate-800/30 flex justify-end gap-3">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-300 bg-slate-800 border border-slate-700 rounded-lg hover:bg-slate-700 transition-colors">
                    Cancelar
                </button>
                <button type="button" @click="submitForm()" :disabled="isSubmitting" class="px-5 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 border border-indigo-500/50 rounded-lg shadow-lg shadow-indigo-900/20 transition-all flex items-center gap-2 disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Guardar Ajuste
                </button>
            </div>
         </div>
    </div>
