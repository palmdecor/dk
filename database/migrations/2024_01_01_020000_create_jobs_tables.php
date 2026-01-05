<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('processing');
            $table->integer('total_files');
            $table->integer('processed_files')->default(0);
            $table->timestamps();
        });

        Schema::create('job_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->string('original_path');
            $table->string('processed_path')->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('jobs_queue', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs_queue');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_files');
        Schema::dropIfExists('jobs');
    }
};
