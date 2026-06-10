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
            $table->integer('start_km')->nullable()->after('student_id');
           $table->integer('end_km')->nullable()->after('start_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_students', function (Blueprint $table) {
            $table->dropColumn(['start_km', 'end_km']);
        });
    }
};
