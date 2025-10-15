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
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('codigo', 100);
            $table->text('descripcion')->nullable();
            $table->foreignId('galeria_id')->nullable()->constrained('galerias')->onDelete('set null');
            $table->foreignId('bodega_id')->constrained('bodegas')->onDelete('cascade');
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->integer('cantidad')->default(0);
            $table->integer('minimo')->default(0);
            $table->integer('maximo')->default(0);
            $table->decimal('precio_compra', 10, 2)->default(0.00);
            $table->decimal('precio_venta', 10, 2)->default(0.00);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
