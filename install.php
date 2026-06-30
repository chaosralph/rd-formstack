<?php
/**
 * Installation Script
 */
require_once __DIR__ . '/config/config.php';

// Prüfe ob bereits installiert
if (file_exists(__DIR__ . '/.installed')) {
    die('System ist bereits installiert. Löschen Sie die Datei .installed um die Installation erneut durchzuführen.');
}

$errors = [];
$success = [];

// Schritt 1: Datenbankverbindung prüfen
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $success[] = "Datenbankverbindung erfolgreich";
} catch (PDOException $e) {
    $errors[] = "Datenbankverbindung fehlgeschlagen: " . $e->getMessage();
}

// Schritt 2: Datenbank erstellen
if (empty($errors)) {
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $success[] = "Datenbank '" . DB_NAME . "' erstellt oder bereits vorhanden";
    } catch (PDOException $e) {
        $errors[] = "Fehler beim Erstellen der Datenbank: " . $e->getMessage();
    }
}

// Schritt 3: Schema importieren
if (empty($errors)) {
    try {
        $pdo->exec("USE " . DB_NAME);
        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        
        // Entferne CREATE DATABASE und USE Statements
        $schema = preg_replace('/CREATE DATABASE.*?;/i', '', $schema);
        $schema = preg_replace('/USE.*?;/i', '', $schema);
        
        // Führe Statements aus
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        $success[] = "Datenbankschema importiert";
    } catch (PDOException $e) {
        $errors[] = "Fehler beim Importieren des Schemas: " . $e->getMessage();
    }
}

// Schritt 4: Upload-Verzeichnis erstellen
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        $success[] = "Upload-Verzeichnis erstellt";
    } else {
        $errors[] = "Fehler beim Erstellen des Upload-Verzeichnisses";
    }
} else {
    $success[] = "Upload-Verzeichnis bereits vorhanden";
}

// Schritt 5: .htaccess für Uploads
$htaccessContent = "Options -Indexes\nDeny from all";
if (file_put_contents($uploadDir . '/.htaccess', $htaccessContent)) {
    $success[] = ".htaccess für Uploads erstellt";
}

// Schritt 6: Installation abschließen
if (empty($errors)) {
    file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));
    $success[] = "Installation abgeschlossen";
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - RD Formstack Solutions</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            line-height: 1.6;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        h1 {
            color: #2563eb;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <h1>RD Formstack Solutions - Installation</h1>
    
    <?php if (!empty($errors)): ?>
        <h2>Fehler:</h2>
        <?php foreach ($errors as $error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <h2>Erfolgreich:</h2>
        <?php foreach ($success as $msg): ?>
            <div class="success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (empty($errors)): ?>
        <p><strong>Installation erfolgreich abgeschlossen!</strong></p>
        <p>Standard-Login-Daten:</p>
        <ul>
            <li><strong>E-Mail:</strong> admin@rd-formstack.de</li>
            <li><strong>Passwort:</strong> admin123</li>
        </ul>
        <p><strong>WICHTIG:</strong> Bitte ändern Sie das Passwort nach dem ersten Login!</p>
        <a href="index.php" class="btn">Zur Startseite</a>
        <a href="login.php" class="btn">Zum Login</a>
    <?php else: ?>
        <p>Bitte beheben Sie die Fehler und führen Sie die Installation erneut durch.</p>
    <?php endif; ?>
</body>
</html>
