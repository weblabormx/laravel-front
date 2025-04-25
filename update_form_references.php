<?php

// This script updates all references to \Form:: to use our custom Form facade

$directory = __DIR__ . '/src/Inputs';
$files = scandir($directory);

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $filePath = $directory . '/' . $file;
        $content = file_get_contents($filePath);
        
        // Replace \Form:: with \WeblaborMx\Front\Facades\Form::
        $updatedContent = str_replace('\Form::', '\WeblaborMx\Front\Facades\Form::', $content);
        
        if ($content !== $updatedContent) {
            file_put_contents($filePath, $updatedContent);
            echo "Updated: $file\n";
        }
    }
}

// Update blade files that use Form::
$bladeDirectories = [
    __DIR__ . '/resources/views/crud',
    __DIR__ . '/resources/views/inputs',
    __DIR__ . '/resources/views/elements',
];

foreach ($bladeDirectories as $directory) {
    if (!is_dir($directory)) {
        continue;
    }
    
    $files = scandir($directory);
    
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php' || strpos($file, '.blade.php') !== false) {
            $filePath = $directory . '/' . $file;
            $content = file_get_contents($filePath);
            
            // Replace Form:: with \WeblaborMx\Front\Facades\Form::
            $updatedContent = str_replace('Form::', '\WeblaborMx\Front\Facades\Form::', $content);
            
            if ($content !== $updatedContent) {
                file_put_contents($filePath, $updatedContent);
                echo "Updated: $file\n";
            }
        }
    }
}

echo "Update complete!\n";
