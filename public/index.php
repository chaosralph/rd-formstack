<?php

declare(strict_types=1);

use App\Application\Auth\AdminSetupService;
use App\Application\Auth\LoginService;
use App\Application\Contact\ContactSubmissionService;
use App\Bootstrap\AppBootstrap;
use App\Config\Env;
use App\Controller\AuthController;
use App\Controller\ContactController;
use App\Database\Connection;
use App\Http\ErrorHandler;
use App\Http\Request;
use App\Http\Routing\RouteCatalog;
use App\Mail\NativeMailTransport;
use App\Repository\ContactRepository;
use App\Repository\ReferenceRepository;
use App\Repository\UserRepository;
use App\Security\AuthSession;
use App\Security\Csrf;
use App\Support\AppUrl;
use App\Support\SecurityHeaderPolicy;
use App\View\HomepageContent;
use App\View\SiteRenderer;

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});

$projectRoot = dirname(__DIR__);
AppBootstrap::init($projectRoot);

$requestId = bin2hex(random_bytes(8));
header('X-Request-Id: ' . $requestId);
$_SERVER['HTTP_X_REQUEST_ID'] = $requestId;
set_exception_handler(static function (Throwable $exception) use ($requestId): void {
    ErrorHandler::handle($exception, $requestId);
    exit;
});

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/');
$path = $path === '' ? '/' : $path;
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$appBaseUrl = AppUrl::baseUrl($scheme, (string) $host);
SecurityHeaderPolicy::apply();

$redirect = static function (string $target): void {
    header('Location: ' . $target, true, 302);
    exit;
};

$resolvePdo = static function () use ($projectRoot) {
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $databaseConfig = require $projectRoot . '/config/database.php';
    $pdo = Connection::get($databaseConfig);

    return $pdo;
};

$users = static fn (): UserRepository => new UserRepository($resolvePdo());
$contacts = static fn (): ContactRepository => new ContactRepository($resolvePdo());
$referencesRepo = static fn (): ReferenceRepository => new ReferenceRepository($resolvePdo());
$mailer = static fn (): NativeMailTransport => new NativeMailTransport();

$buildAuthController = static function () use ($users): AuthController {
    $userRepository = $users();

    return new AuthController(
        new LoginService($userRepository),
        new AdminSetupService($userRepository),
        $userRepository,
    );
};

$dashboardSectionForPath = static function (string $path): string {
    return match ($path) {
        '/dashboard/postbox' => 'postbox',
        '/dashboard/references' => 'references',
        '/dashboard/profile' => 'profile',
        default => 'home',
    };
};

$isDashboardRoute = str_starts_with($path, '/dashboard');
$dashboardSection = $dashboardSectionForPath($path);

$requireAuth = static function (string $intendedPath) use ($redirect): array {
    if (!AuthSession::check()) {
        AuthSession::rememberIntendedPath($intendedPath);
        $_SESSION['flash_error'] = 'Bitte zuerst einloggen.';
        $redirect('/login');
    }

    return AuthSession::user() ?? [];
};

$requireCsrf = static function (string $fallback) use ($redirect): void {
    if (!Csrf::validate(Request::post('_csrf'))) {
        $_SESSION['flash_error'] = 'Ungültige Anfrage. Bitte erneut versuchen.';
        $redirect($fallback);
    }
};

$normalizeReferencePayload = static function (): array {
    return [
        'title' => trim(Request::post('title')),
        'industry' => trim(Request::post('industry')),
        'description' => trim(Request::post('description')),
        'outcome' => trim(Request::post('outcome')),
        'focus_lines' => trim(Request::post('focus_lines')),
        'url' => trim(Request::post('url')),
        'link_label' => trim(Request::post('link_label')),
        'sort_order' => (int) Request::post('sort_order'),
        'is_visible' => Request::post('is_visible') === '1' || Request::post('is_visible') === 'on',
    ];
};

