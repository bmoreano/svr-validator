<?php

namespace App\Jobs;

use App\Models\Question;
use App\Models\User;
use App\Models\Criterion; // Importamos el modelo Criterion
use App\Notifications\AiValidationCompleted;
use App\Notifications\AiValidationFailed;
use App\Notifications\ReviewRequestForValidators;
use App\Services\AiValidatorFactory;
use App\Services\ValidationParserService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class ValidateQuestionJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300; // Aumentamos a 5 minutos por las múltiples llamadas a la API

    public function __construct(
        public int $questionId,
        public string $engineName,
        public ?int $prompt_id = null
    ) {}

    public function handle(AiValidatorFactory $factory, ValidationParserService $parser): void
    {
        if ($this->batch() && $this->batch()->cancelled()) { return; }
        $question = Question::find($this->questionId);
        if (!$question) { return; }
        
        $allowedStatuses = ['en_validacion_ai', 'en_validacion_comparativa'];
        if (!in_array($question->status, $allowedStatuses)) { return; }

        try {
            // Obtenemos el validador una sola vez
            $validator = $factory->make($this->engineName);
            
            // 1. Obtenemos todos los criterios y los dividimos en lotes (chunks).
            $allCriteria = Criterion::where('is_active', true)->get();
            $criteriaChunks = $allCriteria->chunk(10); // Lotes de 10 criterios
            $finalJsonResponseArray = [];

            // 2. Iteramos sobre cada lote de criterios.
            foreach ($criteriaChunks as $chunk) {
                // 3. Llamamos al validador con los TRES argumentos requeridos.
                $partialJsonResponse = $validator->validate($question, $this->prompt_id, $chunk);
                
                $cleanedPartialJson = $parser->cleanJsonResponse($partialJsonResponse);
                $partialArray = json_decode($cleanedPartialJson, true);

                if (is_array($partialArray)) {
                    // 4. Unimos los resultados parciales al array final.
                    $finalJsonResponseArray = array_merge($finalJsonResponseArray, $partialArray);
                } else {
                    Log::warning("Un lote de criterios no devolvió un JSON válido.", ['response' => $partialJsonResponse]);
                }
                sleep(1); // Pequeña pausa para no saturar la API.
            }

            // 5. Si obtuvimos resultados, los procesamos.
            if (!empty($finalJsonResponseArray)) {
                $fullJsonResponse = json_encode($finalJsonResponseArray);
                if ($parser->saveAiValidation($question, $fullJsonResponse, $this->engineName)) {
                    if (!$this->batch()) {
                        // Se actualiza el estado de la pregunta a 'revisado_por_ai'.
                        $question->update(['status' => 'revisado_por_ai']);
                        
                        // Las siguientes líneas envían las notificaciones.
                        $question->author->notify(new AiValidationCompleted($question, $this->engineName));
                        $validators = User::where('role', 'validador')->get();
                        if ($validators->isNotEmpty()) {
                            Notification::send($validators, new ReviewRequestForValidators($question, $this->engineName));
                        }
                    }
                    Log::info("Pregunta #{$this->questionId} validada con éxito...");
                    
                    Log::info("Pregunta #{$this->questionId} validada con {$this->engineName} (en lotes).");
                } else {
                    $this->fail(new \Exception("El ValidationParserService falló al procesar la respuesta combinada."));
                }
            } else {
                $this->fail(new \Exception("Ningún lote de criterios devolvió una respuesta válida."));
            }

        } catch (\Exception $e) {
            Log::error("Excepción en Job de Validación (en lotes)", ['exception' => $e]);
            $this->fail($e);
        }
    }
    
    public function failed(Throwable $exception): void
    {
        // ... (lógica del método failed sin cambios)
    }
}
