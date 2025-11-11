<?php

return [
    'semantic_similarity_threshold' => env('VALIDATION_SEMANTIC_SIMILARITY_THRESHOLD', 0.98),
    'lexical_similarity_threshold' => env('VALIDATION_LEXICAL_SIMILARITY_THRESHOLD', 0.7), // COMENTARIO: Nuevo umbral para la similitud léxica (Jaccard)
    'deep_plagiarism_threshold' => env('VALIDATION_DEEP_PLAGIARISM_THRESHOLD', 0.15), 
];