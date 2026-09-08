<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-light tracking-tight text-white flex items-center gap-3">
                <a href="{{ route('maquinas.index') }}" class="text-slate-500 hover:text-cyan-400 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </a>
                <span class="w-1.5 h-8 bg-cyan-400 rounded-full shadow-[0_0_10px_rgba(34,211,238,0.8)]"></span>
                {{ $maquina->nombre }}
            </h2>
            <div class="flex items-center gap-4">
                <span class="px-3 py-1 bg-slate-800 text-xs text-slate-400 font-mono tracking-widest uppercase rounded border border-slate-700 hidden sm:inline-block">Planograma Visual</span>
                <form method="POST" action="{{ route('maquinas.destroy', $maquina->id) }}" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta máquina por completo?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-rose-900/50 hover:bg-rose-600 text-rose-300 hover:text-white px-3 py-1.5 rounded-lg border border-rose-700/50 transition-colors text-sm font-bold flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-black min-h-screen font-sans text-slate-200 relative overflow-x-auto">
        <!-- Tron Grid -->
        <div class="absolute inset-0 z-0 opacity-20" style="background-image: linear-gradient(rgba(34, 211, 238, 0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(34, 211, 238, 0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>
        
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 relative z-10 min-w-max pb-20">
            
            @if(session('success'))
                <div class="mb-6 bg-teal-900/30 border border-teal-500/50 text-teal-300 px-4 py-3 rounded-xl backdrop-blur-md">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-rose-900/30 border border-rose-500/50 text-rose-300 px-4 py-3 rounded-xl backdrop-blur-md">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-6 bg-rose-900/30 border border-rose-500/50 text-rose-300 px-4 py-3 rounded-xl backdrop-blur-md">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>- {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex gap-10">
                @foreach($estructura as $seccionNum => $pisos)
                    <div class="flex flex-col bg-slate-900/60 backdrop-blur-3xl border-2 border-slate-700/80 rounded-t-3xl rounded-b-md p-4 shadow-[0_0_50px_rgba(0,0,0,0.5)] flex-shrink-0">
                        <div class="text-center mb-6 border-b border-slate-700/50 pb-2">
                            <h3 class="text-xs font-mono tracking-widest text-cyan-500">SECCIÓN {{ $seccionNum }}</h3>
                        </div>

                        <div class="flex flex-col gap-6">
                            @foreach($pisos as $pisoNum => $slots)
                                <div class="bg-black/50 rounded-2xl p-4 border border-slate-800 shadow-inner relative">
                                    <div class="absolute -left-2 top-1/2 -translate-y-1/2 text-[10px] text-slate-600 font-mono rotate-180" style="writing-mode: vertical-rl;">PISO {{ $pisoNum }}</div>
                                    
                                    <!-- Repisa base -->
                                    <div class="absolute bottom-4 left-4 right-4 h-1 bg-gradient-to-r from-slate-700 via-slate-500 to-slate-700 rounded-full shadow-[0_2px_10px_rgba(255,255,255,0.1)]"></div>

                                    <div class="flex gap-4 ml-4 pb-2">
                                        @foreach($slots as $slot)
                                            <div class="w-32 flex flex-col items-center justify-end relative group">
                                                
                                                @if($slot->producto)
                                                    <!-- Bottle Representation -->
                                                    <div class="h-24 w-10 border border-white/20 rounded-t-xl rounded-b-md flex items-end justify-center overflow-hidden relative shadow-[0_0_15px_rgba(255,255,255,0.05)] transition-transform group-hover:-translate-y-2"
                                                         style="background: linear-gradient(180deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 20%, rgba(255,255,255,0.05) 100%);">
                                                        <!-- Liquid fill based on actual count vs capacity -->
                                                        @php $fill = $slot->capacidad_maxima > 0 ? ($slot->cantidad_actual / $slot->capacidad_maxima) * 100 : 0; @endphp
                                                        <div class="absolute bottom-0 w-full bg-cyan-500/60 shadow-[0_0_15px_rgba(34,211,238,0.8)] transition-all duration-500" style="height: {{ $fill }}%;"></div>
                                                    </div>
                                                    <div class="text-[9px] text-slate-300 font-medium text-center leading-tight mt-2 truncate w-full px-1" title="{{ $slot->producto->nombre }}">
                                                        {{ \Illuminate\Support\Str::limit($slot->producto->nombre, 20) }}
                                                    </div>
                                                    
                                                    <!-- Action controls -->
                                                    <div class="mt-2 flex items-center justify-center gap-1 bg-slate-900/80 rounded border border-slate-700 p-1">
                                                        <form method="POST" action="{{ route('maquinas.slot.remove', $slot->id) }}">
                                                            @csrf <button type="submit" class="w-5 h-5 flex items-center justify-center bg-slate-800 hover:bg-rose-500/20 text-rose-400 rounded transition-colors">-</button>
                                                        </form>
                                                        <span class="text-xs font-mono w-8 text-center">{{ $slot->cantidad_actual }}/{{ $slot->capacidad_maxima }}</span>
                                                        <form method="POST" action="{{ route('maquinas.slot.add', $slot->id) }}">
                                                            @csrf <button type="submit" class="w-5 h-5 flex items-center justify-center bg-slate-800 hover:bg-cyan-500/20 text-cyan-400 rounded transition-colors">+</button>
                                                        </form>
                                                    </div>

                                                    <!-- Unassign -->
                                                    <form method="POST" action="{{ route('maquinas.slot.unassign', $slot->id) }}" class="mt-1">
                                                        @csrf <button type="submit" class="text-[10px] text-slate-500 hover:text-rose-400 underline">Quitar</button>
                                                    </form>
                                                @else
                                                    <!-- Empty Slot -->
                                                    <div class="h-24 w-10 border border-dashed border-slate-700 rounded-t-xl rounded-b-md flex items-center justify-center mb-2">
                                                        <span class="text-[10px] text-slate-600">Vacío</span>
                                                    </div>
                                                    <div class="text-[9px] text-slate-500 mb-2">Max: {{ $slot->capacidad_maxima }} bot.</div>
                                                    
                                                    <!-- Assign Form -->
                                                    <form method="POST" action="{{ route('maquinas.slot.assign', $slot->id) }}" class="flex flex-col w-full" x-data="{ search: '' }">
                                                        @csrf
                                                        <div class="relative w-full mb-1">
                                                            <input type="text" x-model="search" placeholder="Buscar..." class="w-full text-[9px] bg-slate-900 border-slate-700 text-slate-200 rounded py-1 pl-5 pr-1 focus:border-cyan-500 focus:ring-cyan-500">
                                                            <svg class="w-2.5 h-2.5 text-slate-500 absolute left-1.5 top-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                                        </div>
                                                        <select name="catalogo_producto_id" required class="w-full text-[10px] bg-slate-800 border-slate-700 text-slate-200 rounded py-1 px-1 mb-1">
                                                            <option value="">Asignar...</option>
                                                            @foreach($productos as $prod)
                                                                <option value="{{ $prod->id }}" x-show="search === '' || $el.innerText.toLowerCase().includes(search.toLowerCase())" x-bind:hidden="search !== '' && !$el.innerText.toLowerCase().includes(search.toLowerCase())" x-bind:disabled="search !== '' && !$el.innerText.toLowerCase().includes(search.toLowerCase())">{{ $prod->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="bg-cyan-600/50 hover:bg-cyan-500 text-white text-[10px] rounded py-1 transition-colors">Guardar</button>
                                                    </form>
                                                @endif

                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
