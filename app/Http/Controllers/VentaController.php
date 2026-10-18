<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Bodega;
use App\Models\CatalogoProducto;
use App\Models\MapeoCodigo;
use Illuminate\Http\Request;
use App\Services\InventarioService;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['bodega', 'user'])->latest()->paginate(10);
        return view('ventas.index', compact('ventas'));
    }

    public function create()
    {
        $bodegas = Bodega::where('activa', true)->get();
        return view('ventas.create', compact('bodegas'));
    }

    /**
     * Procesa la importación de ventas diarias o semanales mediante archivo CSV (POS).
     *
     * MANEJO DE TIEMPOS Y FILTRADO DE PRODUCTOS:
     * 1. Filtrado Automático: El archivo POS del negocio puede traer cientos de productos ajenos
     *    (pan, snacks, abarrotes, etc.). El sistema solo procesa los productos propios (Coca-Cola, CCU, etc.)
     *    y omite silenciosamente los demás sin fallar ni requerir limpieza manual previa en Excel.
     * 2. Mapeo Flexible: Reconoce tanto el SKU del catálogo como los códigos de proveedor o barras (MapeoCodigo).
     * 3. Manejo de Desfases: Permite indicar la fecha exacta de venta (días anteriores) para mantener
     *    las estadísticas e histórico contable exactos.
     * 4. Integridad FEFO: Descuenta los lotes por orden de vencimiento (más próximo a vencer primero).
     * 5. Prevención de Duplicados: Evita la re-importación accidental del mismo archivo para la misma fecha y bodega.
     *
     * @param Request $request
     * @param \App\Services\DescuentoFefoService $fefoService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, \App\Services\DescuentoFefoService $fefoService)
    {
        $request->validate([
            'bodega_id' => 'required|exists:bodegas,id',
            'archivo_ventas' => 'required|file|mimes:csv,txt|max:5120',
            'fecha_venta' => 'nullable|date|before_or_equal:today',
        ], [
            'fecha_venta.before_or_equal' => 'La fecha de la venta no puede ser futura.',
        ]);

        $file = $request->file('archivo_ventas');
        $csvData = file_get_contents($file->getRealPath());

        if (empty(trim($csvData))) {
            return back()->with('error', 'El archivo CSV está vacío.')->withInput();
        }

        // Detectar delimitador (, o ; o \t)
        $firstLine = strtok($csvData, "\r\n");
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        $lines = preg_split("/\r\n|\n|\r/", trim($csvData));
        $rows = array_map(function ($line) use ($delimiter) {
            return str_getcsv($line, $delimiter);
        }, $lines);

        $header = array_shift($rows);
        if (!$header) {
            return back()->with('error', 'El archivo CSV no contiene encabezados válidos.')->withInput();
        }

        $headerLower = array_map(function ($h) {
            return strtolower(trim((string)$h));
        }, (array)$header);

        // Buscar columna SKU / Código (admite variantes comunes de POS)
        $posiblesSku = ['sku', 'codigo', 'codigo_barra', 'cod_articulo', 'barcode', 'articulo', 'producto', 'producto_id'];
        $skuIndex = false;
        foreach ($posiblesSku as $col) {
            $idx = array_search($col, $headerLower);
            if ($idx !== false) {
                $skuIndex = $idx;
                break;
            }
        }

        // Buscar columna Cantidad
        $posiblesQty = ['cantidad', 'cant', 'qty', 'unidades', 'total_cantidad'];
        $qtyIndex = false;
        foreach ($posiblesQty as $col) {
            $idx = array_search($col, $headerLower);
            if ($idx !== false) {
                $qtyIndex = $idx;
                break;
            }
        }

        // Buscar columna Precio (opcional)
        $posiblesPrecio = ['precio', 'precio_unitario', 'unit_price', 'precio_venta', 'valor_unitario', 'valor'];
        $precioIndex = false;
        foreach ($posiblesPrecio as $col) {
            $idx = array_search($col, $headerLower);
            if ($idx !== false) {
                $precioIndex = $idx;
                break;
            }
        }

        if ($skuIndex === false || $qtyIndex === false) {
            return back()->with('error', 'El archivo CSV debe contener al menos una columna de código ("sku" o "codigo") y una de cantidad ("cantidad" o "cant").')->withInput();
        }

        // =========================================================================
        // DETERMINACIÓN DE LA FECHA REAL DE VENTA (SOPORTE PARA VENTAS RETRASADAS)
        // =========================================================================
        if ($request->filled('fecha_venta')) {
            $fechaVenta = \Carbon\Carbon::parse($request->fecha_venta)->setTime(now()->hour, now()->minute, now()->second);
        } else {
            // Intentar detectar si el CSV trae columna de fecha
            $fechaIndex = array_search('fecha', $headerLower);
            if ($fechaIndex === false) {
                $fechaIndex = array_search('fecha_venta', $headerLower);
            }
            if ($fechaIndex !== false && !empty($rows[0][$fechaIndex])) {
                try {
                    $fechaVenta = \Carbon\Carbon::parse(trim($rows[0][$fechaIndex]));
                } catch (\Exception $e) {
                    $fechaVenta = now();
                }
            } else {
                $fechaVenta = now();
            }
        }

        // =========================================================================
        // REGLA ANTI-DUPLICADOS: PREVENCIÓN DE DOBLE CARGA DE PLANILLAS DE VENTA
        // =========================================================================
        $nombreArchivo = $file->getClientOriginalName();
        $ventaExistente = Venta::where('bodega_id', $request->bodega_id)
            ->whereDate('fecha_venta', $fechaVenta->toDateString())
            ->where('origen_archivo', $nombreArchivo)
            ->first();

        if ($ventaExistente) {
            $fechaCarga = $ventaExistente->created_at ? $ventaExistente->created_at->format('d/m/Y H:i') : 'previamente';
            return back()->with('error', "El archivo \"{$nombreArchivo}\" ya fue procesado el {$fechaCarga} para esta bodega con fecha {$fechaVenta->format('d/m/Y')}. No se permite subir una planilla duplicada para evitar doble deducción de inventario.")->withInput();
        }

        try {
            DB::beginTransaction();

            $venta = Venta::create([
                'fecha_venta' => $fechaVenta,
                'total' => 0,
                'origen_archivo' => $nombreArchivo,
                'bodega_id' => $request->bodega_id,
                'user_id' => auth()->id(),
            ]);

            $totalVenta = 0;
            $ventasParaFefo = [];
            $procesadosCount = 0;
            $unidadesProcesadas = 0;
            $omitidosCount = 0;

            // Cache en memoria para optimizar y evitar consultas N+1 en archivos grandes
            $cacheProductosPorSku = CatalogoProducto::all()->keyBy(function ($p) {
                return strtoupper(trim($p->sku));
            });
            $cacheMapeos = MapeoCodigo::with('catalogoProducto')->get()->keyBy(function ($m) {
                return strtoupper(trim($m->codigo_proveedor));
            });

            foreach ($rows as $index => $row) {
                if (empty($row) || empty(trim(implode('', $row)))) continue; // Saltar filas vacías

                if (!isset($row[$skuIndex]) || !isset($row[$qtyIndex])) continue;

                $codigoInput = trim($row[$skuIndex]);
                if ($codigoInput === '') continue;

                $cantidad = (float) str_replace(',', '.', trim($row[$qtyIndex]));
                if ($cantidad <= 0) continue;

                $codigoUpper = strtoupper($codigoInput);

                // 1. Buscar en catálogo por SKU directo
                $producto = $cacheProductosPorSku->get($codigoUpper);

                // 2. Si no está en catálogo directo, buscar por mapeo de código de proveedor/POS (ej. CCU, Coca-Cola)
                if (!$producto && $cacheMapeos->has($codigoUpper)) {
                    $producto = $cacheMapeos->get($codigoUpper)->catalogoProducto;
                }

                // 3. Si el producto NO es de nuestro catálogo (es pan, abarrotes, snacks, etc. del local)
                // Se OMITE automáticamente sin fallar, permitiendo subir el archivo completo del POS sin limpiarlo antes
                if (!$producto) {
                    $omitidosCount++;
                    continue;
                }

                // Acumular para el motor FEFO
                $skuProducto = $producto->sku;
                if (isset($ventasParaFefo[$skuProducto])) {
                    $ventasParaFefo[$skuProducto] += $cantidad;
                } else {
                    $ventasParaFefo[$skuProducto] = $cantidad;
                }

                // Determinar precio unitario
                $precioUnitario = 0;
                if ($precioIndex !== false && isset($row[$precioIndex])) {
                    $precioRaw = (float) str_replace(['$', '.', ' '], ['', '', ''], trim($row[$precioIndex]));
                    if ($precioRaw > 0) {
                        $precioUnitario = $precioRaw;
                    }
                }

                if ($precioUnitario <= 0) {
                    $precioUnitario = $producto->precio_venta > 0 ? (float)$producto->precio_venta : 1000;
                }

                $subtotal = $cantidad * $precioUnitario;

                $venta->detalles()->create([
                    'catalogo_producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotal,
                ]);

                $totalVenta += $subtotal;
                $procesadosCount++;
                $unidadesProcesadas += $cantidad;
            }

            // Si ninguna fila coincidió con productos propios
            if ($procesadosCount === 0) {
                DB::rollBack();
                return back()->with('error', "No se encontraron productos de tu catálogo en el archivo CSV subido ({$omitidosCount} productos ajenos ignorados). Verifica que los códigos correspondan a tus SKUs o códigos de proveedor mapeados.")->withInput();
            }

            $venta->update(['total' => $totalVenta]);

            // Procesar stock irrefutable a través de la cascada FEFO en la bodega seleccionada
            $fefoService->procesarVentasDiarias($ventasParaFefo, $request->bodega_id);

            DB::commit();

            $msg = "Ventas procesadas con éxito. Se registraron {$unidadesProcesadas} unidades vendidas ({$procesadosCount} productos) y se dedujo el stock por vencimiento FEFO.";
            if ($omitidosCount > 0) {
                $msg .= " Se omitieron automáticamente {$omitidosCount} líneas de productos que no pertenecen a tu catálogo (productos ajenos del POS).";
            }

            return redirect()->route('ventas.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error procesando ventas: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error inesperado al procesar las ventas. Por favor, inténtelo de nuevo más tarde.');
        }
    }

    public function show(Venta $venta)
    {
        $venta->load(['bodega', 'user', 'detalles.producto']);
        return view('ventas.show', compact('venta'));
    }
}
