<?php
namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    // Solo un administrador puede ver la lista de usuarios.
    public function viewAny(User $user): bool
    {
        return $user->role === 'administrador';
    }

    // Solo un administrador puede crear usuarios.
    public function create(User $user): bool
    {
        return $user->role === 'administrador';
    }

    // Un administrador puede editar a otros usuarios, pero no a otros administradores.
    public function update(User $currentUser, User $targetUser): bool
    {
        return $currentUser->role === 'administrador' && $targetUser->role !== 'administrador';
    }

    // Un administrador puede eliminar a otros usuarios, pero no a otros administradores.
    public function delete(User $currentUser, User $targetUser): bool
    {
        return $currentUser->role === 'administrador' && $targetUser->role !== 'administrador';
    }
}