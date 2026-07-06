<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-slate-200">
                {{ __('Ingreso de Mercadería (Recepciones)') }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('recepciones.create') }}" class="px-5 py-2.5 text-sm font-semibold text-white transition-all bg-slate-700 hover:bg-slate-600 rounded-xl shadow-lg border border-slate-600">
                    + Ingreso Manual
                </a>
                <a href="{{ route('recepciones.scanner') }}" class="flex items-center px-5 py-2.5 text-sm font-bold text-slate-900 transition-all bg-emerald-500 hover:bg-emerald-400 rounded-xl shadow-[0_0_15px_rgba(16,185,129,0.4)]">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Modo Escáner
                </a>
                <a href="{{ route('recepciones.ocr') }}" class="flex items-center px-5 py-2.5 text-sm font-bold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-[0_0_15px_rgba(79,70,229,0.4)]">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Escanear Factura (OCR)
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                    <span class="font-medium">Éxito!</span> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                    <span class="font-medium">Error!</span> {{ session('error') }}
                </div>
            @endif

            <div class="bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative">
                <div class="p-6 text-slate-300 border-b border-slate-800/50">
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-400 whitespace-nowrap">
                            <thead class="text-xs text-slate-300 uppercase bg-slate-800/80 sticky top-0 z-0 shadow-sm border-b border-slate-700 backdrop-blur-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-3">ID</th>
                                    <th scope="col" class="px-6 py-3">Fecha</th>
                                    <th scope="col" class="px-6 py-3">Proveedor</th>
                                    <th scope="col" class="px-6 py-3">Bodega Destino</th>
                                    <th scope="col" class="px-6 py-3">Documento</th>
                                    <th scope="col" class="px-6 py-3">Estado</th>
                                    <th scope="col" class="px-6 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recepciones as $recepcion)
                                    <tr class="border-b border-slate-800/50 bg-slate-900/30 hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-200 font-mono text-xs">
                                            #{{ str_pad($recepcion->id, 5, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ \Carbon\Carbon::parse($recepcion->fecha_recepcion)->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ optional($recepcion->proveedor)->nombre }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ optional($recepcion->bodega)->nombre }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $recepcion->documento_referencia ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($recepcion->estado === \App\Enums\EstadoRecepcion::CONFIRMADO)
                                                <span class="bg-emerald-500/10 text-emerald-400 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-emerald-500/20">Confirmado</span>
                                            @else
                                                <span class="bg-amber-500/10 text-amber-400 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-amber-500/20">Borrador</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 space-x-2">
                                            <a href="{{ route('recepciones.show', $recepcion) }}" class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors">Ver / Gestionar</a>
                                            
                                            @if($recepcion->estado === \App\Enums\EstadoRecepcion::BORRADOR)
                                            <form action="{{ route('recepciones.destroy', $recepcion) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de eliminar esta recepción en borrador?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-rose-400 hover:text-rose-300 transition-colors">Eliminar</button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-500 bg-slate-900/30">
                                            No hay recepciones registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $recepciones->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
