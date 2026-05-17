<?php

$translations = json_decode(file_get_contents(dirname(__DIR__).'/es.json'), true) ?: [];

return [
    'no_importable_columns' => $translations['front.no_importable_columns'] ?? 'front.no_importable_columns',
    'normal' => $translations['front.normal'] ?? 'front.normal',
    'unreadable_file' => $translations['front.unreadable_file'] ?? 'front.unreadable_file',
];
