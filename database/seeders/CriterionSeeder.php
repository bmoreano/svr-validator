<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Criterion;

class CriterionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $criteria = [
            // Formulación
            ['category' => 'formulacion', 'text' => 'El enunciado se relaciona con la Definición Operacional y el tema.'],
            ['category' => 'formulacion', 'text' => 'El enunciado es claro y cerrado, sin posibilidad de más de una respuesta.'],
            ['category' => 'formulacion', 'text' => 'No se usan términos imprecisos como "posiblemente" o "podría ser".'],
            ['category' => 'formulacion', 'text' => 'La redacción está en positivo (no se usa "EXCEPTO", "NO", etc.).'],
            ['category' => 'formulacion', 'text' => 'El lenguaje es formal, no coloquial.'],
            ['category' => 'formulacion', 'text' => 'El lenguaje, ejemplos o casos no son ofensivos ni hieren susceptibilidades.'],
            ['category' => 'formulacion', 'text' => 'El cuestionamiento está de acuerdo con el perfil profesional (competencias).'],
            ['category' => 'formulacion', 'text' => 'El contenido de la pregunta está de acuerdo con el perfil de egreso de la carrera (resultados de aprendizaje y malla curricular)'], 
            ['category' => 'formulacion', 'text' => 'La pregunta se relaciona con el Modelo de Atención Integral de Salud (MAIS) (no necesariamente).'],
            ['category' => 'formulacion', 'text' => 'El nivel cognitivo está de acuerdo con el cuestionamiento de la pregunta.'],
            ['category' => 'formulacion', 'text' => 'El nivel de complejidad es el adecuado de la pregunta con relación a la exigencia cognitiva.'],
            ['category' => 'formulacion', 'text' => 'El nivel de complejidad es el adecuado de la pregunta con relación a la exigencia cognitiva.'],
            ['category' => 'formulacion', 'text' => 'La redacción no debe permitir responder por lógica o sentido común.'],
            ['category' => 'formulacion', 'text' => 'La formulaci ó n de la pregunta no debe contener, ni completa ni parcialmente, a la respuesta correcta.'],
            ['category' => 'formulacion', 'text' => 'La orden o la instrucción debe ser un verbo conjugado, no en infinitivo, es decir, el verbo no debe terminar en ar, er, ir.'],
            // Opciones
            ['category' => 'opciones', 'text' => 'La respuesta correcta debe responder completamente y no de forma parcial al cuestionamiento, instrucci ó n u orden planteado en el enunciado.'],
            ['category' => 'opciones', 'text' => 'Debe existir una sola respuesta correcta para cada pregunta..'],
            ['category' => 'opciones', 'text' => 'No deben contener juicios de valor u opiniones.'],
            ['category' => 'opciones', 'text' => 'Deben estar dentro de la misma categoría conceptual que la respuesta correcta (por ejemplo, diagnósticos, análisis, tratamientos, pronósticos, alternativas de disposición).'],
            ['category' => 'opciones', 'text' => 'Tienen que ser convincentes, gramaticalmente correctas, lógicamente compatibles y de la misma extensión (relativa) que la respuesta correcta.'],
            ['category' => 'opciones', 'text' => 'Deben ser plausibles es decir del mismo nivel conceptual y estructura gramatical.'],
            ['category' => 'opciones', 'text' => 'Deben ser independientes, no deben contener parcialmente la respuesta correcta.'],
            // argumentaciones
            ['category' => 'argumentacion', 'text' => 'Verificar que la argumentación sea concreta y clara, con base académica o científica conceptual, respaldada en bibliografía de consulta especializada de acuerdo con el área a evaluar.'],
            ['category' => 'argumentacion', 'text' => 'Revisar que la argumentaci ó n no sea redundante, es decir, que no contenga explicaciones de argumentos como: “es correcta porque está bien” o “es incorrecta porque no es la respuesta correcta”, entre otros.'],
            ['category' => 'argumentacion', 'text' => 'Revisar que las justificaciones de las opciones de respuestas no sean repetitivas, es decir que los expertos validadores no deben utilizar la misma argumentación para justificar dos o más opciones de respuesta, esto hace que pierda rigurosidad conceptual en las argumentaciones.'],
            // bibliografia
            ['category' => 'bibliografia', 'text' => 'La bibliograf í a debe estar de acuerdo con las especificaciones de las normas APA sexta edición.'],
            ['category' => 'bibliografia', 'text' => 'En carreras de salud la bibliografía base son las Guías Prácticas Clínicas, Protocolos del MSP, MAIS y textos base en cada componente.'],
            ['category' => 'bibliografia', 'text' => 'Las fuentes bibliogr á ficas deben ser confiables, de acuerdo con la especialidad del área de estudio.'],
            ['category' => 'bibliografia', 'text' => 'Las fuentes bibliográficas deben ser públicas, no colocar bibliografía de sitios web pagados como UpToDate u otros.'],
            ['category' => 'bibliografia', 'text' => 'Para el caso de las bibliografías inferiores a más de cinco años, estas se deben actualizar, a excepci ó n de los libros considerados con conocimientos base de las carreras.'],
            
        ];

        foreach ($criteria as $index => $criterion) {
            Criterion::create(array_merge($criterion, ['sort_order' => $index + 1]));
        }
    }
}
