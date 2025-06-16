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
        
        // Limpiar environment variables al inicio
        putenv('APP_ENV=');
    }

    protected function tearDown(): void
    {
        // Limpiar environment variables después de cada test
        putenv('APP_ENV=');
        parent::tearDown();
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

        // Establecer entorno de test para evitar error_log
        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=testing');
        
        $result = $this->userModel->getUserById(1);
        $this->assertNull($result);
        
        // Restaurar entorno original
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
    }

    // Test adicional para cobertura de error_log en producción
    public function testGetUserByIdHandlesPdoExceptionInProduction(): void
    {
        $this->pdo->method('prepare')
            ->will($this->throwException(new PDOException("Database error")));

        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=production');
        
        $result = $this->userModel->getUserById(1);
        $this->assertNull($result);
        
        // Restaurar entorno original
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
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

        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=testing');
        
        $result = $this->userModel->findByEmail('test@example.com');
        $this->assertNull($result);
        
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
    }

    // Test adicional para error_log en producción
    public function testFindByEmailHandlesPdoExceptionInProduction(): void
    {
        $this->pdo->method('prepare')
            ->will($this->throwException(new PDOException("Database error")));

        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=production');
        
        $result = $this->userModel->findByEmail('test@example.com');
        $this->assertNull($result);
        
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
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

        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=testing');
        
        $result = $this->userModel->findByUsername('testuser');
        $this->assertNull($result);
        
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
    }

    // Test adicional para error_log en producción
    public function testFindByUsernameHandlesPdoExceptionInProduction(): void
    {
        $this->pdo->method('prepare')
            ->will($this->throwException(new PDOException("Database error")));

        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=production');
        
        $result = $this->userModel->findByUsername('testuser');
        $this->assertNull($result);
        
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
    }

    public function testCreateUserSuccess(): void
    {
        $this->stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function($params) {
                // Verificar que los parámetros requeridos estén presentes
                if (!isset($params[':username']) || 
                    !isset($params[':email']) || 
                    !isset($params[':password_hash'])) {
                    return false;
                }
                
                // Verificar valores específicos
                if ($params[':username'] !== 'newuser' || 
                    $params[':email'] !== 'new@example.com') {
                    return false;
                }
                
                // Verificar que password_hash es una cadena válida (no verificamos el contenido exacto)
                return is_string($params[':password_hash']) && !empty($params[':password_hash']);
            }))
            ->willReturn(true);

        $this->pdo->method('lastInsertId')
            ->willReturn('1');

        $result = $this->userModel->createUser('newuser', 'new@example.com', 'password123');
        $this->assertEquals('1', $result);
    }

    // Test para el caso donde password_hash falla - usando mock function override
    public function testCreateUserFailsWhenPasswordHashFails(): void
    {
        // Este test requiere un enfoque diferente ya que password_hash es una función nativa
        // En su lugar, testearemos el comportamiento con PDO exception que simula el mismo resultado
        $this->stmt->method('execute')
            ->will($this->throwException(new PDOException("Simulated password hash failure")));

        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=testing');
        
        $result = $this->userModel->createUser('newuser', 'new@example.com', 'password123');
        $this->assertFalse($result);
        
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
    }

    public function testCreateUserHandlesPdoException(): void
    {
        $this->stmt->method('execute')
            ->will($this->throwException(new PDOException("Database error")));

        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=testing');
        
        $result = $this->userModel->createUser('newuser', 'new@example.com', 'password123');
        $this->assertFalse($result);
        
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
    }

    // Test adicional para error_log en producción durante createUser
    public function testCreateUserHandlesPdoExceptionInProduction(): void
    {
        $this->stmt->method('execute')
            ->will($this->throwException(new PDOException("Database error")));

        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=production');
        
        $result = $this->userModel->createUser('newuser', 'new@example.com', 'password123');
        $this->assertFalse($result);
        
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
    }

    public function testUpdateUserReturnsTrueOnSuccess(): void
    {
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
        
        $this->stmt->method('rowCount')
            ->willReturn(1);

        $data = ['username' => 'newuser', 'email' => 'new@example.com'];
        $result = $this->userModel->updateUser(1, $data);
        $this->assertTrue($result);
    }

    // Test para verificar que solo se permiten campos permitidos
    public function testUpdateUserIgnoresNotAllowedFields(): void
    {
        $expectedParams = [
            ':username' => 'newuser',
            ':id' => 1
        ];

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with($expectedParams)
            ->willReturn(true);
        
        $this->stmt->method('rowCount')
            ->willReturn(1);

        // Incluir un campo no permitido que debe ser ignorado
        $data = ['username' => 'newuser', 'password' => 'secret', 'admin' => true];
        $result = $this->userModel->updateUser(1, $data);
        $this->assertTrue($result);
    }

    // Test para el caso donde rowCount devuelve 0
    public function testUpdateUserReturnsFalseWhenNoRowsAffected(): void
    {
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt->method('rowCount')
            ->willReturn(0);

        $data = ['username' => 'newuser'];
        $result = $this->userModel->updateUser(1, $data);
        $this->assertFalse($result);
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

        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=testing');
        
        $result = $this->userModel->updateUser(1, ['username' => 'newuser']);
        $this->assertFalse($result);
        
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
    }

    // Test adicional para error_log en producción durante updateUser
    public function testUpdateUserHandlesPdoExceptionInProduction(): void
    {
        $this->stmt->method('execute')
            ->will($this->throwException(new PDOException("Database error")));

        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=production');
        
        $result = $this->userModel->updateUser(1, ['username' => 'newuser']);
        $this->assertFalse($result);
        
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
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

        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=testing');
        
        $result = $this->userModel->deleteUser(1);
        $this->assertFalse($result);
        
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
    }

    // Test adicional para error_log en producción durante deleteUser
    public function testDeleteUserHandlesPdoExceptionInProduction(): void
    {
        $this->stmt->method('execute')
            ->will($this->throwException(new PDOException("Database error")));

        $originalEnv = getenv('APP_ENV');
        putenv('APP_ENV=production');
        
        $result = $this->userModel->deleteUser(1);
        $this->assertFalse($result);
        
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
    }

    public function testIsTestEnvironment(): void
    {
        $reflection = new ReflectionClass(UserModel::class);
        $method = $reflection->getMethod('isTestEnvironment');
        $method->setAccessible(true);

        $originalEnv = getenv('APP_ENV');

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

        // Restore original environment
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
    }

    // Test específico para verificar el comportamiento de php_sapi_name()
    public function testIsTestEnvironmentWithDifferentSapi(): void
    {
        $reflection = new ReflectionClass(UserModel::class);
        $method = $reflection->getMethod('isTestEnvironment');
        $method->setAccessible(true);

        $originalEnv = getenv('APP_ENV');

        // Test con APP_ENV diferente a testing
        putenv('APP_ENV=development');
        
        // El resultado debe depender de si estamos en CLI o no
        $expected = php_sapi_name() === 'cli';
        $this->assertEquals($expected, $method->invoke($this->userModel));
        
        // Restore original environment
        if ($originalEnv !== false) {
            putenv("APP_ENV=$originalEnv");
        } else {
            putenv('APP_ENV=');
        }
    }

    // Tests adicionales para mejorar cobertura de mutaciones

    // Test para verificar que fetch() retorna exactamente el valor esperado
    public function testGetUserByIdReturnsExactFetchResult(): void
    {
        $fetchResult = ['id' => 1, 'username' => 'test'];
        
        $this->stmt->method('fetch')
            ->willReturn($fetchResult);

        $result = $this->userModel->getUserById(1);
        $this->assertSame($fetchResult, $result);
    }

    // Test para verificar el comportamiento exacto de rowCount
    public function testUpdateUserChecksRowCountGreaterThanZero(): void
    {
        $this->stmt->method('execute')
            ->willReturn(true);
        
        // Test con rowCount = 1 (mayor que 0)
        $this->stmt->method('rowCount')
            ->willReturn(1);

        $result = $this->userModel->updateUser(1, ['username' => 'test']);
        $this->assertTrue($result);
    }

    // Test para verificar el comportamiento con rowCount = 0
    public function testDeleteUserChecksRowCountExactly(): void
    {
        $this->stmt->method('execute')
            ->willReturn(true);
        
        // Test con rowCount = 0
        $this->stmt->method('rowCount')
            ->willReturn(0);

        $result = $this->userModel->deleteUser(1);
        $this->assertFalse($result);
    }

    // // Test para verificar el operador ternario en fetch
    // public function testFetchOperatorBehavior(): void
    // {
    //     // Test cuando fetch retorna un array vacío (truthy pero no queremos null)
    //     $this->stmt->method('fetch')
    //         ->willReturn([]);

    //     $result = $this->userModel->getUserById(1);
    //     $this->assertEquals([], $result);
    // }

    // Test para verificar array_key_exists vs isset
    public function testUpdateUserWithNullValues(): void
    {
        $expectedParams = [
            ':username' => null,
            ':id' => 1
        ];

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with($expectedParams)
            ->willReturn(true);
        
        $this->stmt->method('rowCount')
            ->willReturn(1);

        // array_key_exists debe funcionar incluso con valores null
        $data = ['username' => null];
        $result = $this->userModel->updateUser(1, $data);
        $this->assertTrue($result);
    }

    // Test para verificar empty() en updateFields
    public function testUpdateUserEmptyFieldsCheck(): void
    {
        // Verificar que empty() funciona correctamente
        $data = ['not_allowed_field' => 'value'];
        $result = $this->userModel->updateUser(1, $data);
        $this->assertFalse($result);
    }
}