<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bodega;
use App\Models\Proveedor;
use App\Models\Recepcion;
use App\Services\OcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;

class OcrDiscrepanciaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Proveedor $provCcu;
    protected Proveedor $provCoca;
    protected Bodega $bodegaCcu;
    protected Bodega $bodegaCoca;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->provCcu = Proveedor::create([
            'codigo' => 'CCU',
            'nombre' => 'Compañía Cervecerías Unidas S.A.',
            'rut' => '90.413.000-1',
            'activo' => true,
        ]);

        $this->provCoca = Proveedor::create([
            'codigo' => 'COCA-COLA',
            'nombre' => 'Coca-Cola Company',
            'rut' => '80.000.000-0',
            'activo' => true,
        ]);

        $this->bodegaCcu = Bodega::create([
            'codigo' => 'BCCU',
            'nombre' => 'Bodega CCU',
            'activa' => true,
            'es_refrigerada' => false,
        ]);

        $this->bodegaCoca = Bodega::create([
            'codigo' => 'B-COCA',
            'nombre' => 'Bodega Coca-Cola',
            'activa' => true,
            'es_refrigerada' => false,
        ]);
    }

    /**
     * Prueba que si el usuario selecciona proveedor Coca-Cola pero sube una factura de CCU,
     * el sistema detecta la discrepancia y rechaza la carga con status 422.
     */
    public function test_rechaza_factura_ccu_cuando_se_selecciona_proveedor_coca_cola(): void
    {
        $mockOcr = Mockery::mock(OcrService::class);
        $mockOcr->shouldReceive('processInvoiceImage')->once()->andReturn([
            'emisor_razon_social' => 'COMPAÑIA CERVECERIAS UNIDAS S.A.',
            'rut_emisor' => '90.413.000-1',
            'marca_detectada' => 'CCU',
            'numero_factura' => '888123',
            'fecha_emision' => '2026-05-16',
            'total_factura' => 150000,
            'items' => [
                [
                    'codigo' => '870400',
                    'descripcion' => 'CRISTAL LATA 350CC',
                    'cantidad' => 10,
                    'precio_unitario' => 12000,
                ]
            ]
        ]);
        $this->app->instance(OcrService::class, $mockOcr);

        $file = UploadedFile::fake()->image('factura_ccu.jpg');

        $response = $this->postJson(route('recepciones.ocr.process'), [
            'proveedor_id' => $this->provCoca->id, // Seleccionó Coca-Cola
            'bodega_id' => $this->bodegaCoca->id,  // Seleccionó Bodega Coca-Cola
            'factura' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'tipo_error' => 'discrepancia_proveedor',
        ]);
        $this->assertStringContainsString('Discrepancia', $response->json('message'));
        $this->assertStringContainsString('CCU', $response->json('message'));

        // No debe haberse creado ninguna recepción en la base de datos
        $this->assertDatabaseMissing('recepciones', ['numero_factura' => '888123']);
    }

    /**
     * Prueba que si la factura es de CCU pero se selecciona una bodega dedicada a Coca-Cola,
     * el sistema detecta la discrepancia de bodega y la rechaza.
     */
    public function test_rechaza_factura_ccu_cuando_se_asigna_a_bodega_coca_cola(): void
    {
        $mockOcr = Mockery::mock(OcrService::class);
        $mockOcr->shouldReceive('processInvoiceImage')->once()->andReturn([
            'emisor_razon_social' => 'COMPAÑIA CERVECERIAS UNIDAS S.A.',
            'rut_emisor' => '90.413.000-1',
            'marca_detectada' => 'CCU',
            'numero_factura' => '888124',
            'fecha_emision' => '2026-05-16',
            'total_factura' => 150000,
            'items' => [
                [
                    'codigo' => '870400',
                    'descripcion' => 'CRISTAL LATA 350CC',
                    'cantidad' => 5,
                    'precio_unitario' => 12000,
                ]
            ]
        ]);
        $this->app->instance(OcrService::class, $mockOcr);

        $file = UploadedFile::fake()->image('factura_ccu.jpg');

        $response = $this->postJson(route('recepciones.ocr.process'), [
            'proveedor_id' => $this->provCcu->id,  // Proveedor CCU (correcto)
            'bodega_id' => $this->bodegaCoca->id,  // Bodega Coca-Cola (INCORRECTO)
            'factura' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'tipo_error' => 'discrepancia_bodega',
        ]);
        $this->assertStringContainsString('Discrepancia de Bodega', $response->json('message'));
    }

    /**
     * Prueba que si el proveedor, bodega y factura coinciden (CCU con Bodega CCU),
     * el escaneo se procesa correctamente y crea el borrador.
     */
    public function test_acepta_factura_cuando_proveedor_y_bodega_coinciden(): void
    {
        $mockOcr = Mockery::mock(OcrService::class);
        $mockOcr->shouldReceive('processInvoiceImage')->once()->andReturn([
            'emisor_razon_social' => 'COMPAÑIA CERVECERIAS UNIDAS S.A.',
            'rut_emisor' => '90.413.000-1',
            'marca_detectada' => 'CCU',
            'numero_factura' => '888125',
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

        $file = UploadedFile::fake()->image('factura_ccu.jpg');

        $response = $this->postJson(route('recepciones.ocr.process'), [
            'proveedor_id' => $this->provCcu->id,  // CCU
            'bodega_id' => $this->bodegaCcu->id,   // Bodega CCU
            'factura' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('recepciones', [
            'numero_factura' => '888125',
            'proveedor_id' => $this->provCcu->id,
            'bodega_id' => $this->bodegaCcu->id,
        ]);
    }
}
