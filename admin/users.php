<?php
/**
 * Admin-Verwaltung – Benutzerverwaltung
 */
session_start();

// Nur Admin darf hier rein
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzerverwaltung – RD Formstack Solutions</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .admin-header h1 {
            font-size: 2rem;
            color: var(--text-color);
        }
        .admin-nav {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .admin-nav a {
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            background: var(--bg-secondary);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }
        .admin-nav a:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        .admin-nav a.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Tabelle */
        .users-table-wrapper {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        .users-table th,
        .users-table td {
            padding: 1rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .users-table th {
            background: var(--bg-secondary);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-light);
        }
        .users-table tr:last-child td {
            border-bottom: none;
        }
        .users-table tr:hover td {
            background: #f8faf9;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-admin {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-user {
            background: var(--bg-secondary);
            color: var(--text-light);
        }
        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Aktions-Buttons */
        .action-btns {
            display: flex;
            gap: 0.4rem;
        }
        .btn-xs {
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-xs:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        .btn-xs.danger:hover {
            border-color: var(--danger-color);
            color: var(--danger-color);
        }
        
        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-box {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            box-shadow: var(--shadow-lg);
        }
        .modal-box h2 {
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            color: var(--text-color);
        }
        .modal-footer {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
        
        /* Message */
        .flash-message {
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: none;
        }
        .flash-message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .flash-message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        /* Statistik */
        .users-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-box {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1rem 1.5rem;
            text-align: center;
            flex: 1;
        }
        .stat-box .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        .stat-box .stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }
        
        @media (max-width: 768px) {
            .users-table-wrapper {
                overflow-x: auto;
            }
            .users-stats {
                flex-direction: column;
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
                <li><a href="settings.php">⚙️ Verwaltung</a></li>
                <li><a href="#" id="logoutBtn">Abmelden</a></li>
            </ul>
        </div>
    </nav>

    <div class="admin-container">
        <div class="admin-header">
            <h1>👥 Benutzerverwaltung</h1>
            <button class="btn btn-primary" onclick="openCreateModal()">+ Neuer Benutzer</button>
        </div>
        
        <!-- Admin-Navigation -->
        <div class="admin-nav">
            <a href="settings.php">🏢 Einstellungen</a>
            <a href="users.php" class="active">👥 Benutzer</a>
        </div>
        
        <!-- Flash-Meldungen -->
        <div id="flashMessage" class="flash-message"></div>
        
        <!-- Statistik -->
        <div class="users-stats">
            <div class="stat-box">
                <div class="stat-number" id="statTotal">-</div>
                <div class="stat-label">Benutzer gesamt</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" id="statAdmins">-</div>
                <div class="stat-label">Administratoren</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" id="statActive">-</div>
                <div class="stat-label">Aktiv</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" id="statInactive">-</div>
                <div class="stat-label">Inaktiv</div>
            </div>
        </div>
        
        <!-- Tabelle -->
        <div class="users-table-wrapper">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>E-Mail</th>
                        <th>Rolle</th>
                        <th>Status</th>
                        <th>Erstellt</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text-light);">Lade Benutzer...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal: Neuer Benutzer / Bearbeiten -->
    <div class="modal-overlay" id="userModal">
        <div class="modal-box">
            <h2 id="modalTitle">Neuer Benutzer</h2>
            <form id="userForm">
                <input type="hidden" id="editUserId" value="">
                <div class="form-group">
                    <label for="userName">Name *</label>
                    <input type="text" id="userName" required>
                </div>
                <div class="form-group">
                    <label for="userEmail">E-Mail *</label>
                    <input type="email" id="userEmail" required>
                </div>
                <div class="form-group" id="passwordGroup">
                    <label for="userPassword">Passwort *</label>
                    <input type="password" id="userPassword" minlength="8">
                    <small style="color:var(--text-light); font-size:0.75rem;">Min. 8 Zeichen</small>
                </div>
                <div class="form-group">
                    <label for="userRole">Rolle</label>
                    <select id="userRole" style="width:100%; padding:0.75rem; border:1px solid var(--border-color); border-radius:var(--radius); font-size:1rem;">
                        <option value="user">Benutzer</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Abbrechen</button>
                    <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Anlegen</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal: Passwort zurücksetzen -->
    <div class="modal-overlay" id="resetPwModal">
        <div class="modal-box">
            <h2>🔒 Passwort zurücksetzen</h2>
            <p style="margin-bottom:1rem; color:var(--text-light);">Benutzer: <strong id="resetPwUser"></strong></p>
            <form id="resetPwForm">
                <input type="hidden" id="resetPwUserId">
                <div class="form-group">
                    <label for="resetPwNew">Neues Passwort *</label>
                    <input type="password" id="resetPwNew" required minlength="8">
                    <small style="color:var(--text-light); font-size:0.75rem;">Min. 8 Zeichen</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeResetPwModal()">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Passwort setzen</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal: Löschen bestätigen -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-box">
            <h2>⚠️ Benutzer löschen</h2>
            <p style="margin-bottom:1rem;">Soll der Benutzer <strong id="deleteUserName"></strong> wirklich gelöscht werden?</p>
            <p style="color:var(--danger-color); font-size:0.875rem; margin-bottom:1rem;">Achtung: Alle Belege dieses Benutzers werden ebenfalls gelöscht!</p>
            <input type="hidden" id="deleteUserId">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Abbrechen</button>
                <button type="button" class="btn btn-primary" style="background:var(--danger-color);" onclick="confirmDelete()">Endgültig löschen</button>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = (function() {
            const path = window.location.pathname;
            const base = path.substring(0, path.lastIndexOf('/'));
            const parent = base.substring(0, base.lastIndexOf('/'));
            return parent + '/api';
        })();
        
        const currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
        let allUsers = [];

        // ---- Flash-Meldung ----
        function flash(msg, type = 'success') {
            const el = document.getElementById('flashMessage');
            el.textContent = msg;
            el.className = 'flash-message ' + type;
            el.style.display = 'block';
            setTimeout(() => { el.style.display = 'none'; }, 4000);
        }

        // ---- Benutzer laden ----
        async function loadUsers() {
            try {
                const response = await fetch(`${API_BASE}/users.php`, {
                    headers: {
                        'X-Auth-Token': localStorage.getItem('auth_token') || ''
                    }
                });
                const data = await response.json();
                
                if (data.users) {
                    allUsers = data.users;
                    renderUsers(allUsers);
                    updateStats(allUsers);
                } else {
                    flash(data.error || 'Fehler beim Laden', 'error');
                }
            } catch (err) {
                flash('Netzwerkfehler: ' + err.message, 'error');
            }
        }

        // ---- Tabelle rendern ----
        function renderUsers(users) {
            const tbody = document.getElementById('usersTableBody');
            
            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text-light);">Keine Benutzer gefunden</td></tr>';
                return;
            }
            
            tbody.innerHTML = users.map(u => {
                const roleBadge = u.role === 'admin' 
                    ? '<span class="badge badge-admin">Admin</span>' 
                    : '<span class="badge badge-user">User</span>';
                const statusBadge = u.is_active 
                    ? '<span class="badge badge-active">Aktiv</span>' 
                    : '<span class="badge badge-inactive">Inaktiv</span>';
                const created = new Date(u.created_at).toLocaleDateString('de-DE');
                const isSelf = u.id == currentUserId;
                
                return `<tr>
                    <td>${u.id}</td>
                    <td><strong>${escHtml(u.name)}</strong>${isSelf ? ' <small>(Sie)</small>' : ''}</td>
                    <td>${escHtml(u.email)}</td>
                    <td>${roleBadge}</td>
                    <td>${statusBadge}</td>
                    <td>${created}</td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-xs" onclick="openEditModal(${u.id})" title="Bearbeiten">✏️</button>
                            <button class="btn-xs" onclick="openResetPwModal(${u.id}, '${escHtml(u.email)}')" title="Passwort zurücksetzen">🔑</button>
                            <button class="btn-xs" onclick="toggleActive(${u.id}, ${u.is_active ? 'false' : 'true'})" title="${u.is_active ? 'Deaktivieren' : 'Aktivieren'}">${u.is_active ? '🚫' : '✅'}</button>
                            ${!isSelf ? `<button class="btn-xs danger" onclick="openDeleteModal(${u.id}, '${escHtml(u.name)} (${escHtml(u.email)})')" title="Löschen">🗑️</button>` : ''}
                        </div>
                    </td>
                </tr>`;
            }).join('');
        }

        // ---- Statistiken ----
        function updateStats(users) {
            document.getElementById('statTotal').textContent = users.length;
            document.getElementById('statAdmins').textContent = users.filter(u => u.role === 'admin').length;
            document.getElementById('statActive').textContent = users.filter(u => u.is_active).length;
            document.getElementById('statInactive').textContent = users.filter(u => !u.is_active).length;
        }

        // ---- HTML escapen ----
        function escHtml(str) {
            const div = document.createElement('div');
            div.textContent = str || '';
            return div.innerHTML;
        }

        // ---- MODAL: Neuer Benutzer ----
        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Neuer Benutzer';
            document.getElementById('modalSubmitBtn').textContent = 'Anlegen';
            document.getElementById('editUserId').value = '';
            document.getElementById('userName').value = '';
            document.getElementById('userEmail').value = '';
            document.getElementById('userPassword').value = '';
            document.getElementById('userRole').value = 'user';
            document.getElementById('passwordGroup').style.display = 'block';
            document.getElementById('userPassword').required = true;
            document.getElementById('userModal').classList.add('active');
        }

        // ---- MODAL: Bearbeiten ----
        function openEditModal(userId) {
            const user = allUsers.find(u => u.id == userId);
            if (!user) return;
            
            document.getElementById('modalTitle').textContent = 'Benutzer bearbeiten';
            document.getElementById('modalSubmitBtn').textContent = 'Speichern';
            document.getElementById('editUserId').value = userId;
            document.getElementById('userName').value = user.name;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userPassword').value = '';
            document.getElementById('userRole').value = user.role;
            document.getElementById('passwordGroup').style.display = 'none';
            document.getElementById('userPassword').required = false;
            document.getElementById('userModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('userModal').classList.remove('active');
        }

        // ---- MODAL: Passwort zurücksetzen ----
        function openResetPwModal(userId, email) {
            document.getElementById('resetPwUserId').value = userId;
            document.getElementById('resetPwUser').textContent = email;
            document.getElementById('resetPwNew').value = '';
            document.getElementById('resetPwModal').classList.add('active');
        }

        function closeResetPwModal() {
            document.getElementById('resetPwModal').classList.remove('active');
        }

        // ---- MODAL: Löschen ----
        function openDeleteModal(userId, name) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = name;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // ---- Anlegen / Bearbeiten absenden ----
        document.getElementById('userForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const editId = document.getElementById('editUserId').value;
            const name = document.getElementById('userName').value.trim();
            const email = document.getElementById('userEmail').value.trim();
            const password = document.getElementById('userPassword').value;
            const role = document.getElementById('userRole').value;
            
            if (editId) {
                // === BEARBEITEN ===
                try {
                    const response = await fetch(`${API_BASE}/users.php?id=${editId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Auth-Token': localStorage.getItem('auth_token') || ''
                        },
                        body: JSON.stringify({ name, email, role })
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        flash('✅ ' + data.message);
                        closeModal();
                        loadUsers();
                    } else {
                        flash('❌ ' + (data.error || 'Fehler'), 'error');
                    }
                } catch (err) {
                    flash('❌ Netzwerkfehler', 'error');
                }
            } else {
                // === ANLEGEN ===
                if (!password || password.length < 8) {
                    flash('❌ Passwort muss mindestens 8 Zeichen lang sein', 'error');
                    return;
                }
                
                try {
                    const response = await fetch(`${API_BASE}/users.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Auth-Token': localStorage.getItem('auth_token') || ''
                        },
                        body: JSON.stringify({ name, email, password, role })
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        flash('✅ ' + data.message);
                        closeModal();
                        loadUsers();
                    } else {
                        flash('❌ ' + (data.error || 'Fehler'), 'error');
                    }
                } catch (err) {
                    flash('❌ Netzwerkfehler', 'error');
                }
            }
        });

        // ---- Passwort zurücksetzen ----
        document.getElementById('resetPwForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const userId = document.getElementById('resetPwUserId').value;
            const newPassword = document.getElementById('resetPwNew').value;
            
            try {
                const response = await fetch(`${API_BASE}/users.php?action=reset-password&id=${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Auth-Token': localStorage.getItem('auth_token') || ''
                    },
                    body: JSON.stringify({ new_password: newPassword })
                });
                const data = await response.json();
                
                if (data.success) {
                    flash('✅ ' + data.message);
                    closeResetPwModal();
                } else {
                    flash('❌ ' + (data.error || 'Fehler'), 'error');
                }
            } catch (err) {
                flash('❌ Netzwerkfehler', 'error');
            }
        });

        // ---- Status umschalten ----
        async function toggleActive(userId, isActive) {
            try {
                const response = await fetch(`${API_BASE}/users.php?id=${userId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Auth-Token': localStorage.getItem('auth_token') || ''
                    },
                    body: JSON.stringify({ is_active: isActive })
                });
                const data = await response.json();
                
                if (data.success) {
                    flash('✅ Status aktualisiert');
                    loadUsers();
                } else {
                    flash('❌ ' + (data.error || 'Fehler'), 'error');
                }
            } catch (err) {
                flash('❌ Netzwerkfehler', 'error');
            }
        }

        // ---- Löschen bestätigen ----
        async function confirmDelete() {
            const userId = document.getElementById('deleteUserId').value;
            
            try {
                const response = await fetch(`${API_BASE}/users.php?id=${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Auth-Token': localStorage.getItem('auth_token') || ''
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    flash('✅ ' + data.message);
                    closeDeleteModal();
                    loadUsers();
                } else {
                    flash('❌ ' + (data.error || 'Fehler'), 'error');
                }
            } catch (err) {
                flash('❌ Netzwerkfehler', 'error');
            }
        }

        // ---- Logout ----
        document.getElementById('logoutBtn')?.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                await fetch(`${API_BASE}/auth.php?action=logout`, { method: 'POST' });
            } catch (err) {}
            localStorage.removeItem('auth_token');
            document.cookie = 'auth_token=; path=/; max-age=0';
            window.location.href = '../login.php';
        });

        // ---- Init ----
        loadUsers();
    </script>
</body>
</html>
