<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\ListItem;
use PhpOffice\PhpWord\Element\AbstractElement; // Importamos la clase base

class DocxConverterController extends Controller
{
    public function create(): View
    {
        return view('tools.docx-converter.create');
    }

    public function store(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'docx_file' => 'required|file|mimes:docx|max:5120',
        ]);

        $file = $validated['docx_file'];
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFileName = $originalName . '.txt';

        return response()->streamDownload(function () use ($file) {
            try {
                $phpWord = IOFactory::load($file->getRealPath());
                $fullText = '';

                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        
                        // --- 1. MANEJO DE PÁRRAFOS (TextRun) ---
                        if ($element instanceof TextRun) {
                            $paragraphText = $this->extractTextFromElement($element);
                            if (!empty(trim($paragraphText))) {
                                $fullText .= trim($paragraphText) . "\n\n";
                            }
                        }
                        
                        // --- 2. MANEJO DE TABLAS (Table) ---
                        elseif ($element instanceof Table) {
                            $fullText .= "--- INICIO DE TABLA ---\n";
                            foreach ($element->getRows() as $row) {
                                $rowText = [];
                                foreach ($row->getCells() as $cell) {
                                    // Usamos la función auxiliar para extraer el texto de la celda
                                    $rowText[] = trim($this->extractTextFromElement($cell));
                                }
                                $fullText .= implode("\t|\t", $rowText) . "\n";
                            }
                            $fullText .= "--- FIN DE TABLA ---\n\n";
                        }

                        // --- 3. MANEJO DE LISTAS (ListItem) ---
                        // Esta lógica ahora está integrada en la función auxiliar,
                        // pero la mantenemos por si un ListItem viene solo.
                        elseif ($element instanceof ListItem) {
                            $listItemText = $this->extractTextFromElement($element);
                            if (!empty(trim($listItemText))) {
                                $fullText .= "- " . trim($listItemText) . "\n";
                            }
                        }
                    }
                }
                
                // --- 4. LIMPIEZA FINAL DE ESPACIOS ---
                $cleanedText = preg_replace("/\n{3,}/", "\n\n", $fullText);
                $cleanedText = preg_replace("/[ ]{2,}/", " ", $cleanedText);

                echo trim($cleanedText);

            } catch (\Exception $e) {
                echo "Error al procesar el archivo. Detalle: " . $e->getMessage();
            }
        }, $newFileName);
    }
    
    /**
     * Función auxiliar recursiva para extraer texto de cualquier elemento de PhpWord.
     * Puede manejar elementos anidados (ej. TextRun dentro de una Celda de Tabla).
     *
     * @param AbstractElement $element
     * @return string
     */
    private function extractTextFromElement(AbstractElement $element): string
    {
        $text = '';
        if ($element instanceof Text) {
            $text .= $element->getText();
        } elseif ($element instanceof TextBreak) {
            $text .= "\n";
        } elseif (method_exists($element, 'getElements')) {
            // Si el elemento es un contenedor (TextRun, Cell, ListItem, etc.),
            // llamamos recursivamente a esta función para sus hijos.
            foreach ($element->getElements() as $childElement) {
                $text .= $this->extractTextFromElement($childElement);
            }
        }
        return $text;
    }
}