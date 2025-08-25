<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Criterion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class ProcessCriteriaCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $filePath, public int $userId) {}

    public function handle(): void
    {
        if (!Storage::exists($this->filePath)) {
            Log::error("Archivo CSV de criterios no encontrado: {$this->filePath}");
            return;
        }

        try {
            $content = Storage::get($this->filePath);
            $csv = Reader::createFromString($content);
            $csv->setHeaderOffset(0); // Primera fila es la cabecera

            $records = $csv->getRecords();
            $createdCount = 0;

            foreach ($records as $record) {
                // Validamos los campos requeridos del CSV
                if (empty($record['text']) || empty($record['category'])) {
                    Log::warning('Fila de CSV de criterios omitida por datos faltantes.', ['record' => $record]);
                    continue;
                }

                // Usamos updateOrCreate para actualizar criterios existentes o crear nuevos
                // Asumimos que el 'text' del criterio es único
                Criterion::updateOrCreate(
                    ['text' => trim($record['text'])],
                    [
                        'category' => strtolower(trim($record['category'])),
                        'is_active' => true,
                        'sort_order' => $record['sort_order'] ?? 0,
                    ]
                );
                $createdCount++;
            }
            Log::info("{$createdCount} criterios procesados desde el archivo {$this->filePath} por el usuario {$this->userId}.");

        } catch (\Exception $e) {
            Log::error("Error al procesar el archivo CSV de criterios {$this->filePath}: " . $e->getMessage());
        } finally {
            Storage::delete($this->filePath); // Limpiamos el archivo temporal
        }
    }


    
}