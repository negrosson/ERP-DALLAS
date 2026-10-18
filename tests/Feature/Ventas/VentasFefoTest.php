<?php

namespace Tests\Feature\Ventas;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bodega;
use App\Models\Proveedor;
use App\Models\CatalogoProducto;
use App\Models\Venta;
use App\Models\LoteStock;
use App\Models\MapeoCodigo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

/**
 * ============================================================================
 * TEST: INGESTIÓN DE VENTAS (POS/CSV) & DEDUCCIÓN AUTOMÁTICA FEFO
 * ============================================================================
 * Propósito para la presentación/defensa del proyecto:
 * Valida la recepción de ventas diarias/semanales con reglas de negocio avanzadas:
 * 1. Manejo de tiempos desfasados: Soporta subir ventas del día anterior o días pasados
 *    sin desincronizar los saldos históricos ni corromper las fechas.
 * 2. Filtrado inteligente de productos ajenos: El archivo POS suele traer pan, snacks, abarrotes,
 *    el sistema omite automáticamente los productos que no son del catálogo Dallas y procesa los propios.
 * 3. Rotación FEFO (First Expired, First Out): Al registrar la venta, el sistema descuenta
 *    unidades del lote activo con la fecha de vencimiento más próxima en esa bodega.
 * 4. Mapeo de Códigos de Proveedor: Permite cruzar códigos de barra del POS con el SKU interno.
 */
class VentasFefoTest extends TestCase
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
            ['sku' => 'SKU-BEB-350'],
            [
                'nombre' => 'Bebida 350cc Lata',
                'unidad_medida' => 'UN',
                'precio_compra_ref' => 500,
                'precio_venta' => 1000,
                'constante_vencimiento_meses' => 6,
                'activo' => true
            ]
        );
    }

    /**
     * Prueba que VentaController soporta fecha_venta de días anteriores (desfases temporales).
     */
    public function test_ventas_soporta_fecha_desfasada_de_dias_anteriores(): void
    {
        $csvContent = "sku,cantidad\n{$this->producto->sku},2\n";
        $csvFile = UploadedFile::fake()->createWithContent('ventas_ayer.csv', $csvContent);

        $fechaAyer = now()->subDay()->format('Y-m-d');

        $response = $this->post(route('ventas.store'), [
            'bodega_id' => $this->bodega->id,
            'archivo_ventas' => $csvFile,
            'fecha_venta' => $fechaAyer,
        ]);

        $response->assertRedirect(route('ventas.index'));

        $venta = Venta::where('origen_archivo', 'ventas_ayer.csv')->first();
        $this->assertNotNull($venta);
        $this->assertEquals($fechaAyer, \Carbon\Carbon::parse($venta->fecha_venta)->format('Y-m-d'));
    }

    /**
     * Prueba que al importar un CSV con productos ajenos al negocio (pan, snacks, cigarros),
     * el sistema los omite y procesa exitosamente los productos propios aplicando rotación FEFO.
     */
    public function test_ventas_csv_con_productos_ajenos_omite_ajenos_y_procesa_los_propios(): void
    {
        // Crear lote con stock en la bodega para el producto propio
        $lote = LoteStock::create([
            'catalogo_producto_id' => $this->producto->id,
            'bodega_id' => $this->bodega->id,
            'lote_codigo' => 'LOTE-TEST-FEFO-01',
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 10,
            'fecha_vencimiento' => now()->addDays(30),
        ]);

        // CSV simulando el POS con productos ajenos (pan, papas) y nuestro producto
        $csvContent = "sku,cantidad\n" .
                      "PAN-MARRAQUETA,5\n" .
                      "{$this->producto->sku},4\n" .
                      "SNACK-DORITOS,12\n" .
                      "CIGARROS-LUCKY,2\n";

        $csvFile = UploadedFile::fake()->createWithContent('ventas_pos_completo.csv', $csvContent);

        $response = $this->post(route('ventas.store'), [
            'bodega_id' => $this->bodega->id,
            'archivo_ventas' => $csvFile,
            'fecha_venta' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('ventas.index'));
        $response->assertSessionHas('success');

        // Verificar que la venta se creó y SOLO tiene el producto del catálogo Dallas
        $venta = Venta::where('origen_archivo', 'ventas_pos_completo.csv')->first();
        $this->assertNotNull($venta);
        $this->assertCount(1, $venta->detalles);
        $this->assertEquals(4, (float)$venta->detalles->first()->cantidad);

        // Verificar que el stock FEFO del lote se redujo de 10 a 6 (10 - 4)
        $lote->refresh();
        $this->assertEquals(6, (float)$lote->cantidad_disponible);
    }

    /**
     * Prueba que un CSV que contiene únicamente productos ajenos al negocio se rechaza amablemente.
     */
    public function test_ventas_csv_solo_con_productos_ajenos_se_rechaza_amablemente(): void
    {
        $csvContent = "sku,cantidad\n" .
                      "PAN-MARRAQUETA,5\n" .
                      "SNACK-DORITOS,12\n";

        $csvFile = UploadedFile::fake()->createWithContent('ventas_solo_ajenos.csv', $csvContent);

        $response = $this->post(route('ventas.store'), [
            'bodega_id' => $this->bodega->id,
            'archivo_ventas' => $csvFile,
            'fecha_venta' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('ventas', ['origen_archivo' => 'ventas_solo_ajenos.csv']);
    }

    /**
     * Prueba que un CSV de ventas puede usar el código del proveedor (MapeoCodigo)
     * para identificar el producto del catálogo propio.
     */
    public function test_ventas_csv_resuelve_por_mapeo_codigo_proveedor(): void
    {
        MapeoCodigo::create([
            'proveedor_id' => $this->proveedor->id,
            'catalogo_producto_id' => $this->producto->id,
            'codigo_proveedor' => 'COD-POS-BARCODE-999',
            'descripcion_proveedor' => 'Lata 350cc POS',
        ]);

        $lote = LoteStock::create([
            'catalogo_producto_id' => $this->producto->id,
            'bodega_id' => $this->bodega->id,
            'lote_codigo' => 'LOTE-TEST-MAPEO-01',
            'cantidad_inicial' => 20,
            'cantidad_disponible' => 20,
            'fecha_vencimiento' => now()->addDays(45),
        ]);

        $csvContent = "codigo,cantidad\n" .
                      "COD-POS-BARCODE-999,3\n" .
                      "PRODUCTO-DESCONOCIDO,10\n";

        $csvFile = UploadedFile::fake()->createWithContent('ventas_mapeadas.csv', $csvContent);

        $response = $this->post(route('ventas.store'), [
            'bodega_id' => $this->bodega->id,
            'archivo_ventas' => $csvFile,
            'fecha_venta' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('ventas.index'));
        $response->assertSessionHas('success');

        $lote->refresh();
        $this->assertEquals(17, (float)$lote->cantidad_disponible);
    }
}
