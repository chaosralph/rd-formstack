<?php
/**
 * Admin-Verwaltung – Einstellungen
 */
session_start();

// Nur Admin darf hier rein
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../classes/Settings.php';
$settings = Settings::getInstance();

$success = '';
$error = '';

// Formular verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $data = [];
    
    foreach ($_POST as $key => $value) {
        if ($key === 'save_settings' || $key === 'active_tab') continue;
        // Nur gültige setting_keys akzeptieren
        if (preg_match('/^[a-z_]+$/', $key)) {
            $data[$key] = trim($value);
        }
    }
    
    // Boolean-Felder: wenn nicht im POST → "0"
    $booleanFields = ['company_kleinunternehmer'];
    foreach ($booleanFields as $field) {
        if (!isset($_POST[$field])) {
            $data[$field] = '0';
        }
    }
    
    if ($settings->setMultiple($data)) {
        $settings->reload();
        $success = 'Einstellungen erfolgreich gespeichert!';
    } else {
        $error = 'Fehler beim Speichern der Einstellungen.';
    }
}

$groups = $settings->getGroups();
$activeTab = $_POST['active_tab'] ?? $_GET['tab'] ?? 'company';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verwaltung - <?php echo htmlspecialchars($settings->appName()); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .admin-header h1 {
            font-size: 1.75rem;
            color: var(--text-color);
        }
        .admin-nav {
            display: flex;
            gap: 0.5rem;
        }
        .admin-nav a {
            padding: 0.5rem 1rem;
            background: var(--bg-secondary);
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--text-color);
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        .admin-nav a:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Tabs */
        .settings-tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 2rem;
            overflow-x: auto;
        }
        .settings-tab {
            padding: 0.875rem 1.25rem;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 0.9rem;
            color: var(--text-light);
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            white-space: nowrap;
            transition: all 0.2s;
        }
        .settings-tab:hover {
            color: var(--primary-color);
        }
        .settings-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            font-weight: 600;
        }
        .settings-tab .tab-icon {
            margin-right: 0.5rem;
        }

        /* Tab Content */
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }

        /* Form */
        .settings-form {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 2rem;
        }
        .settings-group-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }
        .form-row {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 1rem;
            align-items: start;
            margin-bottom: 1.25rem;
        }
        .form-row label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-color);
            padding-top: 0.6rem;
        }
        .form-row input[type="text"],
        .form-row input[type="email"],
        .form-row input[type="url"],
        .form-row input[type="tel"],
        .form-row input[type="password"],
        .form-row textarea,
        .form-row select {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-family: inherit;
            transition: border-color 0.2s;
            background: white;
        }
        .form-row input:focus,
        .form-row textarea:focus,
        .form-row select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(46, 155, 94, 0.1);
        }
        .form-row textarea {
            min-height: 80px;
            resize: vertical;
        }
        .form-row .input-hint {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }
        .form-row .password-wrapper {
            position: relative;
        }
        .form-row .password-wrapper input {
            padding-right: 3rem;
        }
        .form-row .toggle-password {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-light);
            font-size: 1.1rem;
        }

        /* Checkbox/Boolean */
        .form-row .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-top: 0.6rem;
        }
        .form-row .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }

        /* Save Button */
        .form-actions {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* API Key Masking */
        .api-key-masked {
            font-family: monospace;
            font-size: 0.8rem;
            color: var(--text-light);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0.25rem;
            }
            .form-row label {
                padding-top: 0;
            }
            .settings-tabs {
                flex-wrap: nowrap;
            }
            .settings-tab {
                padding: 0.75rem 0.875rem;
                font-size: 0.8rem;
            }
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body class="dashboard-page">
    <!-- Navigation -->
    <nav class="dashboard-nav">
        <div class="container">
            <a href="../index.php" class="nav-brand">
                <h2>
                    <span class="logo-rd">RD</span> <span class="logo-formstack">Formstack</span>
                    <span class="logo-subtitle">Form Solutions</span>
                </h2>
            </a>
            <ul class="nav-menu">
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="../profile.php">Profil</a></li>
                <li><a href="settings.php" class="active">⚙️ Verwaltung</a></li>
                <li><a href="#" id="logoutBtn">Abmelden</a></li>
            </ul>
        </div>
    </nav>

    <div class="admin-container">
        <div class="admin-header">
            <h1>⚙️ Einstellungen</h1>
            <div class="admin-nav">
                <a href="../dashboard.php">← Dashboard</a>
            </div>
        </div>
        
        <!-- Admin-Navigation -->
        <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
            <a href="settings.php" style="padding:0.5rem 1rem; border-radius:var(--radius); text-decoration:none; font-weight:500; font-size:0.9rem; background:var(--primary-color); color:white; border:1px solid var(--primary-color);">🏢 Einstellungen</a>
            <a href="users.php" style="padding:0.5rem 1rem; border-radius:var(--radius); text-decoration:none; font-weight:500; font-size:0.9rem; background:var(--bg-secondary); color:var(--text-color); border:1px solid var(--border-color);">👥 Benutzer</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="settings.php" id="settingsForm">
            <input type="hidden" name="active_tab" id="activeTab" value="<?php echo htmlspecialchars($activeTab); ?>">

            <!-- Tabs -->
            <div class="settings-tabs">
                <?php foreach ($groups as $groupKey => $groupInfo): ?>
                    <button type="button" 
                            class="settings-tab <?php echo $activeTab === $groupKey ? 'active' : ''; ?>"
                            data-tab="<?php echo $groupKey; ?>">
                        <span class="tab-icon"><?php echo $groupInfo['icon']; ?></span>
                        <?php echo htmlspecialchars($groupInfo['label']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Tab Contents -->
            <?php foreach ($groups as $groupKey => $groupInfo): ?>
                <div class="tab-content <?php echo $activeTab === $groupKey ? 'active' : ''; ?>" id="tab-<?php echo $groupKey; ?>">
                    <div class="settings-form">
                        <div class="settings-group-title">
                            <?php echo $groupInfo['icon']; ?> <?php echo htmlspecialchars($groupInfo['label']); ?>
                        </div>

                        <?php
                        $fields = $settings->getGroup($groupKey);
                        foreach ($fields as $field):
                            $value = $field['setting_value'] ?? '';
                            $key = $field['setting_key'];
                            $label = $field['setting_label'];
                            $type = $field['setting_type'];
                        ?>
                            <div class="form-row">
                                <label for="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></label>
                                <div>
                                    <?php if ($type === 'textarea'): ?>
                                        <textarea id="<?php echo $key; ?>" 
                                                  name="<?php echo $key; ?>"
                                                  rows="3"><?php echo htmlspecialchars($value); ?></textarea>

                                    <?php elseif ($type === 'password'): ?>
                                        <div class="password-wrapper">
                                            <input type="password" 
                                                   id="<?php echo $key; ?>" 
                                                   name="<?php echo $key; ?>"
                                                   value="<?php echo htmlspecialchars($value); ?>"
                                                   autocomplete="off">
                                            <button type="button" class="toggle-password" data-target="<?php echo $key; ?>">👁️</button>
                                        </div>
                                        <?php if (!empty($value)): ?>
                                            <div class="input-hint api-key-masked">✅ Key hinterlegt</div>
                                        <?php else: ?>
                                            <div class="input-hint">⚠️ Noch nicht konfiguriert</div>
                                        <?php endif; ?>

                                    <?php elseif ($type === 'boolean'): ?>
                                        <div class="checkbox-wrapper">
                                            <input type="checkbox" 
                                                   id="<?php echo $key; ?>" 
                                                   name="<?php echo $key; ?>"
                                                   value="1"
                                                   <?php echo $value === '1' ? 'checked' : ''; ?>>
                                            <label for="<?php echo $key; ?>" style="padding-top: 0; font-weight: 400;">Ja</label>
                                        </div>

                                    <?php else: ?>
                                        <input type="<?php echo htmlspecialchars($type); ?>" 
                                               id="<?php echo $key; ?>" 
                                               name="<?php echo $key; ?>"
                                               value="<?php echo htmlspecialchars($value); ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Save -->
            <div class="form-actions">
                <div class="input-hint">Änderungen werden sofort auf allen Seiten wirksam.</div>
                <button type="submit" name="save_settings" value="1" class="btn btn-primary" style="min-width: 200px;">
                    💾 Einstellungen speichern
                </button>
            </div>
        </form>
    </div>

    <script>
        // Tab-Wechsel
        document.querySelectorAll('.settings-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Tabs deaktivieren
                document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Aktiven Tab setzen
                tab.classList.add('active');
                const tabId = tab.dataset.tab;
                document.getElementById('tab-' + tabId).classList.add('active');
                document.getElementById('activeTab').value = tabId;
            });
        });

        // Passwort anzeigen/verstecken
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                if (input.type === 'password') {
                    input.type = 'text';
                    btn.textContent = '🔒';
                } else {
                    input.type = 'password';
                    btn.textContent = '👁️';
                }
            });
        });

        // Logout
        document.getElementById('logoutBtn')?.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const path = window.location.pathname;
                const base = path.substring(0, path.lastIndexOf('/'));
                const parentBase = base.substring(0, base.lastIndexOf('/'));
                await fetch(parentBase + '/api/auth.php?action=logout', { method: 'POST' });
            } catch (err) {}
            localStorage.removeItem('auth_token');
            document.cookie = 'auth_token=; path=/; max-age=0';
            window.location.href = '../login.php';
        });

        // Unsaved changes warning
        let formChanged = false;
        document.getElementById('settingsForm').addEventListener('change', () => { formChanged = true; });
        document.getElementById('settingsForm').addEventListener('input', () => { formChanged = true; });
        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        document.getElementById('settingsForm').addEventListener('submit', () => { formChanged = false; });
    </script>

    <script src="../assets/js/auth.js?v=<?php echo time(); ?>"></script>
</body>
</html>
