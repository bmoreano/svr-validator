<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt; // Importamos el Facade de Encriptación

class EncryptExistingQuestions extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'app:encrypt-existing-questions';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Encrypts existing plain text data in the questions and options tables.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting encryption of existing questions and options...');
        
        DB::transaction(function () {
            
            // --- Encriptar Preguntas ---
            $this->comment('Processing questions table...');
            // Obtenemos los datos crudos usando el Query Builder
            $questions = DB::table('questions')->get();
            $questionBar = $this->output->createProgressBar($questions->count());
            
            foreach ($questions as $questionData) {
                // Verificamos si el dato ya está encriptado para no volver a encriptarlo
                if (!$this->isEncrypted($questionData->stem)) {
                    DB::table('questions')->where('id', $questionData->id)->update([
                        'stem' => Crypt::encryptString($questionData->stem),
                        'bibliography' => Crypt::encryptString($questionData->bibliography),
                    ]);
                }
                $questionBar->advance();
            }
            $questionBar->finish();
            $this->info("\nQuestions processed.");

            
            // --- Encriptar Opciones ---
            $this->comment('Processing options table...');
            $options = DB::table('options')->get();
            $optionBar = $this->output->createProgressBar($options->count());

            foreach ($options as $optionData) {
                if (!$this->isEncrypted($optionData->option_text)) {
                    DB::table('options')->where('id', $optionData->id)->update([
                        'option_text' => Crypt::encryptString($optionData->option_text),
                        // La argumentación puede ser nula, así que lo comprobamos
                        'argumentation' => $optionData->argumentation ? Crypt::encryptString($optionData->argumentation) : null,
                    ]);

                }
                $optionBar->advance();
            }
            $optionBar->finish();
            $this->info("\nOptions processed.");

        });

        $this->info("\nEncryption process completed successfully!");
        return 0;
    }

    /**
     * Una función simple para verificar si una cadena parece estar encriptada por Laravel.
     * Las cadenas encriptadas de Laravel son strings largos codificados en base64.
     */
    private function isEncrypted($value): bool
    {
        if (is_null($value)) return true; // Los nulos no necesitan encriptación
        
        // Intentamos decodificar. Si falla, no es base64 válido. Si es muy corto, tampoco.
        try {
            return base64_decode($value, true) !== false && strlen($value) > 50;
        } catch (\Exception $e) {
            return false;
        }
    }
}