$referenceValidationErrors = static function (array $payload): array {
    $errors = [];

    if ($payload['title'] === '') {
        $errors['title'] = 'Bitte einen Titel angeben.';
    }
    if ($payload['industry'] === '') {
        $errors['industry'] = 'Bitte eine Branche angeben.';
    }
    if ($payload['description'] === '') {
        $errors['description'] = 'Bitte eine Beschreibung angeben.';
    }
    if ($payload['outcome'] === '') {
        $errors['outcome'] = 'Bitte ein Ergebnis angeben.';
    }

    $focusItems = preg_split('/\r\n|\r|\n/', $payload['focus_lines']) ?: [];
    $focusItems = array_values(array_filter(array_map('trim', $focusItems), static fn (string $item): bool => $item !== ''));
    if ($focusItems === []) {
        $errors['focus_lines'] = 'Bitte mindestens einen Fokuspunkt angeben.';
    }

    if ($payload['url'] === '' || filter_var($payload['url'], FILTER_VALIDATE_URL) === false) {
        $errors['url'] = 'Bitte eine gültige URL angeben.';
    }
    if ($payload['link_label'] === '') {
        $errors['link_label'] = 'Bitte eine Button-Beschriftung angeben.';
    }

    return $errors;
};

$publicReferences = static function () use ($referencesRepo): array {
    try {
        return $referencesRepo()->listVisible();
    } catch (Throwable) {
        return HomepageContent::references();
    }
};

if (Request::method() === 'GET' && $path === '/sitemap.xml') {
    $urls = ['/', '/leistungen', '/referenzen', '/kontakt', '/impressum', '/datenschutz'];
    $lastMod = gmdate('c');

    header('Content-Type: application/xml; charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $urlPath) {
        $loc = htmlspecialchars(AppUrl::absolute($appBaseUrl, $urlPath), ENT_QUOTES, 'UTF-8');
        echo '<url><loc>' . $loc . '</loc><lastmod>' . $lastMod . '</lastmod></url>';
    }
    echo '</urlset>';
    exit;
}

$action = $_POST['_action'] ?? '';

if (Request::method() === 'POST' && $action === 'contact.submit') {
    ContactController::guardSubmitRequest();

    try {
        $contactController = new ContactController(new ContactSubmissionService($contacts()));
        $contactController->submit();
    } catch (Throwable) {
        $_SESSION['flash_error'] = 'Kontaktanfrage konnte temporär nicht gespeichert werden. Bitte später erneut versuchen.';
        $_SESSION['old'] = [
            'name' => Request::post('name'),
            'company' => Request::post('company'),
            'email' => Request::post('email'),
            'phone' => Request::post('phone'),
            'message' => Request::post('message'),
        ];
        $redirect('/kontakt');
    }
}

if (Request::method() === 'POST' && $action === 'auth.login') {
    try {
        $buildAuthController()->login();
    } catch (Throwable) {
        $_SESSION['flash_error'] = 'Login ist aktuell nicht verfügbar. Bitte später erneut versuchen.';
        $_SESSION['old'] = ['auth_email' => Request::post('email')];
        $redirect('/login');
    }
}

if (Request::method() === 'POST' && $action === 'auth.setup') {
    try {
        $buildAuthController()->setupInitialAdmin();
    } catch (Throwable) {
        $_SESSION['flash_error'] = 'Der erste Zugang konnte aktuell nicht eingerichtet werden. Bitte später erneut versuchen.';
        $_SESSION['old'] = [
            'setup_display_name' => Request::post('display_name'),
            'setup_email' => Request::post('email'),
        ];
        $redirect('/login');
    }
}

if (Request::method() === 'POST' && $action === 'auth.logout') {
    $buildAuthController()->logout();
}

