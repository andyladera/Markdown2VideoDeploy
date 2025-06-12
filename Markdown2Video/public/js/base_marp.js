document.addEventListener('DOMContentLoaded', function() {
    const editorTextareaMarp = document.getElementById('editor-marp');
    const previewDivMarp = document.getElementById('ppt-preview');
    const modeSelectMarp = document.getElementById('mode-select-marp-page');
    const generateButtons = document.querySelectorAll('.generate-btn');
    const generatingOverlay = document.getElementById('generating-overlay');

    let marpDebounceTimer;
    let marpCodeMirrorEditor;

    if (!editorTextareaMarp) {
        console.error("Textarea #editor-marp no encontrado. Editor Marp no se inicializará.");
        return;
    }

    marpCodeMirrorEditor = CodeMirror.fromTextArea(editorTextareaMarp, {
        mode: 'markdown',
        theme: 'dracula',
        lineNumbers: true,
        lineWrapping: true,
        matchBrackets: true,
        placeholder: editorTextareaMarp.getAttribute('placeholder') || "Escribe tu presentación Marp aquí...",
        extraKeys: { "Enter": "newlineAndIndentContinueMarkdownList" }
    });

    function refreshMarpEditorLayout() {
        if (marpCodeMirrorEditor) {
            marpCodeMirrorEditor.setSize('100%', '100%');
            marpCodeMirrorEditor.refresh();
        }
    }
    setTimeout(refreshMarpEditorLayout, 50);

    async function updateMarpPreview() {
        if (!previewDivMarp || !marpCodeMirrorEditor) return;
        const markdownText = marpCodeMirrorEditor.getValue();
        previewDivMarp.innerHTML = '<p>Generando vista previa Marp...</p>';

        try {
            const renderEndpoint = `${BASE_URL}/markdown/render-marp-preview`;
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

    if (marpCodeMirrorEditor) {
        marpCodeMirrorEditor.on('change', () => {
            clearTimeout(marpDebounceTimer);
            marpDebounceTimer = setTimeout(updateMarpPreview, 700);
        });
    }

    if (modeSelectMarp) {
        modeSelectMarp.addEventListener('change', function() {
            const selectedMode = this.value;
            if (selectedMode === 'markdown') {
                window.location.href = `${BASE_URL}/markdown/create`;
            }
        });
    }

    generateButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const format = this.dataset.format;
            if (!format) {
                alert('Formato de archivo no especificado.');
                return;
            }

            if (format !== 'pdf') {
                alert(`La generación de ${format.toUpperCase()} aún no está implementada.`);
                return;
            }

            const markdownContent = marpCodeMirrorEditor.getValue();
            if (!markdownContent.trim()) {
                alert('No hay contenido en el editor para generar el archivo.');
                return;
            }

            if (generatingOverlay) generatingOverlay.style.display = 'flex';

            try {
                const generateEndpoint = `${BASE_URL}/markdown/generate-file`;
                const csrfToken = this.dataset.csrf;

                const formData = new FormData();
                formData.append('markdown_content', markdownContent);
                formData.append('format', format);
                formData.append('csrf_token', csrfToken);

                const response = await fetch(generateEndpoint, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = BASE_URL + result.downloadPageUrl;
                } else {
                    alert('Error al generar el archivo: ' + (result.error || 'Error desconocido.'));
                }

            } catch (error) {
                console.error('Error en la solicitud de generación:', error);
                alert('Ocurrió un error de red al intentar generar el archivo.');
            } finally {
                if (generatingOverlay) generatingOverlay.style.display = 'none';
            }
        });
    });

    if (marpCodeMirrorEditor && previewDivMarp) {
        setTimeout(updateMarpPreview, 100);
    }
});
