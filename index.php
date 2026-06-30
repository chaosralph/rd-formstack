<?php
/**
 * RD Formstack Solutions - Landingpage
 * Alle Daten dynamisch aus der Verwaltung
 */
session_start();
$pageTitle = 'RD Formstack Solutions - Professionelle Webentwicklung';
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
                <li><a href="#home">Start</a></li>
                <li><a href="#services">Leistungen</a></li>
                <li><a href="#references">Referenzen</a></li>
                <li><a href="dms/dms/">DMS</a></li>
                <li><a href="#contact">Kontakt</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php" class="btn btn-primary">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn btn-primary">Login</a></li>
                <?php endif; ?>
            </ul>
            <button class="nav-toggle" aria-label="Menü">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Professionelle Webentwicklung & Digitale Lösungen</h1>
                <p class="hero-subtitle">Wir entwickeln maßgeschneiderte Softwarelösungen für Ihr Unternehmen</p>
                <div class="hero-buttons">
                    <a href="login.php" class="btn btn-primary btn-large">Jetzt einloggen</a>
                    <a href="dms/dms/" class="btn btn-secondary btn-large">Zum DMS</a>
                    <a href="#services" class="btn btn-secondary btn-large">Mehr erfahren</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-graphic">
                    <svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                        <rect x="100" y="50" width="200" height="260" rx="12" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.3)" stroke-width="2"/>
                        <rect x="100" y="50" width="200" height="50" rx="12" fill="rgba(255,255,255,0.1)"/>
                        <rect x="130" y="70" width="80" height="8" rx="4" fill="rgba(255,255,255,0.4)"/>
                        <rect x="130" y="130" width="140" height="6" rx="3" fill="rgba(255,255,255,0.2)"/>
                        <rect x="130" y="150" width="120" height="6" rx="3" fill="rgba(255,255,255,0.2)"/>
                        <rect x="130" y="170" width="100" height="6" rx="3" fill="rgba(255,255,255,0.2)"/>
                        <rect x="130" y="200" width="60" height="20" rx="4" fill="rgba(255,255,255,0.25)"/>
                        <rect x="200" y="200" width="60" height="20" rx="4" fill="rgba(255,255,255,0.15)"/>
                        <circle cx="280" cy="290" r="35" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
                        <path d="M 262 290 L 275 303 L 298 277" stroke="rgba(255,255,255,0.6)" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="120" cy="350" r="25" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.3)" stroke-width="2"/>
                        <circle cx="120" cy="350" r="10" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <h2 class="section-title">Unsere Leistungen</h2>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">💻</div>
                    <h3>Webentwicklung</h3>
                    <p>Moderne, responsive Websites und Webanwendungen nach neuesten Standards</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">📱</div>
                    <h3>Mobile Apps</h3>
                    <p>Native und Cross-Platform Mobile Applications für iOS und Android</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">🔧</div>
                    <h3>Custom Software</h3>
                    <p>Individuelle Softwarelösungen für Ihre spezifischen Anforderungen</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">📊</div>
                    <h3>Belegverwaltung</h3>
                    <p>Intelligente Belegerkennung mit automatischer Kategorisierung und Buchungsvorschlägen</p>
                </div>
            </div>
        </div>
    </section>

    <!-- References Section -->
    <section id="references" class="references">
        <div class="container">
            <h2 class="section-title">Unsere Referenzen</h2>
            <p class="section-subtitle">Erfolgreiche Projekte, die wir für unsere Kunden umgesetzt haben</p>
            <div class="references-grid">
                <div class="reference-card">
                    <div class="reference-header">
                        <h3>TimePro Solutions</h3>
                        <span class="reference-badge">Zeiterfassung</span>
                    </div>
                    <p>Professionelles Zeiterfassungssystem mit Mobile App, GPS-Tracking und umfangreichen Reporting-Funktionen.</p>
                    <div class="reference-tech">
                        <span class="tech-tag">PHP</span>
                        <span class="tech-tag">React Native</span>
                        <span class="tech-tag">MySQL</span>
                    </div>
                    <a href="https://timepro-solutions.de" target="_blank" rel="noopener noreferrer" class="reference-link">
                        Zur Website <span>→</span>
                    </a>
                </div>
                <div class="reference-card">
                    <div class="reference-header">
                        <h3>RM CargoTec</h3>
                        <span class="reference-badge">Logistik</span>
                    </div>
                    <p>Komplettes Logistikmanagementsystem für Transportunternehmen mit Fahrzeugverwaltung, Routenplanung und Rechnungsstellung.</p>
                    <div class="reference-tech">
                        <span class="tech-tag">PHP</span>
                        <span class="tech-tag">JavaScript</span>
                        <span class="tech-tag">MySQL</span>
                    </div>
                    <a href="https://rm-cargotec.de" target="_blank" rel="noopener noreferrer" class="reference-link">
                        Zur Website <span>→</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title">Kontakt</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <h3>Lassen Sie uns sprechen</h3>
                    <p>Haben Sie ein Projekt im Kopf? Wir helfen Ihnen dabei, es umzusetzen.</p>
                    <div class="contact-details">
                        <div class="contact-item">
                            <strong>📧 E-Mail:</strong>
                            <a href="mailto:<?php echo htmlspecialchars($settings->companyEmail()); ?>"><?php echo htmlspecialchars($settings->companyEmail()); ?></a>
                        </div>
                        <?php if ($settings->companyPhone()): ?>
                        <div class="contact-item">
                            <strong>📞 Telefon:</strong>
                            <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^+0-9]/', '', $settings->companyPhone())); ?>"><?php echo htmlspecialchars($settings->companyPhone()); ?></a>
                        </div>
                        <?php endif; ?>
                        <div class="contact-item">
                            <strong>📍 Adresse:</strong>
                            <span><?php echo htmlspecialchars($settings->companyStreet() . ', ' . $settings->companyZip() . ' ' . $settings->companyCity()); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
