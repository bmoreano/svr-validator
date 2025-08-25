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
        // Definimos la lista COMPLETA y ACTUALIZADA de todos los estados permitidos.
        $allowedStatuses = [
            'pendiente', 
            'completado', 
            'failed' // <-- El nuevo estado
        ];
        $statusList = "'" . implode("','", $allowedStatuses) . "'";

        DB::transaction(function () use ($statusList) {
            // Paso 1: Eliminamos la restricción CHECK antigua, si existe.
            // El nombre por defecto de la restricción es <table>_<column>_check.
            DB::statement('ALTER TABLE validations DROP CONSTRAINT IF EXISTS validations_status_check');

            // Paso 2: Añadimos la nueva columna para el mensaje de error.
            Schema::table('validations', function (Blueprint $table) {
                $table->text('failure_reason')->nullable()->after('status');
            });
            
            // Paso 3: Volvemos a crear la restricción CHECK con la lista de valores actualizada.
            DB::statement("ALTER TABLE validations ADD CONSTRAINT validations_status_check CHECK (status IN ({$statusList}))");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Para revertir, hacemos lo contrario: eliminamos la restricción y la columna.
        DB::transaction(function () {
             DB::statement('ALTER TABLE validations DROP CONSTRAINT IF EXISTS validations_status_check');
            Schema::table('validations', function (Blueprint $table) {
                $table->dropColumn('failure_reason');
            });
            // Opcionalmente, podríamos recrear la restricción sin 'failed'.
        });
    }
};