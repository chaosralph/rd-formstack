<?php
/**
 * Dashboard - Belegverwaltung
 */
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RD Formstack Solutions</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Fonts werden lokal geladen (DSGVO-konform) -->
</head>
<body class="dashboard-page">
    <!-- Navigation -->
    <nav class="dashboard-nav">
        <div class="container">
            <a href="index.php" class="nav-brand">
                <h2>
                    <span class="logo-rd">RD</span> <span class="logo-formstack">Formstack</span>
                    <span class="logo-subtitle">Form Solutions</span>
                </h2>
            </a>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="dms/dms/">DMS</a></li>
                <li><a href="references.php">Referenzen</a></li>
                <li><a href="profile.php">👤 Profil</a></li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li><a href="admin/settings.php">⚙️ Verwaltung</a></li>
                <?php endif; ?>
                <li><a href="#" id="logoutBtn">Abmelden</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1>Belegverwaltung</h1>
                <button class="btn btn-primary" id="uploadBtn">
                    <span>📤</span> Beleg hochladen
                </button>
            </div>

            <!-- Upload Modal -->
            <div id="uploadModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Beleg hochladen</h2>
                        <button class="modal-close" id="closeModal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="upload-options">
                            <div class="upload-option" id="cameraOption">
                                <div class="upload-icon">📷</div>
                                <h3>Kamera</h3>
                                <p>Foto mit Kamera aufnehmen</p>
                                <input type="file" id="cameraInput" accept="image/*" capture="environment" style="display: none;">
                            </div>
                            <div class="upload-option" id="fileOption">
                                <div class="upload-icon">📁</div>
                                <h3>Datei auswählen</h3>
                                <p>Bild oder PDF hochladen</p>
                                <input type="file" id="fileInput" accept="image/*,application/pdf" style="display: none;">
                            </div>
                        </div>
                        <div id="uploadPreview" class="upload-preview" style="display: none;">
                            <img id="previewImage" src="" alt="Vorschau">
                            <div class="upload-category" style="margin: 1rem 0;">
                                <label for="uploadCategory" style="display:block; margin-bottom:0.5rem; font-weight:600; font-size:0.875rem; color:var(--text-light);">Kategorie (optional, sonst automatisch):</label>
                                <select id="uploadCategory" style="width:100%; padding:0.6rem; border:1px solid var(--border-color); border-radius:var(--radius); font-size:0.9rem;">
                                    <option value="">🤖 Automatisch erkennen</option>
                                    <option value="ausgaben">📤 Ausgabe (Eingangsrechnung, Quittung)</option>
                                    <option value="einnahmen">📥 Einnahme (Ausgangsrechnung)</option>
                                    <option value="sonstige">📋 Sonstige</option>
                                </select>
                            </div>
                            <div class="upload-actions">
                                <button class="btn btn-secondary" id="cancelUpload">Abbrechen</button>
                                <button class="btn btn-primary" id="confirmUpload">📤 Hochladen & Erkennen</button>
                            </div>
                        </div>
                        <div id="uploadProgress" class="upload-progress" style="display: none;">
                            <div class="progress-bar">
                                <div class="progress-fill" id="progressFill"></div>
                            </div>
                            <p id="progressText">Wird verarbeitet...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="dashboard-filters">
                <select id="categoryFilter" class="filter-select">
                    <option value="">Alle Kategorien</option>
                    <option value="einnahmen">Einnahmen</option>
                    <option value="ausgaben">Ausgaben</option>
                    <option value="sonstige">Sonstige</option>
                </select>
                <select id="statusFilter" class="filter-select">
                    <option value="">Alle Status</option>
                    <option value="pending">Ausstehend</option>
                    <option value="processed">Verarbeitet</option>
                    <option value="booked">Gebucht</option>
                    <option value="archived">Archiviert</option>
                </select>
            </div>

            <!-- Receipts List -->
            <div id="receiptsList" class="receipts-list">
                <div class="loading">Lade Belege...</div>
            </div>
        </div>
    </main>

    <!-- Receipt Detail Modal -->
    <div id="receiptModal" class="modal" style="display: none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Beleg-Details</h2>
                <button class="modal-close" id="closeReceiptModal">&times;</button>
            </div>
            <div class="modal-body" id="receiptDetails">
                <!-- Wird dynamisch gefüllt -->
            </div>
        </div>
    </div>

    <script src="assets/js/dashboard.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/auth.js?v=<?php echo time(); ?>"></script>
</body>
</html>
