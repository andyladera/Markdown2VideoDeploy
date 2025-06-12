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
    // --- Begin Chrome/Puppeteer discovery ---
    // Find Chromium path using our helper script. This is necessary because in many
    // server environments (like Docker, AWS Elastic Beanstalk), a pre-installed
    // Chrome browser is not available. By adding 'puppeteer' to package.json,
    // a compatible version of Chromium is downloaded into node_modules.
    $nodePath = 'node'; // Assuming node is in the system's PATH
    $getChromePathScript = __DIR__ . '/get_chrome_path.js';
    $chromePath = '';

    // Check if the helper script exists before trying to execute it
    if (file_exists($getChromePathScript)) {
        // Execute the node script to get the path to the puppeteer-managed Chromium
        // Redirect stderr to nul to avoid printing errors if the command fails
        $chromePath = trim(shell_exec("{$nodePath} {$getChromePathScript} 2>nul"));
    }

    // Set environment variables for marp-cli if a valid path was found.
    // marp-cli's underlying chrome-launcher will pick these up.
    if (!empty($chromePath) && file_exists($chromePath)) {
        putenv("CHROME_PATH={$chromePath}");
        // These flags are often necessary for running Chrome headless in server/container environments.
        putenv("CHROME_NO_SANDBOX=true");
        putenv("CHROME_DISABLE_GPU=true");
    }
    // --- End Chrome/Puppeteer discovery ---

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
        header('Content-Type: application/json');

    $response = ['success' => false];
    $debug_details = [];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['error'] = 'Invalid request method.';
        echo json_encode($response);
        exit;
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['markdown'])) {
        $response['error'] = 'No markdown content provided.';
        echo json_encode($response);
        exit;
    }

    $markdown = $data['markdown'];

    // Crear archivos temporales
    $tempInputFile = tempnam(sys_get_temp_dir(), 'marp_in_') . '.md';
    $tempOutputFile = tempnam(sys_get_temp_dir(), 'marp_out_') . '.pdf';
    file_put_contents($tempInputFile, $markdown);

    // --- INICIO DE LA LÓGICA DE DEPURACIÓN ---

    // 1. Encontrar la ruta de Node.js
    $node_path = trim(shell_exec('which node'));
    if (empty($node_path)) {
        if (file_exists('/usr/bin/node')) {
            $node_path = '/usr/bin/node';
        } else {
            $node_path = 'node'; // Asumir que está en el PATH
        }
    }
    $debug_details['node_path'] = $node_path;

    // 2. Ejecutar el script para obtener la ruta de Chrome
    $chromePathJs = __DIR__ . '/get_chrome_path.js';
    $debug_details['chrome_path_script_path'] = $chromePathJs;
    $debug_details['chrome_path_script_exists'] = file_exists($chromePathJs);

    $chrome_path_command = escapeshellarg($node_path) . " " . escapeshellarg($chromePathJs);
    $chromePathOutput = shell_exec($chrome_path_command . " 2>&1");
    $chromePath = trim((string)$chromePathOutput);
    $debug_details['chrome_path_script_output'] = $chromePath;

    // 3. Verificar si la ruta de Chrome es válida
    $chromePathExists = !empty($chromePath) && file_exists($chromePath);
    $debug_details['chrome_path_found_and_exists'] = $chromePathExists;

    // 4. Construir las variables de entorno
    if (!$chromePathExists) {
        $envVars = 'CHROME_NO_SANDBOX=true ';
        $debug_details['env_vars_set'] = 'CHROME_NO_SANDBOX=true (Chrome path not found)';
    } else {
        $envVars = 'CHROME_PATH=' . escapeshellarg($chromePath) . ' CHROME_NO_SANDBOX=true ';
        $debug_details['env_vars_set'] = 'CHROME_PATH=' . $chromePath . ' CHROME_NO_SANDBOX=true';
    }

    // 5. Encontrar la ruta de marp-cli
    $marpCliPath = realpath(__DIR__ . '/../node_modules/.bin/marp');
    $debug_details['marp_cli_path'] = $marpCliPath;
    $debug_details['marp_cli_path_exists'] = (bool)$marpCliPath;

    if (!$marpCliPath) {
        $response['error'] = "Marp CLI not found. Ensure 'npm install' was successful.";
        $response['debug_details'] = $debug_details;
        echo json_encode($response);
        unlink($tempInputFile);
        if(file_exists($tempOutputFile)) unlink($tempOutputFile);
        exit;
    }

    // 6. Construir y ejecutar el comando final
    $command = "{$envVars}" . escapeshellarg($node_path) . " " . escapeshellarg($marpCliPath) . " " . escapeshellarg($tempInputFile) . " --pdf --o " . escapeshellarg($tempOutputFile) . " --allow-local-files";
    $debug_details['full_command_executed'] = $command;

    $marp_output = shell_exec($command . " 2>&1");
    $debug_details['marp_cli_output'] = $marp_output;

    // --- FIN DE LA LÓGICA DE DEPURACIÓN ---

    if (file_exists($tempOutputFile) && filesize($tempOutputFile) > 0) {
        $response['success'] = true;
        $response['pdf_url'] = 'data:application/pdf;base64,' . base64_encode(file_get_contents($tempOutputFile));
    } else {
        $response['error'] = 'Error al generar el archivo PDF desde Marp. Verifique los detalles de depuración.';
        $response['debug_details'] = $debug_details;
    }

    // Limpiar archivos temporales
    unlink($tempInputFile);
    unlink($tempOutputFile);

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
