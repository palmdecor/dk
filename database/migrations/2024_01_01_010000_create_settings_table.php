<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('watermark_path')->nullable();
            $table->string('position_mode')->default('bottom_right');
            $table->integer('custom_x')->nullable();
            $table->integer('custom_y')->nullable();
            $table->string('scale_mode')->default('percent');
            $table->integer('scale_value')->default(20);
            $table->integer('opacity')->default(80);
            $table->integer('padding')->default(10);
            $table->integer('max_upload_mb')->default(10);
            $table->integer('output_quality')->default(90);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
