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
        Schema::create('incidents', function (Blueprint $table) {
           $table->id();

            // Tipo de incidencia
            $table->unsignedBigInteger('tipo_id');
            $table->foreign('tipo_id')
                ->references('id')
                ->on('type_incidents')
                ->cascadeOnDelete();

            // Prioridad, estado y descripción
            $table->string('prioridad');
            $table->string('estado')->default('abierta');
            $table->text('descripcion')->nullable();

            // Reserva relacionada
            $table->unsignedBigInteger('reserva_id')->nullable();
            $table->foreign('reserva_id')
                ->references('id')
                ->on('class_sessions')
                ->nullOnDelete();

            // Usuario asignado (quien gestiona la incidencia)
            $table->unsignedBigInteger('asignado_a')->nullable();
            $table->foreign('asignado_a')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // NUEVO: Responsable de la incidencia
            // alumno / profesor / externo
            $table->string('responsable')->nullable();

            // NUEVO: Profesor implicado
            $table->unsignedBigInteger('profesor_asignado')->nullable();
            $table->foreign('profesor_asignado')
                ->references('id')
                ->on('teacher_profiles')
                ->nullOnDelete();

            // NUEVO: Alumno implicado
            $table->unsignedBigInteger('alumno_asignado')->nullable();
            $table->foreign('alumno_asignado')
                ->references('id')
                ->on('student_profiles')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
