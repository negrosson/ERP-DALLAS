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
        Schema::create('mermas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalogo_producto_id')->constrained('catalogo_productos');
            $table->foreignId('bodega_id')->constrained('bodegas');
            $table->decimal('cantidad', 12, 2);
            $table->string('motivo');
            $table->dateTime('fecha_merma');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mermas');
    }
};
