<?php

$translations = json_decode(file_get_contents(dirname(__DIR__).'/es.json'), true) ?: [];

return [
    'missing_excel_id' => $translations['front.missing_excel_id'] ?? 'front.missing_excel_id',
    'import_id_not_found' => $translations['front.import_id_not_found'] ?? 'front.import_id_not_found',
    'no_importable_columns' => $translations['front.no_importable_columns'] ?? 'front.no_importable_columns',
    'normal' => $translations['front.normal'] ?? 'front.normal',
    'unreadable_file' => $translations['front.unreadable_file'] ?? 'front.unreadable_file',
];
