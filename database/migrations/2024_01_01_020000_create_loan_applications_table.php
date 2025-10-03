<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('national_id', 11);
            $table->decimal('monthly_income', 12, 2);
            $table->string('employment_status');
            $table->decimal('loan_amount', 12, 2);
            $table->unsignedInteger('loan_term');
            $table->string('phone');
            $table->string('email');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('kvkk_approved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
