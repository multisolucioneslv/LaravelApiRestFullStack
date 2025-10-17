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
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['user', 'assistant', 'system']); // Rol del mensaje
            $table->longText('content'); // Contenido del mensaje
            $table->json('metadata')->nullable(); // Metadata adicional (tokens, n8n_data, etc.)
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ai_conversation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};
