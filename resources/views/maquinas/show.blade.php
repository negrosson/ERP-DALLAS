<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl sm:text-3xl font-light tracking-tight text-white flex items-center gap-3">
                <a href="{{ route('maquinas.index') }}" class="text-slate-500 hover:text-cyan-400 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </a>
                <span class="w-1.5 h-8 bg-cyan-400 rounded-full shadow-[0_0_10px_rgba(34,211,238,0.8)]"></span>
                <span class="truncate">{{ $maquina->nombre }}</span>
            </h2>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('maquinas.destroy', $maquina->id) }}" onsubmit="return confirm('¿Eliminar esta máquina?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-rose-900/50 hover:bg-rose-600 text-rose-300 hover:text-white px-3 py-1.5 rounded-lg border border-rose-700/50 transition-colors text-xs font-bold flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        <span class="hidden sm:inline">Eliminar</span>
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6 min-h-screen font-sans text-slate-200 relative" x-data="maquinaManager()">

        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 relative z-10 pb-20 space-y-6">
            
            @if(session('success'))
                <div class="bg-teal-900/30 border border-teal-500/50 text-teal-300 px-4 py-3 rounded-xl backdrop-blur-md text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-rose-900/30 border border-rose-500/50 text-rose-300 px-4 py-3 rounded-xl backdrop-blur-md text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Toast de éxito AJAX -->
            <div x-show="toast" x-transition x-cloak
                 class="fixed top-4 right-4 z-[200] bg-teal-900/90 border border-teal-500/50 text-teal-300 px-4 py-3 rounded-xl backdrop-blur-md text-sm shadow-2xl">
                <span x-text="toastMsg"></span>
            </div>

            @foreach($pisoConsolidado as $seccion => $pisos)
                <!-- Header de Sección -->
                <div class="flex items-center gap-3 pt-2">
                    <div class="w-8 h-8 rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center shadow-[0_0_10px_rgba(34,211,238,0.2)]">
                        <span class="text-xs font-mono text-cyan-400 font-bold">{{ $seccion }}</span>
                    </div>
                    <h3 class="text-sm font-mono tracking-widest text-cyan-500 uppercase">Sección {{ $seccion }}</h3>
                </div>

                @foreach($pisos as $piso => $productosEnPiso)
                    <!-- TARJETA DE PISO -->
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-700/60 rounded-2xl overflow-hidden shadow-lg transition-all duration-300"
                         :class="editingFloor === '{{ $seccion }}-{{ $piso }}' ? 'border-cyan-500/50 shadow-[0_0_20px_rgba(34,211,238,0.15)]' : ''">
                        
                        <!-- Header del Piso -->
                        <div class="flex items-center justify-between px-4 py-3 bg-slate-800/40 border-b border-slate-700/40">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-slate-700/50 flex items-center justify-center">
                                    <span class="text-[11px] font-mono text-slate-300 font-bold">P{{ $piso }}</span>
                                </div>
                                <span class="text-xs text-slate-400 font-medium tracking-wide">PISO {{ $piso }}</span>
                                @php
                                    $totalPiso = collect($productosEnPiso)->sum('cantidad_total');
                                    $capPiso = collect($productosEnPiso)->sum('capacidad_total');
                                    $fillPiso = $capPiso > 0 ? round(($totalPiso / $capPiso) * 100) : 0;
                                    $displaysPiso = collect($productosEnPiso)->sum('displays_puestos');
                                @endphp
                                <span class="text-[10px] font-mono text-slate-500">{{ $totalPiso }}/{{ $capPiso }}</span>
                            </div>

                            <!-- Botón Lápiz -->
                            <button @click="toggleEdit('{{ $seccion }}', '{{ $piso }}', {{ json_encode(collect($productosEnPiso)->filter(fn($p) => $p['catalogo_producto_id'])->map(fn($p) => ['catalogo_producto_id' => $p['catalogo_producto_id'], 'nombre' => $p['producto'] ? $p['producto']->nombre : '', 'cantidad' => $p['cantidad_total'], 'displays' => $p['displays_puestos']])->values()) }})"
                                    class="p-2 rounded-xl transition-all duration-200"
                                    :class="editingFloor === '{{ $seccion }}-{{ $piso }}' ? 'bg-cyan-500/20 text-cyan-400' : 'bg-slate-700/50 text-slate-400 hover:text-cyan-400 hover:bg-slate-700'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                        </div>

                        <!-- Barra de llenado -->
                        <div class="w-full h-1 bg-slate-800">
                            <div class="h-1 transition-all duration-500 rounded-r-full"
                                 :class="{'bg-cyan-400 shadow-[0_0_8px_rgba(34,211,238,0.6)]': {{ $fillPiso }} > 50, 'bg-amber-400': {{ $fillPiso }} <= 50 && {{ $fillPiso }} > 20, 'bg-rose-400': {{ $fillPiso }} <= 20}"
                                 style="width: {{ $fillPiso }}%"></div>
                        </div>

                        <!-- Contenido: Productos en el piso (Vista lectura) -->
                        <div class="px-4 py-3 space-y-2" x-show="editingFloor !== '{{ $seccion }}-{{ $piso }}'">
                            @foreach($productosEnPiso as $prod)
                                @if($prod['catalogo_producto_id'])
                                    <div class="flex items-center justify-between py-1.5 border-b border-slate-800/50 last:border-0">
                                        <div class="flex items-center gap-3 min-w-0 flex-1">
                                            <div class="w-2 h-2 rounded-full bg-cyan-400/60 shrink-0"></div>
                                            <span class="text-sm text-slate-200 truncate">{{ $prod['producto']->nombre ?? 'Sin nombre' }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            @if($prod['displays_puestos'] > 0)
                                                <span class="text-[10px] font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-1.5 py-0.5 rounded">{{ $prod['displays_puestos'] }}D</span>
                                            @endif
                                            <span class="text-sm font-mono text-cyan-400 font-bold">{{ $prod['cantidad_total'] }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-3 py-1.5 text-slate-600">
                                        <div class="w-2 h-2 rounded-full bg-slate-700 shrink-0"></div>
                                        <span class="text-sm italic">Sin asignar</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Contenido: Formulario edición (Vista edición) -->
                        <div class="px-4 py-4 space-y-3 bg-slate-900/80" x-show="editingFloor === '{{ $seccion }}-{{ $piso }}'" x-cloak x-transition>
                            
                            <template x-for="(prod, idx) in editProducts" :key="idx">
                                <div class="bg-slate-800/60 rounded-xl p-3 border border-slate-700/40 space-y-3 relative">
                                    <!-- Botón eliminar producto -->
                                    <button @click="editProducts.splice(idx, 1)" 
                                            x-show="editProducts.length > 1"
                                            class="absolute top-2 right-2 p-1 rounded-lg text-rose-400/60 hover:text-rose-400 hover:bg-rose-900/30 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>

                                    <!-- Selector de producto -->
                                    <div>
                                        <label class="text-[10px] text-slate-500 font-mono tracking-widest uppercase mb-1 block">Producto</label>
                                        <select x-model="prod.catalogo_producto_id" 
                                                class="w-full text-sm bg-slate-900 border border-slate-600 text-slate-200 rounded-lg py-2 px-3 focus:border-cyan-500 focus:ring-cyan-500">
                                            <option value="">Seleccionar...</option>
                                            @foreach($productos as $p)
                                                <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <!-- Cantidad + Displays en una fila -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="text-[10px] text-slate-500 font-mono tracking-widest uppercase mb-1 block">Cantidad</label>
                                            <input type="number" x-model.number="prod.cantidad" min="0" 
                                                   class="w-full text-sm bg-slate-900 border border-slate-600 text-slate-200 rounded-lg py-2 px-3 focus:border-cyan-500 focus:ring-cyan-500 font-mono text-center"
                                                   placeholder="0">
                                        </div>
                                        <div>
                                            <label class="text-[10px] text-slate-500 font-mono tracking-widest uppercase mb-1 block">Displays</label>
                                            <input type="number" x-model.number="prod.displays" min="0" 
                                                   class="w-full text-sm bg-slate-900 border border-slate-600 text-slate-200 rounded-lg py-2 px-3 focus:border-cyan-500 focus:ring-cyan-500 font-mono text-center"
                                                   placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Botón agregar producto -->
                            <button @click="editProducts.push({catalogo_producto_id: '', cantidad: 0, displays: 0})"
                                    class="w-full py-2.5 border-2 border-dashed border-slate-600 hover:border-cyan-500/50 rounded-xl text-slate-500 hover:text-cyan-400 text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Agregar otro producto
                            </button>

                            <!-- Acciones -->
                            <div class="flex gap-3 pt-1">
                                <button @click="editingFloor = null" 
                                        class="flex-1 py-2.5 bg-slate-700/50 hover:bg-slate-700 text-slate-300 rounded-xl text-sm font-medium transition-colors">
                                    Cancelar
                                </button>
                                <button @click="saveFloor()" 
                                        :disabled="saving"
                                        class="flex-1 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-sm font-bold transition-all shadow-[0_0_15px_rgba(34,211,238,0.3)] disabled:opacity-50 flex items-center justify-center gap-2">
                                    <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span x-text="saving ? 'Guardando...' : '✓ Guardar'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <script>
    function maquinaManager() {
        return {
            editingFloor: null,
            editSeccion: null,
            editPiso: null,
            editProducts: [],
            saving: false,
            toast: false,
            toastMsg: '',

            toggleEdit(seccion, piso, productosActuales) {
                const key = seccion + '-' + piso;
                if (this.editingFloor === key) {
                    this.editingFloor = null;
                    return;
                }
                this.editingFloor = key;
                this.editSeccion = parseInt(seccion);
                this.editPiso = parseInt(piso);
                
                // Clonar productos actuales o crear uno vacío
                if (productosActuales && productosActuales.length > 0) {
                    this.editProducts = productosActuales.map(p => ({
                        catalogo_producto_id: String(p.catalogo_producto_id),
                        cantidad: p.cantidad || 0,
                        displays: p.displays || 0,
                    }));
                } else {
                    this.editProducts = [{catalogo_producto_id: '', cantidad: 0, displays: 0}];
                }
            },

            async saveFloor() {
                // Filtrar productos vacíos
                const productosValidos = this.editProducts.filter(p => p.catalogo_producto_id);
                if (productosValidos.length === 0) {
                    alert('Selecciona al menos un producto');
                    return;
                }

                this.saving = true;
                try {
                    const response = await fetch('{{ route("maquinas.update-floor", $maquina->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            seccion: this.editSeccion,
                            piso: this.editPiso,
                            productos: productosValidos,
                        }),
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        this.showToast('✓ Piso ' + this.editPiso + ' actualizado');
                        this.editingFloor = null;
                        // Recargar la página para mostrar datos actualizados
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        alert(data.error || 'Error al guardar');
                    }
                } catch (err) {
                    alert('Error de conexión: ' + err.message);
                } finally {
                    this.saving = false;
                }
            },

            showToast(msg) {
                this.toastMsg = msg;
                this.toast = true;
                setTimeout(() => this.toast = false, 2500);
            }
        }
    }
    </script>
</x-app-layout>
