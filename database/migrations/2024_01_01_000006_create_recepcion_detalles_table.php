<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recepcion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recepcion_id')->constrained('recepciones')->cascadeOnDelete();
            $table->foreignId('catalogo_producto_id')->constrained('catalogo_productos')->restrictOnDelete();
            $table->string('codigo_proveedor_usado', 50)
                ->comment('Código que venía en la factura del proveedor');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 12, 2);
            $table->date('fecha_elaboracion')->nullable();
            $table->date('fecha_vencimiento')->nullable()
                ->comment('Autocalculada si el producto tiene constante_vencimiento_meses');
            $table->timestamps();

            $table->index('catalogo_producto_id');

            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepcion_detalles');
    }
};
