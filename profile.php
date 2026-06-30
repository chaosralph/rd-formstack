<?php
/**
 * Profil & Passwort ändern
 */
require_once __DIR__ . '/config/config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
$db = Database::getInstance()->getConnection();

// Aktuelle Benutzerdaten laden
$stmt = $db->prepare("SELECT id, email, name, role, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: login.php');
    exit;
}

$basePath = '';
$pageTitle = 'Mein Profil';
require_once __DIR__ . '/includes/header.php';
?>
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="dms/dms/">DMS</a></li>
                <li><a href="profile.php" class="active">Profil</a></li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li><a href="admin/settings.php">⚙️ Verwaltung</a></li>
                <?php endif; ?>
                <li><a href="#" id="logoutBtn">Abmelden</a></li>
            </ul>
        </div>
    </nav>

    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1>👤 Mein Profil</h1>
                <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
            </div>

            <div class="profile-grid">
                <!-- Profildaten -->
                <div class="profile-card">
                    <h2>Profildaten</h2>
                    <form id="profileForm">
                        <div class="form-group">
                            <label for="profileName">Name</label>
                            <input type="text" id="profileName" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="profileEmail">E-Mail-Adresse</label>
                            <input type="email" id="profileEmail" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Rolle</label>
                            <input type="text" value="<?php echo $user['role'] === 'admin' ? 'Administrator' : 'Benutzer'; ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Mitglied seit</label>
                            <input type="text" value="<?php echo date('d.m.Y', strtotime($user['created_at'])); ?>" disabled>
                        </div>
                        <div id="profileMessage" class="form-message" style="display: none;"></div>
                        <button type="submit" class="btn btn-primary">💾 Profil speichern</button>
                    </form>
                </div>

                <!-- Passwort ändern -->
                <div class="profile-card">
                    <h2>Passwort ändern</h2>
                    <form id="passwordForm">
                        <div class="form-group">
                            <label for="currentPassword">Aktuelles Passwort</label>
                            <input type="password" id="currentPassword" required autocomplete="current-password">
                        </div>
                        <div class="form-group">
                            <label for="newPassword">Neues Passwort</label>
                            <input type="password" id="newPassword" required autocomplete="new-password" minlength="8">
                            <small class="form-hint">Mindestens 8 Zeichen</small>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Neues Passwort bestätigen</label>
                            <input type="password" id="confirmPassword" required autocomplete="new-password">
                        </div>
                        <div id="passwordMessage" class="form-message" style="display: none;"></div>
                        <button type="submit" class="btn btn-primary">🔒 Passwort ändern</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <style>
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        .profile-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 2rem;
        }
        .profile-card h2 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
        }
        .form-hint {
            display: block;
            font-size: 0.75rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }
        .form-message {
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .form-message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .form-message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        const API_BASE = (function() {
            const path = window.location.pathname;
            const base = path.substring(0, path.lastIndexOf('/'));
            return base + '/api';
        })();

        function showMessage(elementId, message, type) {
            const el = document.getElementById(elementId);
            el.textContent = message;
            el.className = 'form-message ' + type;
            el.style.display = 'block';
            setTimeout(() => { el.style.display = 'none'; }, 5000);
        }

        // Profil speichern
        document.getElementById('profileForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const name = document.getElementById('profileName').value.trim();
            const email = document.getElementById('profileEmail').value.trim();
            
            try {
                const response = await fetch(`${API_BASE}/auth.php?action=update-profile`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Auth-Token': localStorage.getItem('auth_token') || ''
                    },
                    body: JSON.stringify({ name, email })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('profileMessage', '✅ ' + data.message, 'success');
                } else {
                    showMessage('profileMessage', '❌ ' + (data.error || 'Fehler'), 'error');
                }
            } catch (error) {
                showMessage('profileMessage', '❌ Netzwerkfehler', 'error');
            }
        });

        // Passwort ändern
        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                showMessage('passwordMessage', '❌ Passwörter stimmen nicht überein', 'error');
                return;
            }
            
            if (newPassword.length < 8) {
                showMessage('passwordMessage', '❌ Passwort muss mindestens 8 Zeichen lang sein', 'error');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/auth.php?action=change-password`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Auth-Token': localStorage.getItem('auth_token') || ''
                    },
                    body: JSON.stringify({
                        current_password: currentPassword,
                        new_password: newPassword,
                        confirm_password: confirmPassword
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('passwordMessage', '✅ ' + data.message, 'success');
                    document.getElementById('passwordForm').reset();
                } else {
                    showMessage('passwordMessage', '❌ ' + (data.error || 'Fehler'), 'error');
                }
            } catch (error) {
                showMessage('passwordMessage', '❌ Netzwerkfehler', 'error');
            }
        });

        // Logout
        document.getElementById('logoutBtn')?.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                await fetch(`${API_BASE}/auth.php?action=logout`, { method: 'POST' });
            } catch (err) {}
            localStorage.removeItem('auth_token');
            document.cookie = 'auth_token=; path=/; max-age=0';
            window.location.href = 'login.php';
        });
    </script>
</body>
</html>
