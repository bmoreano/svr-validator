<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // --- Tabla de Criterios ---
        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->enum('category', ['formulacion', 'opciones', 'argumentacion', 'bibliografia']);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
        });

        // --- Tabla de Preguntas (Reactivos) ---
        $allowedStatuses = [
            'borrador',
            'en_validacion_ai',
            'revisado_por_ai',
            'error_validacion_ai',
            'en_revision_humana',
            'necesita_correccion',
            'aprobado',
            'en_validacion_comparativa',
            'revisado_comparativo',
            'fallo_comparativo',
            'rechazado',
            'activo',
            'pendiente_de_revision',
            'rechazado_permanentemente'
        ];
        $statusList = "'" . implode("','", $allowedStatuses) . "'";


        Schema::create('questions', function (Blueprint $table) use ($statusList) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->text('stem');
            $table->enum('grado_dificultad',['muy_facil','facil','mediana','dificil','muy_dificil'])->default('mediana'); // valor esperado en el examen
            $table->enum('poder_discriminacion',['muy_alto','alto','moderado','bajo','muy_bajo'])->default('moderado');   // valor esperado en el examen
            $table->text('status')->default('borrador');
            $table->text('bibliografia')->nullable();   
            $table->boolean('corregido_administrador')->nullable(); 
            $table->text('comentario_administrador')->nullable();  
            $table->timestamps();
        });

        // Restricción CHECK para la columna status en PostgreSQL
        DB::statement("ALTER TABLE questions ADD CONSTRAINT questions_status_check CHECK (status IN ({$statusList}))");

        // --- Tabla de Opciones ---
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->text('argumentation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('criteria');
    }
};

