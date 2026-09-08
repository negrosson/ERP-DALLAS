<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="text-2xl sm:text-3xl font-light tracking-tight text-white flex items-center gap-3">
                <span class="w-1.5 h-8 bg-cyan-400 rounded-full shadow-[0_0_10px_rgba(34,211,238,0.8)]"></span>
                {{ __('Planogramas de Máquinas') }}
            </h2>
            <button x-data @click="$dispatch('open-modal', 'create-maquina')" class="bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-bold py-2 px-4 rounded-xl shadow-[0_0_15px_rgba(34,211,238,0.4)] transition-all">
                + NUEVA MÁQUINA
            </button>
        </div>
    </x-slot>

    <div class="py-8 bg-black min-h-screen font-sans text-slate-200 relative">
        <!-- Tron Grid Background Overlay -->
        <div class="absolute inset-0 z-0 opacity-20" 
             style="background-image: linear-gradient(rgba(34, 211, 238, 0.2) 1px, transparent 1px), linear-gradient(90deg, rgba(34, 211, 238, 0.2) 1px, transparent 1px); background-size: 50px 50px;">
        </div>
        
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8 relative z-10">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($maquinasStats as $mq)
                <a href="{{ route('maquinas.show', $mq['id']) }}" class="bg-slate-900/50 backdrop-blur-2xl border border-slate-700/50 hover:border-cyan-400/50 rounded-[2rem] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.12)] transition-all duration-300 flex flex-col group relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="flex justify-between items-start mb-6">
                        <div class="p-3 bg-cyan-500/10 border border-cyan-500/30 rounded-2xl shadow-[0_0_15px_rgba(34,211,238,0.15)] group-hover:shadow-[0_0_20px_rgba(34,211,238,0.3)] transition-all">
                            <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                        </div>
                        <div class="text-right">
                            <span class="text-3xl font-thin text-white tracking-tighter">{{ $mq['porcentaje_llenado'] }}%</span>
                            <p class="text-[10px] text-cyan-500 font-mono tracking-widest uppercase mt-1">Llenado</p>
                        </div>
                    </div>

                    <h3 class="text-xl font-light text-white tracking-tight mb-1">{{ $mq['nombre'] }}</h3>
                    
                    <div class="mt-auto pt-6 flex flex-col gap-3">
                        <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-cyan-400 h-1.5 rounded-full shadow-[0_0_8px_rgba(34,211,238,0.8)]" style="width: {{ $mq['porcentaje_llenado'] }}%"></div>
                        </div>
                        
                        <div class="flex justify-between text-xs font-light text-slate-400">
                            <span>{{ $mq['secciones'] }} Secciones | {{ $mq['pisos'] }} Pisos</span>
                            <span class="text-slate-300">{{ $mq['total_actual'] }} / {{ $mq['total_capacidad'] }} bot.</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

        </div>

        <!-- Modal Create Maquina -->
        <x-modal name="create-maquina" focusable>
            <form method="POST" action="{{ route('maquinas.store') }}" class="p-6 bg-slate-900 border border-slate-700 rounded-xl text-slate-200">
                @csrf
                <h2 class="text-xl font-light text-cyan-400 mb-6">Diseñador Automático de Máquina</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-slate-400 mb-1">Nombre / Identificador</label>
                        <input type="text" name="nombre" required class="w-full bg-black border border-slate-700 rounded-lg text-white focus:border-cyan-500 focus:ring-cyan-500" placeholder="Ej: Visicooler Red Bull">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-slate-400 mb-1">Secciones (Puertas)</label>
                            <input type="number" name="secciones" min="1" max="10" required class="w-full bg-black border border-slate-700 rounded-lg text-white focus:border-cyan-500 focus:ring-cyan-500" value="1">
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-slate-400 mb-1">Pisos por Sección</label>
                            <input type="number" name="pisos" min="1" max="20" required class="w-full bg-black border border-slate-700 rounded-lg text-white focus:border-cyan-500 focus:ring-cyan-500" value="4">
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-slate-400 mb-1">Posiciones (Caras)</label>
                            <input type="number" name="caras" min="1" max="20" required class="w-full bg-black border border-slate-700 rounded-lg text-white focus:border-cyan-500 focus:ring-cyan-500" value="5" title="Cuantas botellas caben de frente en cada repisa">
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-slate-400 mb-1">Capacidad de Fondo</label>
                            <input type="number" name="fondo" min="1" max="50" required class="w-full bg-black border border-slate-700 rounded-lg text-white focus:border-cyan-500 focus:ring-cyan-500" value="6" title="Cuantas botellas caben hacia el fondo">
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 text-slate-400 hover:text-white transition-colors">Cancelar</button>
                    <button type="submit" class="bg-cyan-500 hover:bg-cyan-400 text-black font-bold py-2 px-6 rounded-lg shadow-[0_0_15px_rgba(34,211,238,0.4)] transition-all">Generar Máquina</button>
                </div>
            </form>
        </x-modal>

    </div>
</x-app-layout>
