<div>
    @props(['criterion', 'aiResponse' => null])

    <div x-data="{ response: '{{ $aiResponse?->response ?? 'si' }}' }">
        <label class="block font-medium text-sm text-gray-700">{{ $criterion->text }}</label>
        <div class="mt-2 flex items-center space-x-4">
            <label class="inline-flex items-center">
                <input x-model="response" type="radio" name="criteria[{{ $criterion->id }}][response]" value="si"
                    class="text-indigo-600">
                <span class="ml-2">Sí</span>
            </label>
            <label class="inline-flex items-center">
                <input x-model="response" type="radio" name="criteria[{{ $criterion->id }}][response]" value="no"
                    class="text-indigo-600">
                <span class="ml-2">No</span>
            </label>
            <label class="inline-flex items-center">
                <input x-model="response" type="radio" name="criteria[{{ $criterion->id }}][response]" value="adecuar"
                    class="text-indigo-600">
                <span class="ml-2">Se puede adecuar</span>
            </label>
        </div>
        <div x-show="response === 'no' || response === 'adecuar'" x-cloak class="mt-2">
            <label for="comment_{{ $criterion->id }}" class="block text-sm font-medium text-gray-500">Comentario
                (requerido)</label>
            <textarea id="comment_{{ $criterion->id }}" name="criteria[{{ $criterion->id }}][comment]" rows="2"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $aiResponse?->comment ?? '' }}</textarea>
        </div>
    </div>
</div>
