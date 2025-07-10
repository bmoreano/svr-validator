<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Valida, crea un nuevo usuario y le asigna un equipo personal.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        // Usamos una transacción para garantizar la integridad de los datos.
        return DB::transaction(function () use ($input) {
            
            // 1. Creamos el usuario
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'role' => 'autor',
            ]);

            // 2. Creamos su equipo personal y lo asignamos como actual
            $this->createTeam($user);

            // 3. Devolvemos el objeto User, ahora completo
            return $user;
        });
    }

    /**
     * Crea un equipo personal para el usuario y lo establece como su equipo actual.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function createTeam(User $user): void
    {
        // El método ownedTeams()->forceCreate() de Jetstream se encarga de:
        // 1. Crear un nuevo registro en la tabla 'teams'.
        // 2. Crear un registro en la tabla pivote 'team_user'.
        // 3. ACTUALIZAR el campo 'current_team_id' en la tabla 'users' con el ID del nuevo equipo.
        $user->ownedTeams()->forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]);
    }
}