if (Request::method() === 'POST' && $action === 'dashboard.contact.update_meta') {
    $authUser = $requireAuth('/dashboard/postbox');
    $contactId = (int) Request::post('contact_id');
    $fallback = '/dashboard/postbox' . ($contactId > 0 ? '?contact=' . $contactId : '');
    $requireCsrf($fallback);

    $contact = $contacts()->findById($contactId);
    if ($contact === null) {
        $_SESSION['flash_error'] = 'Kontaktanfrage wurde nicht gefunden.';
        $redirect('/dashboard/postbox');
    }

    $status = trim(Request::post('status'));
    $allowedStatuses = ['new', 'in_progress', 'answered', 'archived'];
    if (!in_array($status, $allowedStatuses, true)) {
        $_SESSION['flash_error'] = 'Ungültiger Status.';
        $redirect($fallback);
    }

    $contacts()->updateMeta($contactId, $status, Request::post('admin_note'));
    $_SESSION['flash_success'] = 'Postbox-Eintrag wurde aktualisiert.';
    $redirect($fallback);
}

if (Request::method() === 'POST' && $action === 'dashboard.contact.reply') {
    $authUser = $requireAuth('/dashboard/postbox');
    $contactId = (int) Request::post('contact_id');
    $fallback = '/dashboard/postbox' . ($contactId > 0 ? '?contact=' . $contactId : '');
    $requireCsrf($fallback);

    $contact = $contacts()->findById($contactId);
    if ($contact === null) {
        $_SESSION['flash_error'] = 'Kontaktanfrage wurde nicht gefunden.';
        $redirect('/dashboard/postbox');
    }

    $subject = trim(Request::post('subject'));
    $body = trim(Request::post('body'));
    $errors = [];
    if ($subject === '') {
        $errors['subject'] = 'Bitte einen Betreff angeben.';
    }
    if ($body === '') {
        $errors['body'] = 'Bitte eine Antwort eingeben.';
    }

    if ($errors !== []) {
        $_SESSION['flash_error'] = 'Bitte prüfen Sie die markierten Eingaben.';
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['old'] = [
            'reply_subject' => $subject,
            'reply_body' => $body,
        ];
        $redirect($fallback);
    }

    $fromAddress = trim((string) Env::get('MAIL_FROM_ADDRESS', 'info@rddigital.de'));
    $fromName = trim((string) Env::get('MAIL_FROM_NAME', 'RD Formstack Solutions'));
    $mailResult = $mailer()->send((string) $contact['email'], $subject, $body, $fromAddress, $fromName);
    $contacts()->addReply(
        $contactId,
        (int) ($authUser['id'] ?? 0),
        (string) $contact['email'],
        $subject,
        $body,
        $mailResult['ok'] === true,
        $mailResult['error'] ?? null,
    );

    if ($mailResult['ok'] === true) {
        $contacts()->markAnswered($contactId);
        $_SESSION['flash_success'] = 'Antwort wurde per E-Mail versendet.';
        unset($_SESSION['old']);
    } else {
        $_SESSION['flash_error'] = 'Antwort gespeichert, aber Mailversand fehlgeschlagen: ' . ($mailResult['error'] ?? 'Unbekannter Fehler.');
        $_SESSION['old'] = [
            'reply_subject' => $subject,
            'reply_body' => $body,
        ];
    }

    $redirect($fallback);
}

if (Request::method() === 'POST' && $action === 'dashboard.reference.save') {
    $requireAuth('/dashboard/references');
    $referenceId = (int) Request::post('reference_id');
    $editingNew = $referenceId <= 0;
    $fallback = '/dashboard/references?reference=' . ($editingNew ? 'new' : $referenceId);
    $requireCsrf($fallback);

    $payload = $normalizeReferencePayload();
    $errors = $referenceValidationErrors($payload);
    if ($errors !== []) {
        $_SESSION['flash_error'] = 'Bitte prüfen Sie die Referenzdaten.';
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['old'] = $payload;
        $redirect($fallback);
    }

    $savedId = $referencesRepo()->save($payload, $editingNew ? null : $referenceId);
    $_SESSION['flash_success'] = $editingNew ? 'Referenz wurde angelegt.' : 'Referenz wurde aktualisiert.';
    unset($_SESSION['old']);
    $redirect('/dashboard/references?reference=' . $savedId);
}

