<?php

declare(strict_types=1);

$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require_once $vendorAutoload;
}

use App\Application\Auth\AdminSetupService;
use App\Application\Auth\LoginService;
use App\Application\Contact\ContactSubmissionService;
use App\Application\Lead\LeadInboxSyncService;
use App\Bootstrap\AppBootstrap;
use App\Config\Env;
use App\Controller\AuthController;
use App\Controller\ContactController;
use App\Database\Connection;
use App\Http\ErrorHandler;
use App\Http\Request;
use App\Http\Routing\RouteCatalog;
use App\Mail\ImapMailboxReader;
use App\Mail\NativeMailTransport;
use App\Repository\ContactRepository;
use App\Repository\DmsRepository;
use App\Repository\OutreachRepository;
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
$dms = static fn (): DmsRepository => new DmsRepository($resolvePdo());
$outreach = static fn (): OutreachRepository => new OutreachRepository($resolvePdo());
$referencesRepo = static fn (): ReferenceRepository => new ReferenceRepository($resolvePdo());
$mailer = static fn (): NativeMailTransport => new NativeMailTransport();
$leadInboxSync = static fn (): LeadInboxSyncService => new LeadInboxSyncService($contacts(), new ImapMailboxReader());

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
        '/dashboard/inbox' => 'inbox',
        '/dashboard/outreach' => 'outreach',
        '/dashboard/dms' => 'dms',
        '/dashboard/users' => 'users',
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

$normalizeDmsPayload = static function (): array {
    return [
        'dms_title' => trim(Request::post('dms_title')),
        'dms_category' => trim(Request::post('dms_category')),
        'dms_summary' => trim(Request::post('dms_summary')),
        'dms_change_note' => trim(Request::post('dms_change_note')),
    ];
};

$normalizeDmsReviewNote = static fn (): string => trim(Request::post('dms_review_note'));

$readUploadedDmsFile = static function (string $field): array {
    $file = $_FILES[$field] ?? null;
    if (!is_array($file)) {
        return ['error' => 'Bitte eine Datei auswählen.'];
    }

    $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($errorCode !== UPLOAD_ERR_OK) {
        return ['error' => $errorCode === UPLOAD_ERR_NO_FILE
            ? 'Bitte eine Datei auswählen.'
            : 'Datei konnte nicht hochgeladen werden.'];
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return ['error' => 'Upload wurde vom Server nicht akzeptiert.'];
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0) {
        return ['error' => 'Die ausgewählte Datei ist leer.'];
    }
    if ($size > 15 * 1024 * 1024) {
        return ['error' => 'Bitte nur Dateien bis maximal 15 MB hochladen.'];
    }

    $originalFilename = basename(str_replace('\\', '/', (string) ($file['name'] ?? 'dokument')));
    $safeFilename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $originalFilename) ?? 'dokument';
    $safeFilename = trim($safeFilename, '-.');
    if ($safeFilename === '') {
        $safeFilename = 'dokument';
    }

    $content = file_get_contents($tmpName);
    if (!is_string($content) || $content === '') {
        return ['error' => 'Dateiinhalt konnte nicht gelesen werden.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpName);

    return [
        'original_filename' => $safeFilename,
        'mime_type' => is_string($mimeType) && $mimeType !== '' ? $mimeType : 'application/octet-stream',
        'file_size' => $size,
        'binary_content' => $content,
    ];
};

$allowedDashboardRoles = ['admin', 'reviewer', 'editor'];
$normalizeUserRole = static function (string $role) use ($allowedDashboardRoles): string {
    $normalized = strtolower(trim($role));
    return in_array($normalized, $allowedDashboardRoles, true) ? $normalized : 'editor';
};

$userRoleLabel = static function (string $role): string {
    return match (strtolower(trim($role))) {
        'admin' => 'Admin',
        'reviewer' => 'Reviewer',
        'editor' => 'Editor',
        default => 'Unbekannt',
    };
};

$isAdminUser = static function (array $user): bool {
    return strtolower(trim((string) ($user['role'] ?? ''))) === 'admin';
};

$isDmsApprover = static function (array $user): bool {
    $role = strtolower(trim((string) ($user['role'] ?? '')));
    return in_array($role, ['admin', 'reviewer'], true);
};

$requireDmsApprover = static function (array $user, string $fallback) use ($redirect, $isDmsApprover): void {
    if (!$isDmsApprover($user)) {
        $_SESSION['flash_error'] = 'Für diese DMS-Freigabeaktion fehlen die Rechte.';
        $redirect($fallback);
    }
};

$requireAdminUser = static function (array $user, string $fallback) use ($redirect, $isAdminUser): void {
    if (!$isAdminUser($user)) {
        $_SESSION['flash_error'] = 'Für diese Verwaltungsaktion fehlen die Rechte.';
        $redirect($fallback);
    }
};

$normalizeOutreachPayload = static function (): array {
    return [
        'title' => trim(Request::post('title')),
        'subject' => trim(Request::post('subject')),
        'body' => trim(Request::post('body')),
        'from_email' => trim(Request::post('from_email')),
        'from_name' => trim(Request::post('from_name')),
        'recipients_raw' => trim(Request::post('recipients_raw')),
        'allow_known_resend' => Request::post('allow_known_resend') === '1' || Request::post('allow_known_resend') === 'on',
    ];
};

$parseOutreachRecipients = static function (string $raw): array {
    $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
    $items = [];

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') {
            continue;
        }

        $parts = array_map('trim', explode('|', $trimmed));
        $items[] = [
            'email' => $parts[0] ?? '',
            'company_name' => $parts[1] ?? '',
            'contact_name' => $parts[2] ?? '',
            'notes' => $parts[3] ?? '',
        ];
    }

    return $items;
};

