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
        // --- Tabla de Prompts ---
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('ai_engine', ['chatgpt', 'gemini']);
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['pending_review', 'active', 'rejected'])->default('pending_review');
            $table->text('review_feedback')->nullable();
            $table->timestamps();
        });

        // --- Tabla de Validaciones ---
        Schema::create('validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('validator_id')->constrained('users');
            $table->string('ai_engine')->nullable();
            $table->enum('status', ['pendiente', 'completado'])->default('completado');
            $table->timestamps();
        });

        // --- Tabla de Respuestas de Validación ---
        Schema::create('validation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validation_id')->constrained()->onDelete('cascade');
            $table->foreignId('criterion_id')->constrained();
            $table->enum('response', ['si', 'no', 'adecuar']);
            $table->text('comment')->nullable();
            $table->unique(['validation_id', 'criterion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validation_responses');
        Schema::dropIfExists('validations');
        Schema::dropIfExists('prompts');
    }
};