if (Request::method() === 'POST' && $action === 'dashboard.reference.delete') {
    $requireAuth('/dashboard/references');
    $requireCsrf('/dashboard/references');
    $referenceId = (int) Request::post('reference_id');
    if ($referenceId > 0) {
        $referencesRepo()->delete($referenceId);
        $_SESSION['flash_success'] = 'Referenz wurde gelöscht.';
    }
    $redirect('/dashboard/references');
}

if (Request::method() === 'POST' && $action === 'dashboard.profile.update') {
    $authUser = $requireAuth('/dashboard/profile');
    $requireCsrf('/dashboard/profile');

    $displayName = trim(Request::post('display_name'));
    $email = strtolower(trim(Request::post('email')));
    $errors = [];

    if ($displayName === '') {
        $errors['display_name'] = 'Bitte einen Namen angeben.';
    }
    if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Bitte eine gültige E-Mail-Adresse angeben.';
    } elseif ($users()->emailExistsForOtherUser($email, (int) ($authUser['id'] ?? 0))) {
        $errors['email'] = 'Diese E-Mail-Adresse wird bereits verwendet.';
    }

    if ($errors !== []) {
        $_SESSION['flash_error'] = 'Bitte prüfen Sie die Profildaten.';
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['old'] = [
            'profile_display_name' => $displayName,
            'profile_email' => $email,
        ];
        $redirect('/dashboard/profile');
    }

    $users()->updateProfile((int) ($authUser['id'] ?? 0), $displayName, $email);
    $updatedUser = $users()->findById((int) ($authUser['id'] ?? 0));
    if (is_array($updatedUser)) {
        AuthSession::login($updatedUser);
    }

    $_SESSION['flash_success'] = 'Profil wurde aktualisiert.';
    $redirect('/dashboard/profile');
}

