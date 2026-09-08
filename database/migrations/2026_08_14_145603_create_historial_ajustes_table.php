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
        Schema::create('historial_ajustes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_stock_id')->constrained('lotes_stock')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('cantidad_anterior', 12, 2);
            $table->decimal('cantidad_nueva', 12, 2);
            $table->decimal('diferencia', 12, 2);
            $table->string('motivo', 255);
            $table->boolean('es_reversion')->default(false);
            $table->foreignId('reversion_de_id')->nullable()->constrained('historial_ajustes')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_ajustes');
    }
};
