<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El nombre del tipo ENUM en PostgreSQL. Laravel lo crea automáticamente como
     * 'nombretabla_nombrecolumna_type'.
     */
    private string $enumTypeName = 'status';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // La sintaxis de PostgreSQL para añadir un valor a un tipo ENUM existente.
        // Se ejecuta como una consulta SQL cruda.
        DB::statement("ALTER TYPE {$this->enumTypeName} ADD VALUE 'rechazado'");
    }

    /**
     * Reverse the migrations.
     *
     * ¡Importante! Revertir (eliminar) un valor de un ENUM en PostgreSQL es muy complejo
     * y a menudo no se recomienda en producción porque puede causar problemas si hay
     * datos que usan ese valor.
     *
     * Para desarrollo, lo más simple es dejar el método 'down' vacío o lanzar una excepción
     * para indicar que esta migración no es fácilmente reversible.
     */
    public function down(): void
    {
        // Dejar vacío o lanzar una excepción es la práctica más segura.
        // Si intentas eliminar 'rechazado' mientras una fila lo usa, fallará.
        
        // Si estás seguro y necesitas revertir, tendrías que:
        // 1. Actualizar todas las filas que usan 'rechazado' a otro valor.
        //    DB::table('questions')->where('status', 'rechazado')->update(['status' => 'borrador']);
        // 2. Recrear el tipo ENUM sin el valor 'rechazado', lo cual es destructivo y complejo.
        //    Por simplicidad, lo dejaremos así.
        
        Log::warning("La reversión de la migración que añade 'rechazado' al ENUM 'status' no se realiza automáticamente por seguridad.");
    }
};