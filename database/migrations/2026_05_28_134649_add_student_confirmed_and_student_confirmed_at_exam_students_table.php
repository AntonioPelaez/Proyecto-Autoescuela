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
            $table->boolean('student_confirmed')->default(false)->after('result_notes');
            $table->timestamp('student_confirmed_at')->nullable()->after('student_confirmed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_students', function (Blueprint $table) {
            $table->dropColumn(['student_confirmed', 'student_confirmed_at']);
        });
    }
};
