<?php
// server/render_marp.php
// Este script ahora es llamado/incluido por MarkdownController->renderMarpPreview()
// Asume que $_POST['markdown'] está disponible.

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// --- NUEVA LÓGICA PARA GENERACIÓN DE ARCHIVOS (PDF, etc.) ---
if (isset($_MARP_OUTPUT_FORMAT, $_MARP_OUTPUT_FILE_PATH, $_MARP_MARKDOWN_CONTENT)) {
    
    $markdownContent = $_MARP_MARKDOWN_CONTENT;
    $outputFormat    = strtolower($_MARP_OUTPUT_FORMAT);
    $outputFilePath  = $_MARP_OUTPUT_FILE_PATH;

    if (empty($markdownContent)) {
        error_log("render_marp.php (File Gen Mode): Contenido Markdown vacío.");
        return; 
    }

    $nodeExecutablePath = 'node'; 
    $marpCliScriptPath  = realpath(ROOT_PATH . '/node_modules/@marp-team/marp-cli/marp-cli.js');

    if ($marpCliScriptPath === false) {
        error_log("render_marp.php (File Gen Mode): Marp CLI no encontrado. Ruta intentada: " . ROOT_PATH . '/node_modules/@marp-team/marp-cli/marp-cli.js');
        return;
    }

    $tmpMdFile = tempnam(sys_get_temp_dir(), 'marp_md_gen_');
    if ($tmpMdFile === false) {
        error_log("render_marp.php (File Gen Mode): Error creando archivo temporal MD.");
        return;
    }

    $tmpMdFileWithExt = $tmpMdFile . '.md';
    if (!rename($tmpMdFile, $tmpMdFileWithExt)) {
        error_log("render_marp.php (File Gen Mode): Advertencia, no se pudo renombrar temp MD a .md: " . $tmpMdFile);
        $tmpMdFile = $tmpMdFile;
    } else {
        $tmpMdFile = $tmpMdFileWithExt;
    }

    if (file_put_contents($tmpMdFile, $markdownContent) === false) {
        error_log("render_marp.php (File Gen Mode): Error escribiendo en archivo temporal MD: " . $tmpMdFile);
        if (file_exists($tmpMdFile)) unlink($tmpMdFile);
        return;
    }
    $marpArgs = [];
    if ($outputFormat === 'pdf') {
        $marpArgs[] = '--pdf';
    } elseif ($outputFormat === 'pptx') {
        $marpArgs[] = '--pptx';
    } elseif ($outputFormat === 'html') {
        $marpArgs[] = '--html';
    } else {
        error_log("render_marp.php (File Gen Mode): Formato de salida no soportado '{$outputFormat}'.");
        if (file_exists($tmpMdFile)) unlink($tmpMdFile);
        return;
    }
    
    $command = sprintf(
        '%s "%s" %s %s --allow-local-files --html -o %s',
        escapeshellcmd($nodeExecutablePath),
        $marpCliScriptPath,
        escapeshellarg($tmpMdFile),
        implode(' ', $marpArgs),
        escapeshellarg($outputFilePath)
    );

    $descriptorspec = [ 0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => ["pipe", "w"] ];
    $pipes = [];
    $cwd = ROOT_PATH;
    $process = proc_open($command, $descriptorspec, $pipes, $cwd);

    $cmdOutput = '';
    $cmdErrorOutput = '';

    if (is_resource($process)) {
        fclose($pipes[0]); 
        $cmdOutput = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $cmdErrorOutput = stream_get_contents($pipes[2]); fclose($pipes[2]);
        $return_status = proc_close($process);
    } else {
        $return_status = -1;
        $cmdErrorOutput = "Error al iniciar el proceso de Marp CLI para generación de archivo.";
    }

    if (file_exists($tmpMdFile)) {
        unlink($tmpMdFile);
    }

    if ($return_status !== 0) {
        $errorDetails = "Stderr: " . trim($cmdErrorOutput) . "\nStdout: " . trim($cmdOutput);
        error_log("render_marp.php (File Gen Mode): Error ejecutando Marp CLI. Código: $return_status. Formato: $outputFormat. Comando: $command. " . $errorDetails);
        echo $errorDetails;
    }
    
    return;

} 

