<?php
// src/Models/UserModel.php
namespace Dales\Markdown2video\Models;

use PDO; // Para type hinting y constantes PDO

class UserModel {
    private PDO $pdo; // Almacena la conexión PDO inyectada

    /**
     * Constructor que recibe la conexión PDO.
     * @param PDO $pdo La instancia de la conexión PDO.
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene un usuario por su ID con todos sus datos.
     *
     * @param int $userId El ID del usuario.
     * @return array|null Los datos del usuario como array asociativo, o null si no se encuentra.
     */
    public function getUserById(int $userId): ?array {
        // Query que selecciona el perfil completo del usuario
        $query = "SELECT id, username, email, telefono, nombre, apellido, fecha_nacimiento, dni, estado, created_at 
                  FROM users 
                  WHERE id = :id 
                  LIMIT 1";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\PDOException $e) {
            error_log("Error en UserModel::getUserById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza los datos de un usuario. La validación debe hacerse en el controlador.
     *
     * @param int $userId El ID del usuario a actualizar.
     * @param array $data Un array asociativo con los datos a actualizar.
     * @return bool True si la actualización fue exitosa (afectó filas), false en caso contrario.
     */
    public function updateUser(int $userId, array $data): bool {
        // Lista de campos permitidos para actualizar
        $allowedFields = ['username', 'email', 'telefono', 'nombre', 'apellido', 'fecha_nacimiento', 'dni', 'estado'];
        $updateFields = [];
        $params = [':id' => $userId];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateFields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return false; // No hay campos válidos para actualizar
        }

        $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("Error en UserModel::updateUser: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo usuario. La contraseña se hashea aquí.
     * La validación de datos (ej. email único) debe hacerse en el controlador.
     *
     * @param string $username
     * @param string $email
     * @param string $plainPassword La contraseña en texto plano.
     * @param array $additionalData Otros datos como nombre, apellido, etc.
     * @return string|false El ID del nuevo usuario creado, o false en caso de error.
     */
    public function createUser(string $username, string $email, string $plainPassword, array $additionalData = []): string|false {
        $password_hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        if ($password_hash === false) {
            error_log("Error al hashear la contraseña para el usuario: " . $email);
            return false;
        }

        $fields = ['username', 'email', 'password_hash'];
        $placeholders = [':username', ':email', ':password_hash'];
        $params = [
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $password_hash
        ];

        // Añadir campos adicionales permitidos del perfil completo
        $allowedAdditionalFields = ['telefono', 'nombre', 'apellido', 'fecha_nacimiento', 'dni', 'estado'];
        foreach ($allowedAdditionalFields as $field) {
            if (array_key_exists($field, $additionalData)) {
                $fields[] = $field;
                $placeholders[] = ":$field";
                $params[":$field"] = $additionalData[$field];
            }
        }

        $query = "INSERT INTO users (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Error de duplicado (UNIQUE constraint)
                error_log("Error de duplicado en UserModel::createUser: " . $e->getMessage());
            } else {
                error_log("Error en UserModel::createUser: " . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Elimina un usuario por su ID.
     *
     * @param int $userId El ID del usuario a eliminar.
     * @return bool True si la eliminación fue exitosa, false en caso contrario.
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

    /**
     * Encuentra un usuario por email (para login y verificación de duplicados).
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array {
        $query = "SELECT id, username, email, password_hash FROM users WHERE email = :email LIMIT 1";
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
     * Encuentra un usuario por su nombre de usuario (para verificación de duplicados).
     * (Este método se conservó de la versión del repositorio para no perder funcionalidad)
     *
     * @param string $username El nombre de usuario a buscar.
     * @return array|null
     */
    public function findByUsername(string $username): ?array {
        $query = "SELECT id, username FROM users WHERE username = :username LIMIT 1";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\PDOException $e) {
            error_log("Error en UserModel::findByUsername: " . $e->getMessage());
            return null; 
        }
    }
}