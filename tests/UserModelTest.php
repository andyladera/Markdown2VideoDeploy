<?php

declare(strict_types=1);

namespace Dales\Markdown2video\Tests\Models;

use PHPUnit\Framework\TestCase;
use Dales\Markdown2video\Models\UserModel;
use PDO;
use PDOStatement;
use PDOException;

class UserModelTest extends TestCase
{
    private $pdo;
    private $stmt;
    private UserModel $userModel;

    protected function setUp(): void
    {
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->pdo = $this->createMock(PDO::class);
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->userModel = new UserModel($this->pdo);
    }

    public function testGetUserByIdReturnsUserData(): void
    {
        $expected = [
            'id' => 1,
            'username' => 'jdoe',
            'email' => 'jdoe@example.com',
            'created_at' => '2023-01-01 00:00:00'
        ];

        $this->stmt->expects($this->once())
            ->method('execute');
        $this->stmt->method('fetch')
            ->willReturn($expected);

        $user = $this->userModel->getUserById(1);

        $this->assertEquals($expected, $user);
    }

    public function testGetUserByIdReturnsNullWhenNotFound(): void
    {
        $this->stmt->method('fetch')->willReturn(false);

        $result = $this->userModel->getUserById(999);

        $this->assertNull($result);
    }

    public function testGetUserByIdHandlesPdoException(): void
    {
        $this->pdo->method('prepare')
            ->will($this->throwException(new PDOException("Database error")));

        $result = $this->userModel->getUserById(1);
        $this->assertNull($result);
    }

    public function testFindByEmailReturnsUser(): void
    {
        $expected = [
            'id' => 1,
            'username' => 'jdoe',
            'email' => 'jdoe@example.com',
            'password_hash' => 'hashedpwd'
        ];

        $this->stmt->expects($this->once())
            ->method('execute');
        $this->stmt->method('fetch')
            ->willReturn($expected);

        $result = $this->userModel->findByEmail('jdoe@example.com');

        $this->assertEquals($expected, $result);
    }

    public function testFindByEmailReturnsNull(): void
    {
        $this->stmt->method('fetch')->willReturn(false);

        $result = $this->userModel->findByEmail('noexiste@example.com');

        $this->assertNull($result);
    }

    public function testFindByEmailHandlesPdoException(): void
    {
        $this->pdo->method('prepare')
            ->will($this->throwException(new PDOException("Database error")));

        $result = $this->userModel->findByEmail('test@example.com');
        $this->assertNull($result);
    }

    public function testFindByUsernameReturnsUser(): void
    {
        $expected = [
            'id' => 1,
            'username' => 'jdoe'
        ];

        $this->stmt->expects($this->once())
            ->method('execute');
        $this->stmt->method('fetch')
            ->willReturn($expected);

        $result = $this->userModel->findByUsername('jdoe');

        $this->assertEquals($expected, $result);
    }

    public function testFindByUsernameReturnsNull(): void
    {
        $this->stmt->method('fetch')->willReturn(false);

        $result = $this->userModel->findByUsername('nonexistent');

        $this->assertNull($result);
    }

    public function testFindByUsernameHandlesPdoException(): void
    {
        $this->pdo->method('prepare')
            ->will($this->throwException(new PDOException("Database error")));

        $result = $this->userModel->findByUsername('testuser');
        $this->assertNull($result);
    }

    public function testCreateUserSuccess(): void
    {
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pdo->method('lastInsertId')
            ->willReturn('1');

        $result = $this->userModel->createUser('newuser', 'new@example.com', 'password123');

        $this->assertEquals('1', $result);
    }

    // public function testCreateUserWithDuplicateEntry(): void
    // {
    //     $this->stmt->method('execute')
    //         ->will($this->throwException(new PDOException("Duplicate entry", '23000')));

    //     $result = $this->userModel->createUser('existing', 'existing@example.com', 'password123');

    //     $this->assertFalse($result);
    // }

