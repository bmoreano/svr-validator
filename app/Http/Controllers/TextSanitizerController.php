<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Smalot\PdfParser\Parser as PdfParser; // Importar el parser de PDF

class TextSanitizerController extends Controller
{
    /**
     * Muestra la vista de la herramienta de limpieza de texto.
     */
    public function create(): View
    {
        return view('tools.text-sanitizer.create');
    }

    /**
     * Procesa la entrada (texto o archivo) y devuelve el texto limpio.
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'input_text' => 'nullable|string|required_without:input_file',
            'input_file' => 'nullable|file|mimes:docx,pdf|max:5120|required_without:input_text', // 5MB max
        ], [
            'input_text.required_without' => 'Debes proporcionar texto o subir un archivo.',
            'input_file.required_without' => 'Debes proporcionar texto o subir un archivo.',
        ]);

        $inputText = '';

        if ($request->hasFile('input_file')) {
            $file = $validated['input_file'];
            $extension = $file->getClientOriginalExtension();

            try {
                if ($extension === 'docx') {
                    $inputText = $this->extractTextFromDocx($file);
                } elseif ($extension === 'pdf') {
                    $inputText = $this->extractTextFromPdf($file);
                }
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
            }
        } else {
            $inputText = $validated['input_text'];
        }

        $cleanedText = $this->sanitizeText($inputText);

        return view('tools.text-sanitizer.create', [
            'inputText' => $inputText,
            'cleanedText' => $cleanedText,
        ]);
    }

    /**
     * Extrae texto de un archivo .docx.
     */
    private function extractTextFromDocx($file): string
    {
        $phpWord = WordIOFactory::load($file->getRealPath());
        $fullText = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                // Aquí iría la lógica de extracción de texto para TextRun, Table, etc.
                // (Simplificado por brevedad, pero usaríamos la lógica recursiva que ya creamos)
                if (method_exists($element, 'getText')) {
                    $fullText .= $element->getText() . "\n\n";
                }
            }
        }
        return $fullText;
    }

    /**
     * Extrae texto de un archivo .pdf.
     */
    private function extractTextFromPdf($file): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($file->getRealPath());
        return $pdf->getText();
    }

    /**
     * Lógica central de limpieza de texto.
     */
    private function sanitizeText(string $text): string
    {
        // ... (La lógica de limpieza que ya tenemos no cambia)
        return $text; // Placeholder
    }
}