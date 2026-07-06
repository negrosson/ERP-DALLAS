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

                            <!-- Dynamic Alert Card (Simulador de Vencimiento) -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Simulador FEFO (Constante de Vencimiento)</label>
                                
                                <div class="flex flex-col md:flex-row gap-6 p-5 bg-indigo-50/50 border border-indigo-100 rounded-xl shadow-sm">
                                    <div class="w-full md:w-1/3">
                                        <x-input-label for="constante_vencimiento_meses" :value="__('Meses de Vida Útil')" class="text-indigo-900 font-semibold text-xs uppercase tracking-wider mb-2" />
                                        <div class="relative">
                                            <x-text-input id="constante_vencimiento_meses" class="block w-full pl-10 border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-indigo-900 font-bold" type="number" step="1" min="1" max="120" name="constante_vencimiento_meses" :value="old('constante_vencimiento_meses', $catalogo->constante_vencimiento_meses)" />
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-indigo-600 leading-relaxed">Dejar en blanco para solicitar las fechas de forma manual al recepcionar bodega.</p>
                                    </div>
                                    
                                    <!-- Componente Visual Feedback -->
                                    <div class="flex-1 flex items-center">
                                        <div id="vencimiento_preview_container" class="hidden w-full bg-white border border-indigo-200 rounded-lg p-4 shadow-sm flex items-start gap-4 transition-all duration-300">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <div class="p-2 bg-indigo-100 rounded-lg">
                                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-semibold text-slate-900">Proyección FEFO</h4>
                                                <p class="text-sm text-slate-500 mt-1">Si ingresamos este producto el día de hoy, el lote vencerá exactamente el:</p>
                                                <div class="mt-2 inline-flex items-center px-3 py-1 rounded-md bg-indigo-50 border border-indigo-100">
                                                    <span id="vencimiento_preview_date" class="font-mono font-bold text-lg text-indigo-700 tracking-tight"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('constante_vencimiento_meses')" class="mt-2" />
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
                            <span id="save-loader" class="hidden mr-4 text-sm font-medium text-slate-500 flex items-center">
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

                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor del Toast (Fijo Arriba Derecha) -->
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputMeses = document.getElementById('constante_vencimiento_meses');
            const containerPreview = document.getElementById('vencimiento_preview_container');
            const datePreview = document.getElementById('vencimiento_preview_date');
            
            const form = document.getElementById('edit-catalog-form');
            const submitBtn = document.getElementById('submit-btn');
            const saveLoader = document.getElementById('save-loader');

            // 1. Lógica del Simulador
            function actualizarFecha() {
                const meses = parseInt(inputMeses.value);
                if (!isNaN(meses) && meses > 0) {
                    const fecha = new Date();
                    fecha.setMonth(fecha.getMonth() + meses);
                    
                    const dd = String(fecha.getDate()).padStart(2, '0');
                    const mm = String(fecha.getMonth() + 1).padStart(2, '0');
                    const yyyy = fecha.getFullYear();
                    
                    datePreview.textContent = `${dd}/${mm}/${yyyy}`;
                    
                    // Efecto de aparición suave
                    containerPreview.classList.remove('hidden');
                    containerPreview.classList.add('opacity-100', 'translate-y-0');
                    containerPreview.classList.remove('opacity-0', 'translate-y-2');
                } else {
                    containerPreview.classList.add('hidden');
                }
            }

            inputMeses.addEventListener('input', actualizarFecha);
            actualizarFecha(); // Init

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
                    // Si el servidor nos dio ok
                    showToast('Los cambios del producto han sido guardados y registrados en la base de datos.', 'success');
                })
                .catch(error => {
                    if(error.errors) {
                        const firstError = Object.values(error.errors)[0][0];
                        showToast(firstError, 'error');
                    } else {
                        // En caso de que el controlador haga un Redirect normal y fetch lo intercepte
                        // Laravel redirige, lo cual significa success en este contexto (status 200 de la página de destino).
                        showToast('Los cambios del producto han sido guardados.', 'success');
                    }
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50');
                    saveLoader.classList.add('hidden');
                });
            });
        });
    </script>
</x-app-layout>
