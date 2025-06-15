<?php
// tests/Models/ImageModelTest.php

declare(strict_types=1);

namespace Dales\Markdown2video\Tests\Models;

use PHPUnit\Framework\TestCase;
use Dales\Markdown2video\Models\ImageModel;
use PDO;
use PDOStatement;
use PDOException;

class ImageModelTest extends TestCase
{
    private $pdo;
    private $stmt;
    private ImageModel $imageModel;
    private $userId = 1;
    private $imageName = 'test_image.jpg';
    private $originalFilename = 'original_test.jpg';
    private $imageData = 'binaryimagedata';
    private $mimeType = 'image/jpeg';
    private $baseUrl = 'http://test.local/';

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->stmt = $this->createMock(PDOStatement::class);
        $this->pdo = $this->createMock(PDO::class);
        $this->pdo->method('prepare')->willReturn($this->stmt);

        $this->imageModel = new ImageModel($this->pdo, $this->baseUrl);
        $_SESSION['logged_in'] = true;
    }

    protected function tearDown(): void
    {
        unset($_SESSION['logged_in']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function testConstructorRedirectsWhenNotLoggedIn(): void
    {
        unset($_SESSION['logged_in']);

        $this->expectOutputRegex('#Location: ' . preg_quote($this->baseUrl, '#') . 'auth/login#');
        new ImageModel(null, $this->baseUrl);
    }

    // ... (mantén el resto de los métodos de prueba igual) ...
}
