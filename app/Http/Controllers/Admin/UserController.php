<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Career;
use App\Rules\AllowedDomainForRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Validation\Rules;

/**
 * Class AdminUserController
 *
 * Controlador para la gestión de usuarios por parte de un Administrador.
 * Sigue las convenciones de un controlador de recursos RESTful.
 */
class UserController  extends Controller
{
    /**
     * Muestra una lista paginada de todos los usuarios del sistema.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Se obtienen todos los usuarios, excluyendo la cuenta del sistema de IA,
        // ordenados por nombre y paginados para un mejor rendimiento.
        // Excluimos al propio administrador de la lista para que no pueda editarse/borrarse a sí mismo
        /*$users = User::where('id', '!=', auth()->id())
        ->latest()->paginate(20);

        return view('admin.users.index', compact('users'));*/

        $users = User::where('email', '!=', 'ai@svr.com')
            ->orderBy('name')
            ->paginate(15);
            
        return view('admin.users.index', compact('users'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Pasamos la lista de carreras a la vista de creación
        $careers = Career::where('is_active', true)->orderBy('name')->get();
        return view('admin.users.create', compact('careers'));
    }

    /**
     * Guarda un nuevo usuario creado por el administrador.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => ['required', Rule::in(['autor', 'validador', 'administrador', 'tester', 'tecnico', 'jefe_carrera'])],
            'email' => [
                'required', 'string', 'email', 'max:255', 'unique:users',
                new AllowedDomainForRole($request->input('role')),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'career_id' => 'nullable|integer|exists:careers,id',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'career_id' => $validated['career_id'], 
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Usuario creado exitosamente.');
    }  

    /**
     * Muestra los detalles de un usuario. Este método es opcional,
     * ya que a menudo se va directamente a la edición.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\View\View
     */
    public function show(User $user): View
    {
        // Mostramos la vista de edición directamente, ya que es más útil para un admin.
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Muestra el formulario para editar un usuario específico.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        if ($user->role === 'administrador') { abort(403); }
        // Pasamos la lista de carreras a la vista de edición
        $careers = Career::where('is_active', true)->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'careers'));
    }

    /**
     * Actualiza los datos de un usuario en la base de datos.
     *
     * @param \Illuminate\Http\Request $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * Actualiza un usuario existente.
     */
    public function update(Request $request, User $user)
    {
        if ($user->role === 'administrador') { abort(403); }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => ['required', Rule::in(['autor', 'validador', 'administrador', 'tester', 'tecnico', 'jefe_carrera'])],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($user->id),
                new AllowedDomainForRole($request->input('role')),
            ],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'career_id' => 'nullable|integer|exists:careers,id',
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'career_id' => $validated['career_id'], 
        ]);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina un usuario del sistema.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        if ($user->role === 'administrador') {
            abort(403, 'No se puede eliminar a un administrador.');
        }
        if ($user->role === 'autor' && $user->questions()->exists()) {
            return back()->with('error', 'No se puede eliminar a este autor porque tiene preguntas asociadas. Reasígnelas o elimínelas primero.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Usuario eliminado exitosamente.');
    }
}
