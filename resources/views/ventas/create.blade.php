{{-- 
========================================================================================
VISTA: FORMULARIO DE IMPORTACIÓN DE PLANILLA DE VENTAS (POS / CAJA)
RUTA: /ventas/create
CONTROLADOR: VentaController@create -> VentaController@store
PROPÓSITO:
- Permitir la subida masiva de transacciones comerciales desde archivos CSV exportados por sistemas POS o cajas.
- Funcionalidades clave:
  * FILTRADO AUTOMÁTICO DE PRODUCTOS AJENOS: Permite subir la planilla completa sin necesidad de depuración manual.
    El ERP reconoce los productos del catálogo y descarta automáticamente ítems no asociados (ej: pan, golosinas ajenas).
  * SOPORTE PARA DESFASES COMERCIALES: Selector de "Fecha de las Ventas" con validación de no exceder el día actual,
    garantizando que reportes de fines de semana o turnos anteriores imputen en el período contable exacto.
  * DEDUCCIÓN ESTRICTA POR FEFO: Descuenta automáticamente las unidades de los lotes con caducidad más cercana.
  * PROTECCIÓN ANTI-DUPLICADOS: Evita descontar dos veces la misma planilla en la misma bodega y fecha.
  * RETROALIMENTACIÓN DE ERRORES: Panel de advertencia si alguna fila presenta inconsistencias de formato.
========================================================================================
--}}

