<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_skill_evaluations', function (Blueprint $table) {
            // Mover las columnas a la posición correcta
            $table->boolean('ready_for_exam')->default(false)->after('teacher_profile_id')->change();
            $table->text('notes')->nullable()->after('ready_for_exam')->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_skill_evaluations', function (Blueprint $table) {
            // Volver a ponerlas al final si hiciera falta
            $table->boolean('ready_for_exam')->default(false)->after('updated_at')->change();
            $table->text('notes')->nullable()->after('ready_for_exam')->change();
        });
    }
};
