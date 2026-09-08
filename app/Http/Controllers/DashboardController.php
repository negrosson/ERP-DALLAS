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

        // Alertas de Vencimiento (Listado combinado para la tabla FEFO)
        $lotesVencidos = LoteStock::with(['producto:id,nombre', 'bodega:id,nombre'])
            ->where('cantidad_disponible', '>', 0)
            ->where('fecha_vencimiento', '<', now()->startOfDay())
            ->orderBy('fecha_vencimiento', 'asc')
            ->take(10)
            ->get();

        $lotesPorVencer = LoteStock::with(['producto:id,nombre', 'bodega:id,nombre'])
            ->select('lotes_stock.*')
            ->join('catalogo_productos', 'lotes_stock.catalogo_producto_id', '=', 'catalogo_productos.id')
            ->where('lotes_stock.cantidad_disponible', '>', 0)
            ->where('lotes_stock.fecha_vencimiento', '>=', now()->startOfDay())
            ->whereRaw('lotes_stock.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL IFNULL(catalogo_productos.dias_alerta_vencimiento, 30) DAY)')
            ->orderBy('lotes_stock.fecha_vencimiento', 'asc')
            ->take(10)
            ->get();

        // Todos los counts en una sola query
        $now = now();
        $counts = LoteStock::where('cantidad_disponible', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->selectRaw("
                SUM(CASE WHEN fecha_vencimiento < ? THEN 1 ELSE 0 END) as critico,
                SUM(CASE WHEN fecha_vencimiento >= ? AND fecha_vencimiento <= ? THEN 1 ELSE 0 END) as alto,
                SUM(CASE WHEN fecha_vencimiento > ? AND fecha_vencimiento <= ? THEN 1 ELSE 0 END) as medio,
                SUM(CASE WHEN fecha_vencimiento > ? AND fecha_vencimiento <= ? THEN 1 ELSE 0 END) as bajo
            ", [
                $now->copy()->addDays(7)->endOfDay(),
                $now->copy()->addDays(7)->endOfDay(),  $now->copy()->addDays(14)->endOfDay(),
                $now->copy()->addDays(14)->endOfDay(), $now->copy()->addDays(21)->endOfDay(),
                $now->copy()->addDays(21)->endOfDay(), $now->copy()->addDays(30)->endOfDay(),
            ])
            ->first()
            ->toArray();

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
        $vencimientosPorMes = collect();
        for ($i = 0; $i < 3; $i++) {
            $start = now()->addMonths($i)->startOfMonth();
            $end   = now()->addMonths($i)->endOfMonth();
            $count = LoteStock::where('cantidad_disponible', '>', 0)
                ->whereBetween('fecha_vencimiento', [$start, $end])
                ->count();
            $vencimientosPorMes->push(['mes' => $start->format('M Y'), 'count' => $count]);
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
            'chartVencData'
        ));
    }
}
