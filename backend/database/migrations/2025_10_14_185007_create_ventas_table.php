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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 100)->unique();
            $table->date('fecha');
            $table->string('estado', 50)->default('completada');
            $table->string('tipo_pago', 50)->default('efectivo');
            $table->text('observaciones')->nullable();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cotizacion_id')->nullable()->constrained('cotizaciones')->onDelete('set null');
            $table->foreignId('moneda_id')->nullable()->constrained('monedas')->onDelete('set null');
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->onDelete('set null');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('impuesto', 12, 2)->default(0.00);
            $table->decimal('descuento', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
