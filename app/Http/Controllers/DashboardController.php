<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatalogoProducto;
use App\Models\LoteStock;
use App\Models\Venta;
use App\Models\Bodega;

class DashboardController extends Controller
{
    public function index()
    {
        // Total de productos en el catálogo
        $totalProductos = CatalogoProducto::count();
        $productosConStock = CatalogoProducto::whereHas('lotesStock', fn($q) => $q->where('cantidad_disponible', '>', 0))->count();
        $productosSinStock = $totalProductos - $productosConStock;
        
        // Total de unidades en stock
        $totalUnidades = \Illuminate\Support\Facades\DB::table('lotes_stock')
            ->where('cantidad_disponible', '>', 0)
            ->sum('cantidad_disponible');

        // Valorización total con JOIN directo — sin N+1
        $valorTotal = \Illuminate\Support\Facades\DB::table('lotes_stock')
            ->join('recepcion_detalles', 'lotes_stock.recepcion_detalle_id', '=', 'recepcion_detalles.id')
            ->where('lotes_stock.cantidad_disponible', '>', 0)
            ->sum(\Illuminate\Support\Facades\DB::raw('recepcion_detalles.precio_unitario * lotes_stock.cantidad_disponible'));

        // Alertas de Vencimiento (Compatible con SQLite y MySQL)
        $lotesVencidos = LoteStock::with(['producto:id,sku,nombre,formato,capacidad', 'bodega:id,codigo,nombre'])
            ->where('cantidad_disponible', '>', 0)
            ->where('fecha_vencimiento', '<', now()->startOfDay())
            ->orderBy('fecha_vencimiento', 'asc')
            ->take(10)
            ->get();

        $lotesPorVencer = LoteStock::with(['producto:id,sku,nombre,formato,capacidad', 'bodega:id,codigo,nombre'])
            ->where('cantidad_disponible', '>', 0)
            ->where('fecha_vencimiento', '>=', now()->startOfDay())
            ->where('fecha_vencimiento', '<=', now()->addDays(30)->endOfDay())
            ->orderBy('fecha_vencimiento', 'asc')
            ->take(10)
            ->get();

        // Todos los counts de alertas
        $now = now();
        $counts = [
            'critico' => LoteStock::where('cantidad_disponible', '>', 0)->where('fecha_vencimiento', '<', $now->copy()->addDays(7)->endOfDay())->count(),
            'alto'    => LoteStock::where('cantidad_disponible', '>', 0)->whereBetween('fecha_vencimiento', [$now->copy()->addDays(7)->endOfDay(), $now->copy()->addDays(14)->endOfDay()])->count(),
            'medio'   => LoteStock::where('cantidad_disponible', '>', 0)->whereBetween('fecha_vencimiento', [$now->copy()->addDays(14)->endOfDay(), $now->copy()->addDays(21)->endOfDay()])->count(),
            'bajo'    => LoteStock::where('cantidad_disponible', '>', 0)->whereBetween('fecha_vencimiento', [$now->copy()->addDays(21)->endOfDay(), $now->copy()->addDays(30)->endOfDay()])->count(),
        ];

        // Sincronización detallada por Bodega (Warehouse Hub)
        $bodegasDetalle = Bodega::where('activa', true)
            ->get()
            ->map(function ($bodega) {
                $lotes = LoteStock::where('bodega_id', $bodega->id)
                    ->where('cantidad_disponible', '>', 0)
                    ->get();

                $unidades = (float)$lotes->sum('cantidad_disponible');
                $lotesCount = $lotes->count();

                $valor = \Illuminate\Support\Facades\DB::table('lotes_stock')
                    ->join('recepcion_detalles', 'lotes_stock.recepcion_detalle_id', '=', 'recepcion_detalles.id')
                    ->where('lotes_stock.bodega_id', $bodega->id)
                    ->where('lotes_stock.cantidad_disponible', '>', 0)
                    ->sum(\Illuminate\Support\Facades\DB::raw('recepcion_detalles.precio_unitario * lotes_stock.cantidad_disponible')) ?? 0;

                $now = now()->startOfDay();
                $vencidosCount = $lotes->filter(fn($l) => $l->fecha_vencimiento && $l->fecha_vencimiento->startOfDay()->lt($now))->count();
                $criticosCount = $lotes->filter(fn($l) => $l->fecha_vencimiento && $l->fecha_vencimiento->startOfDay()->gte($now) && $l->fecha_vencimiento->startOfDay()->lte($now->copy()->addDays(30)))->count();

                return [
                    'id' => $bodega->id,
                    'nombre' => $bodega->nombre,
                    'codigo' => $bodega->codigo,
                    'es_refrigerada' => (bool)$bodega->es_refrigerada,
                    'unidades' => $unidades,
                    'lotes_count' => $lotesCount,
                    'valor' => (float)$valor,
                    'vencidos_count' => $vencidosCount,
                    'criticos_count' => $criticosCount,
                ];
            });

        // Radar FEFO Interactivo: Todos los lotes en riesgo o próximos a vencer con metadata completa
        $radarQuery = LoteStock::with(['producto:id,sku,nombre,formato,capacidad', 'bodega:id,codigo,nombre'])
            ->where('cantidad_disponible', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $now = now()->startOfDay();

        $lotesRadar = $radarQuery->map(function ($lote) use ($now) {
            $dias = $lote->fecha_vencimiento ? (int)$now->diffInDays($lote->fecha_vencimiento->startOfDay(), false) : 999;

            $filtro = 'optimo';
            if ($dias < 0) {
                $filtro = 'vencido';
            } elseif ($dias <= 7) {
                $filtro = 'critico';
            } elseif ($dias <= 30) {
                $filtro = 'alerta30';
            } elseif ($dias <= 60) {
                $filtro = 'alerta60';
            } elseif ($dias <= 90) {
                $filtro = 'alerta90';
            } elseif ($dias <= 180) {
                $filtro = 'alerta180';
            } elseif ($dias <= 365) {
                $filtro = 'alerta365';
            }

            return [
                'id' => $lote->id,
                'producto_nombre' => $lote->producto->nombre ?? 'Producto Desconocido',
                'producto_sku' => $lote->producto->sku ?? '-',
                'formato' => $lote->producto->formato ?? 'Lata',
                'capacidad' => $lote->producto->capacidad ?? '350 cc',
                'bodega_id' => $lote->bodega_id,
                'bodega_codigo' => $lote->bodega->codigo ?? '-',
                'bodega_nombre' => $lote->bodega->nombre ?? '-',
                'cantidad' => (float)$lote->cantidad_disponible,
                'fecha_vencimiento' => $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : '-',
                'fecha_vencimiento_iso' => $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('Y-m-d') : '',
                'dias_restantes' => $dias,
                'filtro' => $filtro,
            ];
        });

        // Próximo lote vigente a vencer (para KPI de caducidad)
        $proximoLote = $radarQuery->filter(fn($l) => $l->fecha_vencimiento && $l->fecha_vencimiento->startOfDay()->gte($now))->first();
        $proximaCaducidadTexto = $proximoLote ? ($now->diffInDays($proximoLote->fecha_vencimiento->startOfDay()) . ' días (' . $proximoLote->fecha_vencimiento->format('d/m/Y') . ')') : 'Sin próximas';

        $radarCounts = [
            'todos' => $lotesRadar->count(),
            'vencido' => $lotesRadar->where('dias_restantes', '<', 0)->count(),
            'critico' => $lotesRadar->filter(fn($l) => $l['dias_restantes'] >= 0 && $l['dias_restantes'] <= 7)->count(),
            'mes1' => $lotesRadar->filter(fn($l) => $l['dias_restantes'] >= 0 && $l['dias_restantes'] <= 30)->count(),
            'mes2' => $lotesRadar->filter(fn($l) => $l['dias_restantes'] >= 0 && $l['dias_restantes'] <= 60)->count(),
            'mes3' => $lotesRadar->filter(fn($l) => $l['dias_restantes'] >= 0 && $l['dias_restantes'] <= 90)->count(),
            'mes6' => $lotesRadar->filter(fn($l) => $l['dias_restantes'] >= 0 && $l['dias_restantes'] <= 180)->count(),
            'mes12' => $lotesRadar->filter(fn($l) => $l['dias_restantes'] >= 0 && $l['dias_restantes'] <= 365)->count(),
            'optimo' => $lotesRadar->where('dias_restantes', '>', 365)->count(),
        ];

        // Últimas Ventas
        $ultimasVentas = Venta::with(['bodega', 'user'])->latest()->take(5)->get();
        $totalVentasMonto = Venta::sum('total');

        // Facturas esperando revisión de fechas
        $recepcionesPendientes = \App\Models\Recepcion::with('proveedor')
            ->where('estado', \App\Enums\EstadoRecepcion::PENDIENTE_FECHA->value)
            ->get();

        // Data for Chart: Stock por Proveedor
        $stockPorProveedor = \Illuminate\Support\Facades\DB::table('lotes_stock')
            ->join('catalogo_productos', 'lotes_stock.catalogo_producto_id', '=', 'catalogo_productos.id')
            ->join('mapeo_codigos', 'catalogo_productos.id', '=', 'mapeo_codigos.catalogo_producto_id')
            ->join('proveedores', 'mapeo_codigos.proveedor_id', '=', 'proveedores.id')
            ->select('proveedores.nombre', \Illuminate\Support\Facades\DB::raw('SUM(lotes_stock.cantidad_disponible) as total_stock'))
            ->groupBy('proveedores.nombre')
            ->get();
            
        $chartLabels = $stockPorProveedor->pluck('nombre');
        $chartData = $stockPorProveedor->pluck('total_stock');

        // Data for Chart: Stock por Bodega
        $stockPorBodega = \Illuminate\Support\Facades\DB::table('lotes_stock')
            ->join('bodegas', 'lotes_stock.bodega_id', '=', 'bodegas.id')
            ->select('bodegas.nombre', \Illuminate\Support\Facades\DB::raw('SUM(lotes_stock.cantidad_disponible) as total_stock'))
            ->groupBy('bodegas.nombre')
            ->get();
        
        $chartBodegaLabels = $stockPorBodega->pluck('nombre');
        $chartBodegaData = $stockPorBodega->pluck('total_stock');

        // Data for Chart: Ventas de los últimos 7 días
        $ventasPorDia = \App\Models\Venta::select(
                \Illuminate\Support\Facades\DB::raw('DATE(fecha_venta) as fecha'),
                \Illuminate\Support\Facades\DB::raw('SUM(total) as total_ventas')
            )
            ->where('fecha_venta', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get();
        
        $chartVentasLabels = collect();
        $chartVentasData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartVentasLabels->push(now()->subDays($i)->format('d M'));
            $venta = $ventasPorDia->firstWhere('fecha', $date);
            $chartVentasData->push($venta ? $venta->total_ventas : 0);
        }

        // Chart: Productos por vencer en los próximos 90 días (agrupados por mes)
        $mesesEspanol = [
            'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr',
            'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
            'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic',
        ];

        $vencimientosPorMes = collect();
        for ($i = 0; $i < 4; $i++) {
            $start = now()->addMonths($i)->startOfMonth();
            $end   = now()->addMonths($i)->endOfMonth();
            $count = LoteStock::where('cantidad_disponible', '>', 0)
                ->whereBetween('fecha_vencimiento', [$start, $end])
                ->count();
            
            $mesIngles = $start->format('M');
            $mesTraducido = ($mesesEspanol[$mesIngles] ?? $mesIngles) . ' ' . $start->format('Y');
            $vencimientosPorMes->push(['mes' => $mesTraducido, 'count' => $count]);
        }
        $chartVencLabels = $vencimientosPorMes->pluck('mes')->values()->toArray();
        $chartVencData   = $vencimientosPorMes->pluck('count')->values()->toArray();

        return view('dashboard', compact(
            'totalProductos',
            'productosConStock',
            'productosSinStock',
            'totalUnidades',
            'valorTotal',
            'lotesVencidos',
            'lotesPorVencer',
            'ultimasVentas',
            'totalVentasMonto',
            'recepcionesPendientes',
            'chartLabels',
            'chartData',
            'counts',
            'chartBodegaLabels',
            'chartBodegaData',
            'chartVentasLabels',
            'chartVentasData',
            'chartVencLabels',
            'chartVencData',
            'bodegasDetalle',
            'lotesRadar',
            'radarCounts',
            'proximaCaducidadTexto'
        ));
    }
}
