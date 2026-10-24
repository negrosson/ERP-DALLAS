<?php

namespace Tests\Feature\Bodega;

use App\Models\Bodega;
use App\Models\CatalogoProducto;
use App\Models\HistorialAjuste;
use App\Models\LoteStock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EliminarProductoBodegaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Bodega $bodega;
    private CatalogoProducto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->bodega = Bodega::create([
            'nombre' => 'Bodega Central',
            'codigo' => 'BOD-CENTRAL',
            'es_refrigerada' => false,
            'activa' => true,
        ]);

        $this->producto = CatalogoProducto::create([
            'sku' => 'BEB-TEST-001',
            'nombre' => 'Bebida Refrescante 1.5L',
            'formato' => 'Botella',
            'capacidad' => '1.5 L',
            'activo' => true,
        ]);
    }

    public function test_eliminar_producto_de_bodega_da_de_baja_stock_y_registra_auditoria(): void
    {
        // Crear 2 lotes en esta bodega
        $lote1 = LoteStock::create([
            'bodega_id' => $this->bodega->id,
            'catalogo_producto_id' => $this->producto->id,
            'cantidad_inicial' => 20,
            'cantidad_disponible' => 20,
            'fecha_vencimiento' => now()->addMonths(6),
        ]);

        $lote2 = LoteStock::create([
            'bodega_id' => $this->bodega->id,
            'catalogo_producto_id' => $this->producto->id,
            'cantidad_inicial' => 15,
            'cantidad_disponible' => 15,
            'fecha_vencimiento' => now()->addMonths(9),
        ]);

        $this->actingAs($this->user);

        // Ejecutar eliminación del producto desde la bodega
        $response = $this->delete(route('bodegas.producto.eliminar', [
            'bodega' => $this->bodega->id,
            'producto' => $this->producto->id,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Los lotes deben estar soft-deleted con cantidad_disponible = 0
        $this->assertSoftDeleted('lotes_stock', ['id' => $lote1->id]);
        $this->assertSoftDeleted('lotes_stock', ['id' => $lote2->id]);
        $this->assertEquals(0, $lote1->fresh()->cantidad_disponible);
        $this->assertEquals(0, $lote2->fresh()->cantidad_disponible);

        // Debe haber 2 registros en historial de auditoría
        $this->assertDatabaseHas('historial_ajustes', [
            'lote_stock_id' => $lote1->id,
            'user_id' => $this->user->id,
            'cantidad_anterior' => 20,
            'cantidad_nueva' => 0,
            'diferencia' => -20,
        ]);

        $this->assertDatabaseHas('historial_ajustes', [
            'lote_stock_id' => $lote2->id,
            'user_id' => $this->user->id,
            'cantidad_anterior' => 15,
            'cantidad_nueva' => 0,
            'diferencia' => -15,
        ]);

        // La vista de la bodega ya no debe listar el producto
        $viewResponse = $this->get(route('bodegas.show', $this->bodega));
        $viewResponse->assertDontSee('BEB-TEST-001');

        // Y la auditoría debe mostrar el registro con el producto y usuario
        $historial = HistorialAjuste::where('lote_stock_id', $lote1->id)->first();
        $this->assertNotNull($historial->lote);
        $this->assertEquals('BEBIDA REFRESCANTE 1.5L', $historial->lote->producto->nombre);
    }

    public function test_eliminar_lote_individual_de_bodega_registra_auditoria(): void
    {
        $lote = LoteStock::create([
            'bodega_id' => $this->bodega->id,
            'catalogo_producto_id' => $this->producto->id,
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 10,
            'fecha_vencimiento' => now()->addMonths(4),
        ]);

        $this->actingAs($this->user);

        $response = $this->delete(route('bodegas.lotes.eliminar', [
            'bodega' => $this->bodega->id,
            'lote' => $lote->id,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('lotes_stock', ['id' => $lote->id]);
        $this->assertEquals(0, $lote->fresh()->cantidad_disponible);

        $this->assertDatabaseHas('historial_ajustes', [
            'lote_stock_id' => $lote->id,
            'user_id' => $this->user->id,
            'cantidad_anterior' => 10,
            'cantidad_nueva' => 0,
            'diferencia' => -10,
        ]);
    }

    public function test_no_se_puede_eliminar_producto_de_bodega_inactiva(): void
    {
        $this->bodega->update(['activa' => false]);

        LoteStock::create([
            'bodega_id' => $this->bodega->id,
            'catalogo_producto_id' => $this->producto->id,
            'cantidad_inicial' => 5,
            'cantidad_disponible' => 5,
        ]);

        $this->actingAs($this->user);

        $response = $this->delete(route('bodegas.producto.eliminar', [
            'bodega' => $this->bodega->id,
            'producto' => $this->producto->id,
        ]));

        $response->assertStatus(403);
    }
}
