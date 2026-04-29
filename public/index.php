<?php

declare(strict_types=1);

use App\Bootstrap\AppBootstrap;
use App\Application\Contact\ContactSubmissionService;
use App\Controller\ContactController;
use App\Database\Connection;
use App\Http\ErrorHandler;
use App\Http\Routing\RouteCatalog;
use App\Http\Request;
use App\Repository\ContactRepository;
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
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$appBaseUrl = AppUrl::baseUrl($scheme, (string) $host);
SecurityHeaderPolicy::apply();

if (Request::method() === 'GET' && $path === '/sitemap.xml') {
    $urls = ['/', '/leistungen', '/referenzen', '/kontakt'];
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

if (Request::method() === 'POST' && ($_POST['_action'] ?? '') === 'contact.submit') {
    ContactController::guardSubmitRequest();

    try {
        $databaseConfig = require $projectRoot . '/config/database.php';
        $pdo = Connection::get($databaseConfig);
        $contactRepository = new ContactRepository($pdo);
        $contactService = new ContactSubmissionService($contactRepository);
        $contactController = new ContactController($contactService);
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
        header('Location: /kontakt', true, 302);
        exit;
    }
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

$flashError = $_SESSION['flash_error'] ?? null;
$flashErrors = $_SESSION['flash_errors'] ?? [];
$flashSuccess = $_SESSION['flash_success'] ?? null;
$old = $_SESSION['old'] ?? [];
unset($_SESSION['flash_error'], $_SESSION['flash_errors'], $_SESSION['flash_success']);

$services = HomepageContent::services();
$references = HomepageContent::references();
$contactHighlights = HomepageContent::contactHighlights();
$processSteps = HomepageContent::processSteps();
$nextSteps = HomepageContent::nextSteps();
$mobileActionCta = HomepageContent::mobileActionCta($path);
$bodyClass = 'page-' . ($path === '/' ? 'home' : trim(str_replace('/', '-', $path), '-'));
$canonicalUrl = AppUrl::absolute($appBaseUrl, $path);
$siteName = 'RD Formstack Solutions';
$metaTitle = $siteName . ' | ' . $page['title'];
$metaRobots = $isNotFound || in_array($path, ['/login', '/dms'], true)
    ? 'noindex,follow,max-image-preview:large'
    : 'index,follow,max-image-preview:large';

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
    $active = $href === $currentPath ? ' is-active' : '';
    $ariaCurrent = $href === $currentPath ? ' aria-current="page"' : '';
    return '<a class="nav-link' . $active . '" href="' . $e($href) . '"' . $ariaCurrent . '>' . $e($label) . '</a>';
};

SiteRenderer::render('layout.php', [
    'bodyClass' => $bodyClass,
    'canonicalUrl' => $canonicalUrl,
    'contactHighlights' => $contactHighlights,
    'csrfToken' => Csrf::token(),
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
