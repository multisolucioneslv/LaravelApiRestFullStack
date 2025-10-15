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
        Schema::create('galerias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->json('imagenes')->nullable(); // Array de objetos: [{url, orden, es_principal}]
            $table->morphs('galeriable'); // Crea galeriable_id y galeriable_type
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galerias');
    }
};
