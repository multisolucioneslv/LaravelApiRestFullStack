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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->foreignId('telefono_id')->nullable()->constrained('phones')->onDelete('set null');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('fondo_login')->nullable();
            $table->string('zona_horaria', 50)->default('America/Los_Angeles');
            $table->json('horarios')->nullable()->comment('Horarios de atención en formato JSON');
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
        Schema::dropIfExists('empresas');
    }
};
