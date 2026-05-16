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
        if (Schema::hasColumn('departments', 'manager_id')) {
            return;
        }

        Schema::table('departments', function (Blueprint $table): void {
            $table->uuid('manager_id')->nullable()->after('manager_employee_id');
            $table->index('manager_id');
            $table->foreign('manager_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('departments', 'manager_id')) {
            return;
        }

        Schema::table('departments', function (Blueprint $table): void {
            $table->dropForeign(['manager_id']);
            $table->dropIndex(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }
};
