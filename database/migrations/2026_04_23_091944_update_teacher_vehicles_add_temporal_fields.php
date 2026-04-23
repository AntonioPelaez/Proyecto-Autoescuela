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
    public function up()
{
    Schema::table('teacher_vehicles', function (Blueprint $table) {

        // Añadir timestamps si no existen
        if (!Schema::hasColumn('teacher_vehicles', 'created_at')) {
            $table->timestamp('created_at')->nullable();
        }
        if (!Schema::hasColumn('teacher_vehicles', 'updated_at')) {
            $table->timestamp('updated_at')->nullable();
        }

        // Añadir campos temporales
        if (!Schema::hasColumn('teacher_vehicles', 'is_temporary')) {
            $table->boolean('is_temporary')->default(0);
        }

        if (!Schema::hasColumn('teacher_vehicles', 'start_at')) {
            $table->dateTime('start_at')->nullable();
        }

        if (!Schema::hasColumn('teacher_vehicles', 'end_at')) {
            $table->dateTime('end_at')->nullable();
        }

        // Asegurar que is_primary tiene default
        if (Schema::hasColumn('teacher_vehicles', 'is_primary')) {
            DB::statement("ALTER TABLE teacher_vehicles MODIFY is_primary TINYINT(1) DEFAULT 0");
        }
    });
}

public function down()
{
    Schema::table('teacher_vehicles', function (Blueprint $table) {
        $table->dropColumn(['is_temporary', 'start_at', 'end_at']);
    });
}
};
