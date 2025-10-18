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
        Schema::table('empresas', function (Blueprint $table) {
            // Habilitación del servicio AI Chat
            $table->boolean('ai_chat_enabled')->default(false)->after('email');

            // Modo de detección: regex, function_calling, double_call
            $table->enum('ai_detection_mode', ['regex', 'function_calling', 'double_call'])
                ->default('regex')
                ->after('ai_chat_enabled');

            // Credenciales OpenAI propias de la empresa (encriptadas)
            $table->text('openai_api_key')->nullable()->after('ai_detection_mode');
            $table->string('openai_model', 50)->default('gpt-4')->after('openai_api_key');
            $table->integer('openai_max_tokens')->default(1500)->after('openai_model');
            $table->decimal('openai_temperature', 3, 2)->default(0.70)->after('openai_max_tokens');

            // Control de uso y presupuesto
            $table->decimal('ai_monthly_budget', 10, 2)->nullable()->after('openai_temperature');
            $table->decimal('ai_monthly_usage', 10, 2)->default(0.00)->after('ai_monthly_budget');
            $table->date('ai_usage_reset_date')->nullable()->after('ai_monthly_usage');

            // Estadísticas
            $table->integer('ai_total_queries')->default(0)->after('ai_usage_reset_date');
            $table->timestamp('ai_last_used_at')->nullable()->after('ai_total_queries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'ai_chat_enabled',
                'ai_detection_mode',
                'openai_api_key',
                'openai_model',
                'openai_max_tokens',
                'openai_temperature',
                'ai_monthly_budget',
                'ai_monthly_usage',
                'ai_usage_reset_date',
                'ai_total_queries',
                'ai_last_used_at',
            ]);
        });
    }
};
