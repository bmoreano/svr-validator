<?php

namespace App\Jobs;

use App\Models\Question;
use App\Models\User;
use App\Notifications\BatchValidationComplete;
use App\Notifications\BatchValidationFailed;
use App\Notifications\AiValidationFailed;
use App\Jobs\ValidateQuestionWithChatGpt;
use App\Jobs\ValidateQuestionWithGemini;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader; // Usamos League/Csv, que está en tu composer.json

class ProcessBatchFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public string $filePath;
    public ?int $userId;
    public string $aiEngineName;
    public ?int $prompt_id; // Asumimos que quieres usar un prompt_id específico

    /**
     * Create a new job instance.
     *
     * @param string $filePath
     * @param int|null $userId
     * @param string $aiEngineName
     * @param int|null $prompt_id (Opcional, si lo pasas desde el controlador)
     */
    public function __construct(string $filePath, ?int $userId, string $aiEngineName, ?int $prompt_id = null)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->aiEngineName = $aiEngineName;
        $this->prompt_id = $prompt_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            Log::error("Usuario {$this->userId} no encontrado para el lote {$this->filePath}.");
            return;
        }

        try {
            $path = Storage::path($this->filePath);
            $csv = Reader::createFromPath($path, 'r');
            
            $csv->setHeaderOffset(0); // Asume que la primera fila es el encabezado

            // Especificar que el delimitador es un tabulador (\t)
            $csv->setDelimiter("\t"); 
            
            $jobs = [];
            
            // Iteramos sobre los registros del CSV
            foreach ($csv->getRecords() as $record) {
                // 1. Crear la Pregunta (Reactivo)
                // (Ajusta los nombres de las columnas si es necesario)
                $question = Question::create([
                    'author_id' => $this->userId,
                    'status' => 'en_validacion_ai', // Estado inicial
                    'stem' => $record['stem'],
                    'bibliography' => $record['bibliography'] ?? null,
                    // 'career_id' => $record['career_id'] ?? null, // Ejemplo
                ]);

                // 2. Crear Opciones (Ajusta según tu CSV)
                $question->options()->create([
                    'text' => $record['opcion_a'],
                    'is_correct' => ($record['correcta'] ?? '') == 'A',
                ]);
                $question->options()->create([
                    'text' => $record['opcion_b'],
                    'is_correct' => ($record['correcta'] ?? '') == 'B',
                ]);
                $question->options()->create([
                    'text' => $record['opcion_c'],
                    'is_correct' => ($record['correcta'] ?? '') == 'C',
                ]);
                $question->options()->create([
                    'text' => $record['opcion_d'],
                    'is_correct' => ($record['correcta'] ?? '') == 'D',
                ]);

                // 3. Añadir el job de validación al array
                if ($this->aiEngineName === 'chatgpt') {
                    $jobs[] = new ValidateQuestionWithChatGpt($question->id, $this->prompt_id);
                } elseif ($this->aiEngineName === 'gemini') {
                    $jobs[] = new ValidateQuestionWithGemini($question->id, $this->prompt_id);
                }
            }

            if (empty($jobs)) {
                Log::warning("No se generaron jobs de validación para el archivo {$this->filePath}.");
                $user->notify(new AiValidationFailed(null, 'Sistema', 'El archivo de lote estaba vacío o no pudo ser procesado.'));
                Storage::delete($this->filePath); // Limpiar
                return;
            }

            // 4. Despachar el Lote (Batch)
            $batch = Bus::batch($jobs)
                ->then(function (Batch $batch) use ($user) {
                    // Éxito: Todo el lote terminó sin fallos.
                    $user->notify(new BatchValidationComplete($batch));
                })
                ->catch(function (Batch $batch, \Throwable $e) use ($user) {
                    // Fallo: Al menos un job falló permanentemente.
                    $user->notify(new BatchValidationFailed($batch, $e));
                })
                ->finally(function (Batch $batch) {
                    // Limpieza: Se ejecuta siempre, al final.
                    Storage::delete($this->filePath);
                    Log::info("Lote {$batch->id} finalizado, archivo {$this->filePath} eliminado.");
                })
                ->name("Validación Lote: {$this->filePath}") // Nombre para Horizon/Telescope
                ->allowFailures() // Permite que el lote termine incluso si algunos jobs fallan
                ->dispatch();

        } catch (\Exception $e) {
            Log::error("Error procesando el archivo de lote {$this->filePath}: " . $e->getMessage());
            $user->notify(new AiValidationFailed(null, 'Sistema', 'El archivo de lote no pudo ser procesado: ' . $e->getMessage()));
            // Asegurarse de borrar el archivo si hay un error de parseo
            Storage::delete($this->filePath);
            
            $this->fail($e); // Marcar el job principal como fallido
        }
    }
}