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
        Schema::table('exam_students', function (Blueprint $table) {
            $table->dropColumn('teacher_approved');
            $table->dropColumn('teacher_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_students', function (Blueprint $table) {
            $table->boolean('teacher_approved')->default(false)->after('student_confirmed_at');
            $table->timestamp('teacher_approved_at')->nullable()->after('teacher_approved');
        });
    }
};