if (Request::method() === 'POST' && $action === 'dashboard.password.update') {
    $authUser = $requireAuth('/dashboard/profile');
    $requireCsrf('/dashboard/profile');

    $currentPassword = Request::post('current_password');
    $newPassword = Request::post('new_password');
    $confirmation = Request::post('new_password_confirmation');
    $dbUser = $users()->findById((int) ($authUser['id'] ?? 0));
    $errors = [];

    if (!is_array($dbUser) || !password_verify($currentPassword, (string) ($dbUser['password_hash'] ?? ''))) {
        $errors['current_password'] = 'Das aktuelle Passwort ist nicht korrekt.';
    }
    if (strlen($newPassword) < 8) {
        $errors['new_password'] = 'Das neue Passwort muss mindestens 8 Zeichen lang sein.';
    }
    if ($newPassword !== $confirmation) {
        $errors['new_password_confirmation'] = 'Die Passwort-Bestätigung stimmt nicht überein.';
    }

    if ($errors !== []) {
        $_SESSION['flash_error'] = 'Bitte prüfen Sie die Passwortdaten.';
        $_SESSION['flash_errors'] = $errors;
        $redirect('/dashboard/profile');
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    if (!is_string($hash) || $hash === '') {
        $_SESSION['flash_error'] = 'Passwort konnte nicht aktualisiert werden.';
        $redirect('/dashboard/profile');
    }

    $users()->updatePassword((int) ($authUser['id'] ?? 0), $hash);
    $updatedUser = $users()->findById((int) ($authUser['id'] ?? 0));
    if (is_array($updatedUser)) {
        AuthSession::login($updatedUser);
    }

    $_SESSION['flash_success'] = 'Passwort wurde aktualisiert.';
    $redirect('/dashboard/profile');
}

if ($path === '/login' && AuthSession::check()) {
    $redirect('/dashboard');
}

if ($isDashboardRoute) {
    $requireAuth($path . ($queryString !== '' ? '?' . $queryString : ''));
}

$routes = RouteCatalog::pages();
$isNotFound = !isset($routes[$path]);
if ($isNotFound) {
    http_response_code(404);
    $page = [
        'title' => 'Seite nicht gefunden',
        'description' => 'Die angeforderte Seite konnte nicht gefunden werden.',
        'headline' => 'Diese Seite wurde nicht gefunden',
        'intro' => 'Der Link ist möglicherweise veraltet oder die Adresse wurde falsch eingegeben. Nutzen Sie die Navigation oder starten Sie über die Startseite neu.',
    ];
} else {
    $page = $routes[$path];
}

$authSetupAvailable = false;
$authRuntimeError = false;
if ($path === '/login') {
    try {
        $authSetupAvailable = $users()->countUsers() === 0;
    } catch (Throwable) {
        $authRuntimeError = true;
    }
}

$flashError = $_SESSION['flash_error'] ?? null;
$flashErrors = $_SESSION['flash_errors'] ?? [];
$flashSuccess = $_SESSION['flash_success'] ?? null;
$old = $_SESSION['old'] ?? [];
unset($_SESSION['flash_error'], $_SESSION['flash_errors'], $_SESSION['flash_success'], $_SESSION['old']);

$services = HomepageContent::services();
$references = $publicReferences();
$contactHighlights = HomepageContent::contactHighlights();
$processSteps = HomepageContent::processSteps();
$nextSteps = HomepageContent::nextSteps();
$mobileActionCta = HomepageContent::mobileActionCta($path);
$bodyClass = 'page-' . ($path === '/' ? 'home' : trim(str_replace('/', '-', $path), '-'));
$canonicalUrl = AppUrl::absolute($appBaseUrl, $path);
$siteName = 'RD Formstack Solutions';
$metaTitle = $siteName . ' | ' . $page['title'];
$metaRobots = $isNotFound || $path === '/login' || $path === '/dms' || str_starts_with($path, '/dashboard')
    ? 'noindex,follow,max-image-preview:large'
    : 'index,follow,max-image-preview:large';
$authUser = AuthSession::user();

$dashboardContacts = [];
$dashboardSelectedContact = null;
$dashboardReplies = [];
$dashboardReferences = [];
$dashboardSelectedReference = null;
$dashboardReferenceForm = [];
$dashboardStats = ['open_contacts' => 0, 'references_total' => 0, 'references_visible' => 0];
$dashboardProfileUser = is_array($authUser) ? $users()->findById((int) ($authUser['id'] ?? 0)) : null;

if ($isDashboardRoute && is_array($authUser)) {
    try {
        $dashboardStats['open_contacts'] = $contacts()->countOpen();
        $dashboardReferences = $referencesRepo()->listAll();
        $dashboardStats['references_total'] = count($dashboardReferences);
        $dashboardStats['references_visible'] = count(array_filter($dashboardReferences, static fn (array $item): bool => !empty($item['is_visible'])));

        if ($dashboardSection === 'postbox' || $dashboardSection === 'home') {
            $dashboardContacts = $contacts()->listForDashboard();
            $selectedContactId = isset($_GET['contact']) ? (int) $_GET['contact'] : (isset($dashboardContacts[0]['id']) ? (int) $dashboardContacts[0]['id'] : 0);
            if ($selectedContactId > 0) {
                $dashboardSelectedContact = $contacts()->findById($selectedContactId);
                if (is_array($dashboardSelectedContact)) {
                    $dashboardReplies = $contacts()->listReplies($selectedContactId);
                }
            }
        }

        if ($dashboardSection === 'references') {
            $selectedReferenceRaw = $_GET['reference'] ?? null;
            if ($selectedReferenceRaw === 'new') {
                $dashboardSelectedReference = null;
            } elseif (is_string($selectedReferenceRaw) && ctype_digit($selectedReferenceRaw)) {
                $dashboardSelectedReference = $referencesRepo()->findById((int) $selectedReferenceRaw);
            }

            $dashboardReferenceForm = is_array($old) && $old !== [] ? $old : (
                is_array($dashboardSelectedReference)
                    ? [
                        'title' => $dashboardSelectedReference['title'],
                        'industry' => $dashboardSelectedReference['industry'],
                        'description' => $dashboardSelectedReference['description'],
                        'outcome' => $dashboardSelectedReference['outcome'],
                        'focus_lines' => $dashboardSelectedReference['focus_lines'],
                        'url' => $dashboardSelectedReference['url'],
                        'link_label' => $dashboardSelectedReference['linkLabel'],
                        'sort_order' => $dashboardSelectedReference['sort_order'],
                        'is_visible' => $dashboardSelectedReference['is_visible'],
                    ]
                    : [
                        'title' => '',
                        'industry' => '',
                        'description' => '',
                        'outcome' => '',
                        'focus_lines' => '',
                        'url' => '',
                        'link_label' => 'Zur Website',
                        'sort_order' => ($dashboardStats['references_total'] + 1) * 10,
                        'is_visible' => true,
                    ]
            );
        }

        if ($dashboardSection === 'profile' && is_array($dashboardProfileUser) && $old === []) {
            $old = [
                'profile_display_name' => (string) ($dashboardProfileUser['display_name'] ?? ''),
                'profile_email' => (string) ($dashboardProfileUser['email'] ?? ''),
            ];
        }
    } catch (Throwable) {
        if ($flashError === null) {
            $flashError = 'Ein Dashboard-Modul konnte nicht vollständig geladen werden. Bitte prüfen Sie die Datenbankmigrationen.';
        }
    }
}

$structuredData = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => AppUrl::absolute($appBaseUrl, '/'),
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => AppUrl::absolute($appBaseUrl, '/'),
        'inLanguage' => 'de-DE',
    ],
];

