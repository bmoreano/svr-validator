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
            // Se añade una columna para almacenar el hash del contenido de la pregunta.
            // SHA256 genera un hash de 64 caracteres.
            // unique() para asegurar que no haya preguntas con contenido duplicado.
            // nullable() es temporal si ya tienes datos, pero para nuevas inserciones NO debería ser null.
            // Se coloca después de 'bibliography' para mantener un orden lógico.
            $table->string('content_hash', 64)->unique()->nullable()->after('bibliography');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Al revertir la migración, se elimina la columna content_hash.
            $table->dropColumn('content_hash');
        });
    }
};
