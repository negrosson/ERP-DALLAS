<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bodega;
use App\Models\Proveedor;
use App\Models\CatalogoProducto;
use App\Models\Recepcion;
use App\Models\Venta;
use App\Models\LoteStock;
use App\Models\MapeoCodigo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class AntiDuplicadosTest extends TestCase
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
     * Prueba que el scanner busca productos reales por SKU.
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
     * Prueba que check-factura detecta facturas ya registradas para un proveedor.
     */
    public function test_check_factura_detecta_factura_existente(): void
    {
        Recepcion::create([
            'proveedor_id' => $this->proveedor->id,
            'bodega_id' => $this->bodega->id,
            'user_id' => $this->user->id,
            'numero_factura' => 'FACT-88899',
            'documento_referencia' => 'FACT-88899',
            'fecha_recepcion' => now(),
            'estado' => \App\Enums\EstadoRecepcion::CONFIRMADO->value,
        ]);

        $response = $this->getJson(route('recepciones.check-factura', [
            'proveedor_id' => $this->proveedor->id,
            'numero_factura' => 'fact-88899', // Case insensitive
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'exists' => true
        ]);
    }

    /**
     * Prueba que guardarScanner consolida productos duplicados sumando sus cantidades.
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

    /**
     * Prueba que no se permite guardar una factura duplicada en el escáner.
     */
    public function test_guardar_scanner_rechaza_factura_duplicada(): void
    {
        Recepcion::create([
            'proveedor_id' => $this->proveedor->id,
            'bodega_id' => $this->bodega->id,
            'user_id' => $this->user->id,
            'numero_factura' => 'FACT-REPETIDA-123',
            'documento_referencia' => 'FACT-REPETIDA-123',
            'fecha_recepcion' => now(),
            'estado' => \App\Enums\EstadoRecepcion::CONFIRMADO->value,
        ]);

        $payload = [
            'proveedor_id' => $this->proveedor->id,
            'bodega_id' => $this->bodega->id,
            'documento_referencia' => 'FACT-REPETIDA-123',
            'items' => [
                ['catalogo_producto_id' => $this->producto->id, 'cantidad' => 5, 'precio_unitario' => 500],
            ]
        ];

        $response = $this->postJson(route('recepciones.scanner.guardar'), $payload);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    /**
     * Prueba que VentaController soporta fecha_venta de días anteriores y rechaza subida duplicada.
     */
    public function test_ventas_soporta_fecha_desfasada_y_rechaza_duplicado(): void
    {
        $csvContent = "sku,cantidad\n{$this->producto->sku},2\n";
        $csvFile = UploadedFile::fake()->createWithContent('ventas_ayer.csv', $csvContent);

        $fechaAyer = now()->subDay()->format('Y-m-d');

        // Primera subida con fecha de ayer
        $response1 = $this->post(route('ventas.store'), [
            'bodega_id' => $this->bodega->id,
            'archivo_ventas' => $csvFile,
            'fecha_venta' => $fechaAyer,
        ]);

        $response1->assertRedirect(route('ventas.index'));

        $venta = Venta::where('origen_archivo', 'ventas_ayer.csv')->first();
        $this->assertNotNull($venta);
        $this->assertEquals($fechaAyer, \Carbon\Carbon::parse($venta->fecha_venta)->format('Y-m-d'));

        // Reintento con el mismo archivo y fecha (debe ser rechazado como duplicado)
        $csvFileDuplicado = UploadedFile::fake()->createWithContent('ventas_ayer.csv', $csvContent);
        $response2 = $this->post(route('ventas.store'), [
            'bodega_id' => $this->bodega->id,
            'archivo_ventas' => $csvFileDuplicado,
            'fecha_venta' => $fechaAyer,
        ]);

        $response2->assertSessionHas('error');
    }

    /**
     * Prueba que al importar un CSV de ventas con productos ajenos del negocio (pan, snacks, etc.),
     * el sistema los ignora y procesa exitosamente los productos propios aplicando FEFO.
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
