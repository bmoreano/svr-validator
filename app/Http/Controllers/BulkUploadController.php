<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Jobs\ProcessQuestionsCsv;

class BulkUploadController extends Controller
{
    /**
     * Muestra el formulario de carga masiva y pasa el contenido de la plantilla.
     */
    public function create(): View
    {
        $templatePath = 'public/templates/plantilla_preguntas.csv';
        $templateContent = 'stem;bibliography;grado_dificultad;poder_discriminacion;opcion_1;argumentacion_1;opcion_2;argumentacion_2;opcion_3;argumentacion_3;opcion_4;argumentacion_4;respuesta_correcta;career_id


Guía para el Usuario (Explicación de las Columnas)
Columna	                Descripción	                                                                            Obligatorio	Valores Permitidos/Ejemplo
stem	                El enunciado completo de la pregunta.	                                                Sí	        "¿Cuál es la capital de Francia?"
bibliography	        La fuente bibliográfica que respalda la pregunta y la respuesta.	                    Sí	        "Manual de Geografía Mundial, 2023"
grado_dificultad	    El nivel de dificultad percibido para la pregunta.	                                    Sí	        "muy_facil","facil","dificil","muy_dificil"
poder_discriminacion	La capacidad de la pregunta para diferenciar entre estudiantes que saben y los que no.	Sí	        "muy_alto","alto","moderado","bajo","muy_bajo"
opcion_1	            El texto de la primera opción de respuesta.	                                            Sí	        "Berlín"
argumentacion_1	        La justificación de por qué la opción 1 es correcta o incorrecta.	                    Sí	        "Capital de Alemania, no de Francia."
opcion_2	            El texto de la segunda opción de respuesta.	                                            Sí	        "París"
argumentacion_2	        La justificación de la opción 2.	                                                    Sí	        "Respuesta correcta, es la capital de Francia."
opcion_3	            El texto de la tercera opción de respuesta.	                                            Sí	        "Roma"
argumentacion_3	        La justificación de la opción 3.	                                                    Sí	        "Capital de Italia."
opcion_4	            El texto de la cuarta opción de respuesta.	                                            Sí	        "Madrid"
argumentacion_4	        La justificación de la opción 4.	                                                    Sí	        "Capital de España."
respuesta_correcta	    El número de la opción que es la respuesta correcta (1, 2, 3 o 4).	                    Sí	        "2" (porque "París" es la opción 2)
career_id       	    El identificador único de la carrera entero sin signo                                   Sí	        "1" or "2" or "3"....

Instrucciones Clave:
Cabeceras:              No modifique la primera fila del archivo, que contiene los nombres de las columnas.
Comillas:               Utilice comillas dobles (") para encerrar , es decir, las 14 columnas entre comillas dobles.  
Número de Opciones:     Debes proporcionar exactamente 4 opciones de respuesta.
Argumentaciones:        No son opcionales, determinan la calidad del reactivo.
grado_dificultad:       Opcional.
poder_discriminacion:   Opcional
Codificación:           Guarda el archivo con codificación UTF-8 para asegurar que las tildes y otros caracteres especiales se lean correctamente.';

       /*
        // Comentado para usar el contenido de la plantilla directamente en el controlador
        // para facilitar la edición sin depender de archivos en storage durante el desarrollo.
        try {
            // Intentamos leer el contenido del archivo de la plantilla desde el storage.
            if (Storage::exists($templatePath)) {
                $templateContent = Storage::get($templatePath);
            } else {
                // Si el archivo no se encuentra, registramos una advertencia y pasamos un mensaje de error.
                Log::warning("Archivo de plantilla de preguntas no encontrado en: {$templatePath}");
                $templateContent = 'Error: El archivo de la plantilla (plantilla_preguntas.csv) no se encontró en el servidor.';
            }
        } catch (\Exception $e) {
            Log::error("Error al leer el archivo de plantilla de preguntas: " . $e->getMessage());
            $templateContent = 'Error: No se pudo leer el archivo de la plantilla.';
        }
        */
        
        // Pasamos el contenido a la vista.
        return view('questions-upload.create', ['templateContent' => $templateContent]);
    }

    /**
     * Procesa el archivo de preguntas subido y lo despacha a la cola.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(['questions_file' => 'required|file|mimes:csv,txt|max:2048']);
        $path = $validated['questions_file']->store('questions-uploads');
        
        ProcessQuestionsCsv::dispatch($path, Auth::id())->onQueue('low');

        return redirect()->route('dashboard')->with('status', 'Archivo de preguntas recibido. Se está procesando en segundo plano.');
    }
    
    /**
     * Descarga la plantilla CSV para la carga masiva de preguntas.
     * Mantenemos esta funcionalidad por si se necesita en otro lugar.
     */
    public function downloadQuestionsTemplate(): StreamedResponse
    {
        $filePath = 'public/templates/plantilla_preguntas.csv';
        if (!Storage::exists($filePath)) {
            abort(404, 'Archivo de plantilla no encontrado.');
        }
        return Storage::download($filePath, 'plantilla_preguntas.csv');
    }
}