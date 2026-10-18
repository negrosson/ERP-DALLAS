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
        Schema::table('recepcion_detalles', function (Blueprint $table) {
            $table->foreignId('catalogo_producto_id')->nullable()->change();
            $table->string('descripcion_proveedor', 255)->nullable()->after('codigo_proveedor_usado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recepcion_detalles', function (Blueprint $table) {
            $table->dropColumn('descripcion_proveedor');
        });
    }
};