function send_json_error_preview(int $statusCode, string $message, ?string $details = null): void {
    if (!headers_sent()) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
    }
    $response = ['error' => $message];
    if ($details !== null && (defined('ENVIRONMENT') && ENVIRONMENT === 'development')) {
        $response['details'] = $details;
    }
    echo json_encode($response);
}

if (!isset($_POST['markdown'])) {
    send_json_error_preview(400, 'No se recibió contenido markdown para la vista previa.');
    return;
}

$markdownContentPreview = $_POST['markdown'];

$nodeExecutablePathPreview = 'node';
$marpCliScriptPathPreview  = realpath(ROOT_PATH . '/node_modules/@marp-team/marp-cli/marp-cli.js');

if ($marpCliScriptPathPreview === false) {
    error_log("render_marp.php (Preview Mode): Marp CLI no encontrado. Ruta: " . ROOT_PATH . '/node_modules/@marp-team/marp-cli/marp-cli.js');
    send_json_error_preview(500, 'Error de configuración del servidor: Marp CLI no encontrado (vista previa).');
    return;
}

$tmpMdFilePreview = tempnam(sys_get_temp_dir(), 'marp_md_preview_');
if ($tmpMdFilePreview === false) {
    error_log("render_marp.php (Preview Mode): Error creando archivo temporal MD para vista previa.");
    send_json_error_preview(500, 'Error interno del servidor al crear archivo temporal (vista previa).');
    return;
}

$tmpMdFilePreviewWithExt = $tmpMdFilePreview . '.md';
if (!rename($tmpMdFilePreview, $tmpMdFilePreviewWithExt)) {
    error_log("render_marp.php (Preview Mode): Advertencia, no se pudo renombrar temp MD a .md: " . $tmpMdFilePreview);
} else {
    $tmpMdFilePreview = $tmpMdFilePreviewWithExt;
}


if (file_put_contents($tmpMdFilePreview, $markdownContentPreview) === false) {
    error_log("render_marp.php (Preview Mode): Error escribiendo en archivo temporal MD: " . $tmpMdFilePreview);
    if (file_exists($tmpMdFilePreview)) unlink($tmpMdFilePreview);
    send_json_error_preview(500, 'Error interno del servidor al escribir archivo temporal (vista previa).');
    return;
}

$commandPreview = sprintf(
    '%s "%s" %s --html --allow-local-files --html -o -',
    escapeshellcmd($nodeExecutablePathPreview),
    $marpCliScriptPathPreview,
    escapeshellarg($tmpMdFilePreview)
);

$descriptorspecPreview = [ 0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => ["pipe", "w"] ];
$pipesPreview = [];
$cwdPreview = ROOT_PATH;
$processPreview = proc_open($commandPreview, $descriptorspecPreview, $pipesPreview, $cwdPreview);

$htmlOutputPreview = '';
$errorOutputPreview = '';

if (is_resource($processPreview)) {
    fclose($pipesPreview[0]); 
    $htmlOutputPreview = stream_get_contents($pipesPreview[1]); fclose($pipesPreview[1]);
    $errorOutputPreview = stream_get_contents($pipesPreview[2]); fclose($pipesPreview[2]);
    $return_status_preview = proc_close($processPreview);
} else {
    $return_status_preview = -1;
    $errorOutputPreview = "Error al iniciar el proceso de Marp CLI para vista previa.";
}

if (file_exists($tmpMdFilePreview)) {
    unlink($tmpMdFilePreview); 
}

if ($return_status_preview === 0 && !empty($htmlOutputPreview)) {
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo $htmlOutputPreview; 
} else {
    $logMsgPreview = "render_marp.php (Preview Mode): Error ejecutando Marp CLI (HTML). Código: $return_status_preview. Comando: $commandPreview. Stderr: $errorOutputPreview. Stdout (parcial): " . substr($htmlOutputPreview, 0, 200);
    error_log($logMsgPreview);
    send_json_error_preview(500, 'Error al generar la vista previa Marp desde el servidor.', $errorOutputPreview ?: 'No hubo salida de error específica.');
}
?>
