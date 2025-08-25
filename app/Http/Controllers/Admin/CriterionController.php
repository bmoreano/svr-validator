<?php

// ASEGÚRATE DE QUE EL NAMESPACE ES CORRECTO
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criterion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Class CriterionController
 *
 * Gestiona el CRUD para los Criterios de Validación por parte del Administrador.
 */
class CriterionController extends Controller
{
    /**
     * Muestra una lista de todos los criterios, agrupados por categoría.
     */
    public function index(): View
    {
        // Se obtienen todos los criterios y se ordenan para una visualización lógica.
        $criteria = Criterion::orderBy('category')->orderBy('sort_order')->get();
        return view('admin.criteria.index', compact('criteria'));
    }

    /**
     * Muestra el formulario para crear un nuevo criterio.
     */
    public function create(): View
    {
        // Pasa un nuevo objeto Criterion para consistencia en el formulario parcial.
        return view('admin.criteria.create', ['criterion' => new Criterion()]);
    }

    /**
     * Guarda un nuevo criterio en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|max:500|unique:criteria,text',
            'category' => ['required', Rule::in(['formulacion', 'opciones', 'argumentacion', 'bibliografia'])],
            'sort_order' => 'required|integer',
            'is_active' => 'nullable|boolean', // Se trata como booleano
        ]);

        // El checkbox no se envía si no está marcado, por lo que se comprueba su presencia.
        $validated['is_active'] = $request->has('is_active');

        Criterion::create($validated);

        return redirect()->route('admin.criteria.index')->with('status', 'Criterio creado exitosamente.');
    }

    /**
     * Muestra los detalles de un criterio. Opcional.
     */
    public function show(Criterion $criterion): View
    {
        return view('admin.criteria.show', compact('criterion'));
    }

    /**
     * Muestra el formulario para editar un criterio existente.
     */
    public function edit(Criterion $criterion): View
    {
        return view('admin.criteria.edit', compact('criterion'));
    }

    /**
     * Actualiza un criterio existente en la base de datos.
     */
    public function update(Request $request, Criterion $criterion): RedirectResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:500', Rule::unique('criteria')->ignore($criterion->id)],
            'category' => ['required', Rule::in(['formulacion', 'opciones', 'argumentacion', 'bibliografia'])],
            'sort_order' => 'required|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $criterion->update($validated);

        return redirect()->route('admin.criteria.index')->with('status', 'Criterio actualizado exitosamente.');
    }

    /**
     * Elimina un criterio de la base de datos.
     */
    public function destroy(Criterion $criterion): RedirectResponse
    {
        // Se podría añadir una comprobación aquí para prevenir la eliminación
        // de criterios que ya están siendo usados en validaciones.
        try {
            $criterion->delete();
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo eliminar el criterio, es posible que esté en uso.');
        }

        return redirect()->route('admin.criteria.index')->with('status', 'Criterio eliminado exitosamente.');
    }
}