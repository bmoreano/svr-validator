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
        Schema::table('questions', function (Blueprint $table) {
            // COMENTARIO: Para almacenar el vector de similitud semántica.
            // JSON es un tipo versátil. Si usas PostgreSQL con la extensión pgvector,
            // podrías usar $table->vector('embedding_vector', 1536)->nullable();
            $table->json('embedding_vector')->nullable()->after('content_hash'); 
            
            // COMENTARIO: Para guardar el reporte de validación con los hallazgos (duplicados, plagio, etc.)
            $table->json('validation_report')->nullable()->after('embedding_vector');

            // COMENTARIO: Asegurarse de que el campo 'status' tenga la longitud suficiente para los nuevos estados.
            // Si 'en_validacion_ai', 'revisado_por_ai', 'necesita_correccion', 'error_validacion_ai' son estados.
            $table->string('status', 50)->change(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('embedding_vector');
            $table->dropColumn('validation_report');
            // COMENTARIO: Revertir el cambio de longitud del campo status si se hizo.
            $table->string('status', 20)->change(); 
        });
    }
};
