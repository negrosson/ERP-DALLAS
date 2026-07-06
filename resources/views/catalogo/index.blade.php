<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Catálogo Maestro de Productos') }}
            </h2>
            <a href="{{ route('catalogo.create') }}" class="px-5 py-2.5 text-sm font-semibold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-lg shadow-indigo-600/30">
                + Nuevo Producto
            </a>
        </div>
    </x-slot>

    <div class="py-6">
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
                                    <th scope="col" class="px-6 py-3">SKU</th>
                                    <th scope="col" class="px-6 py-3">Producto</th>
                                    <th scope="col" class="px-6 py-3">Unidad</th>
                                    <th scope="col" class="px-6 py-3">Precio Venta</th>
                                    <th scope="col" class="px-6 py-3">Autocalculo Venc.</th>
                                    <th scope="col" class="px-6 py-3">Estado</th>
                                    <th scope="col" class="px-6 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($productos as $producto)
                                    <tr class="border-b border-slate-800/50 bg-slate-900/30 hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-200 font-mono text-xs">
                                            {{ $producto->sku }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $producto->nombre }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $producto->unidad_medida }}
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-emerald-400">
                                            ${{ number_format($producto->precio_venta, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($producto->tieneAutocalculoVencimiento())
                                                <span class="text-xs text-indigo-400 bg-indigo-500/10 rounded px-2.5 py-0.5 border border-indigo-500/20">{{ $producto->constante_vencimiento_meses }} meses</span>
                                            @else
                                                <span class="text-xs text-slate-500">Manual</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($producto->activo)
                                                <span class="bg-emerald-500/10 text-emerald-400 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-emerald-500/20">Activo</span>
                                            @else
                                                <span class="bg-rose-500/10 text-rose-400 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-rose-500/20">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 space-x-2">
                                            <a href="{{ route('catalogo.show', $producto) }}" class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors">Ver Detalles</a>
                                            <a href="{{ route('catalogo.edit', $producto) }}" class="font-medium text-blue-400 hover:text-blue-300 transition-colors">Editar</a>
                                            <form action="{{ route('catalogo.destroy', $producto) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de eliminar este producto del catálogo?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-rose-400 hover:text-rose-300 transition-colors">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-500 bg-slate-900/30">
                                            No hay productos en el catálogo maestro.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $productos->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
