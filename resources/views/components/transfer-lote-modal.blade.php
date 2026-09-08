@props(['bodegas'])

<div x-data="{ open: false, loteId: '', bodegaNombre: '' }" 
     @open-transfer-lote-modal.window="open = true; loteId = $event.detail.id; bodegaNombre = $event.detail.bodegaNombre;"
     x-show="open" 
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
     style="display: none;">
    
    <div @click.outside="open = false" class="bg-slate-900 border border-slate-700 p-6 rounded-xl shadow-2xl w-full max-w-md" x-transition>
        
        <button @click="open = false" class="absolute top-4 right-4 text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-full p-1.5 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <h3 class="text-xl font-bold text-white mb-1">Transferir Lote #<span x-text="loteId"></span></h3>
        <p class="text-sm text-slate-400 mb-6">Bodega Actual: <span class="font-semibold text-amber-400" x-text="bodegaNombre"></span></p>
        
        <form :action="'{{ url('lotes') }}/' + loteId + '/transferir'" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-300 mb-1">Bodega Destino</label>
                <select name="bodega_destino_id" required class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5">
                    <option value="">Seleccione una bodega...</option>
                    @foreach($bodegas as $b)
                        <option value="{{ $b->id }}">{{ $b->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-300 mb-1">Motivo / Observaciones</label>
                <input type="text" name="motivo" required placeholder="Ej. Reordenamiento de inventario" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" @click="open = false" class="text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-600 focus:ring-4 focus:outline-none focus:ring-slate-700 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors">Cancelar</button>
                <button type="submit" class="text-white bg-amber-600 hover:bg-amber-700 focus:ring-4 focus:outline-none focus:ring-amber-800 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors shadow-sm">Transferir</button>
            </div>
        </form>
    </div>
</div>
