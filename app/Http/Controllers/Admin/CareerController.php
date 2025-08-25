<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CareerController extends Controller
{
    public function index()
    {
        logger('index: ');
        $careers = Career::latest()->paginate(15);
        
        return view('admin.careers.index', compact('careers'));
    }

    public function create()
    {
        logger('create: ');
        return view('admin.careers.create');
    }

    public function store(Request $request)
    {
        logger('store: ');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:careers,name',
            'description' => 'nullable|string',
            //'is_active' => 'required|boolean',
        ]);
        echo $request->is_active;       
        logger('es $request->is_active: '.print_r($request->is_active, true));
        try {
            $validated['is_active'] = $request->has('is_active1');
            logger('en try :  '.print_r($validated['is_active'], true)); 

            if ($request->is_active==='on')
                $validated['is_active'] = true;
            else 
                $validated['is_active'] = false;

            Career::create($validated);
        } catch (\Exception $e) {
            logger('en try catch :  '); 
            logger('falló:  '.print_r($request->has('') ??  '', true));
        }
        return redirect()->route('admin.careers.index')->with('status', 'Carrera creada exitosamente.');
    }

    public function edit(Career $career)
    {
        return view('admin.careers.edit', compact('career'));
    }

    public function update(Request $request, Career $career)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('careers')->ignore($career->id)],
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        $career->update($validated);

        return redirect()->route('admin.careers.index')->with('status', 'Carrera actualizada exitosamente.');
    }

    public function destroy(Career $career)
    {
        // Opcional: Añadir lógica para prevenir el borrado si la carrera tiene preguntas asociadas.
        if ($career->questions()->exists()) {
            return back()->with('error', 'No se puede eliminar una carrera con preguntas asociadas.');
        }
        $career->delete();
        return redirect()->route('admin.careers.index')->with('status', 'Carrera eliminada exitosamente.');
    }
}
