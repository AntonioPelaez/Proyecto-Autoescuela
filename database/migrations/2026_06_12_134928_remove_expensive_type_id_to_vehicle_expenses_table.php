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
        Schema::table('vehicle_expenses', function (Blueprint $table) {
            $table->dropForeign('vehicle_expenses_expense_type_id_foreign');
            $table->dropColumn('expense_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_expenses', function (Blueprint $table) {
            $table->foreignId('expense_type_id')->constrained('expense_types')->cascadeOnDelete();
        });
    }
};
