/**
 * Authentifizierung
 */

// API-Basis automatisch erkennen (var statt const, da evtl. dashboard.js schon geladen)
if (typeof API_BASE === 'undefined') {
    var API_BASE = (function() {
        const path = window.location.pathname;
        const base = path.substring(0, path.lastIndexOf('/'));
        return base + '/api';
    })();
}

// Login
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }
});

async function handleLogin(e) {
    e.preventDefault();
    
    const form = e.target;
    const email = form.email.value;
    const password = form.password.value;
    const errorMessage = document.getElementById('errorMessage');
    
    errorMessage.style.display = 'none';
    
    try {
        const response = await fetch(`${API_BASE}/auth.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Login fehlgeschlagen');
        }
        
        // Token speichern
        if (data.access_token) {
            localStorage.setItem('auth_token', data.access_token);
            document.cookie = `auth_token=${data.access_token}; path=/; max-age=86400`;
        }
        
        // Optionales Redirect-Ziel (z.B. DMS) berücksichtigen
        const redirectTarget = new URLSearchParams(window.location.search).get('redirect');
        if (redirectTarget && redirectTarget.startsWith('/')) {
            window.location.href = redirectTarget;
            return;
        }

        // Standard-Weiterleitung
        window.location.href = 'dashboard.php';
        
    } catch (error) {
        errorMessage.textContent = error.message;
        errorMessage.style.display = 'block';
    }
}

async function handleLogout(e) {
    e.preventDefault();
    
    try {
        await fetch(`${API_BASE}/auth.php?action=logout`, {
            method: 'POST',
            headers: {
                'X-Auth-Token': localStorage.getItem('auth_token') || ''
            }
        });
    } catch (error) {
        console.error('Logout error:', error);
    }
    
    // Token entfernen
    localStorage.removeItem('auth_token');
    document.cookie = 'auth_token=; path=/; max-age=0';
    
    // Weiterleitung
    window.location.href = 'login.php';
}

// API-Helper
function getAuthHeaders() {
    const token = localStorage.getItem('auth_token');
    return {
        'Content-Type': 'application/json',
        'X-Auth-Token': token || ''
    };
}
