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
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->string('slug', 200)->unique();
            $table->string('icono', 100)->nullable(); // Nombre del icono (ej: 'fas fa-box')
            $table->string('color', 20)->nullable(); // Color en HEX (ej: '#FF5733')
            $table->boolean('activo')->default(true);
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Índices para mejorar búsquedas
            $table->index('slug');
            $table->index('nombre');
            $table->index('empresa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
