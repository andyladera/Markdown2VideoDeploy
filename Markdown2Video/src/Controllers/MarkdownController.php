<?php
namespace Dales\Markdown2video\Controllers;

use PDO;
use Dompdf\Dompdf;     
use Dompdf\Options;  
use Dales\Markdown2video\Models\ImageModel; 
use Spatie\Browsershot\Browsershot; 

class MarkdownController {
    private ?PDO $pdo;

    // Se añade la propiedad para guardar la instancia del ImageModel.
    private ?ImageModel $imageModel = null;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo;
        
        if ($this->pdo) {
            $this->imageModel = new ImageModel($this->pdo);
        }

        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASE_URL . '/auth/login'); 
            exit();
        }
    }

    /**
     */
    // Reemplaza el método create() en src/Controllers/MarkdownController.php

    public function create(): void {
        $base_url = BASE_URL;
        $pageTitle = "Editor de Presentación (Markdown)";
        
        // Token para PDF
        if (empty($_SESSION['csrf_token_generate_pdf'])) { 
            $_SESSION['csrf_token_generate_pdf'] = bin2hex(random_bytes(32)); 
        }
        $csrf_token_generate_pdf = $_SESSION['csrf_token_generate_pdf'];

        // --- CORRECCIÓN: Se añade la creación del token para acciones de imágenes ---
        if (empty($_SESSION['csrf_token_image_action'])) { 
            $_SESSION['csrf_token_image_action'] = bin2hex(random_bytes(32)); 
        }
        $csrf_token_image_action = $_SESSION['csrf_token_image_action'];

        $viewPath = VIEWS_PATH . 'base_markdown.php';
        if (file_exists($viewPath)) {
            // Ahora ambas variables ($csrf_token_generate_pdf y $csrf_token_image_action)
            // existen y se pasan a la vista.
            require_once $viewPath;
        } else {
            $this->showErrorPage("Vista del editor Markdown no encontrada: " . $viewPath);
        }
    }

    /**
     * Muestra el editor para Marp.
     * Ruta: GET /markdown/marp-editor
     */
    public function showMarpEditor(): void {
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
    public function renderMarpPreview(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['markdown'])) {
            http_response_code(400); header('Content-Type: application/json');
            echo json_encode(['error' => 'Petición incorrecta o falta contenido markdown.']);
            exit;
        }
        ob_start();
        $renderScriptPath = ROOT_PATH . '/server/render_marp.php';
        if (file_exists($renderScriptPath)) {
            include $renderScriptPath;
        } else {
            error_log("Script render_marp.php no encontrado: " . $renderScriptPath);
            if (!headers_sent()) { http_response_code(500); header('Content-Type: application/json'); }
            echo json_encode(['error' => 'Error interno (script de renderizado no encontrado).']);
        }
        $output = ob_get_clean();
        echo $output; 
        exit;
    }

    // --- MÉTODOS PARA IMÁGENES (estos ya estaban bien, ahora funcionarán) ---

    public function uploadImage(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Petición inválida.']);
            exit;
        }
        
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token_image_action'], $_POST['csrf_token'])) {
             http_response_code(403);
             echo json_encode(['success' => false, 'error' => 'Error de seguridad (CSRF).']);
             exit;
        }

        if (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Error al subir el archivo.']);
            exit;
        }
        if (empty($_POST['image_name'])) {
            echo json_encode(['success' => false, 'error' => 'El nombre de la imagen es obligatorio.']);
            exit;
        }

        $imageName = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['image_name']);
        if (empty($imageName)) {
            echo json_encode(['success' => false, 'error' => 'El nombre de la imagen contiene caracteres no válidos.']);
            exit;
        }
        
        $file = $_FILES['image_file'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (!in_array($file['type'], $allowedMimes) || $file['size'] > 5 * 1024 * 1024) { // 5MB Limit
            echo json_encode(['success' => false, 'error' => 'Archivo no permitido o demasiado grande (máx 5MB).']);
            exit;
        }

        $imageData = file_get_contents($file['tmp_name']);
        if ($this->imageModel->saveImage($_SESSION['user_id'], $imageName, $file['name'], $imageData, $file['type'])) {
            echo json_encode(['success' => true, 'message' => 'Imagen subida correctamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo guardar la imagen. El nombre ya podría existir.']);
        }
        exit;
    }

    public function getUserImages(): void {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode([]);
            exit;
        }
        $images = $this->imageModel->getImagesByUserId($_SESSION['user_id']);
        echo json_encode($images);
        exit;
    }


    public function deleteImage(): void {
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405); // Method Not Allowed
                echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
                exit;
            }

            if (!isset($_SESSION['user_id'])) {
                http_response_code(401); // Unauthorized
                echo json_encode(['success' => false, 'error' => 'No autorizado.']);
                exit;
            }

            $json_data = file_get_contents('php://input');
            $data = json_decode($json_data, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['id_image']) || !is_numeric($data['id_image'])) {
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'error' => 'Datos JSON inválidos o falta el ID de la imagen.']);
                exit;
            }
            
            if (empty($data['csrf_token']) || !hash_equals($_SESSION['csrf_token_image_action'], $data['csrf_token'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Error de seguridad (CSRF).']);
                exit;
            }

            $wasDeleted = $this->imageModel->deleteImageByIdAndUserId((int)$data['id_image'], $_SESSION['user_id']);

            if ($wasDeleted) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Imagen eliminada correctamente.']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'No se pudo eliminar la imagen: no se encontró o no te pertenece.']);
            }

        } catch (\Throwable $e) {
            error_log("Error en deleteImage: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Ocurrió un error inesperado en el servidor.']);
        }
        
        exit;
    }

    public function generatePdfFromHtml(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['html_content'])) {
            http_response_code(400); header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Petición incorrecta o falta contenido HTML.']);
            exit;
        }
        if (empty($_POST['csrf_token_generate_pdf']) || !hash_equals($_SESSION['csrf_token_generate_pdf'] ?? '', $_POST['csrf_token_generate_pdf'])) {
            http_response_code(403); header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Token CSRF inválido o faltante.']);
            exit;
        }

        try {
            $htmlContent = $_POST['html_content'];
            $userId = $_SESSION['user_id'];
            $pattern = '/<img src="([^"]*\/image\/serve\/([^"]+))"/i';
            
            $callback = function ($matches) use ($userId) {
                $originalSrc = $matches[1];
                $imageName = urldecode($matches[2]);
                $imageDetails = $this->imageModel->getImageByNameAndUserId($imageName, $userId);

                if ($imageDetails) {
                    $imageData = $imageDetails['image_data'];
                    $mimeType = $imageDetails['mime_type'];
                    $finalImageData = $imageData; 

                    if (extension_loaded('gd')) {
                        $sourceImage = @imagecreatefromstring($imageData);

                        if ($sourceImage !== false) {
                            $maxImageWidthInPdf = 650; 
                            $originalWidth = imagesx($sourceImage);
                            
                            if ($originalWidth > $maxImageWidthInPdf) {
                                $originalHeight = imagesy($sourceImage);
                                
                                $ratio = $originalHeight / $originalWidth;
                                $newWidth = $maxImageWidthInPdf;
                                $newHeight = $newWidth * $ratio;

                                $newWidthInt = (int) round($newWidth);
                                $newHeightInt = (int) round($newHeight);
                                
                                $resizedImage = imagecreatetruecolor($newWidthInt, $newHeightInt);
                                
                                if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
                                    imagealphablending($resizedImage, false);
                                    imagesavealpha($resizedImage, true);
                                    $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                                    imagefilledrectangle($resizedImage, 0, 0, $newWidthInt, $newHeightInt, $transparent);
                                }

                                imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidthInt, $newHeightInt, $originalWidth, $originalHeight);
                                
                                ob_start();
                                switch ($mimeType) {
                                    case 'image/png': imagepng($resizedImage); break;
                                    case 'image/gif': imagegif($resizedImage); break;
                                    default: imagejpeg($resizedImage, null, 85); break;
                                }
                                $resizedImageData = ob_get_clean();
                                
                                if ($resizedImageData) {
                                    $finalImageData = $resizedImageData;
                                }
                                
                                imagedestroy($resizedImage);
                            }
                            imagedestroy($sourceImage);
                        } else {
                            error_log("Advertencia: No se pudo procesar la imagen '{$imageName}'.");
                        }
                    }
                
                    $base64Image = base64_encode($finalImageData);
                    $dataUri = 'data:' . $mimeType . ';base64,' . $base64Image;
                    return str_replace($originalSrc, $dataUri, $matches[0]);
                }
                return $matches[0];
            };

            $boundCallback = $callback->bindTo($this, $this);
            $htmlContent = preg_replace_callback($pattern, $boundCallback, $htmlContent);
            
            $clean_html = $htmlContent; 
            
            $userIdForPath = $_SESSION['user_id'] ?? 'guest_' . substr(session_id(), 0, 8);
            $userTempDir = ROOT_PATH . '/public/temp_files/pdfs/' . $userIdForPath . '/';
            if (!is_dir($userTempDir)) { if (!mkdir($userTempDir, 0775, true) && !is_dir($userTempDir)) { exit; } }
            $pdfFileName = 'preview_md_' . time() . '_' . bin2hex(random_bytes(3)) . '.pdf';
            $outputPdfFile = $userTempDir . $pdfFileName;
            
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true); 
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');
            $dompdf = new Dompdf($options);
            $cssPdf = file_exists(ROOT_PATH . '/public/css/pdf_styles.css') ? file_get_contents(ROOT_PATH . '/public/css/pdf_styles.css') : '';
            $fullHtml = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Documento</title>';
            $fullHtml .= '<style>' . $cssPdf . '</style>';
            $fullHtml .= '</head><body>' . $clean_html . '</body></html>';
            $dompdf->loadHtml($fullHtml);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            if (file_put_contents($outputPdfFile, $dompdf->output()) === false) { throw new \Exception("No se pudo guardar el archivo PDF generado."); }
            $_SESSION['pdf_download_file'] = $pdfFileName;
            $_SESSION['pdf_download_full_path'] = $outputPdfFile;
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'PDF generado.','downloadPageUrl' => BASE_URL . '/markdown/download-page/' . urlencode($pdfFileName)]);
            exit;

        } catch (\Throwable $e) {
            error_log("ERROR FATAL en generatePdfFromHtml: " . $e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            http_response_code(500); header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Error interno al generar el PDF.']);
            exit;
        }
    } 

    public function showPdfDownloadPage(string $filenameFromUrl): void {
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
        } else { 
            // Manejo de error básico
            http_response_code(404);
            echo "Archivo no encontrado o sesión inválida.";
            exit;
        }
    }
    
    public function forceDownloadPdf(string $filenameFromUrl): void {
        $filename = basename(urldecode($filenameFromUrl));
        $userIdForPath = $_SESSION['user_id'] ?? 'guest_' . substr(session_id(), 0, 8);
        $expectedSessionPath = $_SESSION['pdf_download_full_path'] ?? null;
        $currentDiskPath = ROOT_PATH . '/public/temp_files/pdfs/' . $userIdForPath . '/' . $filename;

        if ($expectedSessionPath === $currentDiskPath && file_exists($currentDiskPath)) {
            header('Content-Description: File Transfer');
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
            http_response_code(404);
            echo "Archivo no encontrado o acceso no autorizado.";
            exit;
        }
    }

    /**
     * Genera un archivo PDF a partir de contenido Markdown usando Spatie/Browsershot.
     * Ruta: POST /markdown/generate-marp-pdf
     */
     public function generateMarpPdf(): void {
        // 1. Obtener y validar el contenido Markdown
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['markdown'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No se proporcionó contenido markdown.']);
            exit;
        }

        $markdownContent = $data['markdown'];

        // 2. Construir el HTML para que Marp lo renderice en el navegador headless
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Marp Presentation</title>
    <script src="https://cdn.jsdelivr.net/npm/@marp-team/marp-core@latest/lib/marp.browser.js"></script>
</head>
<body>
  <script type="text/markdown">
    {$markdownContent}
  </script>
</body>
</html>
HTML;
        $html = str_replace('{$markdownContent}', $markdownContent, $html);

        try {
            // 3. Usar Browsershot para generar el PDF
            $pdfOutput = Browsershot::html($html)
                ->setChromeExecutablePath('/usr/bin/google-chrome-stable')
                ->waitUntilNetworkIdle() // Espera a que Marp JS termine de renderizar
                ->pdf();

            // 4. Enviar el PDF generado al cliente
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="presentacion.pdf"');
            header('Content-Length: ' . strlen($pdfOutput));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            echo $pdfOutput;

        } catch (\Exception $e) {
            // Capturar errores de Browsershot (ej. Chrome no encontrado)
            error_log('Error de Browsershot: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Falló la generación del PDF con Browsershot.',
                'details' => $e->getMessage()
            ]);
        }
        
        exit;
    }

    private function showErrorPage(string $logMessage, string $userMessage = "Error."): void {
        error_log($logMessage);
        http_response_code(500);
        echo "<h1>Error</h1><p>$userMessage</p>";
    }

    public function createFromTemplate(int $templateId): void {
        if (!$this->pdo) {
            $this->showErrorPage("No hay conexión a la base de datos para cargar la plantilla.");
            return;
        }
        $templateModel = new \Dales\Markdown2video\Models\TemplateModel($this->pdo);
        
        $templateContent = $templateModel->getTemplateContentById($templateId);

        if ($templateContent === null) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        $base_url = BASE_URL;
        $pageTitle = "Editor - Desde Plantilla";
        
        if (empty($_SESSION['csrf_token_generate_pdf'])) { 
            $_SESSION['csrf_token_generate_pdf'] = bin2hex(random_bytes(32)); 
        }
        $csrf_token_generate_pdf = $_SESSION['csrf_token_generate_pdf'];
        if (empty($_SESSION['csrf_token_image_action'])) { 
            $_SESSION['csrf_token_image_action'] = bin2hex(random_bytes(32)); 
        }
        $csrf_token_image_action = $_SESSION['csrf_token_image_action'];

        $initialContent = $templateContent;

        $viewPath = VIEWS_PATH . 'base_markdown.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            $this->showErrorPage("La vista del editor Markdown no se ha encontrado.");
        }
    }

}