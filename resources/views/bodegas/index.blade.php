<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Gestión de Bodegas') }}
            </h2>
            <a href="{{ route('bodegas.create') }}" class="px-5 py-2.5 text-sm font-semibold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-lg shadow-indigo-600/30">
                + Nueva Bodega
            </a>
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
                    
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-400 whitespace-nowrap">
                            <thead class="text-xs text-slate-300 uppercase bg-slate-800/80 sticky top-0 z-0 shadow-sm border-b border-slate-700 backdrop-blur-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Código</th>
                                    <th scope="col" class="px-6 py-3">Nombre</th>
                                    <th scope="col" class="px-6 py-3">Refrigerada</th>
                                    <th scope="col" class="px-6 py-3">Estado</th>
                                    <th scope="col" class="px-6 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bodegas as $bodega)
                                    <tr class="border-b border-slate-800/50 bg-slate-900/30 hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-200 font-mono">
                                            {{ $bodega->codigo }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $bodega->nombre }}
                                            @if($bodega->descripcion)
                                                <br><span class="text-xs text-gray-400 font-sans">{{ Str::limit($bodega->descripcion, 50) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($bodega->es_refrigerada)
                                                <span class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Sí (Refrigerada)</span>
                                            @else
                                                <span class="text-gray-500 text-xs font-medium">Normal</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($bodega->activa)
                                                <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Activa</span>
                                            @else
                                                <span class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Inactiva</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('bodegas.show', $bodega) }}" class="font-medium text-emerald-500 hover:text-emerald-400 hover:underline mr-3">Ver Stock</a>
                                            <a href="{{ route('bodegas.edit', $bodega) }}" class="font-medium text-blue-600 hover:underline">Editar</a>
                                            <form action="{{ route('bodegas.destroy', $bodega) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('¿Está seguro de eliminar esta bodega?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 hover:underline">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-500 bg-slate-900/30">
                                            No hay bodegas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-4">
                        @forelse ($bodegas as $bodega)
                            <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-800">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-mono text-emerald-400 font-bold text-sm">{{ $bodega->codigo }}</div>
                                    <div>
                                        @if($bodega->activa)
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-0.5 rounded">Activa</span>
                                        @else
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-0.5 rounded">Inactiva</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-base text-slate-200 font-bold mb-1">{{ $bodega->nombre }}</div>
                                @if($bodega->descripcion)
                                    <div class="text-xs text-gray-400 font-sans mb-2">{{ Str::limit($bodega->descripcion, 50) }}</div>
                                @endif
                                <div class="text-sm text-slate-300 mb-3">
                                    @if($bodega->es_refrigerada)
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded">❄️ Refrigerada</span>
                                    @else
                                        <span class="text-gray-500 text-xs font-medium">📦 Normal</span>
                                    @endif
                                </div>
                                
                                <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-800/50">
                                    <a href="{{ route('bodegas.show', $bodega) }}" class="flex-1 text-center py-2 px-3 bg-emerald-900/30 text-emerald-400 rounded-lg font-medium border border-emerald-700/50">Ver Stock</a>
                                    <a href="{{ route('bodegas.edit', $bodega) }}" class="flex-1 text-center py-2 px-3 bg-indigo-900/30 text-indigo-400 rounded-lg font-medium border border-indigo-700/50">Editar</a>
                                    <form action="{{ route('bodegas.destroy', $bodega) }}" method="POST" class="w-full mt-2" onsubmit="return confirm('¿Está seguro de eliminar esta bodega?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-center py-2 px-3 bg-rose-900/30 text-rose-400 rounded-lg font-medium border border-rose-700/50">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-500 bg-slate-900/30 rounded-lg">
                                No hay bodegas registradas.
                            </div>
                        @endforelse
                    </div>
                    
                    
                    <div class="mt-4">
                        {{ $bodegas->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
