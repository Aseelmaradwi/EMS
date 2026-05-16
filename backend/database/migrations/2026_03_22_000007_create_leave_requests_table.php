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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->uuid('approved_by')->nullable();
            $table->enum('leave_type', [
                'annual',
                'sick',
                'emergency',
                'unpaid',
                'maternity',
                'paternity',
                'other',
            ]);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled']);
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('approved_by');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('approved_by')
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
        Schema::dropIfExists('leave_requests');
    }
};
