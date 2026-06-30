/**
 * DMS Upload – Kamera, Drag&Drop, Cropper, Multi-Page Upload
 */

const Upload = {
    images: [],          // { file: File, dataUrl: string, cropped: boolean }
    stream: null,
    facingMode: 'environment',
    cropper: null,
    cropIndex: null,
};

function dataUrlToFile(dataUrl, fileName) {
    const parts = dataUrl.split(',');
    const mime = (parts[0].match(/:(.*?);/) || [])[1] || 'image/jpeg';
    const binary = atob(parts[1] || '');
    const len = binary.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
        bytes[i] = binary.charCodeAt(i);
    }
    return new File([bytes], fileName, { type: mime });
}

// ===== File Selection & Drag/Drop =====

function initDropzone() {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const mobileScanBtn = document.getElementById('mobileScanBtn');
    const mobileScanInput = document.getElementById('mobileScanInput');

    dropzone.addEventListener('click', () => fileInput.click());

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
        e.target.value = '';
    });

    if (mobileScanInput) {
        const consumeMobileScanInputFiles = () => {
            const files = mobileScanInput.files;
            if (!files || files.length === 0) return;
            handleFiles(files);
            mobileScanInput.value = '';
        };

        if (mobileScanBtn) {
            mobileScanBtn.addEventListener('click', (e) => {
                e.preventDefault();
                mobileScanInput.click();
            });
        }

        mobileScanInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
            e.target.value = '';
        });

        mobileScanInput.addEventListener('input', consumeMobileScanInputFiles);
        window.addEventListener('focus', consumeMobileScanInputFiles);
        window.addEventListener('pageshow', consumeMobileScanInputFiles);
    }
}

function handleFiles(fileList) {
    const files = Array.from(fileList || []);

    if (files.length === 0) {
        showToast('Kein Beleg übernommen. Bitte erneut scannen.', 'error');
        return;
    }

    let importedCount = 0;

    for (const file of files) {
        const mimeType = (file.type || '').toLowerCase();
        const validMime = mimeType.startsWith('image/');
        const validExt = /\.(jpe?g|png|webp|heic|heif)$/i.test(file.name || '');
        const unknownButUsable = !mimeType && file.size > 0;
        if (!validMime && !validExt && !unknownButUsable) {
            showToast(`"${file.name}" ist kein unterstütztes Bildformat`, 'error');
            continue;
        }
        if (!file.size || file.size <= 0) {
            showToast(`"${file.name || 'Bild'}" konnte nicht gelesen werden`, 'error');
            continue;
        }
        if (file.size > 20 * 1024 * 1024) {
            showToast(`"${file.name}" ist zu groß (max. 20 MB)`, 'error');
            continue;
        }

        Upload.images.push({
            file: file,
            dataUrl: URL.createObjectURL(file),
            cropped: false,
        });
        importedCount++;
    }

    if (importedCount > 0) {
        renderPreviews();
        updateFormVisibility();
        showToast(`${importedCount} Beleg(e) übernommen`, 'success');
    }
}

// ===== Camera =====

function initCamera() {
    const toggle = document.getElementById('cameraToggle');
    const container = document.getElementById('cameraContainer');
    const captureBtn = document.getElementById('captureBtn');
    const switchBtn = document.getElementById('cameraSwitchBtn');
    const closeBtn = document.getElementById('cameraCloseBtn');

    if (!toggle) return;

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        toggle.style.display = 'none';
        showToast('Live-Kamera wird von diesem Browser nicht unterstützt. Nutze "Beleg direkt scannen (Handy)".', 'error');
        return;
    }

    if (!window.isSecureContext) {
        showToast('Für Live-Kamera ist HTTPS erforderlich. Nutze alternativ "Beleg direkt scannen (Handy)".', 'error');
        return;
    }

    toggle.addEventListener('click', async () => {
        if (container.classList.contains('active')) {
            stopCamera();
            return;
        }
        await startCamera();
    });

    captureBtn.addEventListener('click', capturePhoto);
    switchBtn.addEventListener('click', switchCamera);
    closeBtn.addEventListener('click', stopCamera);
}

