<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogo_productos', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique()->comment('Código interno maestro');
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->string('unidad_medida', 10)->default('UN')->comment('UN, CJ, KG, LT');
            $table->decimal('precio_compra_ref', 12, 2)->default(0)->comment('Precio compra referencial');
            $table->decimal('precio_venta', 12, 2)->default(0);
            $table->unsignedSmallInteger('constante_vencimiento_meses')->nullable()
                ->comment('Meses a sumar a fecha_elaboracion para autocalcular vencimiento');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_productos');
    }
};
