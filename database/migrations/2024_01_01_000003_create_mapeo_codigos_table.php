<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapeo_codigos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->cascadeOnDelete();
            $table->foreignId('catalogo_producto_id')->constrained('catalogo_productos')->cascadeOnDelete();
            $table->string('codigo_proveedor', 50)->comment('Código que usa el proveedor para este producto');
            $table->decimal('factor_conversion', 8, 2)->default(1)->comment('Unidades maestras por 1 unidad de proveedor');
            $table->string('descripcion_proveedor', 200)->nullable()
                ->comment('Nombre/descripción según el proveedor');
            $table->timestamps();

            // Un proveedor no puede tener el mismo código dos veces
            $table->unique(['proveedor_id', 'codigo_proveedor'], 'uq_proveedor_codigo');
            // Un proveedor solo puede mapear un producto una vez
            $table->unique(['proveedor_id', 'catalogo_producto_id'], 'uq_proveedor_producto');

            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapeo_codigos');
    }
};
