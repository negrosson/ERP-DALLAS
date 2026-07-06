<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\MapeoCodigoController;
use App\Http\Controllers\BodegaController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\RecepcionDetalleController;
use App\Http\Controllers\LoteStockController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\MermaController;
use App\Http\Controllers\DevolucionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rutas del ERP
    Route::resource('proveedores', ProveedorController::class)->parameters(['proveedores' => 'proveedor']);
    Route::resource('catalogo', CatalogoController::class)->parameters(['catalogo' => 'catalogo']);
    Route::resource('mapeos', MapeoCodigoController::class)->parameters(['mapeos' => 'mapeo']);
    Route::resource('bodegas', BodegaController::class)->parameters(['bodegas' => 'bodega']);
    
    // Recepciones (Ingreso Mercadería)
    Route::get('recepciones/scanner', [RecepcionController::class, 'scanner'])->name('recepciones.scanner');
    Route::get('recepciones/ocr', [RecepcionController::class, 'ocr'])->name('recepciones.ocr');
    Route::resource('recepciones', RecepcionController::class)->except(['edit', 'update']);
    Route::post('recepciones/{recepcion}/confirm', [RecepcionController::class, 'confirm'])->name('recepciones.confirm');
    
    // Rutas para Máquina de Estados (Conciliación de Fechas)
    Route::get('recepciones/{recepcion}/conciliar', [RecepcionController::class, 'conciliar'])->name('recepciones.conciliar');
    Route::post('recepciones/{recepcion}/conciliar', [RecepcionController::class, 'guardarConciliacion'])->name('recepciones.conciliar.store');
    
    // Para simplificar, la ruta de detalles de recepción original usaba una estructura un poco distinta en mis controladores generados, 
    // pero aquí usaré las explícitas que tenía.
    Route::post('recepciones/{recepcion}/detalles', [RecepcionDetalleController::class, 'store'])->name('recepciones.detalles.store');
    Route::delete('recepciones/{recepcion}/detalles/{detalle}', [RecepcionDetalleController::class, 'destroy'])->name('recepciones.detalles.destroy');

    // Inventario (Consulta de Stock / FEFO)
    Route::get('lotes', [LoteStockController::class, 'index'])->name('lotes.index');

    // Fase 3: Ventas, Mermas y Devoluciones
    Route::resource('ventas', VentaController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('mermas', MermaController::class)->only(['index', 'create', 'store']);
    Route::resource('devoluciones', DevolucionController::class)->only(['index', 'create', 'store']);
});

require __DIR__.'/auth.php';
