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
     */
    public function saveAiValidation(Question $question, string $jsonResponse, string $engineName): bool
    {
        try {            
            logger()->info('cleanedJson: 26 ');
            $cleanedJson = $this->cleanJsonResponse($jsonResponse);
            if (empty($cleanedJson)) { throw new \InvalidArgumentException('Respuesta de IA vacía.'); }

            $data = json_decode($cleanedJson, true);         
            logger()->info('data: 31 ');

            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                // --- INICIO DE LA CORRECCIÓN ---
                // Se usa $cleanedJson, que es el JSON limpio, no $jsonResponse.
                $repairedJson = $this->repairJson($cleanedJson);  
                // --- FIN DE LA CORRECCIÓN ---
                
                $data = json_decode($repairedJson, true, 512, JSON_THROW_ON_ERROR);       
                logger()->info('repairedJson ->data : 36 ');
            }
            
            $responses = $data['evaluacion'] ?? $data['criterios'] ?? $data;       
            logger()->info('responses ->data : 40 ');
            if (is_array($responses) && !empty($responses) && !is_array(reset($responses))) { $responses = [$responses]; } 
            logger()->info('cast a arreglo si no lo es responses ->data : 42 ');
            if (!is_array($responses)) { throw new \InvalidArgumentException('Respuesta JSON no es un array iterable.'); } 
            logger()->info('Respuesta JSON no es un array iterable. : 44 ');

            $aiValidator = User::firstOrCreate(
                ['email' => 'ai@svr.com'],
                [
                    'name' => 'AI Validator',
                    'password' => Hash::make(str()->random(20)),
                    'role' => 'validador'
                ]
            );
            logger()->info('aiValidator. : 58 ');

            $aux = DB::transaction(function () use ($question, $aiValidator, $responses, $engineName) {
                $validation = $question->validations()->create([
                    'validator_id' => $aiValidator->id,
                    'status' => 'completado',
                    'ai_engine' => $engineName,
                ]);
                logger()->info('aux. : 66 ');

                foreach ($responses as $responseItem) {
                    if (!is_array($responseItem)) { continue; }
                    $mappedData = $this->mapResponseItem($responseItem);
                    if (is_null($mappedData['criterion_id']) || is_null($mappedData['response'])) {
                        throw new \InvalidArgumentException('Objeto de respuesta no contiene claves esperadas.');
                    }
                    $validation->responses()->create($mappedData);
                }
                logger()->info('Objeto de respuesta no contiene claves esperadas.. : 76 ');
            });

            return true;
        } catch (\Exception $e) {
            Log::error("81 Error al guardar validación AI para pregunta {$question->id}: " . $e->getMessage(), [
                'original_json_response' => $jsonResponse,
            ]);
            return false;
        }
    }

    /**
     * Limpia una cadena JSON que podría estar envuelta en ```json ... ```.
     */
    public function cleanJsonResponse(string $response): string
    {
        $startPos = strpos($response, '[');
        if ($startPos === false) $startPos = strpos($response, '{');
        if ($startPos === false) return '';

        $endPos = strrpos($response, ']');
        if ($endPos === false) $endPos = strrpos($response, '}');
        if ($endPos === false) return '';
        logger()->info('response: 91');
        logger($response);
        
        return substr($response, $startPos, $endPos - $startPos + 1);
    }
    
    /**
     * Intenta reparar errores de sintaxis comunes en una cadena JSON.
     */
    private function repairJson(string $jsonString): string
    {
        // Elimina comas finales (trailing commas) de objetos y arrays
        $jsonString = preg_replace('/,\s*([\}\]])/', '$1', $jsonString);
        // Añade comas faltantes entre objetos '}' y '"' (un patrón común de error)
        $jsonString = preg_replace('/}\s*"/i', '},"', $jsonString);
        logger()->info('jsonString: ', $jsonString);
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
            'response'     => $this->normalizeResponseValue(
                $itemLowercase['response'] ?? $itemLowercase['cumple'] ?? $itemLowercase['valor'] ?? $itemLowercase['cumplido'] ?? null
            ),
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