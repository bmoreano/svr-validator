<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Usamos JSON para guardar la "instantánea" de forma estructurada.
            // Si tu DB no soporta JSON, puedes usar TEXT.
            $table->json('corregido_administrador')->nullable()->after('bibliography');
            
            // Campo para el comentario obligatorio del administrador.
            $table->text('comentario_administrador')->nullable()->after('corregido_administrador');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['corregido_administrador', 'comentario_administrador']);
        });
    }
};