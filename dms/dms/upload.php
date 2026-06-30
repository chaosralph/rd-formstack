<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/helpers.php';

checkAuth();

$pageTitle = 'Neues Dokument';
$currentPage = 'upload';

$db = Database::getConnection();
$categories = $db->query('SELECT * FROM dms_categories ORDER BY sort_order, name')->fetchAll(PDO::FETCH_ASSOC);
$uploadJsPath = __DIR__ . '/assets/js/upload.js';
$uploadJsVersion = file_exists($uploadJsPath) ? (string)filemtime($uploadJsPath) : (string)time();

$extraCss = ['https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css'];
$extraJs = [
    'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js',
    SITE_URL . '/assets/js/upload.js?v=' . $uploadJsVersion,
];

require __DIR__ . '/templates/header.php';
?>

<div class="dms-upload-container">
    <div class="dms-upload-header">
        <h1>
            <span class="material-icons-round" style="vertical-align:middle;margin-right:0.5rem;color:var(--primary-light)">add_a_photo</span>
            Neues Dokument erstellen
        </h1>
        <p>Scanne Belege direkt im Browser oder lade Bilder hoch – sie werden automatisch als PDF gespeichert.</p>
    </div>

    <!-- Camera Section -->
    <div class="dms-camera-section">
        <button class="btn btn-secondary btn-lg" id="cameraToggle" style="width:100%;justify-content:center;margin-bottom:1rem">
            <span class="material-icons-round">photo_camera</span>
            Kamera öffnen
        </button>
        <label class="btn btn-secondary btn-lg" id="mobileScanBtn" for="mobileScanInput" style="width:100%;justify-content:center;margin-bottom:1rem;cursor:pointer">
            <span class="material-icons-round">document_scanner</span>
            Beleg direkt scannen (Handy)
        </label>
        <input type="file" id="mobileScanInput" accept="image/*,.heic,.heif" capture="environment" onclick="this.value=''" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0">
        <div class="dms-camera-container" id="cameraContainer">
            <video class="dms-camera-video" id="cameraVideo" autoplay playsinline></video>
            <div class="dms-camera-controls">
                <button class="dms-camera-btn" id="cameraSwitchBtn" title="Kamera wechseln">
                    <span class="material-icons-round">flip_camera_ios</span>
                </button>
                <button class="dms-camera-btn capture" id="captureBtn" title="Foto aufnehmen">
                    <span class="material-icons-round">camera</span>
                </button>
                <button class="dms-camera-btn" id="cameraCloseBtn" title="Kamera schließen">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
        </div>
        <canvas id="cameraCanvas" style="display:none"></canvas>
    </div>

    <!-- Drop Zone -->
    <div class="dms-dropzone" id="dropzone">
        <div class="dms-dropzone-icon">
            <span class="material-icons-round" style="font-size:inherit">cloud_upload</span>
        </div>
        <h3>Bilder hierher ziehen</h3>
        <p>oder klicke um Dateien auszuwählen</p>
        <p style="font-size:0.75rem;color:var(--text-muted)">JPG, PNG, WebP – max. <?= formatFileSize(MAX_FILE_SIZE) ?> pro Datei</p>
        <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.heif" multiple style="display:none">
    </div>

    <!-- Image Preview List -->
    <div class="dms-preview-list" id="previewList"></div>

    <!-- Document Form -->
    <form id="uploadForm" style="display:none">
        <div class="dms-form-row">
            <div class="dms-form-group">
                <label class="dms-form-label" for="docTitle">Titel *</label>
                <input type="text" class="dms-form-input" id="docTitle" name="title" placeholder="z.B. Rechnung Telekom März 2026" required>
            </div>
            <div class="dms-form-group">
                <label class="dms-form-label" for="docReceiptDate">Belegdatum *</label>
                <input type="date" class="dms-form-input" id="docReceiptDate" name="receipt_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="dms-form-group">
                <label class="dms-form-label" for="docCategory">Kategorie</label>
                <select class="dms-form-select" id="docCategory" name="category_id">
                    <option value="">Keine Kategorie</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="dms-form-group">
            <label class="dms-form-label" for="docDescription">Beschreibung</label>
            <textarea class="dms-form-textarea" id="docDescription" name="description" placeholder="Optionale Beschreibung..."></textarea>
        </div>

        <div class="dms-upload-progress" id="uploadProgress">
            <div class="dms-progress-bar">
                <div class="dms-progress-fill" id="progressFill"></div>
            </div>
            <div class="dms-progress-text" id="progressText">Wird hochgeladen...</div>
        </div>

        <div style="display:flex;gap:0.75rem;justify-content:flex-end">
            <a href="<?= SITE_URL ?>/" class="btn btn-secondary">
                <span class="material-icons-round" style="font-size:1rem">arrow_back</span>
                Abbrechen
            </a>
            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                <span class="material-icons-round" style="font-size:1.125rem">picture_as_pdf</span>
                Als PDF speichern
            </button>
        </div>
    </form>
</div>

<!-- Crop Modal -->
<div class="dms-modal-overlay" id="cropModal">
    <div class="dms-modal crop-modal">
        <div class="dms-modal-header">
            <h3 class="dms-modal-title">Bild zuschneiden</h3>
            <button class="dms-modal-close" onclick="closeCropModal()">&times;</button>
        </div>
        <div class="dms-modal-body">
            <div class="dms-crop-container">
                <img id="cropImage" src="" alt="Zuschneiden">
            </div>
        </div>
        <div class="dms-modal-footer">
            <button class="btn btn-secondary" onclick="closeCropModal()">Abbrechen</button>
            <div style="display:flex;gap:0.5rem">
                <button class="btn btn-secondary" id="cropRotateBtn" title="Drehen">
                    <span class="material-icons-round" style="font-size:1rem">rotate_right</span>
                    Drehen
                </button>
                <button class="btn btn-primary" id="cropApplyBtn">
                    <span class="material-icons-round" style="font-size:1rem">crop</span>
                    Zuschneiden
                </button>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>