async function startCamera() {
    const container = document.getElementById('cameraContainer');
    const video = document.getElementById('cameraVideo');
    const toggle = document.getElementById('cameraToggle');

    const facing = { facingMode: Upload.facingMode };
    const constraintsList = [
        { video: { ...facing, width: { ideal: 1920 }, height: { ideal: 1080 } } },
        { video: facing },
        { video: true },
    ];

    let lastErr = null;
    for (const constraints of constraintsList) {
        try {
            Upload.stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = Upload.stream;
            video.playsInline = true;
            video.muted = true;
            video.setAttribute('playsinline', 'true');
            video.setAttribute('muted', 'true');
            await video.play().catch(() => {});
            container.classList.add('active');
            toggle.innerHTML = '<span class="material-icons-round">close</span> Kamera schließen';
            return;
        } catch (err) {
            lastErr = err;
        }
    }
    if (lastErr && (lastErr.name === 'NotAllowedError' || lastErr.name === 'SecurityError')) {
        showToast('Kamera blockiert. Browser-Berechtigung auf "Zulassen" setzen oder "Beleg direkt scannen (Handy)" nutzen.', 'error');
        return;
    }
    showToast('Kamera konnte nicht geöffnet werden: ' + (lastErr && lastErr.message ? lastErr.message : 'Unbekannter Fehler'), 'error');
}

function stopCamera() {
    const container = document.getElementById('cameraContainer');
    const video = document.getElementById('cameraVideo');
    const toggle = document.getElementById('cameraToggle');

    if (Upload.stream) {
        Upload.stream.getTracks().forEach((t) => t.stop());
        Upload.stream = null;
    }
    video.srcObject = null;
    container.classList.remove('active');
    toggle.innerHTML = '<span class="material-icons-round">photo_camera</span> Kamera öffnen';
}

async function switchCamera() {
    Upload.facingMode = Upload.facingMode === 'environment' ? 'user' : 'environment';
    stopCamera();
    await startCamera();
}

function capturePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const ctx = canvas.getContext('2d');

    if (!Upload.stream || video.readyState < 2 || !video.videoWidth || !video.videoHeight) {
        showToast('Kamera ist noch nicht bereit. Bitte kurz warten und erneut auslösen.', 'error');
        return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);

    canvas.toBlob((blob) => {
        const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
        const file = blob
            ? new File([blob], `foto_${Date.now()}.jpg`, { type: 'image/jpeg' })
            : dataUrlToFile(dataUrl, `foto_${Date.now()}.jpg`);
        Upload.images.push({
            file: file,
            dataUrl: dataUrl,
            cropped: false,
        });
        renderPreviews();
        updateFormVisibility();
        showToast('Foto aufgenommen', 'success');
    }, 'image/jpeg', 0.92);
}

// ===== Preview List =====

function renderPreviews() {
    const list = document.getElementById('previewList');
    if (!list) return;

    list.innerHTML = Upload.images.map((img, i) => `
        <div class="dms-preview-item" data-index="${i}">
            <img src="${img.dataUrl}" alt="Seite ${i + 1}" draggable="true">
            <span class="dms-preview-item-number">${i + 1}</span>
            <button class="dms-preview-item-remove" onclick="removeImage(${i})" title="Entfernen">&times;</button>
            <button class="dms-preview-item-crop" onclick="openCropModal(${i})" title="Zuschneiden">
                <span class="material-icons-round" style="font-size:0.875rem">crop</span>
            </button>
        </div>
    `).join('');

    initDragSort();
}

function removeImage(index) {
    const removed = Upload.images[index];
    if (removed && typeof removed.dataUrl === 'string' && removed.dataUrl.startsWith('blob:')) {
        URL.revokeObjectURL(removed.dataUrl);
    }
    Upload.images.splice(index, 1);
    renderPreviews();
    updateFormVisibility();
}

function updateFormVisibility() {
    const form = document.getElementById('uploadForm');
    if (form) {
        form.style.display = Upload.images.length > 0 ? '' : 'none';
    }
}

// ===== Drag & Drop Sorting =====

function initDragSort() {
    const items = document.querySelectorAll('.dms-preview-item img');
    let dragIndex = null;

    items.forEach((img, i) => {
        const item = img.closest('.dms-preview-item');

        item.addEventListener('dragstart', (e) => {
            dragIndex = i;
            item.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
        });

        item.addEventListener('dragend', () => {
            item.style.opacity = '';
        });

        item.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            item.style.borderColor = 'var(--primary)';
        });

        item.addEventListener('dragleave', () => {
            item.style.borderColor = '';
        });

        item.addEventListener('drop', (e) => {
            e.preventDefault();
            item.style.borderColor = '';
            if (dragIndex !== null && dragIndex !== i) {
                const moved = Upload.images.splice(dragIndex, 1)[0];
                Upload.images.splice(i, 0, moved);
                renderPreviews();
            }
            dragIndex = null;
        });
    });
}

