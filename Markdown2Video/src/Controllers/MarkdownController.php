<?php

namespace Dales\Markdown2video\Controllers;

use PDO;
use Dompdf\Dompdf;
use Dompdf\Options;
use FFMpeg\FFMpeg;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class MarkdownController
{
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
    }

    /**
     */
    public function create(): void
    {
        $base_url = BASE_URL;
        $pageTitle = "Editor de Presentación (Markdown)";

        // Token CSRF específico para acciones en esta página, como generar PDF
        if (empty($_SESSION['csrf_token_generate_pdf'])) {
            $_SESSION['csrf_token_generate_pdf'] = bin2hex(random_bytes(32));
        }
        $csrf_token_generate_pdf = $_SESSION['csrf_token_generate_pdf'];

        $viewPath = VIEWS_PATH . 'base_markdown.php'; // Asume que es Views/base_markdown.php
        if (file_exists($viewPath)) {
            // Las variables $base_url, $pageTitle, $csrf_token_generate_pdf estarán disponibles
            require_once $viewPath;
        } else {
            $this->showErrorPage("Vista del editor Markdown no encontrada: " . $viewPath);
        }
    }

    /**
     * Muestra el editor para Marp.
     * Ruta: GET /markdown/marp-editor
     */
    public function showMarpEditor(): void
    {
        $base_url = BASE_URL;
        $pageTitle = "Editor de Presentación (Marp)";
        if (empty($_SESSION['csrf_token_marp_generate'])) { // Token diferente si es necesario
            $_SESSION['csrf_token_marp_generate'] = bin2hex(random_bytes(32));
        }
        $csrf_token_marp_generate = $_SESSION['csrf_token_marp_generate'];

        $viewPath = VIEWS_PATH . 'base_marp.php'; // Asume que es Views/base_marp.php
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            $this->showErrorPage("Vista del editor Marp no encontrada: " . $viewPath);
        }
    }

    /**
     */
    public function renderMarpPreview(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['markdown'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Petición incorrecta o falta contenido markdown.']);
            exit;
        }
        ob_start();
        $renderScriptPath = ROOT_PATH . '/server/render_marp.php';
        if (file_exists($renderScriptPath)) {
            include $renderScriptPath;
        } else {
            error_log("Script render_marp.php no encontrado: " . $renderScriptPath);
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
            }
            echo json_encode(['error' => 'Error interno (script de renderizado no encontrado).']);
        }
        $output = ob_get_clean();
        echo $output;
        exit;
    }

    /**
     */
    public function generatePdfFromHtml(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['html_content'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Petición incorrecta o falta contenido HTML.']);
            exit;
        }

        // VALIDAR TOKEN CSRF (si lo estás usando para esta acción)
        // El nombre del token en $_POST debe ser 'csrf_token_generate_pdf'
        if (empty($_POST['csrf_token_generate_pdf']) || !hash_equals($_SESSION['csrf_token_generate_pdf'] ?? '', $_POST['csrf_token_generate_pdf'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Token CSRF inválido o faltante.']);
            exit;
        }

        $htmlContent = $_POST['html_content'];

        $clean_html = $htmlContent;

        $userIdForPath = $_SESSION['user_id'] ?? 'guest_' . substr(session_id(), 0, 8);
        $userTempDir = ROOT_PATH . '/public/temp_files/pdfs/' . $userIdForPath . '/';
        if (!is_dir($userTempDir)) {
            if (!mkdir($userTempDir, 0775, true) && !is_dir($userTempDir)) { /* ... error ... */
                exit;
            }
        }

        $pdfFileName = 'preview_md_' . time() . '_' . bin2hex(random_bytes(3)) . '.pdf';
        $outputPdfFile = $userTempDir . $pdfFileName;

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);

            $cssBaseMarkdown = file_exists(ROOT_PATH . '/public/css/base_markdown.css') ? file_get_contents(ROOT_PATH . '/public/css/base_markdown.css') : '';
            $cssHeader = file_exists(ROOT_PATH . '/public/css/header.css') ? file_get_contents(ROOT_PATH . '/public/css/header.css') : '';

            $fullHtml = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Documento</title>';
            $fullHtml .= '<style>' . $cssBaseMarkdown . $cssHeader . ' body { font-family: sans-serif; margin: 20px; } #ppt-preview { border: none!important; padding:0!important; background:transparent!important; } /* Ajustes para preview dentro del PDF */ </style>';
            $fullHtml .= '</head><body><div class="preview-container"><div class="preview-body"><div id="ppt-preview" class="ppt-preview">' . $clean_html . '</div></div></div></body></html>';

            $dompdf->loadHtml($fullHtml);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            if (file_put_contents($outputPdfFile, $dompdf->output()) === false) {
                throw new \Exception("No se pudo guardar el archivo PDF generado.");
            }

            $_SESSION['pdf_download_file'] = $pdfFileName;
            $_SESSION['pdf_download_full_path'] = $outputPdfFile;

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'PDF generado desde preview. Abriendo página de descarga...',
                'downloadPageUrl' => '/markdown/download-page/' . urlencode($pdfFileName)
            ]);
            exit;
        } catch (\Exception $e) {
            error_log("Error generando PDF con Dompdf: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Error al generar el archivo PDF.', 'debug' => (ENVIRONMENT === 'development' ? $e->getMessage() : 'Error interno.')]);
            if (file_exists($outputPdfFile)) unlink($outputPdfFile);
            exit;
        }
    }

    public function showPdfDownloadPage(string $filenameFromUrl): void
    {
        $filename = basename(urldecode($filenameFromUrl));
        $userIdForPath = $_SESSION['user_id'] ?? 'guest_' . substr(session_id(), 0, 8);
        $expectedSessionFile = $_SESSION['pdf_download_file'] ?? null;
        $expectedSessionPath = $_SESSION['pdf_download_full_path'] ?? null;
        $currentExpectedDiskPath = ROOT_PATH . '/public/temp_files/pdfs/' . $userIdForPath . '/' . $filename;

        if ($expectedSessionFile === $filename && $expectedSessionPath === $currentExpectedDiskPath && file_exists($currentExpectedDiskPath)) {
            $base_url = BASE_URL;
            $pageTitle = "Descargar PDF: " . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');
            $downloadLink = BASE_URL . '/markdown/force-download-pdf/' . urlencode($filename);
            $actual_filename = $filename;
            require_once VIEWS_PATH . '/download_pdf.php';
        } else { /* ... manejo de error ... */
            exit;
        }
    }

    public function forceDownloadPdf(string $filenameFromUrl): void
    {
        $filename = basename(urldecode($filenameFromUrl));
        $userIdForPath = $_SESSION['user_id'] ?? 'guest_' . substr(session_id(), 0, 8);
        $expectedSessionPath = $_SESSION['pdf_download_full_path'] ?? null;
        $currentDiskPath = ROOT_PATH . '/public/temp_files/pdfs/' . $userIdForPath . '/' . $filename;

        if ($expectedSessionPath === $currentDiskPath && file_exists($currentDiskPath)) {
            header('Content-Description: File Transfer'); /* ... (resto de headers para descarga) ... */
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($currentDiskPath));
            flush();
            readfile($currentDiskPath);
            unlink($currentDiskPath);
            unset($_SESSION['pdf_download_file'], $_SESSION['pdf_download_full_path']);
            exit;
        } else {
            exit;
        }
    }

    private function showErrorPage(string $logMessage, string $userMessage = "Error."): void
    { /* ... */
    }

    public function generateMarpFile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Método no permitido. Se esperaba POST.']);
            exit;
        }

        $markdownContent = $_POST['markdown'] ?? null;
        $format = $_POST['format'] ?? null;

        if (empty($markdownContent) || empty($format)) {
            http_response_code(400); // Bad Request
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Faltan datos: markdown o formato.']);
            exit;
        }

        // Por ahora, solo implementamos PDF. Otros formatos se pueden añadir después.
        if ($format !== 'pdf') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => "Formato '{$format}' no soportado actualmente para la generación Marp."]);
            exit;
        }

        // Preparar para llamar al script de renderizado/generación de Marp
        // Este script necesitará ser adaptado para manejar diferentes formatos y devolver la ruta del archivo.
        $renderScriptPath = ROOT_PATH . '/server/render_marp.php';
        if (!file_exists($renderScriptPath)) {
            error_log("Script render_marp.php no encontrado: " . $renderScriptPath);
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor (script de generación no encontrado).']);
            exit;
        }

        // Crear un nombre de archivo único para el PDF
        $userIdForPath = $_SESSION['user_id'] ?? 'guest_' . substr(session_id(), 0, 8);
        $outputDir = ROOT_PATH . '/public/temp_files/pdfs/' . $userIdForPath;
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
                error_log("No se pudo crear el directorio temporal para PDFs Marp: " . $outputDir);
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Error interno al crear directorio temporal.']);
                exit;
            }
        }

        $pdfFileName = 'marp_presentation_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
        $outputPdfFile = $outputDir . '/' . $pdfFileName;

        // Pasar datos al script render_marp.php. Podríamos usar variables de entorno o POST simulado si el script es un endpoint.
        // Por simplicidad, si render_marp.php es un script PHP que podemos incluir y que usa variables globales:
        $_MARP_MARKDOWN_CONTENT = $markdownContent;
        $_MARP_OUTPUT_FORMAT = $format; // 'pdf'
        $_MARP_OUTPUT_FILE_PATH = $outputPdfFile;

        ob_start();
        include $renderScriptPath; // Este script debería generar el archivo en $_MARP_OUTPUT_FILE_PATH
        $scriptOutput = ob_get_clean(); // Capturar cualquier salida del script (debería ser JSON o nada)

        unset($_MARP_MARKDOWN_CONTENT, $_MARP_OUTPUT_FORMAT, $_MARP_OUTPUT_FILE_PATH); // Limpiar variables globales

        // El script render_marp.php debería indicar éxito o error.
        // Asumimos que si el archivo PDF existe, fue exitoso.
        // Una mejor implementación sería que render_marp.php devuelva JSON.
        if (file_exists($outputPdfFile) && filesize($outputPdfFile) > 0) {
            $_SESSION['pdf_download_file'] = $pdfFileName;
            $_SESSION['pdf_download_full_path'] = $outputPdfFile;

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Archivo PDF generado desde Marp con éxito.',
                'downloadPageUrl' => '/markdown/download-page/' . urlencode($pdfFileName)
            ]);
            exit;
        } else {
            error_log("render_marp.php no generó el archivo PDF esperado o el archivo está vacío. Ruta: {$outputPdfFile}. Salida del script: {$scriptOutput}");
            if (file_exists($outputPdfFile)) unlink($outputPdfFile); // Limpiar archivo vacío si existe
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Error al generar el archivo PDF desde Marp.', 'debug_script_output' => (ENVIRONMENT === 'development' ? $scriptOutput : null)]);
            exit;
        }
    }

    public function generateVideoFromMarp(): void
    {
        header('Content-Type: application/json');

        // Validar entrada
        $markdownContent = $_POST['markdown'] ?? null;
        if (empty($markdownContent)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Falta contenido markdown.']);
            exit;
        }

        // 1. Crear directorio temporal
        $userIdForPath = $_SESSION['user_id'] ?? 'guest_' . substr(session_id(), 0, 8);
        $uniqueJobId = time() . '_' . bin2hex(random_bytes(4));
        $tempDir = ROOT_PATH . '/public/temp_files/videos/' . $userIdForPath . '/' . $uniqueJobId;

        if (!is_dir($tempDir) && !mkdir($tempDir, 0775, true)) {
            error_log("No se pudo crear el directorio temporal: " . $tempDir);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al crear directorio temporal.']);
            exit;
        }

        // 2. Guardar Markdown en archivo temporal
        $markdownFileName = 'input.md';
        $markdownFilePath = $tempDir . '/' . $markdownFileName;
        if (file_put_contents($markdownFilePath, $markdownContent) === false) {
            error_log("No se pudo escribir el archivo markdown: " . $markdownFilePath);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al guardar markdown.']);
            exit;
        }

        // 3. Ejecutar Marp CLI
        $marpCliPath = ROOT_PATH . '/node_modules/.bin/marp';
        if (!file_exists($marpCliPath)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Marp CLI no está instalado. Ejecuta: npm install @marp-team/marp-cli',
            ]);
            exit;
        }

        // Cambiar al directorio temporal
        $originalCwd = getcwd();
        chdir($tempDir);

        // Ejecutar Marp
        $command = escapeshellcmd($marpCliPath) . " --html --images png " . escapeshellarg($markdownFileName);
        exec($command . ' 2>&1', $execOutput, $execReturnCode);

        // Volver al directorio original
        chdir($originalCwd);

        if ($execReturnCode !== 0) {
            error_log("Error en Marp CLI. Código: $execReturnCode. Salida: " . implode("\n", $execOutput));
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al convertir Markdown a imágenes.',
                'debug' => (ENVIRONMENT === 'development') ? $execOutput : null
            ]);
            exit;
        }

        // Verificar imágenes generadas
        $imageFiles = glob($tempDir . '/*.png');
        if (empty($imageFiles)) {
            error_log("No se generaron imágenes PNG");
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'No se generaron imágenes a partir del Markdown.',
            ]);
            exit;
        }

        // 4. Configurar FFmpeg
        $logFile = sys_get_temp_dir() . '/ffmpeg_' . $uniqueJobId . '.log';
        $logger = new Logger('ffmpeg');
        $logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));

        try {
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => 'C:/ffmpeg/bin/ffmpeg.exe',
                'ffprobe.binaries' => 'C:/ffmpeg/bin/ffprobe.exe',
                'timeout'          => 3600,
                'ffmpeg.threads'   => 12,
            ], $logger);

            $videoPath = $tempDir . '/output.mp4';
            $durationPerSlide = 3; // 3 segundos por diapositiva
            $inputFramerate = 1 / $durationPerSlide;
            $imagePattern = $tempDir . '/input.%03d.png';

            // Construir y ejecutar comando FFmpeg
            $ffmpegCommand = [
                '-framerate',
                $inputFramerate,
                '-i',
                $imagePattern,
                '-c:v',
                'libx264',
                '-r',
                '25',
                '-pix_fmt',
                'yuv420p',
                '-y', // Sobrescribir si existe
                $videoPath
            ];

            $ffmpeg->getFFMpegDriver()->command($ffmpegCommand);
        } catch (\Exception $e) {
            $logger->error("Error en FFmpeg", ['exception' => $e]);
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al generar el video.',
                'log' => (ENVIRONMENT === 'development') ? file_get_contents($logFile) : null
            ]);
            exit;
        }

        // 5. Verificar y devolver resultado
        if (file_exists($videoPath)) {
            // Limpiar archivos temporales
            array_map('unlink', glob($tempDir . '/*.png'));
            unlink($markdownFilePath);

            echo json_encode([
                'success' => true,
                'videoUrl' => '/temp_files/videos/' . $userIdForPath . '/' . $uniqueJobId . '/output.mp4',
                'message' => 'Video generado correctamente'
            ]);
        } else {
            error_log("El video no se generó: " . $videoPath);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo generar el video.']);
        }
        exit;
    }
}
