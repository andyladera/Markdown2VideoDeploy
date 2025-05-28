<?php
// --- Views/base_markdown.php ---
$base_url = $base_url ?? ''; // Pasada por MarkdownController->create()
$pageTitle = $pageTitle ?? 'Editor Markdown';
$csrf_token_editor = $csrf_token_editor ?? ''; // Específico para este editor si es necesario

// Definir variables globales de JavaScript ANTES de incluir el script externo
echo "<script>\n";
echo "  window.BASE_APP_URL = " . json_encode($base_url) . ";\n";
// Si necesitas el token CSRF para alguna acción AJAX desde base_markdown.js:
// echo "  window.CSRF_TOKEN_EDITOR = " . json_encode($csrf_token_editor) . ";\n";
echo "</script>\n";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.59.4/codemirror.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.59.4/theme/dracula.min.css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/public/css/header.css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/public/css/base_markdown.css">
</head>
<body>
  <?php if (defined('VIEWS_PATH') && file_exists(VIEWS_PATH . 'header.php')) { include VIEWS_PATH . 'header.php'; } ?>

  <div class="container">
    <div class="editor-container">
      <div class="editor-header">
        <h2>Editor</h2>
        <select id="mode-select" class="mode-selector"> <!-- ID para el editor Markdown -->
          <option value="markdown" selected>Markdown Estándar</option>
          <option value="marp">Marp</option>
        </select>
      </div>
      <div class="editor-body"><textarea id="editor" class="editor" placeholder="Escribe tu presentación aquí..."></textarea></div>
    </div>
    <div class="preview-container">
      <div class="preview-header"><h2>Vista Previa</h2></div>
      <div class="preview-body"><div id="ppt-preview" class="ppt-preview"><p>La vista previa se mostrará aquí...</p></div></div>
      <div class="button-container">
        <button class="generate-btn">Generar PPT</button>
        <button class="generate-btn">Generar PDF</button>
        <button class="generate-btn">Generar Video MP4</button>
        <button class="generate-btn">Generar HTML</button>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.59.4/codemirror.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.59.4/mode/markdown/markdown.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.59.4/addon/edit/continuelist.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.59.4/addon/display/placeholder.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  
  <script src="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/public/js/base_markdown.js"></script>
</body>
</html>