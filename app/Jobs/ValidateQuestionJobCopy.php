<?php

namespace App\Jobs;

use App\Models\Question;
use App\Services\EmbeddingService;
use App\Services\PlagiarismCheckService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Notification; 
use App\Notifications\QuestionValidationResult; 
use Illuminate\Support\Facades\Crypt; // Necesario para el exact duplicate check si no usas accessor para set

class ValidateQuestionJobCopy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Question $question;

    /**
     * Create a new job instance.
     */
    public function __construct(Question $question)
    {
        $this->question = $question;
    }

    /**
     * Execute the job.
     */
    public function handle(EmbeddingService $embeddingService, PlagiarismCheckService $plagiarismService): void
    {
        Log::info("Iniciando validación para la pregunta ID: {$this->question->id}. Status actual: {$this->question->status}");

        if ($this->question->status !== 'en_validacion_ai') {
            Log::warning("El Job ValidateQuestionJob se disparó para la pregunta ID: {$this->question->id}, pero su estado no es 'en_validacion_ai'. Omitiendo procesamiento.", [
                'current_status' => $this->question->status
            ]);
            return;
        }

        $validationReport = [
            'internal_validation' => [],
            'external_validation' => [],
            'final_decision' => null,
            'reason' => null,
            'errors' => [],
        ];

        try {
            // --- Etapa 1: Validación Interna (Detección de Repetición) ---

            // Fase 1.1: Chequeo de Duplicado Exacto
            // COMENTARIO: Asegúrate de usar Crypt::encryptString() si el 'stem' en la DB está cifrado y no se usa un mutator para comparar.
            // Si el accessor ya decripta al leer, pero necesitas comparar con un valor cifrado en la DB,
            // debes cifrar el valor de búsqueda explícitamente.
            $stemToCheck = $this->question->getRawOriginal('stem'); // Obtener el valor crudo (cifrado) de la DB
            $exactDuplicate = Question::where('stem', $stemToCheck) 
                                    ->where('id', '!=', $this->question->id) 
                                    ->first();
            if ($exactDuplicate) {
                $validationReport['internal_validation']['exact_duplicate'] = [
                    'status' => 'failed',
                    'reason' => 'Duplicado exacto del enunciado de la pregunta ID #' . $exactDuplicate->id,
                    'question_id' => $exactDuplicate->id,
                ];
                $validationReport['final_decision'] = 'necesita_correccion';
                $validationReport['reason'] = $validationReport['internal_validation']['exact_duplicate']['reason'];
                $this->updateQuestionStatusAndReport($validationReport); // Correcto uso de $this->
                return; 
            }
            $validationReport['internal_validation']['exact_duplicate'] = ['status' => 'passed'];

            // Fase 1.2: Chequeo de Similitud Semántica
            // El accessor de 'stem' en el modelo ya decripta, por lo que $this->question->stem dará el texto plano.
            $newQuestionEmbedding = $embeddingService->generateEmbedding($this->question->stem);

            if (!$newQuestionEmbedding) {
                $validationReport['errors'][] = 'Fallo al generar embedding para la nueva pregunta.';
                throw new \Exception('No se pudo generar embedding para la pregunta.');
            }
            $this->question->embedding_vector = $newQuestionEmbedding;
            $this->question->save(); 

            $similarQuestionFound = false;
            $allQuestionsForComparison = Question::whereNotNull('embedding_vector')
                                                ->where('id', '!=', $this->question->id) 
                                                ->get();

            foreach ($allQuestionsForComparison as $comparisonQuestion) {
                $comparisonEmbedding = $comparisonQuestion->embedding_vector;
                
                if (is_array($comparisonEmbedding) && !empty($comparisonEmbedding) && 
                    is_array($newQuestionEmbedding) && !empty($newQuestionEmbedding) &&
                    count($newQuestionEmbedding) === count($comparisonEmbedding)) 
                {
                    $similarity = $embeddingService::cosineSimilarity($newQuestionEmbedding, $comparisonEmbedding); 
                    $threshold = config('validation.semantic_similarity_threshold', 0.98); 

                    if ($similarity > $threshold) {
                        $validationReport['internal_validation']['semantic_similarity'] = [
                            'status' => 'failed',
                            'reason' => 'Semánticamente muy similar a la pregunta ID #' . $comparisonQuestion->id,
                            'question_id' => $comparisonQuestion->id,
                            'similarity_score' => round($similarity, 4),
                            'threshold' => $threshold,
                        ];
                        $similarQuestionFound = true;
                        break;
                    }
                }
            }

            if ($similarQuestionFound) {
                $validationReport['final_decision'] = 'necesita_correccion';
                $validationReport['reason'] = $validationReport['internal_validation']['semantic_similarity']['reason'];
                $this->updateQuestionStatusAndReport($validationReport); // Correcto uso de $this->
                return; 
            }
            $validationReport['internal_validation']['semantic_similarity'] = ['status' => 'passed'];


            // --- Etapa 2: Validación Externa (Detección de Plagio) ---
            
            $phrases = $this->extractKeyPhrases($this->question->stem);
            $plagiarizedUrls = $plagiarismService->searchWebForPhrases($phrases);

            if (!empty($plagiarizedUrls)) {
                $validationReport['external_validation']['web_search'] = [
                    'status' => 'failed',
                    'reason' => 'Posibles fuentes de plagio detectadas en búsqueda web.',
                    'urls' => $plagiarizedUrls,
                ];
                $validationReport['final_decision'] = 'necesita_correccion';
                $validationReport['reason'] = $validationReport['external_validation']['web_search']['reason'];
                $this->updateQuestionStatusAndReport($validationReport); // Correcto uso de $this->
                return; 
            }
            $validationReport['external_validation']['web_search'] = ['status' => 'passed'];


            // Fase 2.2: Análisis Profundo con API de Plagio (opcional/condicional)
            $deepPlagiarismThreshold = config('validation.deep_plagiarism_threshold', 0.15); 
            if ($deepPlagiarismThreshold > 0) { 
                $deepPlagiarismReport = $plagiarismService->deepPlagiarismCheck($this->question->stem);

                if (isset($deepPlagiarismReport['status']) && $deepPlagiarismReport['status'] === 'success' && 
                    $deepPlagiarismReport['percent_copied'] > ($deepPlagiarismThreshold * 100)) 
                {
                    $validationReport['external_validation']['deep_plagiarism'] = [
                        'status' => 'failed',
                        'reason' => 'Alto porcentaje de plagio detectado por API externa.',
                        'percent_copied' => $deepPlagiarismReport['percent_copied'],
                        'sources' => $deepPlagiarismReport['sources'],
                    ];
                    $validationReport['final_decision'] = 'necesita_correccion';
                    $validationReport['reason'] = $validationReport['external_validation']['deep_plagiarism']['reason'];
                    $this->updateQuestionStatusAndReport($validationReport); // Correcto uso de $this->
                    return; 
                }
                $validationReport['external_validation']['deep_plagiarism'] = ['status' => 'passed', 'report' => $deepPlagiarismReport];
            }


            // --- Etapa 3: Decisión y Actualización de Estado ---
            $validationReport['final_decision'] = 'revisado_por_ai'; 
            $validationReport['reason'] = 'La pregunta pasó todas las validaciones automáticas.';
            $this->updateQuestionStatusAndReport($validationReport); // Correcto uso de $this->

        } catch (\Exception $e) {
            $validationReport['final_decision'] = 'error_validacion_ai';
            $validationReport['reason'] = 'Error técnico durante la validación AI: ' . $e->getMessage();
            $validationReport['errors'][] = $e->getMessage();
            $this->updateQuestionStatusAndReport($validationReport); // Correcto uso de $this->
            Log::error("Error crítico en ValidateQuestionJob para pregunta ID: {$this->question->id}. Error: " . $e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * Actualiza el estado de la pregunta y su reporte de validación.
     */
    protected function updateQuestionStatusAndReport(array $report): void
    {
        $this->question->status = $report['final_decision'];
        $this->question->validation_report = $report;
        $this->question->save();
        Log::info("Pregunta ID: {$this->question->id} actualizada a estado: {$report['final_decision']}", ['report' => $report]);

        // Notificar al autor sobre el resultado (descomentar si la notificación existe)
        // if ($this->question->author) {
        //     $this->question->author->notify(new QuestionValidationResult($this->question, $report));
        // }
    }

    /**
     * Extrae frases clave del stem para la búsqueda web.
     * Mejora esta lógica según sea necesario para extraer frases más relevantes.
     *
     * @param string $stem
     * @return array
     */
    protected function extractKeyPhrases(string $stem): array
    {
        // Dividir el stem en frases, limitando a 3 para evitar stems muy largos.
        $sentences = preg_split('/(?<=[.?!])\s+(?=[a-z])/i', $stem, 3, PREG_SPLIT_NO_EMPTY);
        
        $phrases = [];
        foreach ($sentences as $sentence) {
            $trimmedSentence = trim($sentence);
            // Considera frases de al menos 20 caracteres para ser significativas en la búsqueda.
            if (Str::length($trimmedSentence) > 20) { 
                $phrases[] = $trimmedSentence;
            }
        }
        
        // Si no se extrajeron frases, y el stem es lo suficientemente largo, usa el stem completo como frase.
        if (empty($phrases) && Str::length($stem) > 20) {
            $phrases[] = $stem;
        }
        // Si el stem es muy corto, aún no es lo suficientemente significativo para el plagio
        if (empty($phrases) && Str::length($stem) > 5) {
            // Tomar un segmento inicial si el stem es corto pero no vacío.
            $phrases[] = Str::limit($stem, 50, ''); 
        }

        return $phrases;
    }
}