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
        Schema::create('note_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('updater_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->decimal('x', 6, 3);
            $table->decimal('y', 6, 3);
            $table->decimal('width', 6, 3);
            $table->decimal('height', 6, 3);
            $table->unsignedInteger('version');
            $table->boolean('is_new')->default(false);
            $table->timestamps();

            $table->index(['note_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_versions');
    }
};