// ===== Cropper =====

function openCropModal(index) {
    Upload.cropIndex = index;
    const img = document.getElementById('cropImage');
    img.src = Upload.images[index].dataUrl;

    openModal('cropModal');

    setTimeout(() => {
        if (Upload.cropper) {
            Upload.cropper.destroy();
        }
        Upload.cropper = new Cropper(img, {
            viewMode: 1,
            autoCropArea: 1,
            responsive: true,
            background: false,
        });
    }, 200);
}

function closeCropModal() {
    if (Upload.cropper) {
        Upload.cropper.destroy();
        Upload.cropper = null;
    }
    Upload.cropIndex = null;
    closeModal('cropModal');
}

function initCropControls() {
    const rotateBtn = document.getElementById('cropRotateBtn');
    const applyBtn = document.getElementById('cropApplyBtn');

    if (rotateBtn) {
        rotateBtn.addEventListener('click', () => {
            if (Upload.cropper) Upload.cropper.rotate(90);
        });
    }

    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            if (!Upload.cropper || Upload.cropIndex === null) return;

            const canvas = Upload.cropper.getCroppedCanvas({
                maxWidth: 3000,
                maxHeight: 4000,
                imageSmoothingQuality: 'high',
            });

            canvas.toBlob((blob) => {
                const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
                const file = blob
                    ? new File([blob], Upload.images[Upload.cropIndex].file.name, { type: 'image/jpeg' })
                    : dataUrlToFile(dataUrl, Upload.images[Upload.cropIndex].file.name);
                Upload.images[Upload.cropIndex] = {
                    file: file,
                    dataUrl: dataUrl,
                    cropped: true,
                };
                renderPreviews();
                closeCropModal();
                showToast('Bild zugeschnitten', 'success');
            }, 'image/jpeg', 0.92);
        });
    }
}

// ===== Form Submit =====

function initUploadForm() {
    const form = document.getElementById('uploadForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (Upload.images.length === 0) {
            showToast('Bitte mindestens ein Bild hinzufügen', 'error');
            return;
        }

        const title = document.getElementById('docTitle').value.trim();
        if (!title) {
            showToast('Bitte einen Titel eingeben', 'error');
            document.getElementById('docTitle').focus();
            return;
        }

        const receiptDate = document.getElementById('docReceiptDate').value;
        if (!receiptDate) {
            showToast('Bitte ein Belegdatum auswählen', 'error');
            document.getElementById('docReceiptDate').focus();
            return;
        }

        const formData = new FormData();
        formData.append('title', title);
        formData.append('receipt_date', receiptDate);
        formData.append('description', document.getElementById('docDescription').value.trim());
        formData.append('category_id', document.getElementById('docCategory').value);

        Upload.images.forEach((img) => {
            formData.append('images[]', img.file);
        });

        const submitBtn = document.getElementById('submitBtn');
        const progress = document.getElementById('uploadProgress');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="dms-spinner"></span> Wird verarbeitet...';
        progress.classList.add('active');

        try {
            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    progressFill.style.width = pct + '%';
                    progressText.textContent = pct < 100
                        ? `Hochladen: ${pct}%`
                        : 'PDF wird erstellt...';
                }
            });

            const result = await new Promise((resolve, reject) => {
                xhr.onload = () => {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                            resolve(data);
                        } else {
                            reject(new Error(data.error || 'Upload fehlgeschlagen'));
                        }
                    } catch {
                        reject(new Error('Ungültige Server-Antwort'));
                    }
                };
                xhr.onerror = () => reject(new Error('Netzwerkfehler'));
                xhr.open('POST', 'api/upload.php');
                xhr.send(formData);
            });

            progressFill.style.width = '100%';
            progressText.textContent = 'Fertig!';

            showToast(`"${result.document.title}" gespeichert (${result.document.page_count} Seiten)`, 'success');

            setTimeout(() => {
                window.location.href = './';
            }, 1000);

        } catch (err) {
            showToast('Fehler: ' + err.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="material-icons-round" style="font-size:1.125rem">picture_as_pdf</span> Als PDF speichern';
            progress.classList.remove('active');
        }
    });
}

// ===== Init =====

document.addEventListener('DOMContentLoaded', () => {
    initDropzone();
    initCamera();
    initCropControls();
    initUploadForm();
});
