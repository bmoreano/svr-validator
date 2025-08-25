<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Quién hizo el cambio

            // Copia de los campos principales de la pregunta
            $table->string('code');
            $table->text('stem');
            $table->text('bibliography')->nullable();
            $table->text('grado_dificultad')->nullable();
            $table->text('poder_discriminacion')->nullable();

            // Guardamos las opciones y sus argumentaciones como un JSON
            $table->json('options_snapshot');
            
            $table->string('change_reason')->nullable(); // Ej. "Creación inicial", "Corrección del validador"
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('question_revisions'); }
};