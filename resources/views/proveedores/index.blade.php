<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Proveedores') }}
            </h2>
            <a href="{{ route('proveedores.create') }}" class="px-5 py-2.5 text-sm font-semibold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-lg shadow-indigo-600/30">
                + Nuevo Proveedor
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
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-400 whitespace-nowrap">
                            <thead class="text-xs text-slate-300 uppercase bg-slate-800/80 sticky top-0 z-0 shadow-sm border-b border-slate-700 backdrop-blur-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Código</th>
                                    <th scope="col" class="px-6 py-3">RUT</th>
                                    <th scope="col" class="px-6 py-3">Razón Social</th>
                                    <th scope="col" class="px-6 py-3">Contacto</th>
                                    <th scope="col" class="px-6 py-3">Estado</th>
                                    <th scope="col" class="px-6 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($proveedores as $proveedor)
                                    <tr class="border-b border-slate-800/50 bg-slate-900/30 hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-200 font-mono text-xs">
                                            {{ $proveedor->codigo }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $proveedor->rut }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $proveedor->nombre }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $proveedor->contacto_nombre }} <br>
                                            <span class="text-xs text-slate-500">{{ $proveedor->contacto_telefono }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($proveedor->activo)
                                                <span class="bg-emerald-500/10 text-emerald-400 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-emerald-500/20">Activo</span>
                                            @else
                                                <span class="bg-rose-500/10 text-rose-400 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-rose-500/20">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 space-x-2">
                                            <a href="{{ route('proveedores.edit', $proveedor) }}" class="font-medium text-blue-400 hover:text-blue-300 transition-colors">Editar</a>
                                            <form action="{{ route('proveedores.destroy', $proveedor) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de eliminar este proveedor?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-rose-400 hover:text-rose-300 transition-colors">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 bg-slate-900/30">
                                            No hay proveedores registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $proveedores->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
