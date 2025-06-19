<?php
// tests/Models/TemplateModelTest.php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Dales\Markdown2video\Models\TemplateModel;

class TemplateModelTest extends TestCase
{
    private $pdo;
    private $templateModel;

    protected function setUp(): void
    {
        // Mock PDO y PDOStatement
        $this->pdo = $this->createMock(PDO::class);
        $this->templateModel = new TemplateModel($this->pdo);
    }

    public function testGetActiveTemplatesReturnsArray()
    {
        // Datos de prueba simulados
        $expectedTemplates = [
            [
                'id_template' => 1,
                'title' => 'Template 1',
                'description' => 'Descripción 1',
                'preview_image_path' => 'path1.jpg'
            ],
            [
                'id_template' => 2,
                'title' => 'Template 2',
                'description' => 'Descripción 2',
                'preview_image_path' => 'path2.jpg'
            ]
        ];

        // Mock del statement
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
             ->method('fetchAll')
             ->with(PDO::FETCH_ASSOC)
             ->willReturn($expectedTemplates);

        // Configuración del mock PDO
        $this->pdo->expects($this->once())
                  ->method('query')
                  ->with("SELECT id_template, title, description, preview_image_path FROM templates WHERE is_active = 1 ORDER BY title ASC")
                  ->willReturn($stmt);

        // Ejecución del método
        $result = $this->templateModel->getActiveTemplates();

        // Verificaciones
        $this->assertIsArray($result);
        $this->assertEquals($expectedTemplates, $result);
        $this->assertCount(2, $result);
    }

    public function testGetActiveTemplatesReturnsEmptyArrayWhenNoTemplates()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
             ->method('fetchAll')
             ->willReturn([]);

        $this->pdo->expects($this->once())
                  ->method('query')
                  ->willReturn($stmt);

        $result = $this->templateModel->getActiveTemplates();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetTemplateContentByIdReturnsContent()
    {
        $templateId = 1;
        $expectedContent = "# Markdown Content\n\nThis is a template";

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
             ->method('execute')
             ->with(['id_template' => $templateId]);
        $stmt->expects($this->once())
             ->method('fetchColumn')
             ->willReturn($expectedContent);

        $this->pdo->expects($this->once())
                  ->method('prepare')
                  ->with("SELECT markdown_content FROM templates WHERE id_template = :id_template AND is_active = 1")
                  ->willReturn($stmt);

        $result = $this->templateModel->getTemplateContentById($templateId);

        $this->assertEquals($expectedContent, $result);
    }

    public function testGetTemplateContentByIdReturnsNullWhenNotFound()
    {
        $templateId = 999; // ID no existente

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
             ->method('execute')
             ->with(['id_template' => $templateId]);
        $stmt->expects($this->once())
             ->method('fetchColumn')
             ->willReturn(false);

        $this->pdo->expects($this->once())
                  ->method('prepare')
                  ->willReturn($stmt);

        $result = $this->templateModel->getTemplateContentById($templateId);

        $this->assertNull($result);
    }

    public function testGetTemplateContentByIdHandlesPdoException()
    {
        $templateId = 1;

        $this->pdo->expects($this->once())
                  ->method('prepare')
                  ->will($this->throwException(new \PDOException("Database error")));

        $this->expectException(\PDOException::class);
        $this->templateModel->getTemplateContentById($templateId);
    }

    public function testGetActiveTemplatesHandlesPdoException()
    {
        $this->pdo->expects($this->once())
                  ->method('query')
                  ->will($this->throwException(new \PDOException("Database error")));

        $this->expectException(\PDOException::class);
        $this->templateModel->getActiveTemplates();
    }
}