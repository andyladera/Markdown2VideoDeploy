<?php
// tests/Models/ImageModelTest.php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Dales\Markdown2video\Models\ImageModel;

class ImageModelTest extends TestCase
{
    private $pdo;
    private $imageModel;
    private $userId = 1;
    private $imageName = 'test_image.jpg';
    private $originalFilename = 'original_test.jpg';
    private $imageData = 'binaryimagedata';
    private $mimeType = 'image/jpeg';

    protected function setUp(): void
    {
        // Mock PDO y PDOStatement
        $this->pdo = $this->createMock(PDO::class);
        $this->imageModel = new ImageModel($this->pdo);

        // Mock de sesión para evitar redirección en constructor
        $_SESSION['logged_in'] = true;
    }

    protected function tearDown(): void
    {
        unset($_SESSION['logged_in']);
    }

    public function testConstructorRedirectsWhenNotLoggedIn()
    {
        unset($_SESSION['logged_in']);
        
        $this->expectOutputRegex('/Location:/');
        new ImageModel();
    }

    public function testSaveImageSuccessfully()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->exactly(5))
             ->method('bindParam');
        $stmt->expects($this->once())
             ->method('execute')
             ->willReturn(true);

        $this->pdo->expects($this->once())
                  ->method('prepare')
                  ->willReturn($stmt);

        $result = $this->imageModel->saveImage(
            $this->userId,
            $this->imageName,
            $this->originalFilename,
            $this->imageData,
            $this->mimeType
        );

        $this->assertTrue($result);
    }

    public function testSaveImageFailsWithDuplicateEntry()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')
             ->will($this->throwException(
                 new PDOException("Duplicate entry", '23000')
             ));

        $this->pdo->method('prepare')
                  ->willReturn($stmt);

        $result = $this->imageModel->saveImage(
            $this->userId,
            $this->imageName,
            $this->originalFilename,
            $this->imageData,
            $this->mimeType
        );

        $this->assertFalse($result);
    }

    public function testSaveImageFailsWithOtherPdoException()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')
             ->will($this->throwException(
                 new PDOException("General error", 'HY000')
             ));

        $this->pdo->method('prepare')
                  ->willReturn($stmt);

        $result = $this->imageModel->saveImage(
            $this->userId,
            $this->imageName,
            $this->originalFilename,
            $this->imageData,
            $this->mimeType
        );

        $this->assertFalse($result);
    }

    public function testGetImagesByUserId()
    {
        $expectedResult = [
            [
                'id_image' => 1,
                'image_name' => $this->imageName,
                'original_filename' => $this->originalFilename,
                'uploaded_at' => '2023-01-01 00:00:00'
            ]
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
             ->method('execute')
             ->with(['user_id' => $this->userId]);
        $stmt->expects($this->once())
             ->method('fetchAll')
             ->willReturn($expectedResult);

        $this->pdo->expects($this->once())
                  ->method('prepare')
                  ->willReturn($stmt);

        $result = $this->imageModel->getImagesByUserId($this->userId);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetImageByNameAndUserIdFound()
    {
        $expectedResult = [
            'image_data' => $this->imageData,
            'mime_type' => $this->mimeType
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
             ->method('execute')
             ->with([
                 'image_name' => $this->imageName,
                 'user_id' => $this->userId
             ]);
        $stmt->expects($this->once())
             ->method('fetch')
             ->willReturn($expectedResult);

        $this->pdo->expects($this->once())
                  ->method('prepare')
                  ->willReturn($stmt);

        $result = $this->imageModel->getImageByNameAndUserId($this->imageName, $this->userId);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetImageByNameAndUserIdNotFound()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')
             ->willReturn(false);

        $this->pdo->method('prepare')
                  ->willReturn($stmt);

        $result = $this->imageModel->getImageByNameAndUserId('nonexistent.jpg', $this->userId);

        $this->assertNull($result);
    }

    public function testDeleteImageByIdAndUserIdSuccess()
    {
        $idImage = 1;

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->exactly(2))
             ->method('bindParam');
        $stmt->expects($this->once())
             ->method('execute');
        $stmt->expects($this->once())
             ->method('rowCount')
             ->willReturn(1);

        $this->pdo->expects($this->once())
                  ->method('prepare')
                  ->willReturn($stmt);

        $result = $this->imageModel->deleteImageByIdAndUserId($idImage, $this->userId);

        $this->assertTrue($result);
    }

    public function testDeleteImageByIdAndUserIdFailure()
    {
        $idImage = 999; // Non-existent ID

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('rowCount')
             ->willReturn(0);

        $this->pdo->method('prepare')
                  ->willReturn($stmt);

        $result = $this->imageModel->deleteImageByIdAndUserId($idImage, $this->userId);

        $this->assertFalse($result);
    }

    public function testSessionCheckInConstructor()
    {
        $_SESSION['logged_in'] = true;
        $model = new ImageModel();
        $this->assertInstanceOf(ImageModel::class, $model);
    }
}