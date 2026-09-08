<?php
$files = [
    'resources/views/bodegas/show.blade.php', 
    'resources/views/components/ajuste-masivo-modal.blade.php',
    'resources/views/lotes/partials/table.blade.php'
]; 
foreach($files as $file) { 
    $content = file_get_contents($file); 
    // Remove BOM
    $content = preg_replace('/^\\xEF\\xBB\\xBF/', '', $content); 
    // Fix Windows-1252 encoding if it was double encoded by PowerShell
    // Actually, Out-File -Encoding utf8 might have converted utf-8 to utf-16, wait.
    file_put_contents($file, $content); 
}
echo "Done\n";
