<?php

namespace Tests\Feature\Recepciones;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bodega;
use App\Models\Proveedor;
use App\Models\CatalogoProducto;
use App\Models\Recepcion;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * ============================================================================
 * TEST: ESCÁNER MANUAL DE BARRAS & CONSOLIDACIÓN EN RECEPCIÓN
 * ============================================================================
 * Propósito para la presentación/defensa del proyecto:
 * Valida la entrada manual y con pistola de código de barras:
 * 1. Búsqueda instantánea de SKU en el catálogo maestro.
 * 2. Consolidación automática: Si el operador escanea el mismo producto 2 veces
 *    en líneas separadas, el sistema suma las cantidades y no genera duplicados.
 */
class ScannerRecepcionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Proveedor $proveedor;
    protected Bodega $bodega;
    protected CatalogoProducto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->proveedor = Proveedor::firstOrCreate(
            ['rut' => '96.809.310-K'],
            ['nombre' => 'CCU CHILE', 'codigo' => 'PROV-CCU', 'activo' => true]
        );

        $this->bodega = Bodega::firstOrCreate(
            ['codigo' => 'B-TEST'],
            ['nombre' => 'Bodega Central Test', 'activa' => true, 'es_refrigerada' => false]
        );

        $this->producto = CatalogoProducto::firstOrCreate(
            ['sku' => 'SKU-TEST-99'],
            [
                'nombre' => 'Bebida Prueba 350cc',
                'unidad_medida' => 'UN',
                'precio_compra_ref' => 500,
                'precio_venta' => 1000,
                'constante_vencimiento_meses' => 6,
                'activo' => true
            ]
        );
    }

    /**
     * Prueba que el scanner busca productos reales por SKU en el catálogo.
     */
    public function test_scanner_buscar_producto_retorna_datos_correctos(): void
    {
        $response = $this->getJson(route('recepciones.scanner.buscar', [
            'codigo' => $this->producto->sku,
            'proveedor_id' => $this->proveedor->id,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'producto' => [
                'id' => $this->producto->id,
                'sku' => $this->producto->sku,
                'nombre' => $this->producto->nombre,
            ]
        ]);
    }

    /**
     * Prueba que guardarScanner consolida productos repetidos sumando sus cantidades.
     */
    public function test_guardar_scanner_consolida_filas_repetidas(): void
    {
        $payload = [
            'proveedor_id' => $this->proveedor->id,
            'bodega_id' => $this->bodega->id,
            'documento_referencia' => 'FACT-SCAN-001',
            'items' => [
                ['catalogo_producto_id' => $this->producto->id, 'cantidad' => 3, 'precio_unitario' => 500],
                ['catalogo_producto_id' => $this->producto->id, 'cantidad' => 7, 'precio_unitario' => 500], // Duplicado intencional
            ]
        ];

        $response = $this->postJson(route('recepciones.scanner.guardar'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $recepcion = Recepcion::where('numero_factura', 'FACT-SCAN-001')->first();
        $this->assertNotNull($recepcion);

        // Debe haber consolidado en exactamente 1 detalle con cantidad = 10 (3 + 7)
        $this->assertCount(1, $recepcion->detalles);
        $this->assertEquals(10, (float)$recepcion->detalles->first()->cantidad);
    }
}
