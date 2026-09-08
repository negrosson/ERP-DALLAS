<?php

namespace App\Http\Controllers;

use App\Models\Merma;
use App\Models\Bodega;
use App\Models\CatalogoProducto;
use Illuminate\Http\Request;
use App\Services\InventarioService;
use Illuminate\Support\Facades\DB;

class MermaController extends Controller
{
    public function index()
    {
        $mermas = Merma::with(['producto', 'bodega', 'user'])->latest()->paginate(10);
        return view('mermas.index', compact('mermas'));
    }

    public function create()
    {
        $bodegas = Bodega::where('activa', true)->get();
        $productos = CatalogoProducto::where('activo', true)->orderBy('nombre')->get();
        return view('mermas.create', compact('bodegas', 'productos'));
    }

    public function store(Request $request, InventarioService $inventarioService)
    {
        $request->validate([
            'catalogo_producto_id' => 'required|exists:catalogo_productos,id',
            'bodega_id' => 'required|exists:bodegas,id',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request, $inventarioService) {
                // Descontar stock
                $inventarioService->descontarStock(
                    $request->catalogo_producto_id,
                    $request->bodega_id,
                    $request->cantidad
                );

                // Registrar merma
                Merma::create([
                    'catalogo_producto_id' => $request->catalogo_producto_id,
                    'bodega_id' => $request->bodega_id,
                    'cantidad' => $request->cantidad,
                    'motivo' => $request->motivo,
                    'fecha_merma' => now(),
                    'user_id' => auth()->id(),
                ]);
            });

            return redirect()->route('mermas.index')->with('success', 'Merma registrada y stock descontado exitosamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error registrando merma: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error inesperado al registrar la merma.')->withInput();
        }
    }
}
