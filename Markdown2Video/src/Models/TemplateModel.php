<?php
namespace Dales\Markdown2video\Models;

use PDO;

class TemplateModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las plantillas activas.
     */
    public function getActiveTemplates(): array {
        $sql = "SELECT id_template, title, description, preview_image_path FROM templates WHERE is_active = 1 ORDER BY title ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el contenido de una plantilla específica por su ID.
     */
    public function getTemplateContentById(int $id_template): ?string {
        $sql = "SELECT markdown_content FROM templates WHERE id_template = :id_template AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_template' => $id_template]);
        return $stmt->fetchColumn() ?: null;
    }
}