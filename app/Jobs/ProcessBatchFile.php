<?php

namespace App\Jobs;

use App\Models\Question;
use App\Models\User;
use App\Notifications\BatchValidationComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// =======================================================
// ==      IMPORTACIONES CLAVE PARA LEER EL CSV         ==
// =======================================================
use League\Csv\Reader;
use League\Csv\Statement;

class ProcessBatchFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * El número máximo de segundos que este job puede ejecutarse.
     * @var int
     */
    public $timeout = 1800; // 30 minutos

    /**
     * El número de veces que el job se reintentará si falla.
     * @var int
     */
    public $tries = 3;

    /**
     * Crea una nueva instancia del job.
     *
     * @param string $filePath La ruta relativa al archivo dentro de storage/app.
     * @param int $userId El ID del usuario que subió el archivo.
     * @param string $aiEngine El motor de IA elegido ('chatgpt' o 'gemini').
     */
    public function __construct(
        public string $filePath,
        public int $userId,
        public string $aiEngine
    ) {
        // Asigna este job a una cola específica para procesos largos.
        $this->onQueue('batch-processing');
    }

    /**
     * Ejecuta el job principal de procesamiento de lotes.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        $user = User::find($this->userId);

        // Comprobación de seguridad inicial.
        if (!$user || !Storage::exists($this->filePath)) {
            Log::error("Procesamiento de lote cancelado: Usuario #{$this->userId} o archivo {$this->filePath} no encontrado.");
            // Si el archivo no existe, lo eliminamos de la cola para no reintentar.
            if (!Storage::exists($this->filePath)) {
                $this->delete();
            }
            return;
        }

        try {
            // Leer el archivo CSV desde la ruta de almacenamiento de Laravel.
            $csv = Reader::createFromPath(Storage::path($this->filePath), 'r');
            $csv->setHeaderOffset(0); // Indicamos que la primera fila es el encabezado.

            $records = Statement::create()->process($csv);

            foreach ($records as $offset => $record) {
                // Validar cada fila del CSV.
                $validator = Validator::make($record, [
                    'enunciado' => 'required|string|min:10',
                    'opcion1' => 'required|string',
                    'opcion2' => 'required|string',
                    'opcion3' => 'required|string',
                    'opcion4' => 'required|string',
                    'respuesta_correcta_idx' => 'required|integer|in:0,1,2,3',
                    'bibliografia' => 'required|string',
                ]);

                if ($validator->fails()) {
                    Log::warning("Fila de CSV #" . ($offset + 1) . " inválida y omitida: " . $validator->errors()->first(), [
                        'file' => $this->filePath,
                        'user_id' => $this->userId,
                    ]);
                    continue; // Saltar esta fila y continuar con la siguiente.
                }

                // Crear la pregunta y sus opciones dentro de una transacción.
                $question = null;
                DB::transaction(function () use ($record, $user, &$question) {
                    $question = $user->questions()->create([
                        'stem' => $record['enunciado'],
                        'bibliography' => $record['bibliografia'],
                        'status' => 'borrador',
                    ]);

                    $options = [$record['opcion1'], $record['opcion2'], $record['opcion3'], $record['opcion4']];
                    foreach ($options as $index => $optionText) {
                        $question->options()->create([
                            'option_text' => $optionText,
                            'is_correct' => ($index == $record['respuesta_correcta_idx']),
                            // Se podría añadir una argumentación por defecto o dejarla nula.
                            'argumentation' => 'Argumentación generada a partir de subida por lote.',
                        ]);
                    }
                });

                // Si la pregunta se creó correctamente, se despacha el job de validación.
                               if ($question) {
                    $question->update(['status' => 'en_validacion_ai']);
                    
                    // =======================================================
                    // ==             LÓGICA DE SELECCIÓN DE JOB            ==
                    // =======================================================
                    if ($this->aiEngine === 'chatgpt') {
                        ValidateQuestionWithChatGpt::dispatch($question)->onQueue('validations');
                    } else {
                        // Si no es chatgpt, por defecto es gemini (ya validado en el controlador)
                        ValidateQuestionWithGemini::dispatch($question)->onQueue('validations');
                    }
                }
            }

        } finally {
            // Este bloque se ejecuta siempre, haya o no errores, garantizando la limpieza.
            Storage::delete($this->filePath);
            
            // Notificar al usuario que el proceso ha terminado.
            if ($user) {
                // Aquí necesitarías crear la notificación 'BatchValidationComplete'
                $user->notify(new BatchValidationComplete());
            }
            // =============================================
            // ==      ENVÍO DE LA NOTIFICACIÓN FINAL       ==
            // =============================================
            if ($user) {
                // Notificamos al usuario que el proceso de subida y despacho ha terminado.
                $user->notify(new BatchValidationComplete());
            }

            Log::info("Procesamiento por lotes del archivo {$this->filePath} para el usuario #{$this->userId} ha finalizado.");
        }
    }
}