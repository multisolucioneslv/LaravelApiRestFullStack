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
        Schema::table('chatids', function (Blueprint $table) {
            // Verificar si los campos polimórficos ya existen
            if (!Schema::hasColumn('chatids', 'chatable_type')) {
                // Agregar campos polimórficos para permitir que un chatid pertenezca a diferentes modelos
                $table->nullableMorphs('chatable');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatids', function (Blueprint $table) {
            // Eliminar índice
            $table->dropIndex(['chatable_type', 'chatable_id']);

            // Eliminar campos polimórficos
            $table->dropMorphs('chatable');
        });
    }
};
