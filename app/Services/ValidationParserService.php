<?php

namespace App\Services;

use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Class ValidationParserService
 *
 * Responsabilidad: Recibir la respuesta en texto plano de una API de IA,
 * limpiarla, intentar repararla si es necesario, parsearla, validarla,
 * y guardar los datos de validación estructurados en la base de datos.
 */
class ValidationParserService
{
    /**
     * Parsea una respuesta JSON de un validador AI y la guarda en la base de datos.
     *
     * @param Question $question La pregunta que se está validando.
     * @param string $jsonResponse La cadena JSON (o similar) recibida de la API.
     * @param string $engineName El nombre del motor de IA (ej. 'chatgpt', 'gemini').
     * @return bool True si se guardó con éxito, False en caso de error.
     */
    public function saveAiValidation(Question $question, string $jsonResponse, string $engineName): bool
    {
        try {
            $cleanedJson = $this->cleanJsonResponse($jsonResponse);
            if (empty($cleanedJson)) { throw new \InvalidArgumentException('Respuesta de IA vacía o sin JSON.'); }

            $data = json_decode($cleanedJson, true);
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                $repairedJson = $this->repairJson($cleanedJson);
                $data = json_decode($repairedJson, true, 512, JSON_THROW_ON_ERROR);
            }
            
            $responses = $data['evaluacion'] ?? $data['criterios'] ?? $data;
            if (is_array($responses) && !empty($responses) && $this->is_assoc($responses)) {
                $responses = [$responses];
            }
            if (!is_array($responses)) {
                throw new \InvalidArgumentException('La respuesta JSON no es un array iterable.');
            }

            $aiValidator = User::firstOrCreate(
                ['email' => 'ai@svr.com'],
                ['name' => 'AI Validator', 'password' => Hash::make(str()->random(20)), 'role' => 'validador']
            );
            logger()->info('question', [$question->id, $aiValidator->id, $aiValidator->name,$responses,$engineName]);
            DB::transaction(function () use ($question, $aiValidator, $responses, $engineName) {
                $validation = $question->validations()->create([
                    'validator_id' => $aiValidator->id,
                    'status' => 'completado',
                    'ai_engine' => $engineName, // Esta línea asegura que se guarde el motor de IA.
                ]);

                foreach ($responses as $responseItem) {
                    if (!is_array($responseItem)) {
                        Log::warning("Item inválido omitido.", ['item' => $responseItem]);
                        continue;
                    }
                    $mappedData = $this->mapResponseItem($responseItem);
                    if (is_null($mappedData['criterion_id']) || is_null($mappedData['response'])) {
                        throw new \InvalidArgumentException('Objeto de respuesta no contiene claves esperadas. Objeto: ' . json_encode($responseItem));
                    }
                    $validation->responses()->create($mappedData);
                }
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Error al guardar validación AI para pregunta {$question->id}: " . $e->getMessage(), [
                'original_json_response' => $jsonResponse,
            ]);
            return false;
        }
    }

    /**
     * Limpia una cadena JSON que podría estar envuelta en ```json ... ``` u otro texto.
     * Es público para poder ser reutilizado por otros servicios (ej. MetaValidatePromptJob).
     */
    public function cleanJsonResponse(string $response): string
    {
        $startPos = strpos($response, '[');
        if ($startPos === false) $startPos = strpos($response, '{');
        if ($startPos === false) return '';

        $endPos = strrpos($response, ']');
        if ($endPos === false) $endPos = strrpos($response, '}');
        if ($endPos === false) return '';
        
        return substr($response, $startPos, $endPos - $startPos + 1);
    }
    
    /**
     * Intenta reparar errores de sintaxis comunes en una cadena JSON.
     */
    private function repairJson(string $jsonString): string
    {
        // Elimina comas finales (trailing commas) de objetos y arrays.
        $jsonString = preg_replace('/,\s*([\}\]])/', '$1', $jsonString);
        // Añade comas faltantes entre objetos '}' y '"'.
        $jsonString = preg_replace('/}\s*"/i', '},"', $jsonString);
        return $jsonString;
    }

    /**
     * Mapea un único objeto de respuesta de la IA al formato de nuestra base de datos.
     */
    private function mapResponseItem(array $item): array
    {
        $itemLowercase = array_change_key_case($item, CASE_LOWER);
        return [
            'criterion_id' => $itemLowercase['criterion_id'] ?? $itemLowercase['id'] ?? null,
            'response'     => $this->normalizeResponseValue($itemLowercase['response'] ?? $itemLowercase['cumple'] ?? $itemLowercase['valor'] ?? $itemLowercase['cumplido'] ?? null),
            'comment'      => $itemLowercase['comment'] ?? $itemLowercase['justificacion'] ?? $itemLowercase['descripcion'] ?? null,
        ];
    }

    /**
     * Normaliza los valores de respuesta de la IA a nuestro ENUM.
     */
    private function normalizeResponseValue(mixed $value): ?string
    {
        if ($value === null) return null;
        if (is_bool($value)) return $value ? 'si' : 'no';
        
        $lowerValue = strtolower(trim((string)$value));
        switch ($lowerValue) {
            case 'cumple': case 'si': case 'yes': return 'si';
            case 'no cumple': case 'no': return 'no';
            case 'adecuar': case 'parcialmente cumple': case 'no aplica': return 'adecuar';
            default: return 'adecuar';
        }
    }

    /**
     * Comprueba si un array es asociativo (un objeto) en lugar de una lista.
     */
    private function is_assoc(array $arr): bool
    {
        if ([] === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}