<x-app-layout>
    {{-- Slot del encabezado: Título descriptivo y botón de navegación de retorno --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-200">
                    {{ __('Importar Planilla de Ventas') }}
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Deducción de stock por vencimiento con filtrado automático de productos ajenos</p>
            </div>
            {{-- Enlace para volver al listado histórico de importaciones de ventas --}}
            <a href="{{ route('ventas.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-slate-300 transition-all bg-slate-800 hover:bg-slate-700 rounded-xl border border-slate-700">
                &larr; Volver a Ventas
            </a>
        </div>
    </x-slot>

    {{-- Contenedor del formulario central --}}
    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Alerta Flash de error general en la carga o procesamiento del archivo --}}
            @if(session('error'))
                <div class="mb-6 bg-rose-500/10 border border-rose-500/30 text-rose-300 px-4 py-3 rounded-xl flex items-start gap-3 shadow-lg" role="alert">
                    <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>
                        <strong class="font-bold text-rose-200">Error en la carga:</strong>
                        <p class="text-sm mt-1">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Alerta Flash detallada con la lista de filas con inconsistencias en el CSV --}}
            @if(session('errores_csv'))
                <div class="mb-6 bg-rose-500/10 border border-rose-500/30 text-rose-300 px-4 py-3 rounded-xl shadow-lg" role="alert">
                    <strong class="font-bold text-rose-200">Se encontraron inconsistencias en las filas del archivo:</strong>
                    <ul class="mt-2 text-xs list-disc list-inside space-y-1">
                        @foreach(session('errores_csv') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Tarjeta principal del formulario con estética oscura y bordes sutiles --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl shadow-black/50 p-6 sm:p-8">
                
                {{-- Guía explicativa para los operadores sobre el funcionamiento de la importación masiva --}}
                <div class="mb-6 bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-4 text-xs text-indigo-300 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-indigo-200 text-sm">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        ¿Cómo funciona la importación de ventas?
                    </div>
                    <p class="leading-relaxed">
                        • <strong>Filtrado Automático de Productos Ajenos:</strong> Puedes subir la planilla completa de tu POS o caja sin filtrar. El ERP identificará automáticamente tus productos (Coca-Cola, CCU, etc.) y omitirá cualquier otro ítem que no esté en tu catálogo (pan, abarrotes, snacks), sin causar errores.
                    </p>
                    <p class="leading-relaxed">
                        • <strong>Ventas de Días Anteriores (Desfases):</strong> Si subes ventas de ayer, del fin de semana o semanales, indica la fecha comercial correspondiente en <em>"Fecha de las Ventas"</em>. El sistema mantendrá los reportes y estadísticas en su día real.
                    </p>
                    <p class="leading-relaxed">
                        • <strong>Deducción FEFO:</strong> Descuenta prioritariamente los lotes con fecha de vencimiento más próxima (<code class="bg-slate-950 px-1 py-0.5 rounded text-emerald-400 font-mono">vencimiento más cercano primero</code>).
                    </p>
                    <p class="leading-relaxed">
                        • <strong>Protección Anti-Duplicados:</strong> Si el mismo archivo ya fue subido para esa bodega y fecha, la operación se bloquea para evitar doble descuento de mercadería.
                    </p>
                </div>

                {{-- Formulario multipart para subir archivo CSV --}}
                <form method="POST" action="{{ route('ventas.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Selector: Bodega o Máquina donde se originaron las ventas y de donde se deducirá el stock --}}
                        <div>
                            <label for="bodega_id" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                Bodega / Máquina Origen <span class="text-rose-500">*</span>
                            </label>
                            <select id="bodega_id" name="bodega_id" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                @foreach($bodegas as $bodega)
                                    <option value="{{ $bodega->id }}" {{ (old('bodega_id') == $bodega->id || $bodega->codigo === 'B-SALA') ? 'selected' : '' }}>
                                        [{{ $bodega->codigo }}] {{ $bodega->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('bodega_id')" class="mt-2 text-rose-400 text-xs" />
                        </div>

                        {{-- Selector de Fecha de la Venta: Soporta fechas pasadas para imputar ventas con desfase --}}
                        <div>
                            <label for="fecha_venta" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                Fecha de las Ventas <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="fecha_venta" name="fecha_venta" value="{{ old('fecha_venta', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" class="block w-full bg-slate-950 border-slate-800 text-slate-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            <span class="text-[11px] text-slate-500 mt-1 block">Si son ventas de ayer, seleccione la fecha correspondiente.</span>
                            <x-input-error :messages="$errors->get('fecha_venta')" class="mt-2 text-rose-400 text-xs" />
                        </div>
                    </div>

                    {{-- Dropzone / Selector de Archivo CSV --}}
                    <div>
                        <label for="archivo_ventas" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                            Archivo CSV de Ventas <span class="text-rose-500">*</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-800 border-dashed rounded-xl hover:border-indigo-500 transition-colors bg-slate-950">
                            <div class="space-y-2 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-500" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <div class="flex text-sm text-slate-400 justify-center">
                                    <label for="archivo_ventas" class="relative cursor-pointer font-bold text-indigo-400 hover:text-indigo-300">
                                        <span>Seleccionar archivo CSV</span>
                                        {{-- Input de archivo oculto con handler JS para mostrar el nombre seleccionado --}}
                                        <input id="archivo_ventas" name="archivo_ventas" type="file" accept=".csv,.txt" class="sr-only" required onchange="document.getElementById('file-chosen').textContent = this.files[0] ? this.files[0].name : '';">
                                    </label>
                                </div>
                                <p class="text-xs text-slate-500">Columnas requeridas: <code>sku</code> (o <code>codigo</code>) y <code>cantidad</code>. Puedes subir el reporte completo de caja/POS.</p>
                                {{-- Contenedor donde se imprime el nombre del archivo seleccionado --}}
                                <p id="file-chosen" class="text-xs font-mono text-emerald-400 mt-2 font-bold"></p>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('archivo_ventas')" class="mt-2 text-rose-400 text-xs" />
                    </div>

                    {{-- Botones de Acción: Cancelar y Procesar Importación --}}
                    <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <a href="{{ route('ventas.index') }}" class="w-full sm:w-auto text-center px-5 py-3 text-sm font-semibold text-slate-400 hover:text-white transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 text-sm font-bold text-white transition-all bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-[0_0_15px_rgba(79,70,229,0.4)] flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Procesar e Importar Ventas
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
