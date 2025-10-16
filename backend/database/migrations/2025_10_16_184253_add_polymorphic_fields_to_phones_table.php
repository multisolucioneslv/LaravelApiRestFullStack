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
        Schema::table('phones', function (Blueprint $table) {
            // Verificar si los campos polimórficos ya existen
            if (!Schema::hasColumn('phones', 'phonable_type')) {
                // Agregar campos polimórficos para permitir que un teléfono pertenezca a diferentes modelos
                $table->nullableMorphs('phonable');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phones', function (Blueprint $table) {
            // Eliminar índice
            $table->dropIndex(['phonable_type', 'phonable_id']);

            // Eliminar campos polimórficos
            $table->dropMorphs('phonable');
        });
    }
};
