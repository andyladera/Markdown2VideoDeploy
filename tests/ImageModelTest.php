<?php
// tests/Models/ImageModelTest.php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Dales\Markdown2video\Models\ImageModel;

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
    private $baseUrl = 'http://localhost/';

    protected function setUp(): void
    {
        // Mock PDOStatement
        $this->stmt = $this->createMock(PDOStatement::class);

        // Mock PDO
        $this->pdo = $this->createMock(PDO::class);
        $this->pdo->method('prepare')->willReturn($this->stmt);

        // Instancia de ImageModel con PDO simulado y URL base
        $this->imageModel = new ImageModel($this->pdo, $this->baseUrl);

        // Mock de sesión
        $_SESSION['logged_in'] = true;
    }

    protected function tearDown(): void
    {
        unset($_SESSION['logged_in']);
    }

    public function testConstructorRedirectsWhenNotLoggedIn(): void
    {
        unset($_SESSION['logged_in']);

        $this->expectOutputRegex('/Location: ' . preg_quote($this->baseUrl, '/') . 'auth\/login/');
        new ImageModel(null, $this->baseUrl);
    }

    public function testSaveImageSuccessfully(): void
    {
        $this->stmt->expects($this->exactly(5))
            ->method('bindParam');
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->imageModel->saveImage(
            $this->userId,
            $this->imageName,
            $this->originalFilename,
            $this->imageData,
            $this->mimeType
        );

        $this->assertTrue($result);
    }

    public function testSaveImageFailsWithDuplicateEntry(): void
    {
        $this->stmt->method('execute')
            ->will($this->throwException(
                new PDOException("Duplicate entry", '23000')
            ));

        $result = $this->imageModel->saveImage(
            $this->userId,
            $this->imageName,
            $this->originalFilename,
            $this->imageData,
            $this->mimeType
        );

        $this->assertFalse($result);
    }

    public function testSaveImageFailsWithOtherPdoException(): void
    {
        $this->stmt->method('execute')
            ->will($this->throwException(
                new PDOException("General error", 'HY000')
            ));

        $result = $this->imageModel->saveImage(
            $this->userId,
            $this->imageName,
            $this->originalFilename,
            $this->imageData,
            $this->mimeType
        );

        $this->assertFalse($result);
    }

    public function testGetImagesByUserId(): void
    {
        $expectedResult = [
            [
                'id_image' => 1,
                'image_name' => $this->imageName,
                'original_filename' => $this->originalFilename,
                'uploaded_at' => '2023-01-01 00:00:00'
            ]
        ];

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => $this->userId]);
        $this->stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedResult);

        $result = $this->imageModel->getImagesByUserId($this->userId);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetImageByNameAndUserIdFound(): void
    {
        $expectedResult = [
            'image_data' => $this->imageData,
            'mime_type' => $this->mimeType
        ];

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with([
                'image_name' => $this->imageName,
                'user_id' => $this->userId
            ]);
        $this->stmt->expects($this->once())
            ->method('fetch')
            ->willReturn($expectedResult);

        $result = $this->imageModel->getImageByNameAndUserId($this->imageName, $this->userId);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetImageByNameAndUserIdNotFound(): void
    {
        $this->stmt->method('fetch')
            ->willReturn(false);

        $result = $this->imageModel->getImageByNameAndUserId('nonexistent.jpg', $this->userId);

        $this->assertNull($result);
    }

    public function testDeleteImageByIdAndUserIdSuccess(): void
    {
        $idImage = 1;

        $this->stmt->expects($this->exactly(2))
            ->method('bindParam');
        $this->stmt->expects($this->once())
            ->method('execute');
        $this->stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $result = $this->imageModel->deleteImageByIdAndUserId($idImage, $this->userId);

        $this->assertTrue($result);
    }

    public function testDeleteImageByIdAndUserIdFailure(): void
    {
        $idImage = 999;

        $this->stmt->method('rowCount')
            ->willReturn(0);

        $result = $this->imageModel->deleteImageByIdAndUserId($idImage, $this->userId);

        $this->assertFalse($result);
    }

    public function testSessionCheckInConstructor(): void
    {
        $_SESSION['logged_in'] = true;
        $model = new ImageModel(null, $this->baseUrl);
        $this->assertInstanceOf(ImageModel::class, $model);
    }
}
