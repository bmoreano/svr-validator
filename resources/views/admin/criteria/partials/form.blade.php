{{-- Este formulario parcial se incluye en las vistas 'create' y 'edit' --}}
<div class="space-y-6">
    {{-- Muestra cualquier error de validación del backend --}}
    <x-validation-errors class="mb-4" />

    <!-- Campo: Texto del Criterio -->
    <div>
        <x-label for="text" value="Texto del Criterio" />
        <textarea id="text" name="text" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>{{ old('text', $criterion->text) }}</textarea>
        <p class="text-xs text-gray-500 mt-1">Describe el criterio de validación de forma clara y concisa.</p>
    </div>

    <!-- Campo: Categoría -->
    <div>
        <x-label for="category" value="Categoría" />
        <select name="category" id="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            <option value="" disabled @if(!$criterion->exists) selected @endif>-- Selecciona una categoría --</option>
            @foreach(['formulacion', 'opciones', 'argumentacion', 'bibliografia'] as $category)
                <option value="{{ $category }}" @selected(old('category', $criterion->category) === $category)>{{ ucfirst($category) }}</option>
            @endforeach
        </select>
    </div>

    <!-- Campo: Orden de Clasificación -->
    <div>
        <x-label for="sort_order" value="Orden de Aparición" />
        <x-input id="sort_order" name="sort_order" type="number" class="mt-1 block w-full" :value="old('sort_order', $criterion->sort_order ?? 0)" required />
        <p class="text-xs text-gray-500 mt-1">Un número más bajo aparecerá primero en la lista.</p>
    </div>

    <!-- Campo: Estado (Activo/Inactivo) -->
    <div class="block">
        <label for="is_active" class="flex items-center">
            <x-checkbox id="is_active" name="is_active" value="1" :checked="old('is_active', $criterion->is_active ?? true)" />
            <span class="ms-2 text-sm text-gray-600">Criterio Activo (marcado para usarlo en las validaciones)</span>
        </label>
    </div>
</div>

{{-- Barra de Acciones --}}
<div class="flex items-center justify-end mt-8 border-t pt-6">
    <a href="{{ route('admin.criteria.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
    <x-button>
        {{-- El texto del botón cambia si estamos editando o creando --}}
        {{ $criterion->exists ? 'Actualizar Criterio' : 'Guardar Criterio' }}
    </x-button>
</div>