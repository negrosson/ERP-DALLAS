{{-- 
========================================================================================
VISTA: LISTADO HISTÓRICO DE IMPORTACIONES DE VENTAS
RUTA: /ventas
CONTROLADOR: VentaController@index
PROPÓSITO:
- Mostrar el registro consolidado de todas las planillas de ventas procesadas en el ERP.
- Permite auditar qué bodega o máquina fue impactada, la fecha comercial de venta imputada,
  el monto monetario total de la liquidación, el nombre del archivo fuente y el usuario responsable.
- Provee acceso directo a la vista de detalle de cada transacción para inspeccionar los lotes descontados.
- Diseño responsivo dual: Tabla de escritorio y tarjetas táctiles para smartphones.
========================================================================================
--}}

<x-app-layout>
    {{-- Slot del encabezado: Título y botón para iniciar nueva importación de ventas --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-200">
                    {{ __('Registro de Ventas') }}
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Historial de ventas y deducción de lotes por fecha de vencimiento</p>
            </div>
            {{-- Enlace hacia la pantalla de carga masiva de planillas CSV --}}
            <a href="{{ route('ventas.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-bold text-sm text-white shadow-lg shadow-indigo-600/30 transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Importar Planilla de Ventas
            </a>
        </div>
    </x-slot>

    {{-- Contenedor del contenido principal --}}
    <div class="py-6">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            
            {{-- Notificación Flash de éxito tras procesar una importación --}}
            @if(session('success'))
                <div class="p-4 mb-4 text-sm text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center gap-2" role="alert">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Tarjeta contenedora con efecto cristal y bordes discretos --}}
            <div class="bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative">
                <div class="p-6 text-slate-300">
                    
                    {{-- =========================================================================
                         VISTA TABULAR PARA PANTALLAS DE ESCRITORIO (Visible en md y superior)
                         ========================================================================= --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-400 whitespace-nowrap">
                            <thead class="text-xs text-slate-300 uppercase bg-slate-800/80 sticky top-0 z-0 shadow-sm border-b border-slate-700 backdrop-blur-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-3">ID</th>
                                    <th scope="col" class="px-6 py-3">Fecha Venta</th>
                                    <th scope="col" class="px-6 py-3">Bodega Origen</th>
                                    <th scope="col" class="px-6 py-3">Total Venta</th>
                                    <th scope="col" class="px-6 py-3">Archivo Origen</th>
                                    <th scope="col" class="px-6 py-3">Registrado por</th>
                                    <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Itera sobre la colección de ventas paginadas --}}
                                @forelse ($ventas as $venta)
                                    <tr class="border-b border-slate-800/50 bg-slate-900/30 hover:bg-slate-800/50 transition-colors">
                                        {{-- Folio identificador de la transacción --}}
                                        <td class="px-6 py-4 font-medium text-slate-200 font-mono text-xs">#{{ $venta->id }}</td>
                                        {{-- Fecha comercial imputada y timestamp de registro en el servidor --}}
                                        <td class="px-6 py-4">
                                            <span class="text-slate-200 font-semibold">{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</span>
                                            <span class="text-xs text-slate-500 block">Subido: {{ $venta->created_at ? $venta->created_at->format('d/m H:i') : '-' }}</span>
                                        </td>
                                        {{-- Nombre de la bodega afectada por la deducción de existencias --}}
                                        <td class="px-6 py-4">{{ $venta->bodega->nombre }}</td>
                                        {{-- Monto monetario total facturado en la importación --}}
                                        <td class="px-6 py-4 font-bold text-emerald-400">${{ number_format($venta->total, 0, ',', '.') }}</td>
                                        {{-- Nombre del archivo CSV cargado --}}
                                        <td class="px-6 py-4 text-xs text-slate-400 font-mono">{{ $venta->origen_archivo ?? 'N/A' }}</td>
                                        {{-- Usuario operador que ejecutó la importación --}}
                                        <td class="px-6 py-4 text-xs text-slate-400">{{ $venta->user->name ?? 'Sistema' }}</td>
                                        {{-- Botón para consultar el detalle de líneas deducidas --}}
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('ventas.show', $venta) }}" class="text-indigo-400 hover:text-indigo-300 font-medium transition-colors text-xs bg-indigo-950/60 px-3 py-1.5 rounded-lg border border-indigo-700/40">Ver Detalles</a>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- Mensaje para tabla vacía --}}
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-500 bg-slate-900/30">
                                            No se han registrado importaciones de ventas aún.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- =========================================================================
                         VISTA EN TARJETAS PARA DISPOSITIVOS MÓVILES (Visible en pantallas < md)
                         ========================================================================= --}}
                    <div class="md:hidden space-y-4">
                        @forelse ($ventas as $venta)
                            <div class="bg-slate-900/70 border border-slate-800 rounded-xl p-4 shadow-sm">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-mono text-xs text-indigo-400 font-bold">#{{ $venta->id }}</div>
                                    <div class="text-emerald-400 font-bold text-sm">${{ number_format($venta->total, 0, ',', '.') }}</div>
                                </div>
                                <div class="text-sm font-semibold text-slate-200">{{ $venta->bodega->nombre }}</div>
                                <div class="text-xs text-slate-400 mt-1">
                                    Fecha Venta: <span class="text-slate-300 font-bold">{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</span>
                                </div>
                                <div class="text-xs text-slate-500 font-mono mt-1 truncate">
                                    Archivo: {{ $venta->origen_archivo ?? 'N/A' }}
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-800 flex justify-between items-center">
                                    <span class="text-[10px] text-slate-500">Por: {{ $venta->user->name ?? 'Sistema' }}</span>
                                    <a href="{{ route('ventas.show', $venta) }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-bold">Ver Detalles &rarr;</a>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-500 text-sm">
                                No se han registrado importaciones de ventas aún.
                            </div>
                        @endforelse
                    </div>

                    {{-- Enlaces de paginación estándar de Laravel --}}
                    <div class="mt-6">
                        {{ $ventas->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
