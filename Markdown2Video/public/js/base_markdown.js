// public/js/base_markdown.js

document.addEventListener('DOMContentLoaded', function () {
    const editorTextarea = document.getElementById('editor'); // ID del textarea en base_markdown.php
    const previewDiv = document.getElementById('ppt-preview'); // ID del div de previsualización
    const modeSelect = document.getElementById('mode-select');   // ID del select en base_markdown.php
    
    // Obtener BASE_URL de la variable global definida en la vista PHP (Views/base_markdown.php)
    // Ejemplo en la vista PHP: <script>window.BASE_APP_URL = <?php echo json_encode($base_url); ?>;</script>
    const baseUrlJs = typeof window.BASE_APP_URL !== 'undefined' ? window.BASE_APP_URL : '';
    if (baseUrlJs === '') {
        console.warn("ADVERTENCIA: window.BASE_APP_URL no está definida en el HTML. Las redirecciones del combobox de modo podrían fallar.");
    }

    // Verificar si el textarea del editor existe antes de inicializar CodeMirror
    if (!editorTextarea) {
        console.error("Textarea con ID 'editor' no encontrado. CodeMirror no se inicializará.");
        return; // Salir del script si no hay editor
    }

    // Inicializar CodeMirror para el editor Markdown estándar
    const editorInstance = CodeMirror.fromTextArea(editorTextarea, {
        lineNumbers: true,                                  // Mostrar números de línea
        mode: "markdown",                                   // Establecer modo Markdown
        theme: "dracula",                                   // Tema oscuro
        lineWrapping: true,                                 // Envolver líneas largas
        matchBrackets: true,                                // Resaltar paréntesis/corchetes que coinciden
        placeholder: editorTextarea.getAttribute('placeholder') || "Escribe tu Markdown aquí...", // Tomar placeholder del textarea
        extraKeys: { 
            "Enter": "newlineAndIndentContinueMarkdownList" // Comportamiento inteligente de Enter para listas Markdown
        }
    });

    // Función para ajustar el tamaño y refrescar el editor (ayuda con glitches de renderizado)
    function refreshEditorLayout() {
        if (editorInstance) {
            // Ajustar al 100% del contenedor .editor-body
            // Asegúrate de que .editor-body en tu CSS (base_markdown.css) tenga altura flexible
            // (ej. usando flex-grow: 1 si su padre .editor-container es display:flex y flex-direction:column)
            editorInstance.setSize('100%', '100%'); 
            editorInstance.refresh(); // Forzar un redibujado de CodeMirror
        }
    }

    // Llamar a refresh después de un breve retraso para que el DOM se asiente
    setTimeout(refreshEditorLayout, 50); 

    // Función para actualizar la previsualización del Markdown usando Marked.js
    function updateMarkdownPreview() {
        if (!previewDiv) { // Salir si no hay div de previsualización
            // console.warn("Div de previsualización ('ppt-preview') no encontrado.");
            return; 
        }
        if (typeof marked !== 'undefined' && editorInstance) {
            try {
                // Usar la opción 'sanitize' o 'sanitizer' de marked.js si es necesario para seguridad
                // (aunque por defecto marked.parse() ya debería escapar HTML)
                // const options = { sanitize: true }; // Para versiones antiguas de marked, ahora es con DOMPurify
                previewDiv.innerHTML = marked.parse(editorInstance.getValue());
            } catch (e) {
                console.error("Error al parsear Markdown con marked.js:", e);
                previewDiv.innerHTML = "<p style='color:red;'>Error en la previsualización del Markdown.</p>";
            }
        } else if (typeof marked === 'undefined') {
            // console.warn("Marked.js no está cargado. La previsualización no funcionará.");
            previewDiv.innerHTML = "<p style='color:orange;'>Previsualización no disponible (Marked.js no cargado).</p>";
        }
    }

    // Escuchar cambios en el editor para actualizar la previsualización
    if (editorInstance) {
        editorInstance.on("change", updateMarkdownPreview);
        // Llamada inicial para renderizar cualquier contenido al cargar la página, después del refresh
        setTimeout(updateMarkdownPreview, 100); 
    }

    // Manejador para el combobox de selección de modo (Markdown/Marp)
    if (modeSelect) {
        modeSelect.addEventListener("change", function () {
            const selectedMode = this.value; // 'markdown' o 'marp'
            
            if (selectedMode === "marp") {
                if (baseUrlJs) { // Solo redirigir si baseUrlJs tiene un valor
                    // Redirigir a la URL LIMPIA que maneja tu router para el editor Marp
                    window.location.href = baseUrlJs + '/markdown/marp-editor'; 
                } else {
                    console.error("No se puede redirigir a Marp: BASE_URL no está configurada correctamente en JavaScript.");
                    alert("Error de configuración: No se puede cambiar al editor Marp en este momento.");
                }
            } else if (selectedMode === "markdown") {
                // Ya estamos en la página del editor Markdown Estándar.
                console.log("Modo Markdown Estándar seleccionado (ya en esta página).");
                // Podrías forzar una actualización de la previsualización si fuera necesario.
                if (editorInstance) updateMarkdownPreview();
            }
        });
    }
});