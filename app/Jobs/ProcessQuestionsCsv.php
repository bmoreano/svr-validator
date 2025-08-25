<?php

namespace App\Jobs;

use App\Models\Question;
use App\Models\User;
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
use League\Csv\Reader;

class ProcessQuestionsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $filePath, public int $userId) {}

    public function handle(QuestionCodeGeneratorService $codeGenerator, QuestionVersioningService $versioningService): void
    {
        if (!Storage::exists($this->filePath)) {
            Log::error("Archivo CSV de preguntas no encontrado en {$this->filePath}");
            return;
        }

        $author = User::find($this->userId);
        if (!$author) {
            Log::error("Usuario autor {$this->userId} no encontrado para procesar CSV.");
            return;
        }

        try {
            $content = Storage::get($this->filePath);
            $csv = Reader::createFromString($content);
            $csv->setHeaderOffset(0);
            $records = $csv->getRecords();
            $processedCount = 0;

            foreach ($records as $offset => $record) {
                $lineNumber = $offset + 2; // +1 por el header, +1 por el índice base 0

                // 1. Validar la fila usando el validador de Laravel
                $validator = Validator::make($record, [
                    'stem' => 'required|string|min:10',
                    'bibliography' => 'required|string',
                    'grado_dificultad' => 'required|in:muy_facil,facil,dificil,muy_dificil',
                    'poder_discriminacion' => 'required|in:muy_alto,alto,moderado,bajo,muy_bajo',
                    'opcion_1' => 'required|string',
                    'opcion_2' => 'required|string',
                    'opcion_3' => 'required|string',
                    'opcion_4' => 'required|string',
                    'respuesta_correcta' => 'required|integer|in:1,2,3,4',
                    'argumentacion_1' => 'nullable|string',
                    'argumentacion_2' => 'nullable|string',
                    'argumentacion_3' => 'nullable|string',
                    'argumentacion_4' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    Log::warning("Fila #{$lineNumber} del CSV omitida por errores de validación.", [
                        'file' => $this->filePath,
                        'errors' => $validator->errors()->all(),
                    ]);
                    continue; // Saltar a la siguiente fila
                }
                
                $validated = $validator->validated();

                // 2. Crear la pregunta y sus opciones en una transacción
                DB::transaction(function () use ($validated, $author, $codeGenerator, $versioningService) {
                    $newCode = $codeGenerator->generateForNewQuestion($author);
                    
                    $question = $author->questions()->create([
                        'code' => $newCode,
                        'stem' => $validated['stem'],
                        'bibliography' => $validated['bibliography'],
                        'grado_dificultad' => $validated['grado_dificultad'],
                        'poder_discriminacion' => $validated['poder_discriminacion'],
                        'status' => 'borrador',
                        // Asumimos que no se asigna carrera desde el CSV, se puede añadir
                    ]);

                    // Extraer y crear las 4 opciones
                    for ($i = 1; $i <= 4; $i++) {
                        $question->options()->create([
                            'option_text' => $validated["opcion_{$i}"],
                            'is_correct' => ($i == $validated['respuesta_correcta']),
                            'argumentation' => $validated["argumentacion_{$i}"] ?? null,
                        ]);
                    }

                    // Crear la primera revisión
                    $versioningService->createRevision($question->fresh(), 'Creación Masiva desde CSV');
                });
                
                $processedCount++;
            }
            
            Log::info("{$processedCount} preguntas procesadas desde {$this->filePath}.");
            // Aquí se podría enviar una notificación al usuario de que el proceso terminó.

        } catch (\Exception $e) {
            Log::error("Error crítico al procesar el archivo CSV {$this->filePath}: " . $e->getMessage());
        } finally {
            Storage::delete($this->filePath); // Limpiar archivo temporal
        }
    }
}