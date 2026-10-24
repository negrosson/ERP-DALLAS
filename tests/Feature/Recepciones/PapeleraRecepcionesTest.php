<?php

namespace Tests\Feature\Recepciones;

use App\Models\Bodega;
use App\Models\Proveedor;
use App\Models\Recepcion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PapeleraRecepcionesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Proveedor $proveedor;
    protected Bodega $bodega;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->proveedor = Proveedor::create([
            'codigo' => 'CCU-001',
            'nombre' => 'Compañía Cervecerías Unidas',
            'rut' => '90.413.000-1',
            'activo' => true,
        ]);

        $this->bodega = Bodega::create([
            'codigo' => 'BCCU',
            'nombre' => 'Bodega CCU',
            'activa' => true,
        ]);
    }

    public function test_enviar_recepcion_a_papelera(): void
    {
        $recepcion = Recepcion::create([
            'proveedor_id' => $this->proveedor->id,
            'bodega_id' => $this->bodega->id,
            'user_id' => $this->user->id,
            'numero_factura' => 'FACT-1001',
            'fecha_recepcion' => now(),
            'estado' => \App\Enums\EstadoRecepcion::BORRADOR->value,
            'total' => 50000,
        ]);

        $this->actingAs($this->user);

        $response = $this->delete(route('recepciones.destroy', $recepcion));

        $response->assertRedirect(route('recepciones.index'));
        $this->assertSoftDeleted('recepciones', ['id' => $recepcion->id]);
    }

    public function test_restaurar_recepcion_de_papelera(): void
    {
        $recepcion = Recepcion::create([
            'proveedor_id' => $this->proveedor->id,
            'bodega_id' => $this->bodega->id,
            'user_id' => $this->user->id,
            'numero_factura' => 'FACT-1002',
            'fecha_recepcion' => now(),
            'estado' => \App\Enums\EstadoRecepcion::BORRADOR->value,
            'total' => 30000,
        ]);
        $recepcion->delete();

        $this->actingAs($this->user);

        $response = $this->post(route('recepciones.restore', $recepcion->id));

        $response->assertRedirect(route('recepciones.index', ['estado' => 'papelera']));
        $this->assertNotSoftDeleted('recepciones', ['id' => $recepcion->id]);
    }

    public function test_eliminar_definitivamente_recepcion(): void
    {
        $recepcion = Recepcion::create([
            'proveedor_id' => $this->proveedor->id,
            'bodega_id' => $this->bodega->id,
            'user_id' => $this->user->id,
            'numero_factura' => 'FACT-1003',
            'fecha_recepcion' => now(),
            'estado' => \App\Enums\EstadoRecepcion::BORRADOR->value,
            'total' => 20000,
        ]);
        $recepcion->delete();

        $this->actingAs($this->user);

        $response = $this->delete(route('recepciones.force-delete', $recepcion->id));

        $response->assertRedirect(route('recepciones.index', ['estado' => 'papelera']));
        $this->assertDatabaseMissing('recepciones', ['id' => $recepcion->id]);
    }
}
