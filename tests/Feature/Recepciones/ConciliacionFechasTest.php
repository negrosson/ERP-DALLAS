<?php

namespace Tests\Feature\Recepciones;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bodega;
use App\Models\Proveedor;
use App\Models\CatalogoProducto;
use App\Models\Recepcion;
use App\Models\RecepcionDetalle;
use App\Models\LoteStock;
use App\Enums\EstadoRecepcion;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * ============================================================================
 * TEST: CONCILIACIÓN DE FECHAS & MÁQUINA DE ESTADOS DE RECEPCIÓN
 * ============================================================================
 * Propósito para la presentación/defensa del proyecto:
 * Valida el flujo seguro de mercadería física:
 * 1. Estado PENDIENTE_FECHA: Los productos escaneados quedan en cuarentena preventiva.
 * 2. Asignación de fechas de vencimiento y lote: Creación de lotes en bodega solo tras confirmar fechas.
 * 3. Lotes divididos: Si un producto viene con dos fechas de vencimiento distintas, se dividen en 2 LoteStock.
 * 4. Consolidación automática: Si se asigna la misma fecha, se consolidan las cantidades.
 * 5. Alta rápida (Quick-store): Detección y creación guiada de productos nuevos sin duplicar catálogo.
 */
class ConciliacionFechasTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Proveedor $proveedor;
    protected Bodega $bodega;
    protected CatalogoProducto $producto;
    protected Recepcion $recepcion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->proveedor = Proveedor::create([
            'codigo' => 'CCU',
            'nombre' => 'Compañía Cervecerías Unidas S.A.',
            'rut' => '90.413.000-1',
            'activo' => true,
        ]);

        $this->bodega = Bodega::create([
            'codigo' => 'BCCU',
            'nombre' => 'Bodega CCU',
            'activa' => true,
            'es_refrigerada' => false,
        ]);

        $this->producto = CatalogoProducto::create([
            'sku' => 'CCU-CRIS-L350',
            'nombre' => 'Cerveza Cristal 350cc',
            'unidad_medida' => 'UN',
            'precio_compra_ref' => 500,
            'precio_venta' => 1000,
            'constante_vencimiento_meses' => 6,
            'activo' => true,
        ]);

        $this->recepcion = Recepcion::create([
            'proveedor_id' => $this->proveedor->id,
            'bodega_id' => $this->bodega->id,
            'user_id' => $this->user->id,
            'numero_factura' => 'FACT-CONCILIAR-01',
            'documento_referencia' => 'FACT-CONCILIAR-01',
            'fecha_recepcion' => now(),
            'estado' => EstadoRecepcion::PENDIENTE_FECHA->value,
        ]);
    }

    /**
     * Prueba que conciliar renderiza la vista en modo PENDIENTE_FECHA con sus datos listos.
     */
    public function test_conciliar_pantalla_carga_correctamente(): void
    {
        $detalle = RecepcionDetalle::create([
            'recepcion_id' => $this->recepcion->id,
            'catalogo_producto_id' => $this->producto->id,
            'codigo_proveedor_usado' => '870400',
            'cantidad' => 24,
            'precio_unitario' => 500,
        ]);

        $response = $this->get(route('recepciones.conciliar', $this->recepcion));

        $response->assertStatus(200);
        $response->assertSee('Asignación de Fechas y Lotes');
        $response->assertSee('Cerveza Cristal 350cc');
        $response->assertSee('x24 (Caja 24)');
    }

    /**
     * Prueba que guardarConciliacion procesa las fechas, crea los LoteStock en bodega
     * y actualiza el estado a CONFIRMADO.
     */
    public function test_guardar_conciliacion_crea_lotes_y_confirma_recepcion(): void
    {
        $detalle = RecepcionDetalle::create([
            'recepcion_id' => $this->recepcion->id,
            'catalogo_producto_id' => $this->producto->id,
            'codigo_proveedor_usado' => '870400',
            'cantidad' => 24,
            'precio_unitario' => 500,
        ]);

        $fechaVenc = now()->addMonths(6)->format('Y-m-d');
        $fechaElab = now()->format('Y-m-d');

        $payload = [
            'detalles' => [
                [
                    'id' => $detalle->id,
                    'catalogo_producto_id' => $this->producto->id,
                    'cantidad' => 48, // Ajustado a 2 cajas de 24
                    'precio_unitario' => 500,
                    'formato' => 'Lata',
                    'capacidad' => '350 cc',
                    'fecha_elaboracion' => $fechaElab,
                    'fecha_vencimiento' => $fechaVenc,
                ]
            ]
        ];

        $response = $this->post(route('recepciones.conciliar.store', $this->recepcion), $payload);

        $response->assertRedirect(route('recepciones.show', $this->recepcion));
        $response->assertSessionHas('success');

        // La recepción debe haber cambiado a CONFIRMADO
        $this->recepcion->refresh();
        $this->assertEquals(EstadoRecepcion::CONFIRMADO, $this->recepcion->estado);

        // Debe haberse creado un LoteStock para el detalle
        $lote = LoteStock::where('recepcion_detalle_id', $detalle->id)->first();
        $this->assertNotNull($lote);
        $this->assertEquals(48, (float)$lote->cantidad_disponible);
        $this->assertEquals($this->bodega->id, $lote->bodega_id);
        $this->assertEquals($fechaVenc, $lote->fecha_vencimiento->format('Y-m-d'));

        // El producto debe tener formato y capacidad guardados
        $this->producto->refresh();
        $this->assertEquals('Lata', $this->producto->formato);
        $this->assertEquals('350 cc', $this->producto->capacidad);
    }

    /**
     * Prueba que si el usuario divide el lote en dos fechas de vencimiento diferentes,
     * se generan dos LoteStock independientes para rotación FEFO.
     */
    public function test_guardar_conciliacion_con_lote_dividido(): void
    {
        $detalle = RecepcionDetalle::create([
            'recepcion_id' => $this->recepcion->id,
            'catalogo_producto_id' => $this->producto->id,
            'codigo_proveedor_usado' => '870400',
            'cantidad' => 24,
            'precio_unitario' => 500,
        ]);

        $fechaVencLote1 = now()->addMonths(3)->format('Y-m-d');
        $fechaVencLote2 = now()->addMonths(6)->format('Y-m-d');

        $payload = [
            'detalles' => [
                [
                    'id' => $detalle->id,
                    'catalogo_producto_id' => $this->producto->id,
                    'cantidad' => 12,
                    'precio_unitario' => 500,
                    'fecha_vencimiento' => $fechaVencLote1,
                ],
                [
                    'id' => null, // Nuevo lote dividido
                    'catalogo_producto_id' => $this->producto->id,
                    'cantidad' => 12,
                    'precio_unitario' => 500,
                    'fecha_vencimiento' => $fechaVencLote2,
                ],
            ]
        ];

        $response = $this->post(route('recepciones.conciliar.store', $this->recepcion), $payload);

        $response->assertRedirect(route('recepciones.show', $this->recepcion));
        $response->assertSessionHas('success');

        // Debe haber 2 lotes en stock para la recepción
        $lotes = LoteStock::where('catalogo_producto_id', $this->producto->id)
            ->where('bodega_id', $this->bodega->id)
            ->get();

        $this->assertCount(2, $lotes);
        $this->assertEquals(24, (float)$lotes->sum('cantidad_disponible'));
    }

    /**
     * Prueba que si el usuario ingresa dos registros con el mismo producto y la misma fecha,
     * el sistema los consolida sumando sus cantidades para no duplicar lotes.
     */
    public function test_guardar_conciliacion_consolida_duplicados_con_misma_fecha(): void
    {
        $detalle = RecepcionDetalle::create([
            'recepcion_id' => $this->recepcion->id,
            'catalogo_producto_id' => $this->producto->id,
            'codigo_proveedor_usado' => '870400',
            'cantidad' => 10,
            'precio_unitario' => 500,
        ]);

        $fechaVenc = now()->addMonths(4)->format('Y-m-d');

        $payload = [
            'detalles' => [
                [
                    'id' => $detalle->id,
                    'catalogo_producto_id' => $this->producto->id,
                    'cantidad' => 10,
                    'precio_unitario' => 500,
                    'fecha_vencimiento' => $fechaVenc,
                ],
                [
                    'id' => null,
                    'catalogo_producto_id' => $this->producto->id,
                    'cantidad' => 15, // Mismo producto y misma fecha
                    'precio_unitario' => 500,
                    'fecha_vencimiento' => $fechaVenc,
                ],
            ]
        ];

        $response = $this->post(route('recepciones.conciliar.store', $this->recepcion), $payload);

        $response->assertRedirect(route('recepciones.show', $this->recepcion));

        // Debe consolidar en 1 lote de 25 unidades
        $lotes = LoteStock::where('catalogo_producto_id', $this->producto->id)
            ->where('bodega_id', $this->bodega->id)
            ->get();

        $this->assertCount(1, $lotes);
        $this->assertEquals(25, (float)$lotes->first()->cantidad_disponible);
    }

    /**
     * Prueba que el modal AJAX de alta rápida crea el producto activo y su MapeoCodigo.
     */
    public function test_quick_store_crea_producto_y_mapeo_de_proveedor(): void
    {
        $payload = [
            'nombre' => 'Coca-Cola Original Vidrio 237 cc',
            'sku' => 'COC-VID-237ML-TEST',
            'formato' => 'Botella Vidrio',
            'capacidad' => '237 cc',
            'precio_compra_ref' => 529.58,
            'precio_venta' => 800,
            'constante_vencimiento_meses' => 6,
            'proveedor_id' => $this->proveedor->id,
            'codigo_proveedor' => '0166',
        ];

        $response = $this->postJson(route('catalogo.quick-store'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verificar en catalogo_productos
        $this->assertDatabaseHas('catalogo_productos', [
            'sku' => 'COC-VID-237ML-TEST',
            'nombre' => 'Coca-Cola Original Vidrio 237 cc',
            'formato' => 'Botella Vidrio',
            'capacidad' => '237 cc',
            'activo' => 1,
        ]);

        // Verificar en mapeo_codigos
        $this->assertDatabaseHas('mapeo_codigos', [
            'proveedor_id' => $this->proveedor->id,
            'codigo_proveedor' => '0166',
            'descripcion_proveedor' => 'Coca-Cola Original Vidrio 237 cc',
        ]);
    }

    /**
     * Prueba que la pantalla de conciliación muestra alerta si el producto escaneado
     * no existe en el catálogo maestro y no auto-asigna un producto erróneo.
     */
    public function test_conciliar_pantalla_muestra_alerta_si_producto_es_nuevo(): void
    {
        // Detalle sin catalogo_producto_id y con código no registrado
        $detalleNuevo = RecepcionDetalle::create([
            'recepcion_id' => $this->recepcion->id,
            'catalogo_producto_id' => null,
            'codigo_proveedor_usado' => '9999-INVENTADO',
            'descripcion_proveedor' => 'BEBIDA MISTERIOSA X24 VIDRIO 237 CC',
            'cantidad' => 24,
            'precio_unitario' => 500,
        ]);

        $response = $this->get(route('recepciones.conciliar', $this->recepcion));

        $response->assertStatus(200);
        $response->assertSee('Producto No Registrado en Catálogo');
        $response->assertSee('Crear en Catálogo');
        $response->assertSee('BEBIDA MISTERIOSA X24 VIDRIO 237 CC');
    }
}
