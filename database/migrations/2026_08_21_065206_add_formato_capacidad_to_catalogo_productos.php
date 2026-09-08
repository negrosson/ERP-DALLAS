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
        Schema::table('catalogo_productos', function (Blueprint $table) {
            $table->string('formato', 50)->nullable()->after('descripcion');
            $table->string('capacidad', 50)->nullable()->after('formato');
            
            // Verificamos si existe la columna antes de añadirla (por si acaso ya existía en la DB y no en la migración original)
            if (!Schema::hasColumn('catalogo_productos', 'dias_alerta_vencimiento')) {
                $table->integer('dias_alerta_vencimiento')->nullable()->after('constante_vencimiento_meses');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogo_productos', function (Blueprint $table) {
            $table->dropColumn(['formato', 'capacidad']);
        });
    }
};
