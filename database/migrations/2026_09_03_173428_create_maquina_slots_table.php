<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maquina_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maquina_id')->constrained('maquinas')->cascadeOnDelete();
            $table->integer('seccion')->default(1);
            $table->integer('piso')->comment('1 = superior, incrementando hacia abajo');
            $table->integer('posicion')->comment('1 = izquierda, incrementando hacia derecha');
            $table->foreignId('catalogo_producto_id')->nullable()->constrained('catalogo_productos')->nullOnDelete();
            $table->integer('capacidad_maxima')->default(6);
            $table->integer('cantidad_actual')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maquina_slots');
    }
};
