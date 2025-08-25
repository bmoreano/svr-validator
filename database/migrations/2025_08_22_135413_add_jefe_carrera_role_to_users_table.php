<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Definimos la lista COMPLETA y FINAL de todos los roles permitidos.
        $allowedRoles = [
            'autor', 
            'validador', 
            'administrador', 
            'tester', 
            'tecnico', 
            'jefe_carrera' 
        ];
        $roleList = "'" . implode("','", $allowedRoles) . "'";
        
        // Eliminamos la restricción antigua y la volvemos a crear con la lista actualizada.
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ({$roleList}))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Lógica para revertir, eliminando el nuevo rol.
        $oldRoles = ['autor', 'validador', 'administrador', 'tester', 'tecnico'];
        $roleList = "'" . implode("','", $oldRoles) . "'";
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ({$roleList}))");
    }
};