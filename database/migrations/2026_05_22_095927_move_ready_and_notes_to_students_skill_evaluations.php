<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    // 1. Añadir columnas nuevas en la tabla padre
    Schema::table('student_skill_evaluations', function (Blueprint $table) {
        $table->boolean('ready_for_exam')->default(false);
        $table->text('notes')->nullable();
    });

    // 2. Copiar datos desde la tabla hija (FUERA del Schema::table)
    DB::table('student_skills_evaluations')
        ->orderBy('id')
        ->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('student_skill_evaluations')
                    ->where('id', $row->student_skill_id) // RELACIÓN CORRECTA
                    ->update([
                        'ready_for_exam' => $row->ready_for_exam,
                        'notes' => $row->notes,
                    ]);
            }
        });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students_skill_evaluations', function (Blueprint $table) {
            $table->dropColumn('ready_for_exam');
            $table->dropColumn('notes');
        });
    }
};
