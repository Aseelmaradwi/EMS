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
        Schema::table('departments', function (Blueprint $table) {
            $table->uuid('manager_employee_id')->nullable()->after('status');
            $table->index('manager_employee_id');
            $table->foreign('manager_employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['manager_employee_id']);
            $table->dropIndex(['manager_employee_id']);
            $table->dropColumn('manager_employee_id');
        });
    }
};
