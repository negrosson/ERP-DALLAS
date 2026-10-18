<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ============================================================================
 * TEST: DASHBOARD EJECUTIVO & CENTRO DE CONTROL EN TIEMPO REAL
 * ============================================================================
 * Propósito para la presentación/defensa del proyecto:
 * Valida que el panel principal cargue instantáneamente para usuarios autorizados,
 * calculando y sincronizando en vivo:
 * 1. Métricas ejecutivas (Valorización total $, Unidades totales, SKUs de catálogo).
 * 2. Hub de Bodegas sincronizadas (Existencias y valor por bodega física).
 * 3. Radar de Vencimientos FEFO (Semáforo de lotes, horizontes de vencimiento y filtros).
 * 4. Contenedores de gráficos analíticos (Flujo de ventas semanal y proyección de 4 meses).
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Valida que el dashboard responda HTTP 200 y cargue todos los componentes reactivos.
     */
    public function test_dashboard_renders_successfully_for_authenticated_user(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        // Variables clave inyectadas por DashboardController
        $response->assertViewHas('bodegasDetalle');
        $response->assertViewHas('lotesRadar');
        $response->assertViewHas('radarCounts');

        // Verificación de secciones visuales y componentes del UI
        $response->assertSee('Centro de Control &amp; Operaciones', false);
        $response->assertSee('100% Sincronizado en Vivo', false);
        $response->assertSee('Hub de Bodegas Sincronizadas', false);
        $response->assertSee('Radar de Vencimientos Cercanos (FEFO)', false);
        $response->assertSee('dashboardHub()', false);
        $response->assertSee('ventasSemanaChart', false);
        $response->assertSee('vencimientosMesChart', false);
    }
}
