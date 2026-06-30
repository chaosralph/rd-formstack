<?php
/**
 * Referenzseite – dynamischer Footer + Header aus Settings
 */
session_start();
$pageTitle = 'Referenzen - RD Formstack Solutions';
$basePath = '';
require_once __DIR__ . '/includes/header.php';
?>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="nav-brand">
                <h1>
                    <span class="logo-rd">RD</span> <span class="logo-formstack">Formstack</span>
                    <span class="logo-subtitle">Form Solutions</span>
                </h1>
            </a>
            <ul class="nav-menu">
                <li><a href="index.php">Start</a></li>
                <li><a href="references.php" class="active">Referenzen</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="#" id="logoutBtn">Abmelden</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn btn-primary">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- References Section -->
    <section class="references-page">
        <div class="container">
            <h1 class="page-title">Unsere Referenzen</h1>
            <p class="page-subtitle">Erfolgreiche Projekte, die wir für unsere Kunden umgesetzt haben</p>
            
            <div class="references-grid detailed">
                <div class="reference-card detailed">
                    <div class="reference-header">
                        <h2>TimePro Solutions</h2>
                        <span class="reference-badge">Zeiterfassung</span>
                    </div>
                    <div class="reference-content">
                        <p class="reference-description">
                            TimePro Solutions ist ein umfassendes Zeiterfassungssystem, das wir von Grund auf entwickelt haben. 
                            Das System bietet moderne Web- und Mobile-Anwendungen mit GPS-Tracking, Offline-Funktionalität 
                            und umfangreichen Reporting-Funktionen.
                        </p>
                        <div class="reference-features">
                            <h3>Hauptfunktionen:</h3>
                            <ul>
                                <li>Web-basierte Zeiterfassung</li>
                                <li>Mobile App für iOS und Android</li>
                                <li>GPS-Tracking für mobile Mitarbeiter</li>
                                <li>Offline-Funktionalität</li>
                                <li>Umfangreiche Reporting-Tools</li>
                                <li>Urlaubs- und Krankheitsverwaltung</li>
                                <li>Multi-Company Support</li>
                            </ul>
                        </div>
                        <div class="reference-tech">
                            <h3>Technologien:</h3>
                            <div class="tech-tags">
                                <span class="tech-tag">PHP</span>
                                <span class="tech-tag">Laravel</span>
                                <span class="tech-tag">React Native</span>
                                <span class="tech-tag">MySQL</span>
                                <span class="tech-tag">REST API</span>
                            </div>
                        </div>
                        <a href="https://timepro-solutions.de" target="_blank" rel="noopener noreferrer" class="reference-link">
                            Zur Website besuchen <span>→</span>
                        </a>
                    </div>
                </div>

                <div class="reference-card detailed">
                    <div class="reference-header">
                        <h2>RM CargoTec</h2>
                        <span class="reference-badge">Logistik</span>
                    </div>
                    <div class="reference-content">
                        <p class="reference-description">
                            RM CargoTec ist ein komplettes Logistikmanagementsystem für Transportunternehmen. 
                            Das System umfasst Fahrzeugverwaltung, Routenplanung, Auftragsverwaltung, 
                            Rechnungsstellung und umfangreiche Reporting-Funktionen.
                        </p>
                        <div class="reference-features">
                            <h3>Hauptfunktionen:</h3>
                            <ul>
                                <li>Fahrzeug- und Flottenverwaltung</li>
                                <li>Auftrags- und Tourenplanung</li>
                                <li>Rechnungsstellung und Finanzverwaltung</li>
                                <li>Kundenverwaltung</li>
                                <li>Lagerverwaltung</li>
                                <li>Reporting und Analytics</li>
                                <li>Mobile Zugriffsmöglichkeiten</li>
                            </ul>
                        </div>
                        <div class="reference-tech">
                            <h3>Technologien:</h3>
                            <div class="tech-tags">
                                <span class="tech-tag">PHP</span>
                                <span class="tech-tag">JavaScript</span>
                                <span class="tech-tag">MySQL</span>
                                <span class="tech-tag">Bootstrap</span>
                                <span class="tech-tag">jQuery</span>
                            </div>
                        </div>
                        <a href="https://rm-cargotec.de" target="_blank" rel="noopener noreferrer" class="reference-link">
                            Zur Website besuchen <span>→</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
    <?php if (isset($_SESSION['user_id'])): ?>
    <script src="assets/js/auth.js?v=<?php echo time(); ?>"></script>
    <?php endif; ?>
</body>
</html>
