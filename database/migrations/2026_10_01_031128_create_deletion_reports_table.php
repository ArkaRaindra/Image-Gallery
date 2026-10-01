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
        Schema::create('deletion_reports', function (Blueprint $table) {
            $table->id();
            $table->morphs('reportable');
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject_label');
            $table->text('reason');
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['reportable_type', 'reportable_id', 'status'], 'deletion_reports_subject_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deletion_reports');
    }
};
