document.addEventListener('DOMContentLoaded', () => {
    // --- ELEMENTOS DEL DOM ---
    const editorTextArea = document.getElementById('editor-marp');
    const previewDiv = document.getElementById('ppt-preview');
    const generateButtons = document.querySelectorAll('.generate-btn');

    // --- VALIDACIÓN INICIAL ---
    if (!editorTextArea || !previewDiv) {
        console.error('Error crítico: No se encontró el editor o el contenedor de la vista previa.');
        return;
    }

    // --- INICIALIZACIÓN DEL EDITOR CODEMIRROR ---
    const editor = CodeMirror.fromTextArea(editorTextArea, {
        mode: 'markdown',
        theme: 'dracula',
        lineNumbers: true,
        lineWrapping: true,
        placeholder: 'Escribe tu presentación Marp aquí...',
        extraKeys: { "Enter": "newlineAndIndentContinueMarkdownList" }
    });

    // --- LÓGICA DE VISTA PREVIA EN TIEMPO REAL ---
    let debounceTimer;
    const updatePreview = () => {
        const markdownContent = editor.getValue();
        // No enviar si está vacío
        if (!markdownContent.trim()) {
            previewDiv.innerHTML = '<p>Escribe en el editor para ver la vista previa...</p>';
            return;
        }

        const formData = new FormData();
        formData.append('markdown', markdownContent);

        fetch(`${window.BASE_APP_URL}/markdown/render-marp-preview`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error del servidor: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            previewDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Error al actualizar la vista previa:', error);
            previewDiv.innerHTML = `<p style="color: red;">Error al cargar la vista previa: ${error.message}</p>`;
        });
    };

    // Disparar la actualización con un retraso (debounce) para no sobrecargar el servidor
    editor.on('change', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updatePreview, 500); // 500ms de retraso
    });

    // --- LÓGICA PARA BOTONES DE GENERACIÓN ---
    generateButtons.forEach(button => {
        button.addEventListener('click', async (event) => {
            const format = event.target.dataset.format;

            if (format !== 'pdf') {
                alert(`La generación de ${format.toUpperCase()} aún no está implementada.`);
                return;
            }

            const markdownContent = editor.getValue();
            if (!markdownContent.trim()) {
                alert('El editor está vacío. Escribe algo para generar el PDF.');
                return;
            }

            const btn = event.target;
            btn.disabled = true;
            btn.textContent = 'Generando...';

            try {
                const response = await fetch(`${window.BASE_APP_URL}/markdown/generate-pdf`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ markdown: markdownContent })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: 'No se pudo obtener el detalle del error.' }));
                    throw new Error(`Error del servidor: ${response.status}. ${errorData.message}`);
                }

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'presentacion.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

            } catch (error) {
                console.error('Error al generar el PDF:', error);
                alert(`Hubo un error al intentar generar el PDF: ${error.message}`);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Generar PDF';
            }
        });
    });

    // Forzar una primera actualización al cargar la página
    updatePreview();
});
