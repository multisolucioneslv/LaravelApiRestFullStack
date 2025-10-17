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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->string('sku', 100)->unique();
            $table->string('codigo_barras', 100)->nullable()->unique();
            $table->decimal('precio_compra', 10, 2)->default(0.00);
            $table->decimal('precio_venta', 10, 2)->default(0.00);
            $table->decimal('precio_mayoreo', 10, 2)->nullable();
            $table->integer('stock_minimo')->default(0);
            $table->integer('stock_actual')->default(0);
            $table->string('unidad_medida', 50)->default('unidad'); // unidad, kg, litro, metro, etc.
            $table->string('imagen')->nullable(); // URL o path de la imagen
            $table->boolean('activo')->default(true);
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Índices para mejorar búsquedas
            $table->index('sku');
            $table->index('codigo_barras');
            $table->index('nombre');
            $table->index('empresa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
