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
use App\Http\Controllers\MaquinaController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Buscador Global
    Route::get('/search', [SearchController::class, 'global'])->name('search.global');
    
    // Rutas del ERP
    Route::post('proveedores/ajax-store', [ProveedorController::class, 'ajaxStore'])->name('proveedores.ajax-store');
    Route::resource('proveedores', ProveedorController::class)->parameters(['proveedores' => 'proveedor']);
    Route::get('catalogo/{catalogo}/lotes', [CatalogoController::class, 'lotes'])->name('catalogo.lotes');
    Route::post('catalogo/quick-store', [CatalogoController::class, 'quickStore'])->name('catalogo.quick-store');
    Route::resource('catalogo', CatalogoController::class)->parameters(['catalogo' => 'catalogo']);
    Route::resource('mapeos', MapeoCodigoController::class)->parameters(['mapeos' => 'mapeo']);
    Route::resource('bodegas', BodegaController::class)->parameters(['bodegas' => 'bodega']);
    Route::post('bodegas/{bodega}/quick-lote', [BodegaController::class, 'quickAddLote'])->name('bodegas.quick-lote');
    Route::post('bodegas/ajustar/{lote}', [BodegaController::class, 'ajustarStock'])->name('bodegas.ajustar');
    Route::post('bodegas/{bodega}/producto/{producto}/ajuste-masivo', [BodegaController::class, 'ajustarMasivo'])->name('bodegas.ajuste-masivo');
    Route::post('bodegas/revertir/{ajuste}', [BodegaController::class, 'revertirAjuste'])->name('bodegas.revertir');
    
    // Máquinas Visicooler
    Route::resource('maquinas', MaquinaController::class)->only(['index', 'show', 'store', 'destroy']);
    Route::post('maquinas/slot/{slotId}/assign', [MaquinaController::class, 'assignProduct'])->name('maquinas.slot.assign');
    Route::post('maquinas/slot/{slotId}/unassign', [MaquinaController::class, 'unassignProduct'])->name('maquinas.slot.unassign');
    Route::post('maquinas/slot/{slotId}/add', [MaquinaController::class, 'addStock'])->name('maquinas.slot.add');
    Route::post('maquinas/slot/{slotId}/remove', [MaquinaController::class, 'removeStock'])->name('maquinas.slot.remove');

    // Recepciones (Ingreso Mercadería)
    Route::get('recepciones/manual', [RecepcionController::class, 'manual'])->name('recepciones.manual');
    Route::post('recepciones/manual', [RecepcionController::class, 'storeManual'])->name('recepciones.manual.store');
    
    Route::get('recepciones/scanner', [RecepcionController::class, 'scanner'])->name('recepciones.scanner');
    Route::get('recepciones/ocr', [RecepcionController::class, 'ocr'])->name('recepciones.ocr');
    Route::post('recepciones/ocr-process', [RecepcionController::class, 'processOcr'])->name('recepciones.ocr.process');
    
    // Specific routes must come before resource
    Route::post('recepciones/{id}/restore', [RecepcionController::class, 'restore'])->name('recepciones.restore');
    Route::delete('recepciones/{id}/force-delete', [RecepcionController::class, 'forceDelete'])->name('recepciones.force-delete');
    Route::post('recepciones/{recepcion}/confirm', [RecepcionController::class, 'confirm'])->name('recepciones.confirm');
    
    Route::resource('recepciones', RecepcionController::class)->parameters(['recepciones' => 'recepcion']);
    
    // Rutas para Máquina de Estados (Conciliación de Fechas)
    Route::get('recepciones/{recepcion}/conciliar', [RecepcionController::class, 'conciliar'])->name('recepciones.conciliar');
    Route::post('recepciones/{recepcion}/conciliar', [RecepcionController::class, 'guardarConciliacion'])->name('recepciones.conciliar.store');
    
    // Para simplificar, la ruta de detalles de recepción original usaba una estructura un poco distinta en mis controladores generados, 
    // pero aquí usaré las explícitas que tenía.
    Route::post('recepciones/{recepcion}/detalles', [RecepcionDetalleController::class, 'store'])->name('recepciones.detalles.store');
    Route::delete('recepciones/{recepcion}/detalles/{detalle}', [RecepcionDetalleController::class, 'destroy'])->name('recepciones.detalles.destroy');

    Route::post('catalogo/{catalogo}/lotes', [CatalogoController::class, 'crearLote'])->name('catalogo.lotes.store');
    Route::get('catalogo/{catalogo}/lotes', [CatalogoController::class, 'lotes'])->name('catalogo.lotes');
    Route::get('lotes', [LoteStockController::class, 'index'])->name('lotes.index');
    Route::put('lotes/{lote}/fechas', [LoteStockController::class, 'updateFechas'])->name('lotes.fechas.update');
    Route::put('lotes/{lote}/full', [LoteStockController::class, 'updateFull'])->name('lotes.full.update');
    Route::put('lotes/{lote}/transferir', [LoteStockController::class, 'transferirBodega'])->name('lotes.transferir');

    // Fase 3: Ventas, Mermas y Devoluciones
    Route::resource('ventas', VentaController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('mermas', MermaController::class)->only(['index', 'create', 'store']);
    Route::resource('devoluciones', DevolucionController::class)->only(['index', 'create', 'store']);
});

require __DIR__.'/auth.php';
