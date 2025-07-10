<?php
 
// app/Services/ValidationParserService.php
namespace App\Services;

use App\Models\Question;
use App\Models\User;
use App\Models\Validation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ValidationParserService
{
    public function saveAiValidation(Question $question, string $jsonResponse): bool
    {
        try {
            // Limpiar la respuesta de Gemini (a veces añade ```json)
            $cleanedJson = trim(str_replace(['```json', '```'], '', $jsonResponse));
            $responses = json_decode($cleanedJson, true, 512, JSON_THROW_ON_ERROR);

            $aiValidator = User::where('email', 'ai@svr.com')->firstOrFail();

            DB::transaction(function () use ($question, $aiValidator, $responses) {
                $validation = $question->validations()->create([
                    'validator_id' => $aiValidator->id,
                    'status' => 'completado'
                ]);

                foreach ($responses as $response) {
                    $validation->responses()->create([
                        'criterion_id' => $response['criterion_id'],
                        'response' => $response['response'],
                        'comment' => $response['comment'] ?? null,
                    ]);
                }
            });

            return true;
        } catch (\JsonException $e) {
            Log::error("Error de parseo JSON para pregunta {$question->id}: " . $e->getMessage(), ['response' => $jsonResponse]);
            return false;
        } catch (\Exception $e) {
            Log::error("Error al guardar validación AI para pregunta {$question->id}: " . $e->getMessage());
            return false;
        }
    }
}