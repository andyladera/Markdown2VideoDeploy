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

    // Manejar clics en los botones de generación (PDF, PPT, etc.)
    const generateButtons = document.querySelectorAll('.generate-btn');
    generateButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const format = this.dataset.format;
            if (!marpCodeMirrorEditor) {
                alert('El editor Marp no está inicializado.');
                return;
            }
            const markdownContent = marpCodeMirrorEditor.getValue();

            if (!markdownContent.trim()) {
                alert('El editor está vacío. Escribe algo de Markdown para Marp.');
                return;
            }

            // Mostrar algún indicador de carga
            this.disabled = true;
            this.textContent = `Generando ${format.toUpperCase()}...`;
            
            const originalButtonText = this.textContent; // Guardar para restaurar

            try {
                const generateEndpoint = `${baseUrl}/markdown/generate-marp-file`; // Nuevo endpoint
                const requestBody = new FormData();
                requestBody.append('markdown', markdownContent);
                requestBody.append('format', format);
                // Añadir token CSRF si está configurado globalmente y es necesario para este endpoint
                // if (typeof window.CSRF_TOKEN_MARP_GENERATE !== 'undefined') {
                //     requestBody.append('csrf_token', window.CSRF_TOKEN_MARP_GENERATE);
                // }

                const response = await fetch(generateEndpoint, {
                    method: 'POST',
                    body: requestBody 
                    // headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN_MARP_GENERATE } // Si se envía como header
                });

                if (!response.ok) {
                    let errorDetail = 'Error desconocido del servidor.';
                    try {
                        const errorData = await response.json();
                        errorDetail = errorData.error || errorData.message || JSON.stringify(errorData);
                    } catch (e) {
                        errorDetail = await response.text();
                    }
                    throw new Error(`Error del servidor (${response.status}): ${errorDetail}`);
                }

                const result = await response.json();

                if (result.success) {
                    if (result.downloadPageUrl) {
                        // Redirigir a la página de descarga que proporcionará el archivo
                        window.location.href = baseUrl + result.downloadPageUrl;
                    } else if (result.message) {
                        alert(result.message); // Para formatos que no tienen descarga directa (ej. solo mensaje de éxito)
                    }
                } else {
                    throw new Error(result.error || 'Falló la generación del archivo.');
                }

            } catch (error) {
                console.error(`Error al generar ${format.toUpperCase()}:`, error);
                alert(`Hubo un error al generar el archivo ${format.toUpperCase()}:\n${error.message}`);
            } finally {
                // Restaurar el botón
                this.disabled = false;
                // El texto del botón podría haber cambiado si la página se recarga, 
                // pero si no, restaurarlo. Si hay redirección, esto no se ejecutará para ese botón.
                if(this.dataset.format === 'pdf') this.textContent = 'Generar PDF';
                else if(this.dataset.format === 'ppt') this.textContent = 'Generar PPT';
                else if(this.dataset.format === 'mp4') this.textContent = 'Generar Video MP4';
                else if(this.dataset.format === 'html') this.textContent = 'Generar HTML';
            }
        });
    });
});
