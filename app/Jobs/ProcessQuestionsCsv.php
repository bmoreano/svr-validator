<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage; // Asumiendo que se usa para leer/eliminar el archivo
use Illuminate\Support\Facades\Log;     // Para logging

class ProcessQuestionsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param string $filePath The path to the CSV file in storage.
     * @param int $userId The ID of the user who uploaded the file.
     * @return void
     */
    public function __construct(
        // COMENTARIO: Usando la promoción de propiedades en el constructor (PHP 8+).
        // Esto declara las propiedades públicas y las inicializa automáticamente
        // cuando el Job es instanciado, resolviendo el error de "no inicializado".
        public string $filePath, 
        public int $userId
    ) {
        // No se necesita código adicional aquí para la asignación de propiedades
        // debido a la promoción de propiedades.
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // COMENTARIO: Implementa aquí la lógica para procesar el archivo CSV.
        // Ahora $this->filePath y $this->userId están garantizados para estar inicializados.
        try {
            if (Storage::exists($this->filePath)) {
                $content = Storage::get($this->filePath);
                Log::info("Procesando archivo CSV '{$this->filePath}' para el usuario ID: {$this->userId}");
                
                // Aquí iría tu lógica para parsear $content y crear/actualizar preguntas.
                // Ejemplo:
                // $lines = array_map('str_getcsv', explode(PHP_EOL, $content));
                // $header = array_shift($lines); // Si hay cabecera
                // foreach ($lines as $lineData) {
                //     // procesar cada línea de datos
                // }

                // Después de procesar, puedes eliminar el archivo si ya no es necesario
                Storage::delete($this->filePath);
                Log::info("Archivo CSV '{$this->filePath}' procesado y eliminado exitosamente.");
            } else {
                Log::error("El archivo CSV '{$this->filePath}' no se encontró para el procesamiento del Job.");
            }
        } catch (\Exception $e) {
            Log::error("Error al procesar el archivo CSV '{$this->filePath}': " . $e->getMessage());
            // Puedes relanzar la excepción si quieres que el job falle y se reintente
            // throw $e;
        }
    }
}