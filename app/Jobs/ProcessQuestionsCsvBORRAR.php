<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Question; // Asumiendo que existe este modelo
use App\Models\Option; // Asumiendo que existe este modelo
use App\Services\QuestionCodeGeneratorService;
use App\Services\QuestionVersioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use League\Csv\Reader;

class ProcessQuestionsCsvBORRAR implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $userId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(QuestionCodeGeneratorService $codeGenerator, QuestionVersioningService $versioningService): void
    {
        logger()->info("[Paso 1] Iniciando validación de ruta segura para el archivo: {$this->filePath}", ['user_id' => $this->userId]);
        // Validar ruta segura para evitar directory traversal
        if (!Str::startsWith($this->filePath, 'uploads/temp/')) {
            Log::error("[Paso 1 - Error] Ruta de archivo inválida: {$this->filePath}", ['user_id' => $this->userId]);
            return;
        }
        logger()->info("[Paso 1] Ruta segura validada exitosamente.");

        logger()->info("[Paso 2] Verificando existencia y tipo de archivo.");
        // Verificar existencia y tipo de archivo
        if (!Storage::exists($this->filePath)) {
            Log::error("[Paso 2 - Error] Archivo CSV de preguntas no encontrado en {$this->filePath}", ['user_id' => $this->userId]);
            return;
        }

        // Validar MIME type y tamaño (máximo 10MB)
        if (Storage::mimeType($this->filePath) !== 'text/csv' || Storage::size($this->filePath) > 10 * 1024 * 1024) {
            Log::error("[Paso 2 - Error] Archivo inválido: no es CSV o excede el tamaño máximo en {$this->filePath}", ['user_id' => $this->userId]);
            Storage::delete($this->filePath);
            return;
        }
        logger()->info("[Paso 2] Archivo validado exitosamente: MIME text/csv, tamaño aceptable.");

        logger()->info("[Paso 3] Buscando usuario autor con ID: {$this->userId}.");
        $author = User::find($this->userId);
        if (!$author) {
            Log::error("[Paso 3 - Error] Usuario autor {$this->userId} no encontrado para procesar CSV.");
            Storage::delete($this->filePath);
            return;
        }
        logger()->info("[Paso 3] Usuario autor encontrado: " . ($author->name ?? 'ID ' . $author->id) . ".");
        try {
            logger()->info("[Paso 4] Iniciando lectura del CSV desde path: " . Storage::path($this->filePath));
            // Usar Reader::createFromPath para streaming y optimización de memoria
            $csv = Reader::createFromPath(Storage::path($this->filePath), 'r');
            $csv->setHeaderOffset(0);
            $records = iterator_to_array($csv->getRecords()); // Convertir a array para batches
            logger()->info("[Paso 4] CSV leído exitosamente. Total de registros: " . count($records));

            $processedCount = 0;
            $skippedCount = 0;
            $batchSize = 100; // Tamaño de batch para optimización
            $questionsBatch = [];
            $optionsBatch = [];

            foreach ($records as $offset => $record) {
                $lineNumber = $offset + 2; // +1 header, +1 base 0
                logger()->info("[Paso 5 - Fila {$lineNumber}] Iniciando validación de la fila.");

                // Validación con reglas mejoradas (agregar max length, enums si aplica)
                $validator = Validator::make($record, [
                    'stem' => 'required|string|min:10|max:2000',
                    'bibliography' => 'required|string|max:1000',
                    'grado_dificultad' => ['required', Rule::in(['muy_facil', 'facil', 'dificil', 'muy_dificil'])],
                    'poder_discriminacion' => ['required', Rule::in(['muy_alto', 'alto', 'moderado', 'bajo', 'muy_bajo'])],
                    'opcion_1' => 'required|string|max:1000',
                    'opcion_2' => 'required|string|max:1000',
                    'opcion_3' => 'required|string|max:1000',
                    'opcion_4' => 'required|string|max:1000',
                    'respuesta_correcta' => 'required|integer|in:1,2,3,4',
                    'argumentacion_1' => 'nullable|string|max:2000',
                    'argumentacion_2' => 'nullable|string|max:2000',
                    'argumentacion_3' => 'nullable|string|max:2000',
                    'argumentacion_4' => 'nullable|string|max:2000',
                ]);

                if ($validator->fails()) {
                    Log::warning("[Paso 5 - Fila {$lineNumber} - Error] Fila omitida por errores de validación.", [
                        'file' => $this->filePath,
                        'errors' => $validator->errors()->all(),
                        'user_id' => $this->userId,
                    ]);
                    $skippedCount++;
                    continue;
                }
                logger()->info("[Paso 5 - Fila {$lineNumber}] Validación exitosa.");

                $validated = $validator->validated();
                logger()->info("[Paso 6 - Fila {$lineNumber}] Generando código único para la pregunta.");
                $newCode = $codeGenerator->generateForNewQuestion($author);
                logger()->info("[Paso 6 - Fila {$lineNumber}] Código generado: {$newCode}.");

                // Preparar batch para questions
                $questionsBatch[] = [
                    'code' => $newCode,
                    'stem' => $validated['stem'],
                    'bibliography' => $validated['bibliography'],
                    'grado_dificultad' => $validated['grado_dificultad'],
                    'poder_discriminacion' => $validated['poder_discriminacion'],
                    'status' => 'borrador',
                    'user_id' => $author->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Preparar batch para options (question_code temporal para matching)
                for ($i = 1; $i <= 4; $i++) {
                    $optionsBatch[] = [
                        'question_code' => $newCode, // Temporal
                        'option_text' => $validated["opcion_{$i}"],
                        'is_correct' => ($i == $validated['respuesta_correcta']),
                        'argumentation' => $validated["argumentacion_{$i}"] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                logger()->info("[Paso 6 - Fila {$lineNumber}] Datos de pregunta y opciones preparados para batch.");

                // Si batch lleno, procesar
                if (count($questionsBatch) >= $batchSize) {
                    logger()->info("[Paso 7] Procesando batch de " . count($questionsBatch) . " preguntas.");
                    $this->processBatch($questionsBatch, $optionsBatch, $author, $codeGenerator, $versioningService);
                    $processedCount += count($questionsBatch);
                    logger()->info("[Paso 7] Batch procesado exitosamente.");
                    $questionsBatch = [];
                    $optionsBatch = [];
                }
            }

            // Procesar batch remanente
            if (!empty($questionsBatch)) {
                logger()->info("[Paso 7] Procesando batch remanente de " . count($questionsBatch) . " preguntas.");
                $this->processBatch($questionsBatch, $optionsBatch, $author, $codeGenerator, $versioningService);
                $processedCount += count($questionsBatch);
                logger()->info("[Paso 7] Batch remanente procesado exitosamente.");
            }

            logger()->info("[Paso 8] Proceso completado: {$processedCount} preguntas procesadas y {$skippedCount} omitidas desde {$this->filePath}.", ['user_id' => $this->userId]);

            // Enviar notificación al usuario (asumiendo una notificación configurada)
            // $author->notify(new ImportQuestionsCompleted($processedCount, $skippedCount, $this->filePath));

        } catch (\Exception $e) {
            Log::error("[Paso Error Crítico] Error al procesar el archivo CSV {$this->filePath}: " . $e->getMessage(), [
                'user_id' => $this->userId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Re-lanzar para retry si el Job está configurado
        } finally {
            logger()->info("[Paso Final] Limpiando archivo temporal: {$this->filePath}");
            Storage::delete($this->filePath);
            logger()->info("[Paso Final] Archivo temporal eliminado.");
        }
    }

    /**
     * Procesar un batch de preguntas y opciones en una transacción.
     */
    private function processBatch(array $questionsBatch, array $optionsBatch, User $author, QuestionCodeGeneratorService $codeGenerator, QuestionVersioningService $versioningService): void
    {
        logger()->info("[Paso 7.1] Iniciando transacción DB para batch.");
        DB::transaction(function () use ($questionsBatch, $optionsBatch, $author, $codeGenerator, $versioningService) {
            logger()->info("[Paso 7.2] Insertando batch de preguntas en tabla 'questions'.");
            // Insertar questions en batch
            DB::table('questions')->insert($questionsBatch);
            logger()->info("[Paso 7.2] Preguntas insertadas exitosamente.");

            logger()->info("[Paso 7.3] Obteniendo IDs de preguntas insertadas.");
            // Obtener IDs de las preguntas insertadas por code (asumiendo code único)
            $questionIds = DB::table('questions')
                ->whereIn('code', array_column($questionsBatch, 'code'))
                ->pluck('id', 'code')
                ->toArray();
            logger()->info("[Paso 7.3] IDs obtenidos: " . count($questionIds) . " IDs.");

            logger()->info("[Paso 7.4] Preparando e insertando opciones.");
            // Asignar question_id a options y preparar insert
            $optionsToInsert = [];
            foreach ($optionsBatch as $option) {
                $option['question_id'] = $questionIds[$option['question_code']] ?? null;
                unset($option['question_code']); // Limpiar temporal
                if ($option['question_id'] === null) {
                    Log::error("[Paso 7.4 - Error] Error al asignar question_id para code {$option['question_code']}");
                    throw new \Exception("Error al asignar question_id para code {$option['question_code']}");
                }
                $optionsToInsert[] = $option;
            }

            DB::table('options')->insert($optionsToInsert);
            logger()->info("[Paso 7.4] Opciones insertadas exitosamente: " . count($optionsToInsert) . " opciones.");

            logger()->info("[Paso 7.5] Creando revisiones para las preguntas.");
            // Crear revisiones para cada pregunta
            foreach (array_column($questionsBatch, 'code') as $code) {
                $question = Question::where('code', $code)->first();
                if ($question) {
                    $versioningService->createRevision($question, 'Creación Masiva desde CSV');
                    logger()->info("[Paso 7.5] Revisión creada para pregunta con code: {$code}.");
                } else {
                    Log::warning("[Paso 7.5 - Advertencia] Pregunta con code {$code} no encontrada para revisión.");
                }
            }
            logger()->info("[Paso 7.5] Revisiones completadas.");
        });
        logger()->info("[Paso 7.1] Transacción DB completada exitosamente.");
    }
}