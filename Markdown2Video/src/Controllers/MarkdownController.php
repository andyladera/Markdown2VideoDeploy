<?php
namespace Dales\Markdown2video\Controllers;
use PDO;

class MarkdownController {
    private ?PDO $pdo;
    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo;
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASE_URL . '/auth/login'); exit();
        }
    }

    public function create(): void { // Para el editor Markdown estándar
        $base_url = BASE_URL;
        $pageTitle = "Editor de Presentación (Markdown)";
        if (empty($_SESSION['csrf_token_markdown_editor'])) { $_SESSION['csrf_token_markdown_editor'] = bin2hex(random_bytes(32)); }
        $csrf_token = $_SESSION['csrf_token_markdown_editor'];
        $viewPath = VIEWS_PATH . 'base_markdown.php';
        if (file_exists($viewPath)) { require_once $viewPath; }
        else { $this->showErrorPage("Vista Markdown no encontrada: " . $viewPath); }
    }

    public function showMarpEditor(): void { // Para el editor Marp
        $base_url = BASE_URL;
        $pageTitle = "Editor de Presentación (Marp)";
        if (empty($_SESSION['csrf_token_marp_editor'])) { $_SESSION['csrf_token_marp_editor'] = bin2hex(random_bytes(32)); }
        $csrf_token = $_SESSION['csrf_token_marp_editor']; // Token diferente o el mismo, según necesidad
        $viewPath = VIEWS_PATH . 'base_marp.php';
        if (file_exists($viewPath)) { require_once $viewPath; }
        else { $this->showErrorPage("Vista Marp no encontrada: " . $viewPath); }
    }

    /**
     * Endpoint API para renderizar Markdown a HTML usando Marp.
     * Se espera que se llame vía fetch POST con 'markdown' en el cuerpo.
     */
    public function renderMarpPreview(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['markdown'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Petición incorrecta o falta contenido markdown.']);
            exit;
        }

        // Aquí incluimos y ejecutamos la lógica de server/render_marp.php
        // El script render_marp.php leerá $_POST['markdown'] y hará echo del HTML o un error.
        // Es crucial que render_marp.php no haga exit() si queremos capturar su salida
        // o manejar errores de forma más elegante aquí.
        // Por ahora, asumiremos que render_marp.php hace echo y maneja sus propios errores HTTP.

        // Para que las variables de render_marp.php no colisionen, y para control:
        // Considera refactorizar render_marp.php en una función o clase si es posible.
        // Si no, esta es una forma de incluirlo, pero con cuidado.
        
        // Capturar la salida de render_marp.php
        ob_start();
        // No se pasa $pdo a este script directamente, si lo necesita, debe instanciar su propia conexión
        // o ser modificado para aceptar $pdo como parámetro si se refactoriza a función/clase.
        // El script render_marp.php usará $_POST['markdown']
        include ROOT_PATH . '/server/render_marp.php'; // Asegúrate que esta ruta sea correcta
        $output = ob_get_clean();

        // render_marp.php ya establece el header y hace echo (o debería).
        // Si render_marp.php falló y ya envió un código de error HTTP, esto no tendrá efecto.
        // Si tuvo éxito y envió HTML, el header Content-Type ya debería estar puesto por él.
        // Aquí solo imprimimos la salida capturada si el script no hizo exit().
        echo $output; 
        exit; // El controlador termina aquí.
    }

    private function showErrorPage(string $logMessage): void {
        error_log($logMessage);
        http_response_code(500);
        if (defined('VIEWS_PATH') && file_exists(VIEWS_PATH . 'error/500.php')) {
            include VIEWS_PATH . 'error/500.php';
        } else { echo "Error interno del servidor."; }
        exit;
    }
}