<?php

declare(strict_types=1);

namespace Dales\Markdown2video\Tests\Models;

use PHPUnit\Framework\TestCase;
use Dales\Markdown2video\Models\UserModel;
use PDO;
use PDOStatement;
use PDOException;
use ReflectionClass;

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
            ->method('bindParam')
            ->with(':id', 1, PDO::PARAM_INT);
        $this->stmt->expects($this->once())
            ->method('execute');
        $this->stmt->method('fetch')
            ->willReturn($expected);

        $user = $this->userModel->getUserById(1);

        $this->assertEquals($expected, $user);
    }

    public function testGetUserByIdReturnsNullWhenNotFound(): void
    {
        $this->stmt->expects($this->once())
            ->method('execute');
        $this->stmt->method('fetch')
            ->willReturn(false);

        $result = $this->userModel->getUserById(999);

        $this->assertNull($result);
    }

    public function testGetUserByIdHandlesPdoException(): void
    {
        $this->pdo->method('prepare')
            ->will($this->throwException(new PDOException("Database error")));

        putenv('APP_ENV=testing');
        $result = $this->userModel->getUserById(1);
        $this->assertNull($result);
        putenv('APP_ENV=');
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
            ->method('bindParam')
            ->with(':email', 'jdoe@example.com');
        $this->stmt->expects($this->once())
            ->method('execute');
        $this->stmt->method('fetch')
            ->willReturn($expected);

        $result = $this->userModel->findByEmail('jdoe@example.com');

        $this->assertEquals($expected, $result);
    }

    public function testFindByEmailReturnsNull(): void
    {
        $this->stmt->expects($this->once())
            ->method('execute');
        $this->stmt->method('fetch')
            ->willReturn(false);

        $result = $this->userModel->findByEmail('noexiste@example.com');

        $this->assertNull($result);
    }

    public function testFindByEmailHandlesPdoException(): void
    {
        $this->pdo->method('prepare')
            ->will($this->throwException(new PDOException("Database error")));

        putenv('APP_ENV=testing');
        $result = $this->userModel->findByEmail('test@example.com');
        $this->assertNull($result);
        putenv('APP_ENV=');
    }

    public function testFindByUsernameReturnsUser(): void
    {
        $expected = [
            'id' => 1,
            'username' => 'jdoe'
        ];

        $this->stmt->expects($this->once())
            ->method('bindParam')
            ->with(':username', 'jdoe');
        $this->stmt->expects($this->once())
            ->method('execute');
        $this->stmt->method('fetch')
            ->willReturn($expected);

        $result = $this->userModel->findByUsername('jdoe');

        $this->assertEquals($expected, $result);
    }

    public function testFindByUsernameReturnsNull(): void
    {
        $this->stmt->expects($this->once())
            ->method('execute');
        $this->stmt->method('fetch')
            ->willReturn(false);

        $result = $this->userModel->findByUsername('nonexistent');

        $this->assertNull($result);
    }

    public function testFindByUsernameHandlesPdoException(): void
    {
        $this->pdo->method('prepare')
            ->will($this->throwException(new PDOException("Database error")));

        putenv('APP_ENV=testing');
        $result = $this->userModel->findByUsername('testuser');
        $this->assertNull($result);
        putenv('APP_ENV=');
    }

    public function testCreateUserSuccess(): void
    {
        // Cambio principal: El código real usa execute($params), no bindParam individual
        $expectedParams = [
            ':username' => 'newuser',
            ':email' => 'new@example.com',
            ':password_hash' => $this->anything() // El hash puede variar
        ];

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return isset($params[':username']) &&
                    isset($params[':email']) &&
                    isset($params[':password_hash']) &&
                    $params[':username'] === 'newuser' &&
                    $params[':email'] === 'new@example.com';
            }))
            ->willReturn(true);

        $this->pdo->method('lastInsertId')
            ->willReturn('1');

        $result = $this->userModel->createUser('newuser', 'new@example.com', 'password123');
        $this->assertEquals('1', $result);
    }

    public function testUpdateUserReturnsTrueOnSuccess(): void
    {
        // Cambio principal: El código real usa execute($params), no bindParam individual
        $expectedParams = [
            ':username' => 'NuevoNombre',
            ':id' => 1
        ];

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with($expectedParams)
            ->willReturn(true);
        $this->stmt->method('rowCount')
            ->willReturn(1);

        $data = ['username' => 'NuevoNombre'];
        $result = $this->userModel->updateUser(1, $data);
        $this->assertTrue($result);
    }

    public function testUpdateUserWithMultipleFields(): void
    {
        $expectedParams = [
            ':username' => 'newuser',
            ':email' => 'new@example.com',
            ':id' => 1
        ];

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with($expectedParams)
            ->willReturn(true);

        // Agregamos el mock para rowCount que faltaba
        $this->stmt->method('rowCount')
            ->willReturn(1);

        $data = ['username' => 'newuser', 'email' => 'new@example.com'];
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

        putenv('APP_ENV=testing');
        $result = $this->userModel->updateUser(1, ['username' => 'newuser']);
        $this->assertFalse($result);
        putenv('APP_ENV=');
    }

    public function testDeleteUserReturnsTrue(): void
    {
        $this->stmt->expects($this->once())
            ->method('bindParam')
            ->with(':id', 1, PDO::PARAM_INT);
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt->method('rowCount')
            ->willReturn(1);

        $result = $this->userModel->deleteUser(1);
        $this->assertTrue($result);
    }

    public function testDeleteUserReturnsFalseWhenNoRowsAffected(): void
    {
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt->method('rowCount')
            ->willReturn(0);

        $result = $this->userModel->deleteUser(999);
        $this->assertFalse($result);
    }

    public function testDeleteUserHandlesPdoException(): void
    {
        $this->stmt->method('execute')
            ->will($this->throwException(new PDOException("Database error")));

        putenv('APP_ENV=testing');
        $result = $this->userModel->deleteUser(1);
        $this->assertFalse($result);
        putenv('APP_ENV=');
    }

    public function testIsTestEnvironment(): void
    {
        $reflection = new ReflectionClass(UserModel::class);
        $method = $reflection->getMethod('isTestEnvironment');
        $method->setAccessible(true);

        // Test environment
        putenv('APP_ENV=testing');
        $this->assertTrue($method->invoke($this->userModel));

        // CLI environment
        putenv('APP_ENV=production');
        $this->assertEquals(
            PHP_SAPI === 'cli',
            $method->invoke($this->userModel)
        );

        // Non-test, non-CLI
        if (PHP_SAPI !== 'cli') {
            putenv('APP_ENV=production');
            $this->assertFalse($method->invoke($this->userModel));
        }
    }
}
