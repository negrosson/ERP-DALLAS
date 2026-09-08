<?php
$file = 'resources/views/bodegas/show.blade.php';
$content = file_get_contents($file);

$replacements = [
    'Cǟdigo' => 'Código',
    'Cdigo' => 'Código',
    'Cǭmara de Fro' => 'Cámara de Frío',
    '?"? Cǭmara de Fro' => '🧊 Cámara de Frío',
    'Y" Bodega Normal' => '🏭 Bodega Normal',
    'o. Activa' => '✅ Activa',
    '?O Inactiva' => '❌ Inactiva',
    'Ingreso Rǭpido' => 'Ingreso Rápido',
    'Ingreso Rpido' => 'Ingreso Rápido',
    'Cachantǧn' => 'Cachantún',
    'das' => 'días',
    'Auditora' => 'Auditoría',
    'Variacin' => 'Variación',
    'Accin' => 'Acción',
    'Reversin' => 'Reversión',
    'Estǭs seguro' => '¿Estás seguro',
    'crearǭ' => 'creará',
    'Elaboracin' => 'Elaboración',
    'Vida stil' => 'Vida útil',
    'vida ǧtil' => 'vida útil',
    'aqu' => 'aquí',
    'automǭticamente' => 'automáticamente'
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

file_put_contents($file, $content);
echo "Replaced strings in $file\n";
