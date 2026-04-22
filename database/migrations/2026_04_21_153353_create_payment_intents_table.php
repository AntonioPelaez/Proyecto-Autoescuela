<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_intents', function (Blueprint $table) {
            $table->id();

            // Relación con la clase reservada
            $table->foreignId('class_session_id')
                ->constrained('class_sessions')
                ->onDelete('cascade');

            // Datos del proveedor de pago
            $table->string('provider', 50)->nullable();              // Stripe, Redsys, etc.
            $table->string('provider_reference', 120)->nullable();   // ID externo

            // Datos económicos
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('EUR');

            // Estado del pago
            $table->string('status', 20); // pending, paid, failed, refunded

            // Respuesta completa de la pasarela
            $table->json('payload')->nullable();

            // Fecha de cobro
            $table->timestamp('paid_at')->nullable();

            // created_at / updated_at
            $table->timestamps();

            // Índice recomendado
            $table->index(['class_session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
    }
};
