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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_ext', 10);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('width')->default(0);
            $table->unsignedInteger('height')->default(0);

            $table->string('thumbnail_path')->nullable();
            $table->string('md5', 32)->unique();
            $table->enum('rating', ['general', 'sensitive', 'questionable', 'explicit'])->default('general');
            $table->string('source')->nullable();
            $table->text('description')->nullable();
            $table->integer('score')->default(0);
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index('rating');
            $table->index('is_approved');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
