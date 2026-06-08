<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_students', function (Blueprint $table) {

            // ❗ Como NO existen foreign keys, solo borramos las columnas
            if (Schema::hasColumn('exam_students', 'teacher_id')) {
                $table->dropColumn('teacher_id');
            }

            if (Schema::hasColumn('exam_students', 'vehicle_id')) {
                $table->dropColumn('vehicle_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_students', function (Blueprint $table) {

            // Restaurar columnas si hiciera falta
            if (!Schema::hasColumn('exam_students', 'teacher_id')) {
                $table->unsignedBigInteger('teacher_id')->nullable();
            }

            if (!Schema::hasColumn('exam_students', 'vehicle_id')) {
                $table->unsignedBigInteger('vehicle_id')->nullable();
            }
        });
    }
};
