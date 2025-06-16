<?php

use Behat\Behat\Context\Context;
use Dales\Markdown2video\Models\UserModel;
use \PDO;

class UserContext implements Context
{
    private PDO $pdo;
    private UserModel $userModel;
    private ?array $lastUser = null;
    private $lastResult;

    /**
     * @BeforeScenario
     */
    public function setupDatabase()
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Crear tabla de usuarios para pruebas
        $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->userModel = new UserModel($this->pdo);
    }

    /**
     * @Given Tengo una conexión a la base de datos de prueba
     */
    public function tengoConexionBaseDatos()
    {
        // Ya configurado en @BeforeScenario
    }

    /**
     * @Given Tengo un usuario existente con username :username
     */
    public function tengoUsuarioExistenteConUsername($username)
    {
        $this->userModel->createUser($username, "$username@example.com", "password123");
    }

    /**
     * @Given Tengo un usuario existente con email :email
     */
    public function tengoUsuarioExistenteConEmail($email)
    {
        $username = explode('@', $email)[0];
        $this->userModel->createUser($username, $email, "password123");
    }

    /**
     * @When Creo un usuario con username :username, email :email y password :password
     */
    public function creoUsuarioConDatos($username, $email, $password)
    {
        $this->lastResult = $this->userModel->createUser($username, $email, $password);
    }

    /**
     * @When Busco el usuario por su ID
     */
    public function buscoUsuarioPorId()
    {
        $this->lastUser = $this->userModel->getUserById(1); // Asumiendo que es el primero
    }

    /**
     * @When Busco el usuario por email :email
     */
    public function buscoUsuarioPorEmail($email)
    {
        $this->lastUser = $this->userModel->findByEmail($email);
    }

    /**
     * @When Busco el usuario por username :username
     */
    public function buscoUsuarioPorUsername($username)
    {
        $this->lastUser = $this->userModel->findByUsername($username);
    }

    /**
     * @When Actualizo el username a :newUsername
     */
    public function actualizoUsername($newUsername)
    {
        $this->lastResult = $this->userModel->updateUser(1, ['username' => $newUsername]);
    }

    /**
     * @When Elimino el usuario
     */
    public function eliminoUsuario()
    {
        $this->lastResult = $this->userModel->deleteUser(1);
    }

    /**
     * @Then Debo obtener un ID de usuario válido
     */
    public function deboObtenerIdValido()
    {
        if (!is_string($this->lastResult) || empty($this->lastResult)) {
            throw new Exception("No se obtuvo un ID válido");
        }
    }

    /**
     * @Then El usuario con email :email debe existir en la base de datos
     */
    public function usuarioDebeExistir($email)
    {
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            throw new Exception("Usuario con email $email no encontrado");
        }
    }

    /**
     * @Then Debo obtener los datos del usuario incluyendo username :username
     */
    public function deboObtenerDatosConUsername($username)
    {
        if (!$this->lastUser || $this->lastUser['username'] !== $username) {
            throw new Exception("Usuario no encontrado o username no coincide");
        }
    }

    /**
     * @Then El usuario debe tener el username :username en la base de datos
     */
    public function usuarioDebeTenerUsername($username)
    {
        $user = $this->userModel->getUserById(1);
        if (!$user || $user['username'] !== $username) {
            throw new Exception("Username no fue actualizado correctamente");
        }
    }

    /**
     * @Then El usuario no debe existir en la base de datos
     */
    public function usuarioNoDebeExistir()
    {
        $user = $this->userModel->getUserById(1);
        if ($user) {
            throw new Exception("El usuario todavía existe en la base de datos");
        }
    }

    /**
     * @Then Debo obtener los datos del usuario incluyendo su email
     */
    public function deboObtenerDatosConEmail()
    {
        if (!$this->lastUser || !isset($this->lastUser['email'])) {
            throw new Exception("Usuario no encontrado o email no está presente");
        }
    }
}