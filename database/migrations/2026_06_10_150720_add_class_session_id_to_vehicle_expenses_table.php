<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_expenses', function (Blueprint $table) {
            $table->foreignId('class_session_id')
                ->nullable()
                ->after('vehicle_id')
                ->constrained('class_sessions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_expenses', function (Blueprint $table) {
            // Nombre REAL que generará Laravel:
            // vehicle_expenses_class_session_id_foreign
            $table->dropForeign('vehicle_expenses_class_seesion_id_foreign');
            $table->dropColumn('class_seesion_id');
        });
    }
};