$findDuplicateRecipientEmails = static function (array $recipients): array {
    $seen = [];
    $duplicates = [];

    foreach ($recipients as $recipient) {
        $email = strtolower(trim((string) ($recipient['email'] ?? '')));
        if ($email === '') {
            continue;
        }

        if (isset($seen[$email])) {
            $duplicates[$email] = true;
            continue;
        }

        $seen[$email] = true;
    }

    return array_keys($duplicates);
};

$formatResendConflictList = static function (array $matches): string {
    if ($matches === []) {
        return '';
    }

    $items = [];
    foreach ($matches as $match) {
        $items[] = sprintf(
            '%s (Kampagne #%d "%s" am %s)',
            (string) ($match['email'] ?? ''),
            (int) ($match['campaign_id'] ?? 0),
            (string) ($match['title'] ?? ''),
            (string) ($match['sent_at'] ?? '')
        );
    }

    return implode('; ', $items);
};

$outreachValidationErrors = static function (array $payload, array $recipients): array {
    $errors = [];

    if ($payload['title'] === '') {
        $errors['title'] = 'Bitte einen Kampagnentitel angeben.';
    }
    if ($payload['subject'] === '') {
        $errors['subject'] = 'Bitte einen Betreff angeben.';
    }
    if ($payload['body'] === '') {
        $errors['body'] = 'Bitte ein Anschreiben angeben.';
    }
    if ($payload['from_name'] === '') {
        $errors['from_name'] = 'Bitte einen Absendernamen angeben.';
    }
    if ($payload['from_email'] === '' || filter_var($payload['from_email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['from_email'] = 'Bitte eine gültige Absender-E-Mail angeben.';
    }
    if ($recipients === []) {
        $errors['recipients_raw'] = 'Bitte mindestens einen Empfänger eintragen.';
        return $errors;
    }

    foreach ($recipients as $index => $recipient) {
        if (($recipient['email'] ?? '') === '' || filter_var((string) $recipient['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['recipients_raw'] = 'Mindestens eine Empfängerzeile enthält keine gültige E-Mail-Adresse (Zeile ' . ($index + 1) . ').';
            break;
        }
    }

    return $errors;
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

if (Request::method() === 'POST' && $action === 'dashboard.outreach.save') {
    $authUser = $requireAuth('/dashboard/outreach');
    $campaignId = (int) Request::post('campaign_id');
    $fallback = '/dashboard/outreach' . ($campaignId > 0 ? '?campaign=' . $campaignId : '');
    $requireCsrf($fallback);

    $payload = $normalizeOutreachPayload();
    $recipients = $parseOutreachRecipients($payload['recipients_raw']);
    $errors = $outreachValidationErrors($payload, $recipients);

    $duplicateEmails = $findDuplicateRecipientEmails($recipients);
    if ($duplicateEmails !== []) {
        $errors['recipients_raw'] = 'Doppelte Empfänger in der Liste sind nicht erlaubt: ' . implode(', ', $duplicateEmails);
    }

    $previouslySent = $outreach()->findPreviouslySentRecipientUsage(
        array_map(static fn (array $recipient): string => (string) ($recipient['email'] ?? ''), $recipients),
        $campaignId > 0 ? $campaignId : null
    );
    if ($previouslySent !== [] && empty($payload['allow_known_resend'])) {
        $errors['allow_known_resend'] = 'Mindestens eine Adresse wurde bereits angeschrieben. Für ein bewusstes Re-Send bitte aktiv bestätigen: ' . $formatResendConflictList(array_values($previouslySent));
    }

    if ($errors !== []) {
        $_SESSION['flash_error'] = 'Bitte prüfen Sie Anschreiben und Empfängerliste.';
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['old'] = $payload;
        $redirect($fallback);
    }

    if ($campaignId > 0 && $outreach()->findCampaignById($campaignId) !== null) {
        $outreach()->updateCampaign(
            $campaignId,
            $payload['title'],
            $payload['subject'],
            $payload['body'],
            $payload['from_email'],
            $payload['from_name'],
            !empty($payload['allow_known_resend']),
            (int) ($authUser['id'] ?? 0)
        );
    } else {
        $campaignId = $outreach()->createCampaign(
            (int) ($authUser['id'] ?? 0),
            $payload['title'],
            $payload['subject'],
            $payload['body'],
            $payload['from_email'],
            $payload['from_name'],
            !empty($payload['allow_known_resend'])
        );
    }

    $outreach()->replaceRecipients($campaignId, $recipients, (int) ($authUser['id'] ?? 0));
    $_SESSION['flash_success'] = 'Outreach-Entwurf gespeichert. Versand bleibt blockiert, bis du Anschreiben und Empfängerliste freigibst.';
    $redirect('/dashboard/outreach?campaign=' . $campaignId);
}

if (Request::method() === 'POST' && $action === 'dashboard.outreach.approve') {
    $authUser = $requireAuth('/dashboard/outreach');
    $campaignId = (int) Request::post('campaign_id');
    $fallback = '/dashboard/outreach' . ($campaignId > 0 ? '?campaign=' . $campaignId : '');
    $requireCsrf($fallback);

    $campaign = $campaignId > 0 ? $outreach()->findCampaignById($campaignId) : null;
    if ($campaign === null) {
        $_SESSION['flash_error'] = 'Kampagne wurde nicht gefunden.';
        $redirect('/dashboard/outreach');
    }

    $recipients = $outreach()->listRecipients($campaignId);
    if ($recipients === []) {
        $_SESSION['flash_error'] = 'Ohne Empfängerliste ist keine Freigabe möglich.';
        $redirect($fallback);
    }

    $outreach()->approveCampaign($campaignId, (int) ($authUser['id'] ?? 0));
    $_SESSION['flash_success'] = 'Anschreiben und Empfängerliste wurden freigegeben. Erst jetzt ist der Versandbutton aktiv.';
    $redirect($fallback);
}

if (Request::method() === 'POST' && $action === 'dashboard.outreach.send') {
    $authUser = $requireAuth('/dashboard/outreach');
    $campaignId = (int) Request::post('campaign_id');
    $fallback = '/dashboard/outreach' . ($campaignId > 0 ? '?campaign=' . $campaignId : '');
    $requireCsrf($fallback);

    $campaign = $campaignId > 0 ? $outreach()->findCampaignById($campaignId) : null;
    if ($campaign === null) {
        $_SESSION['flash_error'] = 'Kampagne wurde nicht gefunden.';
        $redirect('/dashboard/outreach');
    }
    if ((string) ($campaign['status'] ?? '') !== 'approved') {
        $_SESSION['flash_error'] = 'Versand erst nach Freigabe von Anschreiben und Empfängerliste möglich.';
        $redirect($fallback);
    }

    $approvedRecipients = $outreach()->listApprovedRecipients($campaignId);
    if ($approvedRecipients === []) {
        $_SESSION['flash_error'] = 'Es gibt keine freigegebenen Empfänger für den Versand.';
        $redirect($fallback);
    }

    if (empty($campaign['allow_known_resend'])) {
        $previouslySent = $outreach()->findPreviouslySentRecipientUsage(
            array_map(static fn (array $recipient): string => (string) ($recipient['email'] ?? ''), $approvedRecipients),
            $campaignId
        );
        if ($previouslySent !== []) {
            $_SESSION['flash_error'] = 'Versand gestoppt: Mindestens eine Adresse wurde bereits in einer anderen Kampagne angeschrieben. ' . $formatResendConflictList(array_values($previouslySent));
            $redirect($fallback);
        }
    }

    $outreach()->markSendStarted($campaignId, (int) ($authUser['id'] ?? 0), count($approvedRecipients));
    $sentCount = 0;
    $failedCount = 0;
    $failedEmails = [];

    foreach ($approvedRecipients as $recipient) {
        $mailResult = $mailer()->send(
            (string) $recipient['email'],
            (string) $campaign['subject'],
            (string) $campaign['body'],
            (string) $campaign['from_email'],
            (string) $campaign['from_name']
        );

        if (($mailResult['ok'] ?? false) === true) {
            $outreach()->markRecipientSent((int) $recipient['id']);
            $sentCount++;
        } else {
            $outreach()->markRecipientFailed((int) $recipient['id'], $mailResult['error'] ?? 'Unbekannter Fehler');
            $failedCount++;
            $failedEmails[] = (string) ($recipient['email'] ?? '');
        }
    }

    $finalStatus = $failedCount === 0 ? 'sent' : ($sentCount > 0 ? 'partial' : 'failed');
    $outreach()->markSendCompleted(
        $campaignId,
        (int) ($authUser['id'] ?? 0),
        $finalStatus,
        $sentCount,
        $failedCount,
        $failedEmails
    );

    $_SESSION['flash_success'] = sprintf('Outreach-Versand abgeschlossen: %d versendet, %d fehlgeschlagen.', $sentCount, $failedCount);
    if ($failedCount > 0) {
        $_SESSION['flash_error'] = 'Mindestens ein Empfänger konnte nicht versendet werden. Details stehen in der Empfängerliste.';
    }

    $redirect($fallback);
}

if (Request::method() === 'POST' && $action === 'dashboard.outreach.reset') {
    $authUser = $requireAuth('/dashboard/outreach');
    $campaignId = (int) Request::post('campaign_id');
    $fallback = '/dashboard/outreach' . ($campaignId > 0 ? '?campaign=' . $campaignId : '');
    $requireCsrf($fallback);

    $campaign = $campaignId > 0 ? $outreach()->findCampaignById($campaignId) : null;
    if ($campaign === null) {
        $_SESSION['flash_error'] = 'Kampagne wurde nicht gefunden.';
        $redirect('/dashboard/outreach');
    }

    $outreach()->resetCampaignToDraft($campaignId, (int) ($authUser['id'] ?? 0));
    $_SESSION['flash_success'] = 'Kampagne wurde auf Entwurf zurückgesetzt.';
    $redirect($fallback);
}

if (Request::method() === 'POST' && $action === 'dashboard.outreach.retry_failed') {
    $authUser = $requireAuth('/dashboard/outreach');
    $campaignId = (int) Request::post('campaign_id');
    $fallback = '/dashboard/outreach' . ($campaignId > 0 ? '?campaign=' . $campaignId : '');
    $requireCsrf($fallback);

    $campaign = $campaignId > 0 ? $outreach()->findCampaignById($campaignId) : null;
    if ($campaign === null) {
        $_SESSION['flash_error'] = 'Kampagne wurde nicht gefunden.';
        $redirect('/dashboard/outreach');
    }

    $retryCount = $outreach()->reapproveFailedRecipients($campaignId, (int) ($authUser['id'] ?? 0));
    if ($retryCount < 1) {
        $_SESSION['flash_error'] = 'Es gibt keine fehlgeschlagenen Empfänger für einen Retry.';
    } else {
        $_SESSION['flash_success'] = sprintf('%d fehlgeschlagene Empfänger wurden erneut freigegeben.', $retryCount);
    }
    $redirect($fallback);
}

if (Request::method() === 'POST' && $action === 'dashboard.outreach.duplicate') {
    $authUser = $requireAuth('/dashboard/outreach');
    $campaignId = (int) Request::post('campaign_id');
    $fallback = '/dashboard/outreach' . ($campaignId > 0 ? '?campaign=' . $campaignId : '');
    $requireCsrf($fallback);

    $newCampaignId = $campaignId > 0 ? $outreach()->duplicateCampaign($campaignId, (int) ($authUser['id'] ?? 0)) : 0;
    if ($newCampaignId < 1) {
        $_SESSION['flash_error'] = 'Kampagne konnte nicht dupliziert werden.';
        $redirect($fallback);
    }

    $_SESSION['flash_success'] = 'Kampagne wurde als neuer Entwurf dupliziert.';
    $redirect('/dashboard/outreach?campaign=' . $newCampaignId);
}

if (Request::method() === 'POST' && $action === 'dashboard.outreach.archive') {
    $authUser = $requireAuth('/dashboard/outreach');
    $campaignId = (int) Request::post('campaign_id');
    $fallback = '/dashboard/outreach' . ($campaignId > 0 ? '?campaign=' . $campaignId : '');
    $requireCsrf($fallback);

    $campaign = $campaignId > 0 ? $outreach()->findCampaignById($campaignId) : null;
    if ($campaign === null) {
        $_SESSION['flash_error'] = 'Kampagne wurde nicht gefunden.';
        $redirect('/dashboard/outreach');
    }

    $outreach()->archiveCampaign($campaignId, (int) ($authUser['id'] ?? 0));
    $_SESSION['flash_success'] = 'Kampagne wurde archiviert.';
    $redirect('/dashboard/outreach');
}

if (Request::method() === 'POST' && $action === 'dashboard.inbox.sync') {
    $requireAuth('/dashboard/inbox');
    $requireCsrf('/dashboard/inbox');

    $syncResult = $leadInboxSync()->sync();
    if (($syncResult['ok'] ?? false) === true) {
        $imported = (int) ($syncResult['imported'] ?? 0);
        $skipped = (int) ($syncResult['skipped'] ?? 0);
        $total = (int) ($syncResult['total'] ?? 0);
        $mailbox = (string) ($syncResult['mailbox'] ?? '');
        $_SESSION['flash_success'] = sprintf(
            'IMAP-Import abgeschlossen: %d neu angelegt, %d übersprungen, %d geprüft%s',
            $imported,
            $skipped,
            $total,
            $mailbox !== '' ? ' (' . $mailbox . ')' : ''
        );
    } else {
        $_SESSION['flash_error'] = (string) ($syncResult['error'] ?? 'IMAP-Import fehlgeschlagen.');
    }

    $redirect('/dashboard/inbox');
}

if (Request::method() === 'POST' && $action === 'dashboard.dms.create') {
    $authUser = $requireAuth('/dashboard/dms');
    $requireCsrf('/dashboard/dms');

    $payload = $normalizeDmsPayload();
    $errors = [];
    if ($payload['dms_title'] === '') {
        $errors['dms_title'] = 'Bitte einen Dokumenttitel angeben.';
    }
    if ($payload['dms_category'] === '') {
        $errors['dms_category'] = 'Bitte eine Kategorie angeben.';
    }

    $upload = $readUploadedDmsFile('document_file');
    if (isset($upload['error']) && is_string($upload['error'])) {
        $errors['document_file'] = $upload['error'];
    }

    if ($errors !== []) {
        $_SESSION['flash_error'] = 'Bitte DMS-Titel, Kategorie und Datei prüfen.';
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['old'] = $payload;
        $redirect('/dashboard/dms');
    }

    $documentId = $dms()->createDocument(
        (int) ($authUser['id'] ?? 0),
        $payload['dms_title'],
        $payload['dms_category'],
        $payload['dms_summary']
    );
    $dms()->addVersion(
        $documentId,
        (int) ($authUser['id'] ?? 0),
        (string) $upload['original_filename'],
        (string) $upload['mime_type'],
        (int) $upload['file_size'],
        (string) $upload['binary_content'],
        $payload['dms_change_note']
    );

    $_SESSION['flash_success'] = 'DMS-Dokument wurde angelegt und Version 1 hochgeladen.';
    $redirect('/dashboard/dms?document=' . $documentId);
}

if (Request::method() === 'POST' && $action === 'dashboard.dms.update_meta') {
    $authUser = $requireAuth('/dashboard/dms');
    $documentId = (int) Request::post('document_id');
    $fallback = '/dashboard/dms' . ($documentId > 0 ? '?document=' . $documentId : '');
    $requireCsrf($fallback);

    $document = $documentId > 0 ? $dms()->findDocumentById($documentId) : null;
    if (!is_array($document)) {
        $_SESSION['flash_error'] = 'DMS-Dokument wurde nicht gefunden.';
        $redirect('/dashboard/dms');
    }

    $payload = $normalizeDmsPayload();
    $errors = [];
    if ($payload['dms_title'] === '') {
        $errors['dms_title'] = 'Bitte einen Dokumenttitel angeben.';
    }
    if ($payload['dms_category'] === '') {
        $errors['dms_category'] = 'Bitte eine Kategorie angeben.';
    }

    if ($errors !== []) {
        $_SESSION['flash_error'] = 'Bitte Metadaten prüfen.';
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['old'] = $payload;
        $redirect($fallback);
    }

    $dms()->updateDocumentMeta(
        $documentId,
        $payload['dms_title'],
        $payload['dms_category'],
        $payload['dms_summary'],
        (int) ($authUser['id'] ?? 0)
    );

    $_SESSION['flash_success'] = 'DMS-Metadaten wurden aktualisiert.';
    $redirect($fallback);
}

if (Request::method() === 'POST' && $action === 'dashboard.dms.upload_version') {
    $authUser = $requireAuth('/dashboard/dms');
    $documentId = (int) Request::post('document_id');
    $fallback = '/dashboard/dms' . ($documentId > 0 ? '?document=' . $documentId : '');
    $requireCsrf($fallback);

    $document = $documentId > 0 ? $dms()->findDocumentById($documentId) : null;
    if (!is_array($document)) {
        $_SESSION['flash_error'] = 'DMS-Dokument wurde nicht gefunden.';
        $redirect('/dashboard/dms');
    }

    $payload = $normalizeDmsPayload();
    $upload = $readUploadedDmsFile('document_file');
    if (isset($upload['error']) && is_string($upload['error'])) {
        $_SESSION['flash_error'] = $upload['error'];
        $_SESSION['old'] = [
            'dms_title' => (string) ($document['title'] ?? ''),
            'dms_category' => (string) ($document['category'] ?? ''),
            'dms_summary' => (string) ($document['summary'] ?? ''),
            'dms_change_note' => $payload['dms_change_note'],
        ];
        $redirect($fallback);
    }

    $dms()->addVersion(
        $documentId,
        (int) ($authUser['id'] ?? 0),
        (string) $upload['original_filename'],
        (string) $upload['mime_type'],
        (int) $upload['file_size'],
        (string) $upload['binary_content'],
        $payload['dms_change_note']
    );

    $_SESSION['flash_success'] = 'Neue Dokumentversion wurde hochgeladen. Status steht wieder auf Draft bis zur erneuten Freigabe.';
    $redirect($fallback);
}

if (Request::method() === 'POST' && $action === 'dashboard.dms.submit') {
    $authUser = $requireAuth('/dashboard/dms');
    $documentId = (int) Request::post('document_id');
    $fallback = '/dashboard/dms' . ($documentId > 0 ? '?document=' . $documentId : '');
    $requireCsrf($fallback);

    $document = $documentId > 0 ? $dms()->findDocumentById($documentId) : null;
    if (!is_array($document)) {
        $_SESSION['flash_error'] = 'DMS-Dokument wurde nicht gefunden.';
        $redirect('/dashboard/dms');
    }
    if ((int) ($document['current_version_id'] ?? 0) <= 0) {
        $_SESSION['flash_error'] = 'Ohne hochgeladene Version ist keine Freigabe möglich.';
        $redirect($fallback);
    }

    $reviewNote = $normalizeDmsReviewNote();
    $dms()->submitForApproval($documentId, (int) ($authUser['id'] ?? 0), $reviewNote);
    $_SESSION['flash_success'] = $reviewNote !== ''
        ? 'Dokument wurde mit Review-Notiz zur Freigabe eingereicht.'
        : 'Dokument wurde zur Freigabe eingereicht.';
    $redirect($fallback);
}

if (Request::method() === 'POST' && $action === 'dashboard.dms.approve') {
    $authUser = $requireAuth('/dashboard/dms');
    $documentId = (int) Request::post('document_id');
    $fallback = '/dashboard/dms' . ($documentId > 0 ? '?document=' . $documentId : '');
    $requireCsrf($fallback);
    $requireDmsApprover($authUser, $fallback);

    $document = $documentId > 0 ? $dms()->findDocumentById($documentId) : null;
    if (!is_array($document)) {
        $_SESSION['flash_error'] = 'DMS-Dokument wurde nicht gefunden.';
        $redirect('/dashboard/dms');
    }

    $reviewNote = $normalizeDmsReviewNote();
    $dms()->approveDocument($documentId, (int) ($authUser['id'] ?? 0), $reviewNote);
    $_SESSION['flash_success'] = $reviewNote !== ''
        ? 'Dokument wurde mit Review-Kommentar freigegeben.'
        : 'Dokument wurde freigegeben.';
    $redirect($fallback);
}

if (Request::method() === 'POST' && $action === 'dashboard.dms.reset') {
    $authUser = $requireAuth('/dashboard/dms');
    $documentId = (int) Request::post('document_id');
    $fallback = '/dashboard/dms' . ($documentId > 0 ? '?document=' . $documentId : '');
    $requireCsrf($fallback);
    $requireDmsApprover($authUser, $fallback);

    $document = $documentId > 0 ? $dms()->findDocumentById($documentId) : null;
    if (!is_array($document)) {
        $_SESSION['flash_error'] = 'DMS-Dokument wurde nicht gefunden.';
        $redirect('/dashboard/dms');
    }

    $reviewNote = $normalizeDmsReviewNote();
    $dms()->resetToDraft($documentId, (int) ($authUser['id'] ?? 0), $reviewNote);
    $_SESSION['flash_success'] = $reviewNote !== ''
        ? 'Dokument wurde mit Rückgabe-Kommentar wieder auf Draft gesetzt.'
        : 'Dokument wurde wieder auf Draft gesetzt.';
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

if (Request::method() === 'POST' && $action === 'dashboard.user.update') {
    $authUser = $requireAuth('/dashboard/users');
    $requireAdminUser($authUser, '/dashboard');

    $userId = (int) Request::post('user_id');
    $fallback = '/dashboard/users' . ($userId > 0 ? '?user=' . $userId : '');
    $requireCsrf($fallback);

    $targetUser = $userId > 0 ? $users()->findById($userId) : null;
    if (!is_array($targetUser)) {
        $_SESSION['flash_error'] = 'Benutzer wurde nicht gefunden.';
        $redirect('/dashboard/users');
    }

    $displayName = trim(Request::post('display_name'));
    $email = strtolower(trim(Request::post('email')));
    $role = $normalizeUserRole((string) Request::post('role'));
    $isActive = Request::post('is_active') === '1' || Request::post('is_active') === 'on';
    $errors = [];

    if ($displayName === '') {
        $errors['display_name'] = 'Bitte einen Namen angeben.';
    }
    if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Bitte eine gültige E-Mail-Adresse angeben.';
    } elseif ($users()->emailExistsForOtherUser($email, $userId)) {
        $errors['email'] = 'Diese E-Mail-Adresse wird bereits verwendet.';
    }

    $targetWasActiveAdmin = strtolower((string) ($targetUser['role'] ?? '')) === 'admin' && !empty($targetUser['is_active']);
    if ($targetWasActiveAdmin && (!$isActive || $role !== 'admin') && $users()->countActiveAdmins() <= 1) {
        $errors['role'] = 'Mindestens ein aktiver Admin muss erhalten bleiben.';
    }

    if ($errors !== []) {
        $_SESSION['flash_error'] = 'Bitte prüfen Sie die Benutzerdaten.';
        $_SESSION['flash_errors'] = $errors;
        $_SESSION['old'] = [
            'user_display_name' => $displayName,
            'user_email' => $email,
            'user_role' => $role,
            'user_is_active' => $isActive,
        ];
        $redirect($fallback);
    }

    $users()->updateUserAdmin($userId, $displayName, $email, $role, $isActive);
    $updatedTargetUser = $users()->findById($userId);
    if ((int) ($authUser['id'] ?? 0) === $userId && is_array($updatedTargetUser)) {
        AuthSession::login($updatedTargetUser);
    }

    $_SESSION['flash_success'] = 'Benutzerkonto wurde aktualisiert.';
    $redirect($fallback);
}

if ($path === '/login' && AuthSession::check()) {
    $redirect('/dashboard');
}

if ($isDashboardRoute) {
    $requireAuth($path . ($queryString !== '' ? '?' . $queryString : ''));
}

$dashboardAccessUser = AuthSession::user();
if ($dashboardSection === 'users' && is_array($dashboardAccessUser) && !$isAdminUser($dashboardAccessUser)) {
    $_SESSION['flash_error'] = 'Benutzerverwaltung ist nur für Admins verfügbar.';
    $redirect('/dashboard');
}

if ($path === '/dashboard/dms/download') {
    $requireAuth('/dashboard/dms/download');
    $versionId = isset($_GET['version']) ? (int) $_GET['version'] : 0;
    $version = $versionId > 0 ? $dms()->findVersionBinaryById($versionId) : null;
    if (!is_array($version) || (string) ($version['binary_content'] ?? '') === '') {
        http_response_code(404);
        echo 'Datei nicht gefunden.';
        exit;
    }

    $downloadFilename = preg_replace('/[^A-Za-z0-9._-]+/', '-', (string) ($version['original_filename'] ?? 'dokument')) ?? 'dokument';
    $downloadFilename = trim($downloadFilename, '-.');
    if ($downloadFilename === '') {
        $downloadFilename = 'dokument';
    }

    header('Content-Type: ' . (string) ($version['mime_type'] ?? 'application/octet-stream'));
    header('Content-Length: ' . (string) strlen((string) $version['binary_content']));
    header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');
    header('X-Content-Type-Options: nosniff');
    echo (string) $version['binary_content'];
    exit;
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
$dashboardInboxLeads = [];
$dashboardCampaigns = [];
$dashboardSelectedCampaign = null;
$dashboardOutreachRecipients = [];
$dashboardOutreachEvents = [];
$dashboardOutreachForm = [];
$dashboardCampaignFilter = (string) ($_GET['status'] ?? 'active');
$dashboardDmsDocuments = [];
$dashboardSelectedDmsDocument = null;
$dashboardDmsVersions = [];
$dashboardDmsEvents = [];
$dashboardDmsForm = [];
$dashboardDmsSearch = trim((string) ($_GET['q'] ?? ''));
$dashboardDmsStatusFilter = (string) ($_GET['status'] ?? 'all');
$dashboardReferences = [];
$dashboardSelectedReference = null;
$dashboardReferenceForm = [];
$dashboardUsers = [];
$dashboardSelectedUser = null;
$dashboardUserForm = [];
$dashboardStats = [
    'open_contacts' => 0,
    'references_total' => 0,
    'references_visible' => 0,
    'inbound_leads' => 0,
    'dms_total' => 0,
    'dms_in_review' => 0,
    'dms_approved' => 0,
    'outreach_drafts' => 0,
    'outreach_approved' => 0,
    'outreach_sent' => 0,
    'outreach_failed' => 0,
    'outreach_archived' => 0,
];
$dashboardProfileUser = is_array($authUser) ? $users()->findById((int) ($authUser['id'] ?? 0)) : null;
$dashboardCanApproveDms = is_array($authUser) ? $isDmsApprover($authUser) : false;
$dashboardCanManageUsers = is_array($authUser) ? $isAdminUser($authUser) : false;

if ($isDashboardRoute && is_array($authUser)) {
    try {
        $dashboardStats['open_contacts'] = $contacts()->countOpen();
        $dashboardReferences = $referencesRepo()->listAll();
        $dashboardStats['references_total'] = count($dashboardReferences);
        $dashboardStats['references_visible'] = count(array_filter($dashboardReferences, static fn (array $item): bool => !empty($item['is_visible'])));
        $dashboardStats['inbound_leads'] = $contacts()->countInbound();
        $dashboardStats['dms_total'] = $dms()->countDocuments();
        $dashboardStats['dms_in_review'] = $dms()->countDocumentsByStatus('in_review');
        $dashboardStats['dms_approved'] = $dms()->countDocumentsByStatus('approved');
        $dashboardStats['outreach_drafts'] = $outreach()->countDraftCampaigns();
        $dashboardStats['outreach_approved'] = $outreach()->countApprovedCampaigns();
        $dashboardStats['outreach_sent'] = $outreach()->countSentCampaigns();
        $dashboardStats['outreach_failed'] = $outreach()->countFailedCampaigns();
        $dashboardStats['outreach_archived'] = $outreach()->countArchivedCampaigns();

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

        if ($dashboardSection === 'inbox' || $dashboardSection === 'home') {
            $dashboardInboxLeads = $contacts()->listInboundLeads(25);
        }

        if ($dashboardSection === 'dms' || $dashboardSection === 'home') {
            $allowedDmsStatusFilters = ['all', 'draft', 'in_review', 'approved'];
            if (!in_array($dashboardDmsStatusFilter, $allowedDmsStatusFilters, true)) {
                $dashboardDmsStatusFilter = 'all';
            }

            if ($dashboardSection === 'dms') {
                $dashboardDmsDocuments = $dms()->listDocuments($dashboardDmsSearch, $dashboardDmsStatusFilter);
                $selectedDmsDocumentId = isset($_GET['document']) ? (int) $_GET['document'] : (isset($dashboardDmsDocuments[0]['id']) ? (int) $dashboardDmsDocuments[0]['id'] : 0);
                if ($selectedDmsDocumentId > 0) {
                    $dashboardSelectedDmsDocument = $dms()->findDocumentById($selectedDmsDocumentId);
                    if (is_array($dashboardSelectedDmsDocument)) {
                        $dashboardDmsVersions = $dms()->listVersions($selectedDmsDocumentId);
                        $dashboardDmsEvents = $dms()->listEvents($selectedDmsDocumentId);
                    }
                }

                $dashboardDmsForm = is_array($old) && isset($old['dms_title']) ? $old : (
                    is_array($dashboardSelectedDmsDocument)
                        ? [
                            'dms_title' => (string) $dashboardSelectedDmsDocument['title'],
                            'dms_category' => (string) $dashboardSelectedDmsDocument['category'],
                            'dms_summary' => (string) $dashboardSelectedDmsDocument['summary'],
                            'dms_change_note' => '',
                        ]
                        : [
                            'dms_title' => '',
                            'dms_category' => 'Allgemein',
                            'dms_summary' => '',
                            'dms_change_note' => '',
                        ]
                );
            }
        }

        if ($dashboardSection === 'outreach' || $dashboardSection === 'home') {
            $allowedCampaignFilters = ['active', 'all', 'draft', 'approved', 'sending', 'sent', 'partial', 'failed', 'archived'];
            if (!in_array($dashboardCampaignFilter, $allowedCampaignFilters, true)) {
                $dashboardCampaignFilter = 'active';
            }

            $dashboardCampaigns = $outreach()->listCampaigns($dashboardCampaignFilter);
            $selectedCampaignId = isset($_GET['campaign']) ? (int) $_GET['campaign'] : (isset($dashboardCampaigns[0]['id']) ? (int) $dashboardCampaigns[0]['id'] : 0);
            if ($selectedCampaignId > 0) {
                $dashboardSelectedCampaign = $outreach()->findCampaignById($selectedCampaignId);
                if (is_array($dashboardSelectedCampaign)) {
                    $dashboardOutreachRecipients = $outreach()->listRecipients($selectedCampaignId);
                    $dashboardOutreachEvents = $outreach()->listEvents($selectedCampaignId);
                }
            }

            $dashboardOutreachForm = is_array($old) && isset($old['recipients_raw']) ? $old : (
                is_array($dashboardSelectedCampaign)
                    ? [
                        'title' => (string) $dashboardSelectedCampaign['title'],
                        'subject' => (string) $dashboardSelectedCampaign['subject'],
                        'body' => (string) $dashboardSelectedCampaign['body'],
                        'from_email' => (string) $dashboardSelectedCampaign['from_email'],
                        'from_name' => (string) $dashboardSelectedCampaign['from_name'],
                        'allow_known_resend' => !empty($dashboardSelectedCampaign['allow_known_resend']),
                        'recipients_raw' => implode(PHP_EOL, array_map(
                            static fn (array $recipient): string => implode(' | ', array_filter([
                                (string) ($recipient['email'] ?? ''),
                                (string) ($recipient['company_name'] ?? ''),
                                (string) ($recipient['contact_name'] ?? ''),
                                (string) ($recipient['notes'] ?? ''),
                            ], static fn (string $value): bool => $value !== '')),
                            $dashboardOutreachRecipients
                        )),
                    ]
                    : [
                        'title' => '',
                        'subject' => '',
                        'body' => '',
                        'from_email' => trim((string) Env::get('MAIL_FROM_ADDRESS', 'info@rddigital.de')),
                        'from_name' => trim((string) Env::get('MAIL_FROM_NAME', 'RD Formstack Solutions')),
                        'allow_known_resend' => false,
                        'recipients_raw' => '',
                    ]
            );
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

        if ($dashboardSection === 'users' && $dashboardCanManageUsers) {
            $dashboardUsers = $users()->listUsers();
            $selectedUserId = isset($_GET['user']) ? (int) $_GET['user'] : (isset($dashboardUsers[0]['id']) ? (int) $dashboardUsers[0]['id'] : 0);
            if ($selectedUserId > 0) {
                $dashboardSelectedUser = $users()->findById($selectedUserId);
            }

            $dashboardUserForm = is_array($old) && isset($old['user_display_name']) ? $old : (
                is_array($dashboardSelectedUser)
                    ? [
                        'user_display_name' => (string) ($dashboardSelectedUser['display_name'] ?? ''),
                        'user_email' => (string) ($dashboardSelectedUser['email'] ?? ''),
                        'user_role' => (string) ($dashboardSelectedUser['role'] ?? 'editor'),
                        'user_is_active' => !empty($dashboardSelectedUser['is_active']),
                    ]
                    : []
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
    'dashboardCampaigns' => $dashboardCampaigns,
    'dashboardCampaignFilter' => $dashboardCampaignFilter,
    'dashboardCanApproveDms' => $dashboardCanApproveDms,
    'dashboardCanManageUsers' => $dashboardCanManageUsers,
    'dashboardContacts' => $dashboardContacts,
    'dashboardDmsDocuments' => $dashboardDmsDocuments,
    'dashboardDmsEvents' => $dashboardDmsEvents,
    'dashboardDmsForm' => $dashboardDmsForm,
    'dashboardDmsSearch' => $dashboardDmsSearch,
    'dashboardDmsStatusFilter' => $dashboardDmsStatusFilter,
    'dashboardDmsVersions' => $dashboardDmsVersions,
    'dashboardOutreachEvents' => $dashboardOutreachEvents,
    'dashboardOutreachForm' => $dashboardOutreachForm,
    'dashboardOutreachRecipients' => $dashboardOutreachRecipients,
    'dashboardProfileUser' => $dashboardProfileUser,
    'dashboardUserForm' => $dashboardUserForm,
    'dashboardUsers' => $dashboardUsers,
    'dashboardReferenceForm' => $dashboardReferenceForm,
    'dashboardReferences' => $dashboardReferences,
    'dashboardReplies' => $dashboardReplies,
    'dashboardSection' => $dashboardSection,
    'dashboardSelectedCampaign' => $dashboardSelectedCampaign,
    'dashboardSelectedContact' => $dashboardSelectedContact,
    'dashboardSelectedDmsDocument' => $dashboardSelectedDmsDocument,
    'dashboardSelectedReference' => $dashboardSelectedReference,
    'dashboardStats' => $dashboardStats,
    'dashboardInboxLeads' => $dashboardInboxLeads,
    'dashboardSelectedUser' => $dashboardSelectedUser,
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
