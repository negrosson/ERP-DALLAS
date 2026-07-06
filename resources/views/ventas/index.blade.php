<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ventas (Importador POS)') }}
            </h2>
            <a href="{{ route('ventas.create') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-semibold text-sm text-white shadow-lg shadow-indigo-600/30 transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Importar Ventas (CSV)
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative">
                <div class="p-6 text-slate-300 overflow-x-auto">
                    
                    <table class="w-full text-sm text-left text-slate-400 whitespace-nowrap">
                        <thead class="text-xs text-slate-300 uppercase bg-slate-800/80 sticky top-0 z-0 shadow-sm border-b border-slate-700 backdrop-blur-sm">
                            <tr>
                                <th scope="col" class="px-6 py-3">ID Transacción</th>
                                <th scope="col" class="px-6 py-3">Fecha</th>
                                <th scope="col" class="px-6 py-3">Bodega</th>
                                <th scope="col" class="px-6 py-3">Total Venta</th>
                                <th scope="col" class="px-6 py-3">Archivo Origen</th>
                                <th scope="col" class="px-6 py-3">Registrado por</th>
                                <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ventas as $venta)
                                <tr class="border-b border-slate-800/50 bg-slate-900/30 hover:bg-slate-800/50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-200">#{{ $venta->id }}</td>
                                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4">{{ $venta->bodega->nombre }}</td>
                                    <td class="px-6 py-4 font-bold text-emerald-400">${{ number_format($venta->total, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-xs text-slate-500 font-mono">{{ $venta->origen_archivo ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $venta->user->name }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('ventas.show', $venta) }}" class="text-indigo-400 hover:text-indigo-300 font-medium transition-colors">Ver Detalles</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-500 bg-slate-900/30">
                                        No se han registrado importaciones de ventas aún.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $ventas->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
