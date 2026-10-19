{{-- 
========================================================================================
VISTA: LISTADO GENERAL DE RECEPCIONES DE MERCADERÍA
RUTA: /recepciones
CONTROLADOR: RecepcionController@index
PROPÓSITO:
- Visualizar el historial cronológico de todas las recepciones de inventario registradas en el ERP.
- Facilitar el acceso rápido a los 3 métodos de ingreso: Factura OCR, Escáner Rápido y Manual.
- Proveer filtrado en tiempo real por búsqueda de texto (factura, proveedor, ID) y vista de Papelera (soft-deletes).
- Distinguir visualmente el estado del ciclo de vida de la recepción:
  * BORRADOR: Ingresada inicialmente pero sin finalizar.
  * PENDIENTE_FECHA: Requiere conciliación de fechas de vencimiento de lotes (FEFO).
  * CONFIRMADO: Inventario ya impactado en las existencias físicas de la bodega de destino.
  * EN PAPELERA: Eliminación lógica recuperable o purga permanente.
- Diseño responsivo dual: Tabla completa para Desktop y tarjetas compactas optimizadas para dispositivos móviles.
========================================================================================
--}}

<x-app-layout>
    {{-- Slot del Encabezado Superior: Título descriptivo y Barra de Accesos Directos --}}
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            {{-- Identificación del módulo y descripción de funciones operativas --}}
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-200">
                    {{ __('Ingreso de Mercadería') }}
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Control de recepciones, conciliación FEFO y digitalización de facturas</p>
            </div>

            {{-- Botonera de acciones principales y métodos de captura de mercadería --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Botón para alternar a la vista de elementos eliminados lógicamente (Soft-Deletes) --}}
                <a href="{{ route('recepciones.index', ['estado' => 'papelera']) }}" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-rose-400 transition-all bg-slate-800 hover:bg-slate-700 rounded-xl border border-slate-700">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Papelera
                </a>

                {{-- Botón para registrar recepción tradicional mediante formulario manual sin automatizaciones --}}
                <a href="{{ route('recepciones.manual') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-200 transition-all bg-slate-800 hover:bg-slate-700 rounded-xl border border-slate-700">
                    <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    + Ingreso Manual
                </a>

                {{-- Botón para iniciar el modo de escaneo ultra-rápido por pistola de código de barras USB / cámara --}}
                <a href="{{ route('recepciones.scanner') }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-emerald-300 transition-all bg-emerald-950/70 hover:bg-emerald-900 rounded-xl border border-emerald-500/40 shadow-sm">
                    <svg class="w-4 h-4 mr-1.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Modo Escáner
                </a>

                {{-- Botón para abrir el extractor inteligente OCR de facturas PDF / JPG mediante Google Cloud Vision --}}
                <a href="{{ route('recepciones.ocr') }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-[0_0_15px_rgba(79,70,229,0.4)]">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Escanear Factura
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Contenedor del Cuerpo Principal --}}
    <div class="py-4">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            
            {{-- Banner contextual si el usuario se encuentra navegando la papelera de reciclaje --}}
            @if(request('estado') == 'papelera')
            <div class="mb-4 p-4 bg-rose-500/10 border border-rose-500/20 rounded-xl flex items-center justify-between">
                <div class="flex items-center text-rose-400">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span class="font-semibold">Viendo Papelera</span>
                    <span class="ml-2 text-sm text-rose-300">Estas recepciones fueron eliminadas. Puedes restaurarlas o borrarlas permanentemente.</span>
                </div>
                {{-- Enlace para salir de la papelera y retornar a la lista principal de recepciones activas --}}
                <a href="{{ route('recepciones.index') }}" class="text-sm text-rose-300 hover:text-white transition-colors">Volver a Recepciones</a>
            </div>
            @endif

            {{-- Alertas Flash de feedback al usuario: Notificación de éxito --}}
            @if (session('success'))
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                    <span class="font-medium">Éxito!</span> {{ session('success') }}
                </div>
            @endif
            {{-- Alertas Flash de feedback al usuario: Notificación de error en operaciones --}}
            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                    <span class="font-medium">Error!</span> {{ session('error') }}
                </div>
            @endif

            {{-- Formulario de Búsqueda Rápida: Permite filtrar por número de factura, N° documento o nombre del proveedor --}}
            <div class="mb-4">
                <form action="{{ route('recepciones.index') }}" method="GET" class="flex gap-2">
                    {{-- Campo de texto con valor persistido mediante helper request() --}}
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por número de factura, documento o proveedor..." class="flex-1 bg-slate-900 border-slate-700 text-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    {{-- Botón de envío del formulario de búsqueda --}}
                    <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white font-medium rounded-lg transition-colors border border-slate-600">
                        Buscar
                    </button>
                    {{-- Si hay una búsqueda activa, se muestra un botón para resetear los filtros --}}
                    @if(request('search'))
                        <a href="{{ route('recepciones.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium rounded-lg transition-colors border border-slate-700 flex items-center">
                            Limpiar
                        </a>
                    @endif
                </form>
            </div>

            {{-- Contenedor Card Principal con estilo Glassmorphism y bordes sutiles --}}
            <div class="bg-slate-900/50 backdrop-blur-md shadow-lg shadow-black/50 sm:rounded-xl border border-slate-800/60 overflow-hidden relative">
                <div class="p-6 text-slate-300 border-b border-slate-800/50">
                    
                    {{-- =========================================================================
                         VISTA TABULAR PARA ESCRITORIO (Desktop Table - Visible en md y superior)
                         ========================================================================= --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-400 whitespace-nowrap">
                            {{-- Cabecera con títulos de columna y posicionamiento sticky para scrolls verticales --}}
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
                                {{-- Itera sobre la colección paginada de recepciones --}}
                                @forelse ($recepciones as $recepcion)
                                    {{-- Fila con opacidad atenuada si el registro se encuentra en estado trashed() --}}
                                    <tr class="border-b border-slate-800/50 bg-slate-900/30 hover:bg-slate-800/50 transition-colors {{ $recepcion->trashed() ? 'opacity-75' : '' }}">
                                        {{-- ID formateado en monospace con relleno de ceros a 5 dígitos --}}
                                        <td class="px-6 py-4 font-medium text-slate-200 font-mono text-xs">
                                            #{{ str_pad($recepcion->id, 5, '0', STR_PAD_LEFT) }}
                                        </td>
                                        {{-- Fecha y hora de recepción formateada mediante Carbon en formato día/mes/año --}}
                                        <td class="px-6 py-4">
                                            {{ \Carbon\Carbon::parse($recepcion->fecha_recepcion)->format('d/m/Y H:i') }}
                                        </td>
                                        {{-- Nombre del proveedor con protección de null mediante optional() --}}
                                        <td class="px-6 py-4">
                                            {{ optional($recepcion->proveedor)->nombre }}
                                        </td>
                                        {{-- Nombre de la bodega destino donde ingresará la mercadería --}}
                                        <td class="px-6 py-4">
                                            {{ optional($recepcion->bodega)->nombre }}
                                        </td>
                                        {{-- Número de factura, guía de despacho o documento de referencia del proveedor --}}
                                        <td class="px-6 py-4">
                                            {{ $recepcion->documento_referencia ?? '-' }}
                                        </td>
                                        {{-- Badges de Estado del ciclo de vida de la recepción --}}
                                        <td class="px-6 py-4">
                                            @if($recepcion->trashed())
                                                {{-- Indicador de eliminación lógica (Papelera de reciclaje) --}}
                                                <span class="bg-rose-500/10 text-rose-400 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-rose-500/20">En Papelera</span>
                                            @elseif($recepcion->estado === \App\Enums\EstadoRecepcion::CONFIRMADO)
                                                {{-- Inventario finalizado e ingresado formalmente a las existencias --}}
                                                <span class="bg-emerald-500/10 text-emerald-400 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-emerald-500/20">Confirmado</span>
                                            @elseif($recepcion->estado === \App\Enums\EstadoRecepcion::PENDIENTE_FECHA)
                                                {{-- Requiere ingresar las fechas de vencimiento de cada lote (FEFO) antes de confirmar --}}
                                                <span class="bg-orange-500/10 text-orange-400 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-orange-500/20">Pendiente Fechas</span>
                                            @else
                                                {{-- Estado inicial o borrador en proceso de edición --}}
                                                <span class="bg-amber-500/10 text-amber-400 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-amber-500/20">Borrador</span>
                                            @endif
                                        </td>
                                        {{-- Columna de Acciones contextuales según el estado del registro --}}
                                        <td class="px-6 py-4 space-x-2">
                                            @if($recepcion->trashed())
                                                {{-- Formulario para Restaurar un registro eliminado con Soft-Delete --}}
                                                <form action="{{ route('recepciones.restore', $recepcion->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="font-medium text-emerald-400 hover:text-emerald-300 transition-colors">Restaurar</button>
                                                </form>
                                                {{-- Formulario para Borrado Físico Definitivo de la Base de Datos con confirmación JS --}}
                                                <form action="{{ route('recepciones.force-delete', $recepcion->id) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('¿Eliminar definitivamente? Esta acción no se puede deshacer.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-medium text-rose-500 hover:text-rose-400 transition-colors">Borrar Definitivo</button>
                                                </form>
                                            @else
                                                {{-- Si faltan fechas de vencimiento, enlaza directo a la pantalla de conciliación FEFO --}}
                                                @if($recepcion->estado === \App\Enums\EstadoRecepcion::PENDIENTE_FECHA)
                                                    <a href="{{ route('recepciones.conciliar', $recepcion) }}" class="font-medium text-orange-400 hover:text-orange-300 transition-colors">Conciliar Fechas</a>
                                                @else
                                                    {{-- De lo contrario, permite ver el desglose completo de la recepción --}}
                                                    <a href="{{ route('recepciones.show', $recepcion) }}" class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors">Ver</a>
                                                @endif
                                                
                                                {{-- Si la recepción NO ha sido confirmada, aún se permite editarla o enviarla a la papelera --}}
                                                @if($recepcion->estado !== \App\Enums\EstadoRecepcion::CONFIRMADO)
                                                    <a href="{{ route('recepciones.edit', $recepcion) }}" class="font-medium text-slate-300 hover:text-white transition-colors ml-2">Editar</a>
                                                    
                                                    {{-- Formulario para enviar a la papelera (Soft-Delete) con modal nativo de confirmación --}}
                                                    <form action="{{ route('recepciones.destroy', $recepcion) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('¿Enviar a la papelera?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="font-medium text-rose-400 hover:text-rose-300 transition-colors">Papelera</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    {{-- Estado vacío si no existen recepciones que coincidan con la consulta --}}
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-500 bg-slate-900/30">
                                            No hay recepciones registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- =========================================================================
                         VISTA EN TARJETAS PARA DISPOSITIVOS MÓVILES (Oculta en md y superior)
                         ========================================================================= --}}
                    <div class="md:hidden space-y-4">
                        @forelse ($recepciones as $recepcion)
                            {{-- Tarjeta individual de recepción adaptada para smartphones --}}
                            <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-800 {{ $recepcion->trashed() ? 'opacity-75' : '' }}">
                                {{-- Cabecera de la tarjeta: ID y Badge de estado --}}
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-mono text-emerald-400 font-bold text-sm">#{{ str_pad($recepcion->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    <div>
                                        @if($recepcion->trashed())
                                            <span class="bg-rose-500/10 text-rose-400 text-xs font-medium px-2 py-0.5 rounded border border-rose-500/20">Papelera</span>
                                        @elseif($recepcion->estado === \App\Enums\EstadoRecepcion::CONFIRMADO)
                                            <span class="bg-emerald-500/10 text-emerald-400 text-xs font-medium px-2 py-0.5 rounded border border-emerald-500/20">Confirmado</span>
                                        @elseif($recepcion->estado === \App\Enums\EstadoRecepcion::PENDIENTE_FECHA)
                                            <span class="bg-orange-500/10 text-orange-400 text-xs font-medium px-2 py-0.5 rounded border border-orange-500/20">Pendiente</span>
                                        @else
                                            <span class="bg-amber-500/10 text-amber-400 text-xs font-medium px-2 py-0.5 rounded border border-amber-500/20">Borrador</span>
                                        @endif
                                    </div>
                                </div>
                                {{-- Datos clave en formato vertical legible --}}
                                <div class="text-sm text-slate-200 mb-1"><span class="text-slate-500">Prov:</span> {{ optional($recepcion->proveedor)->nombre }}</div>
                                <div class="text-sm text-slate-300 mb-1"><span class="text-slate-500">Bodega:</span> {{ optional($recepcion->bodega)->nombre }}</div>
                                <div class="text-xs text-slate-400 mb-3">{{ \Carbon\Carbon::parse($recepcion->fecha_recepcion)->format('d/m/Y H:i') }}</div>
                                
                                {{-- Botonera de acciones adaptada con áreas de pulsación táctil amplia --}}
                                <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-800/50">
                                    @if($recepcion->trashed())
                                        {{-- Botón móvil para restaurar registro --}}
                                        <form action="{{ route('recepciones.restore', $recepcion->id) }}" method="POST" class="w-full">
                                            @csrf
                                            <button type="submit" class="w-full text-center py-2 px-3 bg-emerald-900/30 text-emerald-400 rounded-lg font-medium border border-emerald-700/50">Restaurar</button>
                                        </form>
                                    @else
                                        @if($recepcion->estado === \App\Enums\EstadoRecepcion::PENDIENTE_FECHA)
                                            {{-- Botón directo para ingresar a la conciliación FEFO desde el móvil --}}
                                            <a href="{{ route('recepciones.conciliar', $recepcion) }}" class="flex-1 text-center py-2 px-3 bg-orange-900/30 text-orange-400 rounded-lg font-medium border border-orange-700/50">Conciliar</a>
                                        @else
                                            {{-- Botón para consultar el detalle completo de la recepción en móvil --}}
                                            <a href="{{ route('recepciones.show', $recepcion) }}" class="flex-1 text-center py-2 px-3 bg-indigo-900/30 text-indigo-400 rounded-lg font-medium border border-indigo-700/50">Ver Detalle</a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @empty
                            {{-- Mensaje en caso de no existir recepciones en vista móvil --}}
                            <div class="p-6 text-center text-slate-500 bg-slate-900/30 rounded-lg">
                                No hay recepciones registradas.
                            </div>
                        @endforelse
                    </div>
                    
                    {{-- Paginación estándar de Laravel con renderizado de enlaces TailwindCSS --}}
                    <div class="mt-6">
                        {{ $recepciones->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
