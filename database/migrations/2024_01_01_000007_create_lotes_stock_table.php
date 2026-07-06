<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalogo_producto_id')->constrained('catalogo_productos')->restrictOnDelete();
            $table->foreignId('bodega_id')->constrained('bodegas')->restrictOnDelete();
            $table->foreignId('recepcion_detalle_id')->constrained('recepcion_detalles')->restrictOnDelete();
            $table->decimal('cantidad_inicial', 12, 2);
            $table->decimal('cantidad_disponible', 12, 2);
            $table->date('fecha_elaboracion')->nullable();
            $table->date('fecha_vencimiento')->nullable()
                ->comment('Clave para FEFO: se descuenta siempre del lote que vence primero. Null si está pendiente.');
            $table->timestamps();

            // Índice para consultas FEFO: producto + bodega + vencimiento + disponible
            $table->index(
                ['catalogo_producto_id', 'bodega_id', 'fecha_vencimiento'],
                'idx_fefo_lookup'
            );
            $table->index('fecha_vencimiento', 'idx_vencimiento');

            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes_stock');
    }
};
