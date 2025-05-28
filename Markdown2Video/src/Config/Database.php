<?php
namespace Dales\Markdown2video\Config; 

use PDO;
use PDOException;
use Exception; 

class Database {
    private string $host;
    private string $db_name;
    private string $username;
    private string $password;
    private string $port;
    private string $charset;
    private array $options;

    private ?PDO $pdoInstance = null; 

    /**
     * Constructor de la clase Database.
     * Las credenciales y configuración se pasan aquí o se cargan desde variables de entorno.
     *
     * @param string|null $host
     * @param string|null $db_name
     * @param string|null $username
     * @param string|null $password
     * @param string|null $port
     * @param string $charset
     * @param array $options Opciones adicionales de PDO
     */
    public function __construct(
        ?string $host = null,
        ?string $db_name = null,
        ?string $username = null,
        ?string $password = null,
        ?string $port = null,
        string $charset = 'utf8mb4',
        array $options = []
    ) {

        $this->host = $host ?? $_ENV['DB_HOST'] ?? 'markdown2video-db.cbeq24kc8xe3.us-east-2.rds.amazonaws.com';
        $this->db_name = $db_name ?? $_ENV['DB_NAME'] ?? 'markdown2video';
        $this->username = $username ?? $_ENV['DB_USER'] ?? 'admin';
        $this->password = $password ?? $_ENV['DB_PASS'] ?? 'admin1234'; // ¡EN PRODUCCIÓN, NUNCA DEJAR VACÍA Y NO USAR VALOR POR DEFECTO ASÍ!
        //$this->port = $port ?? $_ENV['DB_PORT'] ?? '3306';
        $this->charset = $charset;

        // Opciones por defecto de PDO, se pueden sobrescribir
        $defaultOptions = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->options = array_replace($defaultOptions, $options); // array_replace para que las opciones pasadas tengan precedencia
    }

    /**
     * Establece y/o devuelve la conexión PDO.
     * La conexión se establece de forma "lazy" (solo cuando se necesita por primera vez).
     *
     * @return PDO La instancia de PDO.
     * @throws PDOException Si la conexión falla.
     */
    public function getConnection(): PDO {
        if ($this->pdoInstance === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};port={$this->port};charset={$this->charset}";
            try {
                $this->pdoInstance = new PDO($dsn, $this->username, $this->password, $this->options);
            } catch (PDOException $e) {
                // En producción, loguear el error detallado y lanzar una excepción más genérica
                // o una excepción específica de la aplicación si se prefiere.
                error_log("Error de conexión a la base de datos: " . $e->getMessage() . " (DSN: " . $dsn . ")");
                // Podrías crear una clase de excepción personalizada como DatabaseConnectionException
                throw new PDOException("No se pudo conectar al servidor de datos. Por favor, inténtelo más tarde.", (int)$e->getCode(), $e);
            }
        }
        return $this->pdoInstance;
    }

    /**
     * Cierra la conexión explícitamente si es necesario.
     * PDO normalmente cierra la conexión cuando el script termina o el objeto es destruido,
     * pero este método permite un cierre explícito.
     */
    public function disconnect(): void {
        $this->pdoInstance = null;
    }

    // Los métodos para ejecutar consultas ahora utilizarán la conexión obtenida de getConnection()
    // y podrían ser métodos de esta clase o, preferiblemente, la instancia PDO
    // se pasaría a un "Query Builder" o a los Repositorios/Modelos directamente.

    // Si quieres mantener métodos helper en esta clase (opcional):

    /**
     * Ejecuta una consulta y devuelve el objeto PDOStatement.
     *
     * @param string $sql La consulta SQL.
     * @param array $params Los parámetros para la consulta preparada.
     * @return \PDOStatement
     * @throws PDOException
     */
    public function query(string $sql, array $params = []): \PDOStatement {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en consulta: " . $e->getMessage() . " | SQL: " . $sql . " | Params: " . json_encode($params));
            // Re-lanzar la excepción para que el llamador pueda manejarla si es necesario
            throw $e; // O una excepción personalizada
        }
    }

    /**
     * Ejecuta una consulta SELECT y devuelve todos los resultados.
     *
     * @param string $sql
     * @param array $params
     * @return array
     * @throws PDOException
     */
    public function selectAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Ejecuta una consulta SELECT y devuelve la primera fila.
     *
     * @param string $sql
     * @param array $params
     * @return mixed La fila como array asociativo, o false si no hay resultados.
     * @throws PDOException
     */
    public function selectOne(string $sql, array $params = []) { // puede devolver array o false
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Ejecuta una consulta INSERT y devuelve el ID del último registro insertado.
     *
     * @param string $sql
     * @param array $params
     * @return string|false El ID del último registro o false en caso de error.
     * @throws PDOException
     */
    public function insert(string $sql, array $params = []) { // puede devolver string o false
        $this->query($sql, $params);
        return $this->pdoInstance->lastInsertId(); // Acceder a la instancia PDO después de getConnection()
    }

    /**
     * Ejecuta una consulta UPDATE o DELETE y devuelve el número de filas afectadas.
     *
     * @param string $sql
     * @param array $params
     * @return int El número de filas afectadas.
     * @throws PDOException
     */
    public function execute(string $sql, array $params = []): int {
        return $this->query($sql, $params)->rowCount();
    }

    // Métodos de transacción
    public function beginTransaction(): bool {
        return $this->getConnection()->beginTransaction();
    }

    public function commit(): bool {
        return $this->getConnection()->commit();
    }

    public function rollBack(): bool {
        return $this->getConnection()->rollBack();
    }

    public function inTransaction(): bool {
        // getConnection() asegura que pdoInstance no sea null si hay una transacción activa
        return $this->pdoInstance ? $this->pdoInstance->inTransaction() : false;
    }
}