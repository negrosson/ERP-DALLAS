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

        // Valorización Total del Inventario (usando precio * cantidad_disponible)
        // Para simplificar asumiendo precio_venta como valor. Idealmente se usaría costo_unitario,
        // pero recepcion_detalles tiene precio_unitario (costo). Aquí haremos una aproximación.
        $lotes = LoteStock::where('cantidad_disponible', '>', 0)->get();
        $valorTotal = 0;
        foreach ($lotes as $lote) {
            // Buscamos el costo en el detalle de la recepción
            $costo = optional($lote->recepcionDetalle)->precio_unitario ?? 0;
            $valorTotal += $costo * $lote->cantidad_disponible;
        }

        // Alertas de Vencimiento (Listado combinado para la tabla FEFO)
        $lotesVencidos = LoteStock::with(['producto', 'bodega'])
            ->where('cantidad_disponible', '>', 0)
            ->where('fecha_vencimiento', '<', now()->startOfDay())
            ->orderBy('fecha_vencimiento', 'asc')
            ->take(10)
            ->get();

        $lotesPorVencer = LoteStock::with(['producto', 'bodega'])
            ->where('cantidad_disponible', '>', 0)
            ->where('fecha_vencimiento', '>=', now()->startOfDay())
            ->where('fecha_vencimiento', '<=', now()->addDays(30)->endOfDay())
            ->orderBy('fecha_vencimiento', 'asc')
            ->take(10)
            ->get();

        // Conteos específicos para los badges
        $counts = [
            'critico' => LoteStock::where('cantidad_disponible', '>', 0)->where('fecha_vencimiento', '<', now()->addDays(7)->endOfDay())->count(), // < 7 dias y vencidos
            'alto' => LoteStock::where('cantidad_disponible', '>', 0)->where('fecha_vencimiento', '>', now()->addDays(7)->endOfDay())->where('fecha_vencimiento', '<=', now()->addDays(14)->endOfDay())->count(), // 7 - 14 dias
            'medio' => LoteStock::where('cantidad_disponible', '>', 0)->where('fecha_vencimiento', '>', now()->addDays(14)->endOfDay())->where('fecha_vencimiento', '<=', now()->addDays(21)->endOfDay())->count(), // 14 - 21 dias
            'bajo' => LoteStock::where('cantidad_disponible', '>', 0)->where('fecha_vencimiento', '>', now()->addDays(21)->endOfDay())->where('fecha_vencimiento', '<=', now()->addDays(30)->endOfDay())->count(), // 21 - 30 dias
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

        return view('dashboard', compact(
            'totalProductos',
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
            'chartVentasData'
        ));
    }
}
