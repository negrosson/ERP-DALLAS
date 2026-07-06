<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recepciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->restrictOnDelete();
            $table->foreignId('bodega_id')->constrained('bodegas')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()
                ->comment('Usuario que registró la recepción');
            $table->string('numero_factura', 50);
            $table->date('fecha_recepcion');
            $table->string('estado', 20)->default('BORRADOR')
                ->comment('BORRADOR: sin stock | CONFIRMADO: genera lotes');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['proveedor_id', 'fecha_recepcion']);
            $table->index('estado');

            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepciones');
    }
};
