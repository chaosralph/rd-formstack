<?php
/**
 * Login-Seite
 */
session_start();
// Wenn bereits eingeloggt, weiterleiten
if (isset($_SESSION['user_id'])) {
    $redirect = $_GET['redirect'] ?? 'dashboard.php';
    if (!is_string($redirect) || !str_starts_with($redirect, '/')) {
        $redirect = 'dashboard.php';
    }
    header('Location: ' . $redirect);
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RD Formstack Solutions</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Fonts werden lokal geladen (DSGVO-konform) -->
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <span class="logo-rd" style="font-size: 2rem; font-weight: 700; color: #2E9B5E;">RD</span>
                    <span class="logo-formstack" style="font-size: 2rem; font-weight: 700; color: #2D3436;">Formstack</span>
                    <span class="logo-subtitle" style="display: block; font-size: 0.75rem; font-weight: 400; color: #94A3B8; letter-spacing: 0.15em; text-transform: uppercase; margin-top: 2px;">Form Solutions</span>
                </div>
                <p style="margin-top: 1rem;">Melden Sie sich an, um fortzufahren</p>
            </div>
            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="email">E-Mail-Adresse</label>
                    <input type="email" id="email" name="email" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="password">Passwort</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <div id="errorMessage" class="error-message" style="display: none;"></div>
                <button type="submit" class="btn btn-primary btn-block">Anmelden</button>
            </form>
            <div class="login-footer">
                <a href="index.php">← Zurück zur Startseite</a>
            </div>
        </div>
    </div>
    <script src="assets/js/auth.js?v=<?php echo time(); ?>"></script>
</body>
</html>
