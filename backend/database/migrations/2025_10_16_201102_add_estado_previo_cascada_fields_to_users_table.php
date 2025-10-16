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
        Schema::table('users', function (Blueprint $table) {
            // Campos para guardar el estado previo cuando hay suspensión en cascada
            $table->string('estado_previo_cascada')->nullable()->after('razon_suspendida')
                ->comment('Estado previo del usuario antes de suspensión en cascada por empresa');
            $table->text('razon_previa_cascada')->nullable()->after('estado_previo_cascada')
                ->comment('Razón previa del usuario antes de suspensión en cascada por empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['estado_previo_cascada', 'razon_previa_cascada']);
        });
    }
};