$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$navLink = static function (string $href, string $label, string $currentPath) use ($e): string {
    $isDashboardActive = $href === '/dashboard' && str_starts_with($currentPath, '/dashboard');
    $active = $href === $currentPath || $isDashboardActive ? ' is-active' : '';
    $ariaCurrent = $href === $currentPath || $isDashboardActive ? ' aria-current="page"' : '';
    return '<a class="nav-link' . $active . '" href="' . $e($href) . '"' . $ariaCurrent . '>' . $e($label) . '</a>';
};

SiteRenderer::render('layout.php', [
    'authRuntimeError' => $authRuntimeError,
    'authSetupAvailable' => $authSetupAvailable,
    'authUser' => $authUser,
    'bodyClass' => $bodyClass,
    'canonicalUrl' => $canonicalUrl,
    'contactHighlights' => $contactHighlights,
    'csrfToken' => Csrf::token(),
    'dashboardContacts' => $dashboardContacts,
    'dashboardProfileUser' => $dashboardProfileUser,
    'dashboardReferenceForm' => $dashboardReferenceForm,
    'dashboardReferences' => $dashboardReferences,
    'dashboardReplies' => $dashboardReplies,
    'dashboardSection' => $dashboardSection,
    'dashboardSelectedContact' => $dashboardSelectedContact,
    'dashboardSelectedReference' => $dashboardSelectedReference,
    'dashboardStats' => $dashboardStats,
    'e' => $e,
    'flashError' => $flashError,
    'flashErrors' => $flashErrors,
    'flashSuccess' => $flashSuccess,
    'metaRobots' => $metaRobots,
    'metaTitle' => $metaTitle,
    'mobileActionCta' => $mobileActionCta,
    'navLink' => $navLink,
    'nextSteps' => $nextSteps,
    'old' => $old,
    'page' => $page,
    'path' => $path,
    'processSteps' => $processSteps,
    'references' => $references,
    'services' => $services,
    'siteName' => $siteName,
    'structuredData' => $structuredData,
]);
