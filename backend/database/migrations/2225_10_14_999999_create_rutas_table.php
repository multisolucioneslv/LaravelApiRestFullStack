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
        Schema::create('rutas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sistema_id')->constrained('sistemas')->onDelete('cascade');
            $table->string('ruta', 255);
            $table->string('metodo', 20)->default('GET');
            $table->text('descripcion')->nullable();
            $table->string('controlador', 255)->nullable();
            $table->string('accion', 100)->nullable();
            $table->json('middleware')->nullable();
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
        Schema::dropIfExists('rutas');
    }
};
