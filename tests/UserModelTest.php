<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Dales\Markdown2video\Models\UserModel;

class UserModelTest extends TestCase
{
    private $pdo;
    private $stmt;
    private UserModel $userModel;

    protected function setUp(): void
    {
        // Mock de PDOStatement
        $this->stmt = $this->createMock(PDOStatement::class);

        // Mock de PDO
        $this->pdo = $this->createMock(PDO::class);
        $this->pdo->method('prepare')->willReturn($this->stmt);

        // Instancia de UserModel con PDO simulado
        $this->userModel = new UserModel($this->pdo);
    }

    public function testGetUserByIdReturnsUserData(): void
    {
        $expected = [
            'id' => 1,
            'username' => 'jdoe',
            'email' => 'jdoe@example.com',
            'telefono' => '123456',
            'nombre' => 'John',
            'apellido' => 'Doe',
            'fecha_nacimiento' => '1990-01-01',
            'dni' => '12345678',
            'estado' => 'activo'
        ];

        $this->stmt->expects($this->once())->method('execute');
        $this->stmt->method('fetch')->willReturn($expected);

        $user = $this->userModel->getUserById(1);

        $this->assertEquals($expected, $user);
    }

    public function testGetUserByIdReturnsNullWhenNotFound(): void
    {
        $this->stmt->method('fetch')->willReturn(false);

        $result = $this->userModel->getUserById(999);

        $this->assertNull($result);
    }

    public function testUpdateUserReturnsTrueOnSuccess(): void
    {
        $this->stmt->expects($this->once())->method('execute')->willReturn(true);
        $this->stmt->method('rowCount')->willReturn(1);

        $data = ['username' => 'NuevoNombre'];
        $result = $this->userModel->updateUser(1, $data);

        $this->assertTrue($result);
    }

    public function testUpdateUserReturnsFalseWhenNoFieldsProvided(): void
    {
        $result = $this->userModel->updateUser(1, []);
        $this->assertFalse($result);
    }

    public function testDeleteUserReturnsTrue(): void
    {
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('rowCount')->willReturn(1);

        $result = $this->userModel->deleteUser(1);

        $this->assertTrue($result);
    }

    public function testFindByEmailReturnsUser(): void
    {
        $expected = [
            'id' => 1,
            'username' => 'jdoe',
            'email' => 'jdoe@example.com',
            'password_hash' => 'hashedpwd'
        ];

        $this->stmt->method('fetch')->willReturn($expected);

        $result = $this->userModel->findByEmail('jdoe@example.com');

        $this->assertEquals($expected, $result);
    }

    public function testFindByEmailReturnsNull(): void
    {
        $this->stmt->method('fetch')->willReturn(false);

        $result = $this->userModel->findByEmail('noexiste@example.com');

        $this->assertNull($result);
    }
}
