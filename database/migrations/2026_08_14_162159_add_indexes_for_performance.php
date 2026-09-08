<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índice compuesto en lotes_stock: la consulta más frecuente filtra por
        // catalogo_producto_id + cantidad_disponible > 0, ordenado por fecha_vencimiento
        Schema::table('lotes_stock', function (Blueprint $table) {
            $table->index(
                ['catalogo_producto_id', 'cantidad_disponible', 'fecha_vencimiento'],
                'idx_lotes_producto_disponible_venc'
            );
            $table->index(
                ['bodega_id', 'cantidad_disponible', 'fecha_vencimiento'],
                'idx_lotes_bodega_disponible_venc'
            );
        });

        // Índice para búsquedas en el catálogo
        Schema::table('catalogo_productos', function (Blueprint $table) {
            $table->index(['activo', 'nombre'], 'idx_catalogo_activo_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('lotes_stock', function (Blueprint $table) {
            $table->dropIndex('idx_lotes_producto_disponible_venc');
            $table->dropIndex('idx_lotes_bodega_disponible_venc');
        });
        Schema::table('catalogo_productos', function (Blueprint $table) {
            $table->dropIndex('idx_catalogo_activo_nombre');
        });
    }
};
