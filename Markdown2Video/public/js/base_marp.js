// public/js/base_marp.js
document.addEventListener('DOMContentLoaded', function() {
    const editorTextareaMarp = document.getElementById('editor-marp');
    const previewDivMarp = document.getElementById('ppt-preview');
    const modeSelectMarp = document.getElementById('mode-select-marp-page');

    let marpDebounceTimer;

    if (!editorTextareaMarp) {
        console.error("Textarea #editor-marp no encontrado. Editor Marp no se inicializará.");
        return; 
    }

    const marpCodeMirrorEditor = CodeMirror.fromTextArea(editorTextareaMarp, {
        mode: 'markdown',
        theme: 'dracula',
        lineNumbers: true,
        lineWrapping: true,
        matchBrackets: true,
        placeholder: editorTextareaMarp.getAttribute('placeholder') || "Escribe tu presentación Marp aquí...",
        extraKeys: { "Enter": "newlineAndIndentContinueMarkdownList" }
    });

    function refreshMarpEditorLayout() {
        marpCodeMirrorEditor.setSize('100%', '100%');
        marpCodeMirrorEditor.refresh();
    }
    setTimeout(refreshMarpEditorLayout, 50);

    async function updateMarpPreview() {
        if (!previewDivMarp || !marpCodeMirrorEditor) return;
        const markdownText = marpCodeMirrorEditor.getValue();
        previewDivMarp.innerHTML = '<p>Generando vista previa Marp...</p>';

        try {
            const renderEndpoint = '/markdown/render-marp-preview';
            const requestBody = `markdown=${encodeURIComponent(markdownText)}`;
            const headers = { 'Content-Type': 'application/x-www-form-urlencoded' };

            const response = await fetch(renderEndpoint, { method: 'POST', headers: headers, body: requestBody });

            if (!response.ok) {
                let errorDetail = await response.text();
                try {
                    const errorJson = JSON.parse(errorDetail);
                    errorDetail = errorJson.details || errorJson.error || errorDetail;
                } catch (e) { /* No era JSON */ }
                throw new Error(`Error del servidor: ${response.status} - ${errorDetail}`);
            }

            const htmlResult = await response.text();

            if (typeof DOMPurify !== 'undefined') {
                const cleanHtml = DOMPurify.sanitize(htmlResult, { 
                    USE_PROFILES: { html: true },
                    // Configuraciones específicas de Marp pueden agregarse aquí si es necesario
                });
                previewDivMarp.innerHTML = cleanHtml;
            } else {
                console.warn("DOMPurify no está cargado. El HTML se insertará sin saneamiento.");
                previewDivMarp.innerHTML = htmlResult;
            }

        } catch (error) {
            console.error("Error al generar vista previa Marp:", error);
            previewDivMarp.innerHTML = '';
            const errorParagraph = document.createElement('p');
            errorParagraph.style.color = 'red';
            errorParagraph.textContent = `Error al cargar la previsualización Marp: ${error.message}`;
            previewDivMarp.appendChild(errorParagraph);
        }
    }

    marpCodeMirrorEditor.on('change', () => {
        clearTimeout(marpDebounceTimer);
        marpDebounceTimer = setTimeout(updateMarpPreview, 700);
    });

    if (modeSelectMarp) {
        modeSelectMarp.addEventListener('change', function () {
            const selectedMode = this.value;
            if (selectedMode === 'markdown') {
                window.location.href = '/markdown/create';
            } else if (selectedMode === 'marp') {
                console.log("Modo Marp ya seleccionado.");
            }
        });
    }

    setTimeout(updateMarpPreview, 100);

    // --- NUEVA FUNCIONALIDAD: Generar Archivo ---
    const buttonContainer = document.querySelector('.button-container');

    async function generateFile(format, button) {
        const originalButtonText = button.innerHTML;
        button.innerHTML = 'Generando...';
        button.disabled = true;

        const markdownContent = marpCodeMirrorEditor.getValue();

        try {
            const response = await fetch(`/markdown/generate-marp-${format}`,
             {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ markdown: markdownContent })
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ error: 'Error desconocido del servidor' }));
                throw new Error(errorData.error || 'La respuesta del servidor no fue OK.');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `presentacion.${format}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

        } catch (error) {
            console.error(`Error al generar ${format.toUpperCase()}:`, error);
            alert(`No se pudo generar el archivo ${format.toUpperCase()}: ${error.message}`);
        } finally {
            button.innerHTML = originalButtonText;
            button.disabled = false;
        }
    }

    if (buttonContainer) {
        buttonContainer.addEventListener('click', function(event) {
            const button = event.target.closest('.generate-btn');
            if (button) {
                event.preventDefault(); // Prevenir cualquier acción por defecto
                const format = button.dataset.format;
                if (format === 'pdf') { // Por ahora, solo manejamos PDF
                    generateFile(format, button);
                } else {
                    alert(`La generación de ${format.toUpperCase()} aún no está implementada.`);
                }
            }
        });
    }
});
