<?php
// src/Models/UserModel.php
namespace Dales\Markdown2video\Models;

use PDO; // Para type hinting y constantes PDO::PARAM_INT

class UserModel {
    private PDO $pdo; // Almacena la conexión PDO inyectada

    /**
     * Constructor que recibe la conexión PDO.
     * Esta conexión es creada en index.php y pasada por el controlador que instancie este modelo.
     * @param PDO $pdo La instancia de la conexión PDO.
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene un usuario por su ID.
     * Selecciona las columnas que existen en tu tabla 'users'.
     * @param int $userId El ID del usuario.
     * @return array|null Los datos del usuario como array asociativo, o null si no se encuentra.
     */
    public function getUserById(int $userId): ?array {
        // Query ajustada a las columnas de tu tabla: id, username, email, created_at
        // (password_hash usualmente no se devuelve en un "get user" general por seguridad,
        // a menos que sea para un proceso de cambio de contraseña interno)
        $query = "SELECT id, username, email, created_at 
                  FROM users 
                  WHERE id = :id 
                  LIMIT 1";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(); // PDO::FETCH_ASSOC es el default por tu clase Database
            return $user ?: null; // Devuelve el usuario o null si fetch() devuelve false
        } catch (\PDOException $e) {
            error_log("Error en UserModel::getUserById: " . $e->getMessage());
            return null; // En caso de error, no exponer detalles, solo loguear.
        }
    }

    /**
     * Encuentra un usuario por su email.
     * Utilizado para verificar si un email ya está registrado y para el proceso de login.
     * @param string $email
     * @return array|null El usuario (incluyendo password_hash para login) o null si no se encuentra.
     */
    // En UserModel.php
public function findByEmail(string $email): ?array {
    $query = "SELECT id, username, email, password_hash FROM users WHERE email = :email LIMIT 1"; // <--- Asegúrate que password_hash esté aquí
    try {
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();
        return $user ?: null;
    } catch (\PDOException $e) {
        error_log("Error en UserModel::findByEmail: " . $e->getMessage());
        return null;
    }
}

    /**
     * Encuentra un usuario por su nombre de usuario.
     * Utilizado para verificar si un nombre de usuario ya está en uso durante el registro.
     * @param string $username El nombre de usuario a buscar.
     * @return array|null Los datos del usuario (ej. id, username) si se encuentra, o null si no.
     */
    public function findByUsername(string $username): ?array {
        // Solo necesitamos saber si existe, no necesitamos todos los datos del usuario para esta verificación.
        $query = "SELECT id, username FROM users WHERE username = :username LIMIT 1";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(); // Por defecto es PDO::FETCH_ASSOC por tu config de Database
            return $user ?: null; // Devuelve el usuario si se encontró, o null si no
        } catch (\PDOException $e) {
            error_log("Error en UserModel::findByUsername: " . $e->getMessage());
            return null; 
        }
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * La contraseña se hashea aquí.
     * 'created_at' se asume que tiene un valor por defecto CURRENT_TIMESTAMP en la definición de la tabla BD.
     *
     * @param string $username
     * @param string $email
     * @param string $plainPassword La contraseña en texto plano que el usuario ingresó.
     * @param array $additionalData (No se usa con la estructura de tabla 'users' actual: id, username, email, password_hash, created_at)
     * @return string|false El ID del nuevo usuario creado si tiene éxito, o false en caso de error.
     */
    public function createUser(string $username, string $email, string $plainPassword, array $additionalData = []): string|false {
        // Hashear la contraseña de forma segura
        $password_hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        if ($password_hash === false) {
            error_log("Error crítico al hashear la contraseña para el usuario con email: " . $email);
            return false; // No se pudo hashear, no continuar.
        }

        // Campos a insertar para la tabla 'users' actual: username, email, password_hash.
        // Se asume que 'id' es autoincremental y 'created_at' es DEFAULT CURRENT_TIMESTAMP.
        $fields = ['username', 'email', 'password_hash'];
        $placeholders = [':username', ':email', ':password_hash'];
        $params = [
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $password_hash
        ];

        // Si tuvieras más campos en la tabla 'users' que se establecen durante el registro,
        // y los pasas en $additionalData, aquí iría la lógica para añadirlos a $fields, $placeholders, y $params.
        // Ejemplo (si tuvieras una columna 'nombre_completo'):
        // if (isset($additionalData['nombre_completo'])) {
        //     $fields[] = 'nombre_completo';
        //     $placeholders[] = ':nombre_completo';
        //     $params[':nombre_completo'] = $additionalData['nombre_completo'];
        // }

        $query = "INSERT INTO users (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $this->pdo->lastInsertId(); // Devuelve el ID del nuevo usuario
        } catch (\PDOException $e) {
            // El código de error PDO 23000 usualmente indica una violación de constraint UNIQUE (ej. email o username duplicado)
            if ($e->getCode() == 23000) { // Integrity constraint violation
                error_log("Error de duplicado (email/username ya existe) en UserModel::createUser: " . $e->getMessage());
            } else {
                error_log("Error de BD en UserModel::createUser: " . $e->getMessage());
            }
            return false; // Indicar fallo
        }
    }

    /**
     * Actualiza los datos de un usuario.
     * (La validación de $data debe hacerse en el Controlador)
     * @param int $userId
     * @param array $data (ej. ['username' => 'nuevo_user', 'email' => 'nuevo@mail.com'])
     * @return bool True si se afectaron filas, false si no o si hubo error.
     */
    public function updateUser(int $userId, array $data): bool {
        // Campos permitidos para actualizar en tu tabla 'users' actual (basado en la imagen)
        // password_hash se actualizaría con un método dedicado 'changePassword'.
        // created_at usualmente no se actualiza.
        $allowedFields = ['username', 'email']; // Ajusta si tienes más campos actualizables
        $updateFields = [];
        $params = [':id' => $userId];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateFields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return false; // No se proporcionaron campos válidos para actualizar
        }

        $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount() > 0; // True si se modificó al menos 1 fila
        } catch (\PDOException $e) {
            error_log("Error en UserModel::updateUser: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina un usuario por su ID.
     * @param int $userId
     * @return bool
     */
    public function deleteUser(int $userId): bool {
        $query = "DELETE FROM users WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("Error en UserModel::deleteUser: " . $e->getMessage());
            return false;
        }
    }
}