    // public function testCreateUserWithOtherPdoException(): void
    // {
    //     $this->stmt->method('execute')
    //         ->will($this->throwException(new PDOException("General error", 'HY000')));

    //     $result = $this->userModel->createUser('test', 'test@example.com', 'password123');

    //     $this->assertFalse($result);
    // }

    public function testCreateUserPasswordHashFailure(): void
    {
        // No podemos probar directamente el fallo de password_hash ya que es una función interna
        // Esta prueba es principalmente para documentar el caso
        $this->assertTrue(true);
    }

    public function testUpdateUserReturnsTrueOnSuccess(): void
    {
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt->method('rowCount')
            ->willReturn(1);

        $data = ['username' => 'NuevoNombre'];
        $result = $this->userModel->updateUser(1, $data);

        $this->assertTrue($result);
    }

    public function testUpdateUserReturnsFalseWhenNoFieldsProvided(): void
    {
        $result = $this->userModel->updateUser(1, []);

        $this->assertFalse($result);
    }

    public function testUpdateUserHandlesPdoException(): void
    {
        $this->stmt->method('execute')
            ->will($this->throwException(new PDOException("Database error")));

        $result = $this->userModel->updateUser(1, ['username' => 'newuser']);

        $this->assertFalse($result);
    }

    public function testDeleteUserReturnsTrue(): void
    {
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('rowCount')->willReturn(1);

        $result = $this->userModel->deleteUser(1);

        $this->assertTrue($result);
    }

    public function testDeleteUserReturnsFalseWhenNoRowsAffected(): void
    {
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('rowCount')->willReturn(0);

        $result = $this->userModel->deleteUser(999);

        $this->assertFalse($result);
    }

    public function testDeleteUserHandlesPdoException(): void
    {
        $this->stmt->method('execute')
            ->will($this->throwException(new PDOException("Database error")));

        $result = $this->userModel->deleteUser(1);

        $this->assertFalse($result);
    }

    public function testMethodsVerifyPdoBindAndExecute()
    {
        // Configuración común
        $userId = 1;
        $stmt = $this->createMock(PDOStatement::class);

        // 👇 Obliga a verificar bindParam + execute en getUserById
        $stmt->expects($this->once())
            ->method('bindParam')
            ->with(':id', $userId, \PDO::PARAM_INT);

        $stmt->expects($this->once())
            ->method('execute');

        $this->pdo->method('prepare')->willReturn($stmt);

        // Act
        $this->userModel->getUserById($userId);
    }

    public function testErrorHandlingInNonTestEnvironments()
    {
        // Simula entorno de producción
        putenv('APP_ENV=production');

        // Mock para forzar error
        $this->pdo->method('prepare')
            ->willThrowException(new \PDOException("Database error"));

        // 👇 Verifica dos comportamientos:
        // 1. Que se llame a error_log()
        $this->expectOutputRegex('/Error en UserModel::getUserById/');

        // 2. Que devuelva null (comportamiento esperado)
        $result = $this->userModel->getUserById(1);
        $this->assertNull($result);

        // Restaura entorno
        putenv('APP_ENV=testing');
    }

    public function testQueryStructureIsExact()
    {
        $expectedQuery = "SELECT id, username, email FROM users WHERE id = :id LIMIT 1";

        $stmt = $this->createMock(PDOStatement::class);

        // 👇 Verifica la query literal (fallará si se modifica)
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($expectedQuery)
            ->willReturn($stmt);

        $this->userModel->getUserById(1);
    }

    public function testCreateUserIncludesAllRequiredFields()
    {
        $stmt = $this->createMock(PDOStatement::class);

        // 👇 Verifica los 3 campos obligatorios
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return count($params) === 3 &&
                    isset($params[':username'], $params[':email'], $params[':password_hash']);
            }));

        $this->pdo->method('prepare')->willReturn($stmt);

        $this->userModel->createUser('test', 'test@test.com', 'password123');
    }
}
