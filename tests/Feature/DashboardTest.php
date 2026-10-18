<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;
    public function test_dashboard_renders_successfully_for_authenticated_user(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('bodegasDetalle');
        $response->assertViewHas('lotesRadar');
        $response->assertViewHas('radarCounts');

        // Check key UI sections and components
        $response->assertSee('Centro de Control &amp; Operaciones', false);
        $response->assertSee('100% Sincronizado en Vivo', false);
        $response->assertSee('Hub de Bodegas Sincronizadas', false);
        $response->assertSee('Radar de Vencimientos Cercanos (FEFO)', false);
        $response->assertSee('dashboardHub()', false);
        $response->assertSee('ventasSemanaChart', false);
        $response->assertSee('vencimientosMesChart', false);
    }
}
