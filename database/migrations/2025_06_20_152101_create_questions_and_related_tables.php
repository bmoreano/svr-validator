<?php

// database/migrations/..._create_questions_and_related_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Tabla de Preguntas (Reactivos)
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users');
            $table->text('stem'); // Enunciado
            $table->enum('status', [
                'borrador', 'en_validacion_ai', 'revisado_por_ai', 'error_validacion_ai',
                'en_revision_humana', 'necesita_correccion', 'aprobado'
            ])->default('borrador');
            $table->text('bibliography')->nullable();
            $table->timestamps();
        });

        // Tabla de Opciones
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->text('argumentation')->nullable(); // Justificación de la opción
        });

        // Tabla de Validaciones (un registro por cada intento de validación)
        Schema::create('validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('validator_id')->constrained('users'); // Puede ser el AI o un humano
            $table->enum('status', ['pendiente', 'completado'])->default('completado');
            $table->timestamps();
        });

        // Tabla de Respuestas a los Criterios
        Schema::create('validation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validation_id')->constrained()->onDelete('cascade');
            $table->foreignId('criterion_id')->constrained();
            $table->enum('response', ['si', 'no', 'adecuar']);
            $table->text('comment')->nullable();
            $table->unique(['validation_id', 'criterion_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('validation_responses');
        Schema::dropIfExists('validations');
        Schema::dropIfExists('options');
        Schema::dropIfExists('questions');
    }
};