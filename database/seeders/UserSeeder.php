<?php

// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Validador de IA
        User::create(
            [
                'name' => 'Gemini Validator',
                'password' => Hash::make('password'),
                'email' => 'ai@example.com',
                'email_verified_at' => now(),
                'current_team_id' => 1, // Asignar al equipo por defecto
                'two_factor_confirmed_at' => now(), // Confirmar autenticación de dos factores
                'role' => 'validador',

            ]
        );

        // Usuario Administrador de ejemplo
        User::firstOrCreate()->create(
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'current_team_id' => 1, // Asignar al equipo por defecto
                'two_factor_confirmed_at' => now(), // Confirmar autenticación de dos factores
                'role' => 'administrador',
            ]
        );
        User::firstOrCreate()->create(
            [
                'name' => 'Autor User',
                'password' => Hash::make('password'),
                'email' => 'creator@example.com',
                'email_verified_at' => now(),
                'current_team_id' => 1, // Asignar al equipo por defecto
                'two_factor_confirmed_at' => now(), // Confirmar autenticación de dos factores
                'role' => 'autor',
            ]
        );
        
    }
}
