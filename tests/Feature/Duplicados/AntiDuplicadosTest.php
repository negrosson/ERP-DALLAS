<?php

namespace Tests\Feature\Duplicados;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bodega;
use App\Models\Proveedor;
use App\Models\CatalogoProducto;
use App\Models\Recepcion;
use App\Models\Venta;
use App\Services\OcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;

/**
 * ============================================================================
 * TEST: POLÍTICA ESTRICTA ANTI-DUPLICADOS (FACTURAS, ESCÁNER, OCR Y VENTAS)
 * ============================================================================
 * Propósito para la presentación/defensa del proyecto:
 * Demuestra la robustez del sistema frente al error humano más común en bodegas:
 * 1. Comprobación previa (AJAX): El sistema advierte en tiempo real si el N° de factura ya existe.
 * 2. Bloqueo en Escáner Manual: No permite guardar una factura previamente ingresada.
 * 3. Bloqueo en Escáner OCR / IA: Si la imagen leída contiene un N° de factura existente, se aborta el guardado.
 * 4. Bloqueo en Carga de Ventas: Impide cargar dos veces el mismo archivo de ventas para el mismo día.
 */
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
     * Prueba que el endpoint check-factura detecta facturas ya registradas para un proveedor.
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
            'numero_factura' => 'fact-88899', // Insensible a mayúsculas
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'exists' => true
        ]);
    }

    /**
     * Prueba que no se permite guardar una factura duplicada en el escáner manual.
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
     * Prueba que el escaneo OCR rechaza procesar una factura que ya fue ingresada previamente
     * para el mismo proveedor (Anti-duplicación estricta en OCR).
     */
    public function test_ocr_rechaza_factura_duplicada_si_ya_existe_en_el_sistema(): void
    {
        Recepcion::create([
            'proveedor_id' => $this->proveedor->id,
            'bodega_id' => $this->bodega->id,
            'user_id' => $this->user->id,
            'numero_factura' => '888125',
            'documento_referencia' => '888125',
            'fecha_recepcion' => now(),
            'estado' => \App\Enums\EstadoRecepcion::CONFIRMADO->value,
        ]);

        $mockOcr = Mockery::mock(OcrService::class);
        $mockOcr->shouldReceive('processInvoiceImage')->once()->andReturn([
            'emisor_razon_social' => 'COMPAÑIA CERVECERIAS UNIDAS S.A.',
            'rut_emisor' => '90.413.000-1',
            'marca_detectada' => 'CCU',
            'numero_factura' => '888125', // MISMO NÚMERO DE FACTURA
            'fecha_emision' => '2026-05-16',
            'total_factura' => 150000,
            'items' => [
                [
                    'codigo' => '870400',
                    'descripcion' => 'CRISTAL LATA 350CC',
                    'cantidad' => 8,
                    'precio_unitario' => 12000,
                ]
            ]
        ]);
        $this->app->instance(OcrService::class, $mockOcr);

        $file = UploadedFile::fake()->image('factura_ccu_duplicada.jpg');

        $response = $this->postJson(route('recepciones.ocr.process'), [
            'proveedor_id' => $this->proveedor->id,
            'bodega_id' => $this->bodega->id,
            'factura' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('ya existe en el sistema', $response->json('message'));
        $this->assertStringContainsString('No se permite volver a ingresar una factura duplicada', $response->json('message'));
    }

    /**
     * Prueba que VentaController rechaza la importación duplicada del mismo archivo con la misma fecha.
     */
    public function test_ventas_rechaza_archivo_duplicado_con_misma_fecha(): void
    {
        $csvContent = "sku,cantidad\n{$this->producto->sku},2\n";
        $csvFile = UploadedFile::fake()->createWithContent('ventas_repetidas.csv', $csvContent);
        $fechaVenta = now()->format('Y-m-d');

        // Primera subida
        $response1 = $this->post(route('ventas.store'), [
            'bodega_id' => $this->bodega->id,
            'archivo_ventas' => $csvFile,
            'fecha_venta' => $fechaVenta,
        ]);
        $response1->assertRedirect(route('ventas.index'));

        // Intento duplicado con el mismo archivo y fecha
        $csvFileDuplicado = UploadedFile::fake()->createWithContent('ventas_repetidas.csv', $csvContent);
        $response2 = $this->post(route('ventas.store'), [
            'bodega_id' => $this->bodega->id,
            'archivo_ventas' => $csvFileDuplicado,
            'fecha_venta' => $fechaVenta,
        ]);

        $response2->assertSessionHas('error');
    }
}
