<?php

namespace Database\Seeders;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use phpDocumentor\Reflection\Types\Null_;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Question::create(
            [
                'id' => '1',
                'author_id' => '4',
                'stem' => "'TODO LO SIGUIENTE SE ENCUENTRA EN LA HIPERTENSIÓN PULMONAR PRIMARIA, EXCEPTO'",
                'grado_dificultad' => "'mediana'",
                'poder_discriminacion' => "'moderado'",
                'status' => "'borrador'",
                'bibliografia' => "'https://m365.cloud.microsoft/chat/?auth=2'",
                'corregido_administrador' => false,
                'comentario_administrador' => NULL,
            ]
        );
        Option::create(
            [
                'id' => '1',
                'question_id' => '1',
                'option_text' => "'PROMINENCIA DEL VENTRÍCULO DERECHO EN LA RX DE TÓRAX'",
                'is_correct' =>false,
                'argumentation' => "'PROMINENCIA DEL VENTRÍCULO DERECHO EN LA RX DE TÓRAX'", 
            ]
        );
        Option::create(
            [
                'id' => '2',
                'question_id' => '1',
                'option_text' => "'DESVIACIÓN  DEL EJE A LA DERECHA EN EL ECG.'",
                'is_correct' =>false,
                'argumentation' => "'DESVIACIÓN  DEL EJE A LA DERECHA EN EL ECG.'", 
            ]
        );
        Option::create(
            [
                'id' => '3',
                'question_id' => '1',
                'option_text' => "'REGURGITACIÓN MITRAL'",
                'is_correct' =>true,
                'argumentation' => "'La regurgitación mitral (también llamada insuficiencia mitral) es una afección cardíaca en la que la válvula mitral no se cierra correctamente, lo que permite que la sangre fluya hacia atrás desde el ventrículo izquierdo hacia la aurícula izquierda del corazón durante la contracción cardíaca.'", 
            ]
        );
        Option::create(
            [
                'id' => '4',
                'question_id' => '1',
                'option_text' => "'HIPERTROFIA DEL VENTRÍCULO DERECHO'",
                'is_correct' =>false,
                'argumentation' => "'HIPERTROFIA DEL VENTRÍCULO DERECHO'", 
            ]
        );


        Question::create(
            [
                'id' => '2',
                'author_id' => '4',
                'stem' => "'SE DENOMINA MUESTRA DE ESPUTO ÓPTIMA:'",
                'grado_dificultad' => "'muy_facil'",
                'poder_discriminacion' => "'bajo'",
                'status' => "'borrador'",
                'bibliografia' => "'https://m365.cloud.microsoft/chat/?auth=2'",
                'corregido_administrador' => false,
                'comentario_administrador' => null,
            ]
        );
        Option::create(
            [
                'id' => '5',
                'question_id' => '1',
                'option_text' => "'SI PRESENTA MAS DE 25 CÉLULAS EPITELIALES POR CAMPO.'",
                'is_correct' =>false,
                'argumentation' => "'SI PRESENTA MAS DE 25 CÉLULAS EPITELIALES POR CAMPO.'", 
            ]
        );
        Option::create(
            [
                'id' => '6',
                'question_id' => '1',
                'option_text' => "'SI PRESENTA MAS DE 25 PMN POR CAMPO Y MENOS DE 10 CÉLULAS EPITELIALES POR  CAMPO.'",
                'is_correct' =>true,
                'argumentation' => "'Una muestra de esputo óptima es aquella que cumple con ciertos criterios de calidad para asegurar que proviene del tracto respiratorio inferior (y no está contaminada con saliva u otras secreciones orales), lo cual es fundamental para un diagnóstico microbiológico preciso.'", 
            ]
        );
        Option::create(
            [
                'id' => '7',
                'question_id' => '1',
                'option_text' => "'SI PRESENTA  MAS DE 25 CÉLULAS EPITELIALES Y MÁS DE 25 PMN POR CAMPO.'",
                'is_correct' =>false,
                'argumentation' => "'SI PRESENTA  MAS DE 25 CÉLULAS EPITELIALES Y MÁS DE  25 PMN POR CAMPO.'", 
            ]
        );
        Option::create(
            [
                'id' => '8',
                'question_id' => '1',
                'option_text' => "'SI PRESENTA MENOS DE 25 PMN Y MENOS DE 10 CÉLULAS  EPITELIALES POR CAMPO.'",
                'is_correct' =>false,
                'argumentation' => "'SI PRESENTA MENOS DE 25 PMN Y MENOS DE 10 CÉLULAS  EPITELIALES POR CAMPO.'", 
            ]
        );

        
        Question::create(
            [
                'id' => '3',
                'author_id' => '4',
                'stem' => "'INDIQUE CUAL ES EL FACTOR ETIOLÓGICO  AGRESIVO EN LA PRODUCCIÓN DE LA ULCERA GÁSTRICA:'",
                'grado_dificultad' => "'dificil'",
                'poder_discriminacion' => "'alto'",
                'status' => "'borrador'",
                'bibliografia' => "'https://m365.cloud.microsoft/chat/?auth=2'",
                'corregido_administrador' => false,
                'comentario_administrador' => null,
            ]
        );
        Option::create(
            [
                'id' => '9',
                'question_id' => '1',
                'option_text' => "'ALCOHOL.'",
                'is_correct' =>false,
                'argumentation' => "'Cardiomiopatía alcohólica. Demencia y deterioro cognitivo.Ataxia cerebelosa (problemas de coordinación).'", 
            ]
        );
        Option::create(
            [
                'id' => '10',
                'question_id' => '1',
                'option_text' => "'PEPSINA.'",
                'is_correct' =>false,
                'argumentation' => "'Daño en mucosas fuera del estómago (como en la garganta o pulmones en casos graves).'", 
            ]
        );
        Option::create(
            [
                'id' => '11',
                'question_id' => '1',
                'option_text' => "'BILIS.'",
                'is_correct' =>false,
                'argumentation' => "'Colelitiasis: formación de cálculos biliares por desequilibrio en la composición de la bilis. Colangitis: inflamación de los conductos biliares, a menudo por infección. Ictericia: acumulación de bilirrubina en sangre, que causa coloración amarilla en piel y ojos.'", 
            ]
        );
        Option::create(
            [
                'id' => '12',
                'question_id' => '1',
                'option_text' => "'HELICOBACTER PYLORI.'",
                'is_correct' =>true,
                'argumentation' => "'La Helicobacter pylori (H. pylori) es una bacteria que infecta el estómago humano y está relacionada con diversas enfermedades gastrointestinales, como gastritis crónica, úlceras gástricas y duodenales, e incluso cáncer gástrico en casos prolongados.'", 
            ]
        );
    }
}
