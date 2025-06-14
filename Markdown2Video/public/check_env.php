<?php
header('Content-Type: text/plain; charset=utf-8');

echo "============================================\n";
echo "      INFORME DE ENTORNO DEL SERVIDOR         \n";
echo "============================================\n\n";

// --- 1. Verificación de Node.js y NPX ---
echo "--- 1. Verificación de Node.js y NPX ---\n";
$node_version = shell_exec('node -v 2>&1');
$npx_version = shell_exec('npx -v 2>&1');

echo "Versión de Node.js: " . (trim($node_version) ?: "No encontrado o error") . "\n";
echo "Versión de NPX:     " . (trim($npx_version) ?: "No encontrado o error") . "\n";

if (strpos($node_version, 'v') === 0 && strpos($npx_version, '.') !== false) {
    echo "\n[ÉXITO] Node.js y NPX parecen estar instalados y accesibles.\n";
} else {
    echo "\n[FALLO] Node.js o NPX no se encontraron. Marp CLI no funcionará.\n";
}
echo "\n--------------------------------------------\n\n";

// --- 2. Verificación de shell_exec ---
echo "--- 2. Verificación de shell_exec ---\n";
if (function_exists('shell_exec')) {
    $disabled_functions = ini_get('disable_functions');
    if (strpos($disabled_functions, 'shell_exec') === false) {
        echo "[ÉXITO] La función shell_exec() está habilitada.\n";
    } else {
        echo "[FALLO] La función shell_exec() está DESHABILITADA en php.ini.\n";
    }
} else {
    echo "[FALLO] La función shell_exec() no existe.\n";
}
echo "\n--------------------------------------------\n\n";

// --- 3. Verificación de Permisos de Escritura ---
echo "--- 3. Verificación de Permisos de Escritura ---\n";
$test_file = __DIR__ . '/temp_permission_test.txt';
$file_content = 'test';

if (@file_put_contents($test_file, $file_content)) {
    echo "[ÉXITO] El script tiene permisos para escribir en el directorio 'public'.\n";
    // Limpieza
    unlink($test_file);
} else {
    $error = error_get_last();
    echo "[FALLO] El script NO tiene permisos para escribir en el directorio 'public'.\n";
    echo "Error de PHP: " . ($error['message'] ?? 'No disponible') . "\n";
}

echo "\n============================================\n";
echo "          FIN DEL INFORME                 \n";
echo "============================================\n";
