<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt; 
use Illuminate\Support\Str;          
use App\Models\Question;             
use App\Models\Option;               
use App\Models\Career;               

class ProcessQuestionsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $filePath, 
        public int $userId
    ) {
        // La promoción de propiedades en el constructor ya se encarga de la inicialización.
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info("El archivo CSV '{$this->filePath}' está en ProcessQuestionsCsv->handle (User ID: {$this->userId}).");
        try {
            if (!Storage::exists($this->filePath)) {
                Log::error("El archivo CSV '{$this->filePath}' no se encontró para el procesamiento del Job (User ID: {$this->userId}).");
                return;
            }

            $content = Storage::get($this->filePath);
            //$lines_old = array_map('str_getcsv', explode(PHP_EOL, $content));
            $lines = array_map(function($line) {return str_getcsv($line, ';'); }, explode(PHP_EOL, $content));
            $lines = array_filter($lines, function($line) {
                return is_array($line) && !empty(array_filter($line, fn($field) => $field !== null && $field !== ''));
            });

            if (empty($lines)) {
                Log::warning("El archivo CSV '{$this->filePath}' está vacío después de limpiar líneas (User ID: {$this->userId}).");
                Storage::delete($this->filePath);
                return;
            }

            $header = array_shift($lines); 

            $expectedHeaderMap = [
                'stem'                   => 0,
                'bibliography'           => 1,
                'grado_dificultad'       => 2,
                'poder_discriminacion'   => 3,
                'opcion_1'               => 4,
                'argumentacion_1'        => 5,
                'opcion_2'               => 6,
                'argumentacion_2'        => 7,
                'opcion_3'               => 8,
                'argumentacion_3'        => 9,
                'opcion_4'               => 10,
                'argumentacion_4'        => 11,
                'respuesta_correcta'     => 12,
                'career_id'              => 13,
            ];

            if (count($header) !== count($expectedHeaderMap)) {
                Log::error("Cabecera del archivo CSV '{$this->filePath}' tiene un número de columnas incorrecto. Esperaba " . count($expectedHeaderMap) . ", encontró " . count($header) . ". (User ID: {$this->userId})");
                Storage::delete($this->filePath);
                return;
            }
            $headerNames = array_keys($expectedHeaderMap);
            if (array_diff($headerNames, $header) || array_diff($header, $headerNames)) {
                Log::error("Los nombres de las cabeceras del archivo CSV '{$this->filePath}' no coinciden con las esperadas. Esperado: [" . implode(';', $headerNames) . "]. Recibido: [" . implode(', ', $header) . "]. (User ID: {$this->userId})");
                Storage::delete($this->filePath);
                return;
            }

            Log::info("Iniciando procesamiento de archivo CSV '{$this->filePath}' para el usuario ID: {$this->userId}. Total de líneas de datos a procesar: " . count($lines));
            $processedCount = 0;
            $skippedCount = 0;

            $defaultCareerId = null;
            $defaultCareer = Career::first(); 
            if ($defaultCareer) {
                $defaultCareerId = $defaultCareer->id;
            } else {
                Log::error("No se encontró ninguna carrera por defecto para asignar a las preguntas. La inserción fallará sin un career_id válido.", ['user_id' => $this->userId]);
                Storage::delete($this->filePath);
                return; 
            }

            // Antes del foreach
            Log::info("Iniciando procesamiento de archivo CSV '{$this->filePath}' para el usuario ID: {$this->userId}. Total de líneas de datos a procesar: " . count($lines));
            $processedCount = 0;
            $skippedCount = 0;

            $defaultCareerId = null;
            $defaultCareer = Career::first(); 
            if ($defaultCareer) {
                $defaultCareerId = $defaultCareer->id;
            } else {
                Log::error("No se encontró ninguna carrera por defecto para asignar a las preguntas. La inserción fallará sin un career_id válido.", ['user_id' => $this->userId]);
                Storage::delete($this->filePath);
                return; 
            }
            
            foreach ($lines as $lineNumber => $rowData) {
                $actualLineNumber = $lineNumber + 2; 
                
                if (count($rowData) !== count($expectedHeaderMap)) {
                    Log::warning("Fila #{$actualLineNumber} del CSV omitida: Número de campos incorrecto (esperaba " . count($expectedHeaderMap) . ", encontró " . count($rowData) . ").", [
                        'file' => $this->filePath,
                        'line_data' => $rowData,
                        'user_id' => $this->userId
                    ]);
                    $skippedCount++;
                    continue;
                }

                $dataToValidate = array_combine($header, $rowData);

                $validator = Validator::make($dataToValidate, [
                    'stem' => 'required|string',
                    'bibliography' => 'required|string',
                    'grado_dificultad' => 'required|in:muy_facil,facil,mediana,difícil,muy_dificil',
                    'poder_discriminacion' => 'required|in:muy_alto,alto,moderado,bajo,muy_bajo',
                    'opcion_1' => 'required|string',
                    'argumentacion_1' => 'required|string',
                    'opcion_2' => 'required|string',
                    'argumentacion_2' => 'required|string',
                    'opcion_3' => 'required|string',
                    'argumentacion_3' => 'required|string',
                    'opcion_4' => 'required|string',
                    'argumentacion_4' => 'required|string',
                    'respuesta_correcta' => 'required|integer|min:1|max:4',
                    'career_id' => 'required|integer|min:1',
                ]);

                if ($validator->fails()) {
                    Log::warning("Fila #{$actualLineNumber} del CSV omitida por errores de validación.", [
                        'file' => $this->filePath,
                        'errors' => $validator->errors()->all(),
                        'line_data' => $dataToValidate,
                        'user_id' => $this->userId
                    ]);
                    $skippedCount++;
                    continue;
                }

                try {
                    // COMENTARIO: Construir la representación canónica del contenido para el hash
                    $questionContentData = [
                        'stem' => $dataToValidate['stem'],
                        'bibliography' => $dataToValidate['bibliography'], 
                        'grado_dificultad' => $dataToValidate['grado_dificultad'],
                        'poder_discriminacion' => $dataToValidate['poder_discriminacion'],
                        'correct_answer_index' => $dataToValidate['respuesta_correcta'],
                    ];

                    $optionsForHash = [];
                    for ($i = 1; $i <= 4; $i++) {
                        $optionsForHash[] = [
                            'option_text' => $dataToValidate["opcion_{$i}"],
                            'argumentation' => $dataToValidate["argumentacion_{$i}"],
                            'is_correct' => ($dataToValidate['respuesta_correcta'] == $i),
                        ];
                    }

                    // COMENTARIO: Ordenar las opciones para asegurar que el hash sea consistente
                    // incluso si el orden de las opciones en el CSV varía para la misma pregunta.
                    usort($optionsForHash, function($a, $b) {
                        return strcmp($a['option_text'], $b['option_text']); // Ordenar por texto de opción
                    });

                    $questionContentData['options'] = $optionsForHash;

                    // COMENTARIO: Generar un hash SHA256 del contenido canónico de la pregunta.
                    $contentHash = hash('sha256', json_encode($questionContentData));

                    // COMENTARIO: Verificar si ya existe una pregunta con este hash de contenido.
                    $existingQuestion = Question::where('content_hash', $contentHash)->first();

                    if ($existingQuestion) {
                        Log::warning("Fila #{$actualLineNumber} del CSV omitida: Pregunta duplicada (contenido ya existe con ID: {$existingQuestion->id}).", [
                            'file' => $this->filePath,
                            'line_data' => $dataToValidate,
                            'content_hash' => $contentHash,
                            'user_id' => $this->userId
                        ]);
                        $skippedCount++;
                        continue; // Saltar a la siguiente línea del CSV
                    }

                    // Si no es un duplicado, proceder con la inserción.
                    $questionCode = 'QST-' . Str::uuid(); 
                    $encryptedStem = Crypt::encryptString($dataToValidate['stem']);
                    $encryptedBibliography = Crypt::encryptString($dataToValidate['bibliography']);
                    Log::info("Guardando content_hash '{$contentHash}' de la pregunta '{$questionCode}' para el usuario ID: {$this->userId}. Total de líneas de datos a procesar: " . count($lines));
                    $question = Question::create([
                        'code' => $questionCode,
                        'author_id' => $this->userId,
                        'career_id' => $defaultCareerId, 
                        'assigned_validator_id' => null, 
                        'tema' => null, 
                        'stem' => $encryptedStem,
                        'bibliography' => $encryptedBibliography,
                        'grado_dificultad' => $dataToValidate['grado_dificultad'],
                        'poder_discriminacion' => $dataToValidate['poder_discriminacion'],
                        'status' => 'borrador', 
                        'revision_feedback' => null,
                        'content_hash' => $contentHash, // COMENTARIO: Guardar el hash del contenido
                    ]);

                    for ($i = 1; $i <= 4; $i++) {
                        Option::create([
                            'question_id' => $question->id,
                            'option_text' => Crypt::encryptString($dataToValidate["opcion_{$i}"]),
                            'is_correct' => ($dataToValidate['respuesta_correcta'] == $i),
                            'argumentation' => Crypt::encryptString($dataToValidate["argumentacion_{$i}"]),
                        ]);
                    }
                    
                    Log::info("Fila #{$actualLineNumber} (Pregunta ID: {$question->id}) procesada con éxito.", ['user_id' => $this->userId]);
                    $processedCount++;

                } catch (\Exception $e) {
                    Log::error("Error al crear la pregunta y opciones de la fila #{$actualLineNumber}: " . $e->getMessage(), [
                        'file' => $this->filePath,
                        'line_data' => $dataToValidate,
                        'exception' => $e,
                        'user_id' => $this->userId
                    ]);
                    $skippedCount++;
                }
            }

            Log::info("Procesamiento de archivo CSV '{$this->filePath}' finalizado. Procesadas: {$processedCount}, Omitidas: {$skippedCount}. (User ID: {$this->userId})");
            
        } catch (\Exception $e) {
            Log::error("Error general en el Job ProcessQuestionsCsv para el archivo '{$this->filePath}': " . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $this->userId
            ]);
        } finally {
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
                Log::info("Archivo CSV '{$this->filePath}' eliminado del almacenamiento. (User ID: {$this->userId})");
            }
        }
    }
}