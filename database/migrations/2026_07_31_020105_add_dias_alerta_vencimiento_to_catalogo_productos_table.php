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
            $table->integer('dias_alerta_vencimiento')->nullable()->after('constante_vencimiento_meses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogo_productos', function (Blueprint $table) {
            $table->dropColumn('dias_alerta_vencimiento');
        });
    }
};
