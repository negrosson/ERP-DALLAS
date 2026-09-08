<?php
$files = [
    'resources/views/bodegas/show.blade.php', 
    'resources/views/components/ajuste-masivo-modal.blade.php'
]; 
foreach($files as $file) { 
    $content = file_get_contents($file); 
    
    // Check if it's double encoded
    // By attempting to convert from UTF-8 to Windows-1252, we restore the original bytes
    // which were actually UTF-8 but misinterpreted as Windows-1252
    $restored = mb_convert_encoding($content, 'Windows-1252', 'UTF-8');
    
    // Only save if it doesn't break entirely
    if ($restored !== false && !empty($restored)) {
        // Just in case there is a BOM, remove it
        $restored = preg_replace('/^\\xEF\\xBB\\xBF/', '', $restored);
        file_put_contents($file, $restored);
        echo "Restored: $file\n";
    }
}
echo "Done\n";
