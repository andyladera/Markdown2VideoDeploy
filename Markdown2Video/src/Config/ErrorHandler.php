<?php
// 1. AÑADE EL NAMESPACE
namespace Dales\Markdown2video\Config;

// 2. IMPORTA CLASES NECESARIAS
use Throwable; // Interfaz base para Errores y Excepciones en PHP 7+

class ErrorHandler
{
    /**
     * Manejador de errores de PHP.
     * Convierte errores en ErrorExceptions (si no son silenciados).
     *
     * @param int $errno Nivel del error.
     * @param string $errstr Mensaje del error.
     * @param string $errfile Archivo donde ocurrió el error.
     * @param int $errline Línea donde ocurrió el error.
     * @return bool
     * @throws \ErrorException
     */
    public static function errorHandler(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Si el error ha sido suprimido con el operador @, no hacer nada.
        if (!(error_reporting() & $errno)) {
            return false;
        }

        // En lugar de hacer echo, lanzamos una ErrorException para que sea capturada
        // por el exceptionHandler o por un try-catch más arriba en la pila de llamadas.
        // Esto unifica el manejo de errores y excepciones.
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Manejador de excepciones no capturadas.
     *
     * @param Throwable $exception La excepción o error capturado.
     */
    public static function exceptionHandler(Throwable $exception): void
    {
        // Limpiar cualquier salida que ya se haya enviado
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Determinar si estamos en entorno de desarrollo o producción
        // ENVIRONMENT debe ser una constante definida en tu index.php o accesible globalmente.
        $isDevelopment = (defined('ENVIRONMENT') && ENVIRONMENT === 'development');

        // Código de respuesta HTTP (500 para errores de servidor)
        // Asegurarse de que no se envíen cabeceras después de esto si ya se enviaron
        if (!headers_sent()) {
            http_response_code(500);
            // Podrías añadir un header Content-Type si vas a mostrar una página HTML de error
            // header('Content-Type: text/html; charset=UTF-8');
        }

        // Construir el mensaje de log
        $logMessage = sprintf(
            "[%s] %s: %s in %s on line %d\nStack trace:\n%s\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        // Loguear el error SIEMPRE (en desarrollo y producción)
        // ROOT_PATH debe estar definido globalmente
        $logFilePath = defined('ROOT_PATH') ? ROOT_PATH . '/logs/application_errors.log' : 'application_errors.log';
        error_log($logMessage, 3, $logFilePath);


        // Mostrar información detallada SOLO en desarrollo
        if ($isDevelopment) {
            echo "<h1>Oops! Algo salió mal.</h1>";
            echo "<p><strong>Tipo:</strong> " . get_class($exception) . "</p>";
            echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
            echo "<p><strong>Archivo:</strong> " . htmlspecialchars($exception->getFile(), ENT_QUOTES, 'UTF-8') . " (Línea: " . $exception->getLine() . ")</p>";
            echo "<h2>Traza de Pila:</h2>";
            echo "<pre style='background-color: #f0f0f0; padding: 10px; border: 1px solid #ccc; overflow-x: auto;'>" . htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8') . "</pre>";
        } else {
            // En PRODUCCIÓN, mostrar una página de error genérica y amigable.
            // No reveles detalles técnicos.
            // Puedes incluir una vista o un simple mensaje.
            // Asegúrate que VIEWS_PATH esté definido si vas a incluir una vista.
            $errorPagePath = defined('VIEWS_PATH') ? VIEWS_PATH . 'error/500.php' : '';
            if (file_exists($errorPagePath)) {
                include $errorPagePath;
            } else {
                echo "<h1>Error del Servidor</h1>";
                echo "<p>Lo sentimos, algo salió mal en nuestros servidores. Estamos trabajando para solucionarlo.</p>";
                echo "<p>Por favor, inténtalo de nuevo más tarde.</p>";
            }
        }

        exit(1); // Terminar la ejecución después de manejar un error/excepción fatal.
    }

    /**
     * Manejador para errores fatales (shutdown).
     */
    public static function shutdownHandler(): void
    {
        $error = error_get_last();
        // Solo manejar errores fatales que no hayan sido manejados por errorHandler o exceptionHandler
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_PARSE])) {
            // Crear una ErrorException para pasarla a nuestro exceptionHandler
            // Esto permite un manejo consistente.
            self::exceptionHandler(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }
    }

    /**
     * Inicializa los manejadores de errores y excepciones.
     * Esta función DEBERÍA ser llamada DESPUÉS de que se haya configurado el entorno (ENVIRONMENT)
     * y los ajustes de error_reporting / display_errors en index.php.
     */
    public static function init(): void
    {
        // La configuración de error_reporting y display_errors ya se hace en index.php
        // basado en ENVIRONMENT. Aquí solo registramos los manejadores.

        set_error_handler([self::class, 'errorHandler']);
        set_exception_handler([self::class, 'exceptionHandler']);
        register_shutdown_function([self::class, 'shutdownHandler']);
    }
}