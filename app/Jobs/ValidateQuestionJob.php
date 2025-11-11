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
use Illuminate\Support\Facades\Crypt; 

class ValidateQuestionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Question $question;

    /**
     * Create a new job instance.
     */
    public function __construct(Question $question)
    {
        //$this->question = $question;
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
            'internal_validation' => [
                'exact_duplicate' => ['status' => 'not_run'],
                'semantic_similarity' => ['status' => 'not_run'],
                'lexical_fallback_similarity' => ['status' => 'not_run'], // Nuevo campo para el fallback
            ],
            'external_validation' => [
                'web_search' => ['status' => 'not_run'],
                'deep_plagiarism' => ['status' => 'not_run'],
            ],
            'final_decision' => null,
            'reason' => null,
            'errors' => [],
        ];
        $needsCorrection = false; // Bandera para rastrear si se necesita corrección

        try {
            // --- Etapa 1: Validación Interna (Detección de Repetición) ---

            // Fase 1.1: Chequeo de Duplicado Exacto
            $stemToCheck = $this->question->getRawOriginal('stem'); 
            $exactDuplicate = Question::where('stem', $stemToCheck) 
                                    ->where('id', '!=', $this->question->id) 
                                    ->first();
            if ($exactDuplicate) {
                $validationReport['internal_validation']['exact_duplicate'] = [
                    'status' => 'failed',
                    'reason' => 'Duplicado exacto del enunciado de la pregunta ID #' . $exactDuplicate->id,
                    'question_id' => $exactDuplicate->id,
                ];
                $needsCorrection = true;
                $validationReport['reason'] = $validationReport['internal_validation']['exact_duplicate']['reason'];
            } else {
                $validationReport['internal_validation']['exact_duplicate'] = ['status' => 'passed'];
            }

            if ($needsCorrection) { // Detener si ya se encontró un duplicado exacto
                $validationReport['final_decision'] = 'necesita_correccion';
                $this->updateQuestionStatusAndReport($validationReport);
                return;
            }

            // Fase 1.2: Chequeo de Similitud Semántica (con fallback léxico)
            if ($embeddingService->isAvailable()) {
                $newQuestionEmbedding = $embeddingService->generateEmbedding($this->question->stem);

                if (!$newQuestionEmbedding) {
                    $validationReport['errors'][] = 'Fallo al generar embedding para la nueva pregunta.';
                    throw new \Exception('No se pudo generar embedding para la pregunta.'); // Error crítico si el servicio está disponible pero falla
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
                            $needsCorrection = true;
                            $validationReport['reason'] = $validationReport['internal_validation']['semantic_similarity']['reason'];
                            break;
                        }
                    }
                }
                if (!$similarQuestionFound) {
                    $validationReport['internal_validation']['semantic_similarity'] = ['status' => 'passed'];
                }

            } else {
                // COMENTARIO: Fallback para similitud léxica si el servicio de embeddings no está disponible.
                Log::info("Servicio de Embeddings no disponible. Usando fallback de similitud léxica para pregunta ID: {$this->question->id}");
                $lexicalSimilarQuestionFound = false;
                $lexicalSimilarityThreshold = config('validation.lexical_similarity_threshold', 0.7); // Umbral configurable para Jaccard
                
                $allQuestionsStems = Question::where('id', '!=', $this->question->id)
                                             ->get(['id', 'stem']); // Obtener solo el ID y el stem (decriptado por accessor)

                foreach ($allQuestionsStems as $comparisonQuestion) {
                    $similarity = EmbeddingService::lexicalSimilarity($this->question->stem, $comparisonQuestion->stem);
                    if ($similarity > $lexicalSimilarityThreshold) {
                        $validationReport['internal_validation']['lexical_fallback_similarity'] = [
                            'status' => 'failed',
                            'reason' => 'Léxicamente muy similar a la pregunta ID #' . $comparisonQuestion->id . ' (fallback)',
                            'question_id' => $comparisonQuestion->id,
                            'similarity_score' => round($similarity, 4),
                            'threshold' => $lexicalSimilarityThreshold,
                        ];
                        $lexicalSimilarQuestionFound = true;
                        $needsCorrection = true;
                        $validationReport['reason'] = $validationReport['internal_validation']['lexical_fallback_similarity']['reason'];
                        break;
                    }
                }
                if (!$lexicalSimilarQuestionFound) {
                    $validationReport['internal_validation']['lexical_fallback_similarity'] = ['status' => 'passed'];
                }
                $validationReport['internal_validation']['semantic_similarity'] = ['status' => 'skipped_no_api', 'reason' => 'Servicio de Embeddings no disponible.'];
            }

            if ($needsCorrection) { // Detener si se encontró una similitud interna (semántica o léxica)
                $validationReport['final_decision'] = 'necesita_correccion';
                $this->updateQuestionStatusAndReport($validationReport);
                return;
            }

            // --- Etapa 2: Validación Externa (Detección de Plagio) ---
            
            // Fase 2.1: Búsqueda Web Dirigida
            if ($plagiarismService->isGoogleSearchAvailable()) {
                $phrases = $this->extractKeyPhrases($this->question->stem);
                $plagiarizedUrls = $plagiarismService->searchWebForPhrases($phrases);

                if (!empty($plagiarizedUrls)) {
                    $validationReport['external_validation']['web_search'] = [
                        'status' => 'failed',
                        'reason' => 'Posibles fuentes de plagio detectadas en búsqueda web.',
                        'urls' => $plagiarizedUrls,
                    ];
                    $needsCorrection = true;
                    $validationReport['reason'] = $validationReport['external_validation']['web_search']['reason'];
                } else {
                    $validationReport['external_validation']['web_search'] = ['status' => 'passed'];
                }
            } else {
                Log::info("Servicio de Google Search no disponible. Omitiendo búsqueda web dirigida para pregunta ID: {$this->question->id}");
                $validationReport['external_validation']['web_search'] = ['status' => 'skipped_no_api', 'reason' => 'API de Google Search no configurada.'];
            }
            
            if ($needsCorrection) { // Detener si se encontró plagio en búsqueda web
                $validationReport['final_decision'] = 'necesita_correccion';
                $this->updateQuestionStatusAndReport($validationReport);
                return;
            }

            // Fase 2.2: Análisis Profundo con API de Plagio (opcional/condicional)
            $deepPlagiarismThreshold = config('validation.deep_plagiarism_threshold', 0.15); // ej. 15%
            if ($deepPlagiarismThreshold > 0 && $plagiarismService->isCopyscapeAvailable()) { 
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
                    $needsCorrection = true;
                    $validationReport['reason'] = $validationReport['external_validation']['deep_plagiarism']['reason'];
                } else {
                    $validationReport['external_validation']['deep_plagiarism'] = ['status' => 'passed', 'report' => $deepPlagiarismReport];
                }
            } else {
                Log::info("Análisis profundo de plagio deshabilitado/no configurado para pregunta ID: {$this->question->id}");
                $validationReport['external_validation']['deep_plagiarism'] = ['status' => 'skipped_no_api', 'reason' => 'Umbral configurado a 0 o API de Copyscape no configurada.'];
            }

            // --- Etapa 3: Decisión Final y Actualización de Estado ---
            if ($needsCorrection) {
                $validationReport['final_decision'] = 'necesita_correccion';
            } else {
                $validationReport['final_decision'] = 'revisado_por_ai'; 
                $validationReport['reason'] = 'La pregunta pasó todas las validaciones automáticas configuradas.';
            }
            $this->updateQuestionStatusAndReport($validationReport);

        } catch (\Exception $e) {
            $validationReport['final_decision'] = 'error_validacion_ai';
            $validationReport['reason'] = 'Error técnico durante la validación AI: ' . $e->getMessage();
            $validationReport['errors'][] = $e->getMessage();
            $this->updateQuestionStatusAndReport($validationReport); 
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
        if ($this->question->author) {
            // Asegurarse de que el autor tenga el trait Notifiable
            $this->question->author->notify(new QuestionValidationResult($this->question, $report));
        }
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
        $sentences = preg_split('/(?<=[.?!])\s+(?=[a-z])/i', $stem, 3, PREG_SPLIT_NO_EMPTY);
        
        $phrases = [];
        foreach ($sentences as $sentence) {
            $trimmedSentence = trim($sentence);
            if (Str::length($trimmedSentence) > 20) { 
                $phrases[] = $trimmedSentence;
            }
        }
        
        if (empty($phrases) && Str::length($stem) > 20) {
            $phrases[] = $stem;
        }
        if (empty($phrases) && Str::length($stem) > 5) {
            $phrases[] = Str::limit($stem, 50, ''); 
        }

        return $phrases;
    }
}