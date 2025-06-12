document.addEventListener('DOMContentLoaded', function() {
    const editorTextareaMarp = document.getElementById('editor-marp');
    const previewDivMarp = document.getElementById('ppt-preview');
    const modeSelectMarp = document.getElementById('mode-select-marp-page');
    const buttonContainer = document.querySelector('.button-container');

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
            const renderEndpoint = `${window.BASE_APP_URL}/markdown/render-marp-preview`;
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
                    ADD_TAGS: ['style'], // Permitir las etiquetas de estilo que Marp necesita
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
                window.location.href = `${window.BASE_APP_URL}/markdown/create`;
            } else if (selectedMode === 'marp') {
                console.log("Modo Marp ya seleccionado.");
            }
        });
    }

    // --- LÓGICA PARA LOS BOTONES DE GENERACIÓN ---
    if (buttonContainer) {
        buttonContainer.addEventListener('click', async function(event) {
            const targetButton = event.target.closest('.generate-btn');
            if (!targetButton) return;

            const format = targetButton.dataset.format;

            if (format === 'pdf') {
                targetButton.disabled = true;
                targetButton.textContent = 'Generando PDF...';

                try {
                    const markdownContent = marpCodeMirrorEditor.getValue();
                    const csrfToken = window.CSRF_TOKEN_MARP_GENERATE;

                    if (!csrfToken) {
                        throw new Error('No se encontró el token de seguridad (CSRF). Por favor, recargue la página.');
                    }

                    const formData = new FormData();
                    formData.append('markdown_content', markdownContent);
                    formData.append('csrf_token_marp_generate', csrfToken);

                    const response = await fetch(`${window.BASE_APP_URL}/markdown/generate-pdf-marp`, {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        window.location.href = result.download_url;
                    } else {
                        throw new Error(result.error || 'Ocurrió un error desconocido en el servidor.');
                    }

                } catch (error) {
                    console.error('Error al generar el PDF:', error);
                    alert(`Error al generar el PDF: ${error.message}`);
                } finally {
                    targetButton.disabled = false;
                    targetButton.textContent = 'Generar PDF';
                }
            } else {
                alert(`La generación de ${format.toUpperCase()} aún no está implementada.`);
            }
        });
    }

    setTimeout(updateMarpPreview, 100);
});
