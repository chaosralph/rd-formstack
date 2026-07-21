<?php
$imapConfigured = trim((string) \App\Config\Env::get('IMAP_HOST', '')) !== ''
    && trim((string) \App\Config\Env::get('IMAP_USERNAME', '')) !== ''
    && trim((string) \App\Config\Env::get('IMAP_PASSWORD', '')) !== '';
$imapReady = $imapConfigured && class_exists(\Webklex\PHPIMAP\ClientManager::class);
$smtpConfigured = \App\Mail\NativeMailTransport::smtpConfigured();
$smtpReady = \App\Mail\NativeMailTransport::smtpReady();

$dashboardNavItems = [
    '/dashboard' => 'Übersicht',
    '/dashboard/postbox' => 'Postbox',
    '/dashboard/inbox' => 'Inbox',
    '/dashboard/outreach' => 'Outreach',
    '/dashboard/dms' => 'DMS',
    '/dashboard/references' => 'Referenzen',
    '/dashboard/profile' => 'Profil',
];
if (!empty($dashboardCanManageUsers)) {
    $dashboardNavItems['/dashboard/users'] = 'Benutzer';
}

$dashboardRoleLabels = [
    'admin' => 'Admin',
    'reviewer' => 'Reviewer',
    'editor' => 'Editor',
];
?>
<section class="section section-muted dashboard-shell-section" id="dashboard-app">
    <div class="shell dashboard-page-shell">
        <article class="service-card dashboard-hero-card">
            <p class="eyebrow">Geschützter Bereich</p>
            <h1><?= $e($page['headline']) ?></h1>
            <p><?= $e($page['intro']) ?></p>

            <?php if (is_string($flashSuccess)): ?>
                <div class="alert alert-success" role="status"><?= $e($flashSuccess) ?></div>
            <?php endif; ?>

            <?php if (is_string($flashError)): ?>
                <div class="alert alert-error" role="alert">
                    <p><?= $e($flashError) ?></p>
                    <?php if (is_array($flashErrors) && $flashErrors !== []): ?>
                        <ul class="alert-list">
                            <?php foreach ($flashErrors as $field => $errorItem): ?>
                                <?php if (is_string($errorItem)): ?>
                                    <li><strong><?= $e((string) $field) ?>:</strong> <?= $e($errorItem) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-toolbar">
                <span class="tag">Rolle: <?= $e((string) (($authUser['role'] ?? 'admin'))) ?></span>
                <span class="tag">E-Mail: <?= $e((string) (($authUser['email'] ?? ''))) ?></span>
                <span class="tag">Offene Anfragen: <?= $e((string) ($dashboardStats['open_contacts'] ?? 0)) ?></span>
                <span class="tag">IMAP-Leads: <?= $e((string) ($dashboardStats['inbound_leads'] ?? 0)) ?></span>
                <span class="tag">DMS gesamt: <?= $e((string) ($dashboardStats['dms_total'] ?? 0)) ?></span>
                <span class="tag">DMS in Review: <?= $e((string) ($dashboardStats['dms_in_review'] ?? 0)) ?></span>
                <span class="tag">DMS freigegeben: <?= $e((string) ($dashboardStats['dms_approved'] ?? 0)) ?></span>
                <span class="tag">Outreach-Entwürfe: <?= $e((string) ($dashboardStats['outreach_drafts'] ?? 0)) ?></span>
                <span class="tag">Outreach gesendet: <?= $e((string) ($dashboardStats['outreach_sent'] ?? 0)) ?></span>
                <span class="tag">Outreach mit Fehlern: <?= $e((string) ($dashboardStats['outreach_failed'] ?? 0)) ?></span>
                <span class="tag">Sichtbare Referenzen: <?= $e((string) ($dashboardStats['references_visible'] ?? 0)) ?></span>
            </div>
        </article>

        <nav class="dashboard-subnav" aria-label="Dashboard-Navigation">
            <?php foreach ($dashboardNavItems as $href => $label): ?>
                <?php $active = $path === $href ? ' is-active' : ''; ?>
                <a class="dashboard-subnav-link<?= $active ?>" href="<?= $e($href) ?>"<?= $path === $href ? ' aria-current="page"' : '' ?>><?= $e($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if ($dashboardSection === 'home'): ?>
            <div class="dashboard-grid">
                <article class="service-card dashboard-module-card">
                    <h2>Postbox</h2>
                    <p>Neue Kontaktanfragen zentral prüfen, kategorisieren, beantworten und mit internen Notizen versehen.</p>
                    <ul>
                        <li>Aktuell offen: <?= $e((string) ($dashboardStats['open_contacts'] ?? 0)) ?></li>
                        <li>Status-Workflow: neu, in Bearbeitung, beantwortet, archiviert</li>
                    </ul>
                    <a class="btn btn-primary" href="/dashboard/postbox">Postbox öffnen</a>
                </article>

                <article class="service-card dashboard-module-card">
                    <h2>Referenzen</h2>
                    <p>Referenzen im Backend pflegen und ohne Codeänderung auf der Landingpage ausgeben.</p>
                    <ul>
                        <li>Gesamt: <?= $e((string) ($dashboardStats['references_total'] ?? 0)) ?></li>
                        <li>Sichtbar: <?= $e((string) ($dashboardStats['references_visible'] ?? 0)) ?></li>
                    </ul>
                    <a class="btn btn-primary" href="/dashboard/references">Referenzen verwalten</a>
                </article>

                <article class="service-card dashboard-module-card">
                    <h2>Profil</h2>
                    <p>Zugangsdaten, Name und Passwort direkt im geschützten Bereich aktualisieren.</p>
                    <ul>
                        <li>Name: <?= $e((string) (($dashboardProfileUser['display_name'] ?? $authUser['display_name'] ?? ''))) ?></li>
                        <li>E-Mail: <?= $e((string) (($dashboardProfileUser['email'] ?? $authUser['email'] ?? ''))) ?></li>
                    </ul>
                    <a class="btn btn-primary" href="/dashboard/profile">Profil öffnen</a>
                </article>

                <article class="service-card dashboard-module-card">
                    <h2>Inbox</h2>
                    <p>IMAP-Mails als Leads lesen und mit einem Klick in die Postbox übernehmen.</p>
                    <ul>
                        <li>Importiert: <?= $e((string) ($dashboardStats['inbound_leads'] ?? 0)) ?></li>
                        <li>Sync im geschützten Bereich, ohne Formularspam zu vermischen.</li>
                    </ul>
                    <a class="btn btn-primary" href="/dashboard/inbox">Inbox öffnen</a>
                </article>

                <?php if (!empty($dashboardCanManageUsers)): ?>
                    <article class="service-card dashboard-module-card">
                        <h2>Benutzer</h2>
                        <p>Rollen, Aktiv-Status und Kontaktdaten direkt im Dashboard pflegen.</p>
                        <ul>
                            <li>Rollen: Admin, Reviewer, Editor</li>
                            <li>Freigaben im DMS laufen über Reviewer oder Admin</li>
                        </ul>
                        <a class="btn btn-primary" href="/dashboard/users">Benutzerverwaltung öffnen</a>
                    </article>
                <?php endif; ?>

                <article class="service-card dashboard-module-card">
                    <h2>Outreach</h2>
                    <p>Anschreiben und Empfängerliste erst freigeben, dann kontrolliert versenden.</p>
                    <ul>
                        <li>Entwürfe: <?= $e((string) ($dashboardStats['outreach_drafts'] ?? 0)) ?></li>
                        <li>Freigegeben: <?= $e((string) ($dashboardStats['outreach_approved'] ?? 0)) ?></li>
                        <li>Gesendet: <?= $e((string) ($dashboardStats['outreach_sent'] ?? 0)) ?></li>
                        <li>Fehler/Teilversand: <?= $e((string) ($dashboardStats['outreach_failed'] ?? 0)) ?></li>
                    </ul>
                    <a class="btn btn-primary" href="/dashboard/outreach">Outreach öffnen</a>
                </article>

                <article class="service-card dashboard-module-card">
                    <h2>DMS</h2>
                    <p>Dokumente versionieren, zur Freigabe einreichen und zentral mit Suchgrundgerüst verwalten.</p>
                    <ul>
                        <li>Dokumente: <?= $e((string) ($dashboardStats['dms_total'] ?? 0)) ?></li>
                        <li>In Review: <?= $e((string) ($dashboardStats['dms_in_review'] ?? 0)) ?></li>
                        <li>Freigegeben: <?= $e((string) ($dashboardStats['dms_approved'] ?? 0)) ?></li>
                    </ul>
                    <a class="btn btn-primary" href="/dashboard/dms">DMS öffnen</a>
                </article>
            </div>
        <?php elseif ($dashboardSection === 'postbox'): ?>
            <div class="dashboard-postbox-layout">
                <aside class="service-card dashboard-list-card">
                    <div class="dashboard-list-card-head">
                        <h2>Kontaktanfragen</h2>
                        <span class="tag"><?= $e((string) count($dashboardContacts)) ?> Einträge</span>
                    </div>
                    <?php if ($dashboardContacts === []): ?>
                        <p>Es liegen aktuell keine Kontaktanfragen vor.</p>
                    <?php else: ?>
                        <div class="dashboard-contact-list">
                            <?php foreach ($dashboardContacts as $contact): ?>
                                <a class="dashboard-contact-item<?= is_array($dashboardSelectedContact) && (int) $dashboardSelectedContact['id'] === (int) $contact['id'] ? ' is-active' : '' ?>" href="/dashboard/postbox?contact=<?= $e((string) $contact['id']) ?>">
                                    <div class="dashboard-contact-item-head">
                                        <strong><?= $e((string) $contact['name']) ?></strong>
                                        <span class="tag tag-status tag-status-<?= $e((string) $contact['status']) ?>"><?= $e((string) $contact['status']) ?></span>
                                    </div>
                                    <p><?= $e((string) $contact['email']) ?></p>
                                    <small><?= $e((string) $contact['created_at']) ?> · Antworten: <?= $e((string) $contact['reply_count']) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>

                <div class="dashboard-detail-stack">
                    <?php if (is_array($dashboardSelectedContact)): ?>
                        <article class="service-card dashboard-detail-card">
                            <div class="dashboard-detail-card-head">
                                <div>
                                    <h2><?= $e((string) $dashboardSelectedContact['name']) ?></h2>
                                    <p><?= $e((string) $dashboardSelectedContact['email']) ?><?php if (($dashboardSelectedContact['phone'] ?? '') !== ''): ?> · <?= $e((string) $dashboardSelectedContact['phone']) ?><?php endif; ?></p>
                                    <?php if (($dashboardSelectedContact['company'] ?? '') !== ''): ?><p>Unternehmen: <?= $e((string) $dashboardSelectedContact['company']) ?></p><?php endif; ?>
                                </div>
                                <span class="tag tag-status tag-status-<?= $e((string) $dashboardSelectedContact['status']) ?>"><?= $e((string) $dashboardSelectedContact['status']) ?></span>
                            </div>

                            <div class="dashboard-message-box">
                                <h3>Nachricht</h3>
                                <p><?= nl2br($e((string) $dashboardSelectedContact['message'])) ?></p>
                            </div>

                            <form method="post" action="/dashboard/postbox?contact=<?= $e((string) $dashboardSelectedContact['id']) ?>" class="auth-form-stack dashboard-form-stack">
                                <input type="hidden" name="_action" value="dashboard.contact.update_meta">
                                <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                <input type="hidden" name="contact_id" value="<?= $e((string) $dashboardSelectedContact['id']) ?>">

                                <label for="contact-status">Status</label>
                                <select id="contact-status" name="status">
                                    <?php foreach (['new' => 'Neu', 'in_progress' => 'In Bearbeitung', 'answered' => 'Beantwortet', 'archived' => 'Archiviert'] as $value => $label): ?>
                                        <option value="<?= $e($value) ?>"<?= (string) $dashboardSelectedContact['status'] === $value ? ' selected' : '' ?>><?= $e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <label for="contact-note">Interne Notiz</label>
                                <textarea id="contact-note" name="admin_note" rows="5"><?= $e((string) $dashboardSelectedContact['admin_note']) ?></textarea>

                                <button class="btn btn-ghost" type="submit">Status/Notiz speichern</button>
                            </form>
                        </article>

                        <article class="service-card dashboard-detail-card">
                            <h2>Per E-Mail antworten</h2>
                            <form method="post" action="/dashboard/postbox?contact=<?= $e((string) $dashboardSelectedContact['id']) ?>" class="auth-form-stack dashboard-form-stack">
                                <input type="hidden" name="_action" value="dashboard.contact.reply">
                                <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                <input type="hidden" name="contact_id" value="<?= $e((string) $dashboardSelectedContact['id']) ?>">

                                <label for="reply-subject">Betreff</label>
                                <input id="reply-subject" type="text" name="subject" required value="<?= $e((string) ($old['reply_subject'] ?? ('Ihre Anfrage bei RD Formstack Solutions'))) ?>">

                                <label for="reply-body">Antwort</label>
                                <textarea id="reply-body" name="body" rows="9" required><?= $e((string) ($old['reply_body'] ?? '')) ?></textarea>

                                <button class="btn btn-primary" type="submit">Antwort senden</button>
                            </form>
                        </article>

                        <article class="service-card dashboard-detail-card">
                            <div class="dashboard-list-card-head">
                                <h2>Antwortverlauf</h2>
                                <span class="tag"><?= $e((string) count($dashboardReplies)) ?> Antworten</span>
                            </div>
                            <?php if ($dashboardReplies === []): ?>
                                <p>Noch kein Antwortverlauf vorhanden.</p>
                            <?php else: ?>
                                <div class="dashboard-reply-history">
                                    <?php foreach ($dashboardReplies as $reply): ?>
                                        <article class="dashboard-reply-item">
                                            <div class="dashboard-reply-item-head">
                                                <strong><?= $e((string) $reply['subject']) ?></strong>
                                                <span class="tag<?= $reply['sent_success'] ? '' : ' tag-warning' ?>"><?= $reply['sent_success'] ? 'versendet' : 'fehlgeschlagen' ?></span>
                                            </div>
                                            <p>An: <?= $e((string) $reply['recipient_email']) ?> · Von: <?= $e((string) $reply['user_display_name']) ?></p>
                                            <p><?= nl2br($e((string) $reply['body'])) ?></p>
                                            <?php if (!$reply['sent_success'] && (string) $reply['error_message'] !== ''): ?>
                                                <p class="form-help form-help-error">Fehler: <?= $e((string) $reply['error_message']) ?></p>
                                            <?php endif; ?>
                                            <small><?= $e((string) ($reply['sent_at'] !== '' ? $reply['sent_at'] : $reply['created_at'])) ?></small>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php else: ?>
                        <article class="service-card dashboard-detail-card">
                            <h2>Keine Anfrage ausgewählt</h2>
                            <p>Wählen Sie links eine Kontaktanfrage aus, um Details, Notizen und den Antwortverlauf zu sehen.</p>
                        </article>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($dashboardSection === 'inbox'): ?>
            <div class="dashboard-postbox-layout dashboard-references-layout">
                <article class="service-card dashboard-detail-card">
                    <div class="dashboard-detail-card-head">
                        <div>
                            <h2>IMAP-Inbox synchronisieren</h2>
                            <p>Neue Nachrichten werden aus dem Postfach gelesen und als Leads in die Postbox übernommen.</p>
                        </div>
                        <span class="tag">IMAP</span>
                    </div>

                    <div class="dashboard-action-row" style="margin-top:12px">
                        <form method="post" action="/dashboard/inbox" class="dashboard-inline-danger-form" style="margin-top:0">
                            <input type="hidden" name="_action" value="dashboard.inbox.sync">
                            <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                            <button class="btn btn-primary" type="submit">Jetzt synchronisieren</button>
                        </form>
                        <span class="tag">Inbox-Leads: <?= $e((string) ($dashboardStats['inbound_leads'] ?? 0)) ?></span>
                        <span class="tag"><?= $imapReady ? 'IMAP bereit' : ($imapConfigured ? 'IMAP-Bibliothek fehlt' : 'IMAP unvollständig konfiguriert') ?></span>
                        <span class="tag"><?= $smtpReady ? 'SMTP bereit' : ($smtpConfigured ? 'SMTP unvollständig konfiguriert' : 'SMTP nicht konfiguriert') ?></span>
                    </div>

                    <p style="margin-top:12px">Konfiguration: Host, Port, Verschlüsselung, Benutzer und Passwort werden aus .env gelesen.</p>
                </article>

                <aside class="service-card dashboard-list-card">
                    <div class="dashboard-list-card-head">
                        <h2>Neueste importierte Leads</h2>
                        <span class="tag"><?= $e((string) count($dashboardInboxLeads)) ?> Einträge</span>
                    </div>
                    <?php if ($dashboardInboxLeads === []): ?>
                        <p>Noch keine importierten IMAP-Leads vorhanden.</p>
                    <?php else: ?>
                        <div class="dashboard-contact-list">
                            <?php foreach ($dashboardInboxLeads as $lead): ?>
                                <article class="dashboard-contact-item">
                                    <div class="dashboard-contact-item-head">
                                        <strong><?= $e((string) ($lead['name'] !== '' ? $lead['name'] : $lead['email'])) ?></strong>
                                        <span class="tag tag-status tag-status-<?= $e((string) $lead['status']) ?>"><?= $e((string) $lead['status']) ?></span>
                                    </div>
                                    <p><?= $e((string) $lead['email']) ?></p>
                                    <small>
                                        <?= $e((string) ($lead['source_received_at'] !== '' ? $lead['source_received_at'] : $lead['created_at'])) ?>
                                        · UID: <?= $e((string) $lead['source_uid']) ?>
                                    </small>
                                    <?php if (($lead['source_subject'] ?? '') !== ''): ?>
                                        <p><strong>Betreff:</strong> <?= $e((string) $lead['source_subject']) ?></p>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
        <?php elseif ($dashboardSection === 'outreach'): ?>
            <div class="dashboard-postbox-layout dashboard-references-layout">
                <aside class="service-card dashboard-list-card">
                    <div class="dashboard-list-card-head">
                        <h2>Outreach-Kampagnen</h2>
                        <a class="btn btn-ghost btn-sm" href="/dashboard/outreach">Neu</a>
                    </div>
                    <div class="dashboard-action-row" style="margin-bottom:12px">
                        <?php foreach (['active' => 'Aktiv', 'all' => 'Alle', 'draft' => 'Draft', 'approved' => 'Freigegeben', 'sent' => 'Gesendet', 'failed' => 'Fehler', 'archived' => 'Archiv'] as $filterValue => $filterLabel): ?>
                            <a class="btn <?= ($dashboardCampaignFilter ?? 'active') === $filterValue ? 'btn-primary' : 'btn-ghost' ?> btn-sm" href="/dashboard/outreach?status=<?= $e($filterValue) ?>"><?= $e($filterLabel) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($dashboardCampaigns === []): ?>
                        <p>Noch keine Outreach-Kampagnen vorhanden.</p>
                    <?php else: ?>
                        <div class="dashboard-contact-list">
                            <?php foreach ($dashboardCampaigns as $campaign): ?>
                                <a class="dashboard-contact-item<?= is_array($dashboardSelectedCampaign) && (int) $dashboardSelectedCampaign['id'] === (int) $campaign['id'] ? ' is-active' : '' ?>" href="/dashboard/outreach?status=<?= $e((string) ($dashboardCampaignFilter ?? 'active')) ?>&campaign=<?= $e((string) $campaign['id']) ?>">
                                    <div class="dashboard-contact-item-head">
                                        <strong><?= $e((string) $campaign['title']) ?></strong>
                                        <span class="tag tag-status tag-status-<?= $e((string) $campaign['status']) ?>"><?= $e((string) $campaign['status']) ?></span>
                                    </div>
                                    <p><?= $e((string) $campaign['subject']) ?></p>
                                    <small>
                                        Empfänger: <?= $e((string) $campaign['recipient_count']) ?>
                                        · Freigegeben: <?= $e((string) $campaign['approved_recipient_count']) ?>
                                        · Versendet: <?= $e((string) $campaign['sent_recipient_count']) ?>
                                        · Fehlgeschlagen: <?= $e((string) $campaign['failed_recipient_count']) ?>
                                    </small>
                                    <small>
                                        Läufe: <?= $e((string) ($campaign['send_attempt_count'] ?? 0)) ?>
                                        <?php if (($campaign['archived_at'] ?? '') !== ''): ?> · Archiviert: <?= $e((string) $campaign['archived_at']) ?><?php endif; ?>
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>

                <div class="dashboard-detail-stack">
                    <article class="service-card dashboard-detail-card">
                        <div class="dashboard-detail-card-head">
                            <div>
                                <h2><?= is_array($dashboardSelectedCampaign) ? 'Kampagne bearbeiten' : 'Neue Kampagne anlegen' ?></h2>
                                <p>Format Empfängerliste: E-Mail | Unternehmen | Ansprechpartner | Notiz</p>
                            </div>
                            <span class="tag"><?= $smtpReady ? 'SMTP bereit' : ($smtpConfigured ? 'SMTP unvollständig konfiguriert' : 'SMTP nicht konfiguriert') ?></span>
                        </div>

                        <form method="post" action="/dashboard/outreach<?= is_array($dashboardSelectedCampaign) ? '?status=' . $e((string) ($dashboardCampaignFilter ?? 'active')) . '&campaign=' . $e((string) $dashboardSelectedCampaign['id']) : '' ?>" class="auth-form-stack dashboard-form-stack">
                            <input type="hidden" name="_action" value="dashboard.outreach.save">
                            <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                            <input type="hidden" name="campaign_id" value="<?= $e((string) ($dashboardSelectedCampaign['id'] ?? 0)) ?>">

                            <label for="outreach-title">Kampagne</label>
                            <input id="outreach-title" type="text" name="title" required value="<?= $e((string) ($dashboardOutreachForm['title'] ?? '')) ?>">

                            <label for="outreach-subject">Betreff</label>
                            <input id="outreach-subject" type="text" name="subject" required value="<?= $e((string) ($dashboardOutreachForm['subject'] ?? '')) ?>">

                            <label for="outreach-from-name">Absendername</label>
                            <input id="outreach-from-name" type="text" name="from_name" required value="<?= $e((string) ($dashboardOutreachForm['from_name'] ?? '')) ?>">

                            <label for="outreach-from-email">Absender-E-Mail</label>
                            <input id="outreach-from-email" type="email" name="from_email" required value="<?= $e((string) ($dashboardOutreachForm['from_email'] ?? '')) ?>">

                            <label for="outreach-body">Anschreiben</label>
                            <textarea id="outreach-body" name="body" rows="10" required><?= $e((string) ($dashboardOutreachForm['body'] ?? '')) ?></textarea>

                            <label for="outreach-recipients">Empfängerliste</label>
                            <textarea id="outreach-recipients" name="recipients_raw" rows="10" required><?= $e((string) ($dashboardOutreachForm['recipients_raw'] ?? '')) ?></textarea>
                            <p class="form-help">Je Zeile: mail@example.de | Firma GmbH | Max Mustermann | optionale Notiz</p>

                            <label class="checkbox-row"><input type="checkbox" name="allow_known_resend" value="1"<?= !empty($dashboardOutreachForm['allow_known_resend']) ? ' checked' : '' ?>> <span>Bewusst auch Adressen zulassen, die bereits in einer anderen Kampagne angeschrieben wurden</span></label>

                            <div class="dashboard-action-row">
                                <button class="btn btn-primary" type="submit">Entwurf speichern</button>
                            </div>
                        </form>
                    </article>

                    <article class="service-card dashboard-detail-card">
                        <div class="dashboard-detail-card-head">
                            <div>
                                <h2>Freigabe und Versand</h2>
                                <p>Versand erst nach Freigabe von Anschreiben und Empfängerliste.</p>
                            </div>
                            <span class="tag"><?= is_array($dashboardSelectedCampaign) ? $e((string) ($dashboardSelectedCampaign['status'] ?? 'draft')) : 'draft' ?></span>
                        </div>

                        <?php if (is_array($dashboardSelectedCampaign)): ?>
                            <div class="dashboard-action-row" style="margin-bottom:12px">
                                <span class="tag">Empfänger: <?= $e((string) count($dashboardOutreachRecipients)) ?></span>
                                <span class="tag">Freigegeben: <?= $e((string) ($dashboardSelectedCampaign['approved_recipient_count'] ?? 0)) ?></span>
                                <span class="tag">Versendet: <?= $e((string) ($dashboardSelectedCampaign['sent_recipient_count'] ?? 0)) ?></span>
                                <span class="tag">Fehlgeschlagen: <?= $e((string) ($dashboardSelectedCampaign['failed_recipient_count'] ?? 0)) ?></span>
                                <span class="tag">Versandläufe: <?= $e((string) ($dashboardSelectedCampaign['send_attempt_count'] ?? 0)) ?></span>
                            </div>

                            <div class="dashboard-action-row" style="margin-bottom:12px">
                                <?php if (($dashboardSelectedCampaign['approved_at'] ?? '') !== ''): ?>
                                    <span class="tag">Freigabe: <?= $e((string) $dashboardSelectedCampaign['approved_at']) ?><?= ($dashboardSelectedCampaign['approved_by_display_name'] ?? '') !== '' ? ' · ' . $e((string) $dashboardSelectedCampaign['approved_by_display_name']) : '' ?></span>
                                <?php endif; ?>
                                <?php if (($dashboardSelectedCampaign['last_send_finished_at'] ?? '') !== ''): ?>
                                    <span class="tag">Letzter Lauf: <?= $e((string) $dashboardSelectedCampaign['last_send_finished_at']) ?><?= ($dashboardSelectedCampaign['last_sent_by_display_name'] ?? '') !== '' ? ' · ' . $e((string) $dashboardSelectedCampaign['last_sent_by_display_name']) : '' ?></span>
                                <?php endif; ?>
                                <?php if (!empty($dashboardSelectedCampaign['allow_known_resend'])): ?>
                                    <span class="tag tag-warning">Resend bewusst erlaubt</span>
                                <?php endif; ?>
                            </div>

                            <div class="dashboard-action-row">
                                <form method="post" action="/dashboard/outreach?status=<?= $e((string) ($dashboardCampaignFilter ?? 'active')) ?>&campaign=<?= $e((string) $dashboardSelectedCampaign['id']) ?>" style="margin-top:0">
                                    <input type="hidden" name="_action" value="dashboard.outreach.approve">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="campaign_id" value="<?= $e((string) $dashboardSelectedCampaign['id']) ?>">
                                    <button class="btn btn-ghost" type="submit">Anschreiben + Liste freigeben</button>
                                </form>

                                <form method="post" action="/dashboard/outreach?status=<?= $e((string) ($dashboardCampaignFilter ?? 'active')) ?>&campaign=<?= $e((string) $dashboardSelectedCampaign['id']) ?>" style="margin-top:0">
                                    <input type="hidden" name="_action" value="dashboard.outreach.send">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="campaign_id" value="<?= $e((string) $dashboardSelectedCampaign['id']) ?>">
                                    <button class="btn btn-primary" type="submit"<?= (string) ($dashboardSelectedCampaign['status'] ?? '') === 'approved' ? '' : ' disabled' ?>>Freigegeben jetzt versenden</button>
                                </form>

                                <form method="post" action="/dashboard/outreach?status=<?= $e((string) ($dashboardCampaignFilter ?? 'active')) ?>&campaign=<?= $e((string) $dashboardSelectedCampaign['id']) ?>" style="margin-top:0">
                                    <input type="hidden" name="_action" value="dashboard.outreach.retry_failed">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="campaign_id" value="<?= $e((string) $dashboardSelectedCampaign['id']) ?>">
                                    <button class="btn btn-ghost" type="submit"<?= (int) ($dashboardSelectedCampaign['failed_recipient_count'] ?? 0) > 0 ? '' : ' disabled' ?>>Fehlgeschlagene erneut freigeben</button>
                                </form>
                            </div>

                            <?php if (empty($dashboardCanApproveDms)): ?>
                                <p class="form-help">Dein Account kann Dokumente anlegen, hochladen und einreichen. Freigaben sind aktuell nur für Admins aktiv.</p>
                            <?php endif; ?>

                            <div class="dashboard-action-row" style="margin-top:12px">
                                <form method="post" action="/dashboard/outreach?status=<?= $e((string) ($dashboardCampaignFilter ?? 'active')) ?>&campaign=<?= $e((string) $dashboardSelectedCampaign['id']) ?>" style="margin-top:0">
                                    <input type="hidden" name="_action" value="dashboard.outreach.reset">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="campaign_id" value="<?= $e((string) $dashboardSelectedCampaign['id']) ?>">
                                    <button class="btn btn-ghost" type="submit">Auf Entwurf zurücksetzen</button>
                                </form>

                                <form method="post" action="/dashboard/outreach?status=<?= $e((string) ($dashboardCampaignFilter ?? 'active')) ?>&campaign=<?= $e((string) $dashboardSelectedCampaign['id']) ?>" style="margin-top:0">
                                    <input type="hidden" name="_action" value="dashboard.outreach.duplicate">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="campaign_id" value="<?= $e((string) $dashboardSelectedCampaign['id']) ?>">
                                    <button class="btn btn-ghost" type="submit">Als neuen Entwurf duplizieren</button>
                                </form>

                                <form method="post" action="/dashboard/outreach?status=<?= $e((string) ($dashboardCampaignFilter ?? 'active')) ?>&campaign=<?= $e((string) $dashboardSelectedCampaign['id']) ?>" style="margin-top:0">
                                    <input type="hidden" name="_action" value="dashboard.outreach.archive">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="campaign_id" value="<?= $e((string) $dashboardSelectedCampaign['id']) ?>">
                                    <button class="btn btn-danger" type="submit"<?= (string) ($dashboardSelectedCampaign['status'] ?? '') === 'archived' ? ' disabled' : '' ?>>Archivieren</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <p>Speichere zuerst einen Entwurf, damit Freigabe, Historie und Versand aktiviert werden.</p>
                        <?php endif; ?>
                    </article>

                    <article class="service-card dashboard-detail-card">
                        <div class="dashboard-list-card-head">
                            <h2>Empfängerliste</h2>
                            <span class="tag"><?= $e((string) count($dashboardOutreachRecipients)) ?> Einträge</span>
                        </div>
                        <?php if ($dashboardOutreachRecipients === []): ?>
                            <p>Noch keine Empfänger vorhanden.</p>
                        <?php else: ?>
                            <div class="dashboard-reply-history">
                                <?php foreach ($dashboardOutreachRecipients as $recipient): ?>
                                    <article class="dashboard-reply-item">
                                        <div class="dashboard-reply-item-head">
                                            <strong><?= $e((string) $recipient['email']) ?></strong>
                                            <span class="tag<?= (string) ($recipient['status'] ?? '') === 'failed' ? ' tag-warning' : '' ?>"><?= $e((string) $recipient['status']) ?></span>
                                        </div>
                                        <p>
                                            <?= $e((string) ($recipient['company_name'] !== '' ? $recipient['company_name'] : 'ohne Unternehmen')) ?>
                                            <?php if (($recipient['contact_name'] ?? '') !== ''): ?> · <?= $e((string) $recipient['contact_name']) ?><?php endif; ?>
                                        </p>
                                        <?php if (($recipient['notes'] ?? '') !== ''): ?>
                                            <p><?= nl2br($e((string) $recipient['notes'])) ?></p>
                                        <?php endif; ?>
                                        <?php if (($recipient['error_message'] ?? '') !== ''): ?>
                                            <p class="form-help form-help-error">Fehler: <?= $e((string) $recipient['error_message']) ?></p>
                                        <?php endif; ?>
                                        <small><?= $e((string) (($recipient['sent_at'] ?? '') !== '' ? $recipient['sent_at'] : $recipient['updated_at'])) ?></small>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>

                    <article class="service-card dashboard-detail-card">
                        <div class="dashboard-list-card-head">
                            <h2>Audit / Verlauf</h2>
                            <span class="tag"><?= $e((string) count($dashboardOutreachEvents ?? [])) ?> Ereignisse</span>
                        </div>
                        <?php if (($dashboardOutreachEvents ?? []) === []): ?>
                            <p>Noch keine Historie vorhanden.</p>
                        <?php else: ?>
                            <div class="dashboard-reply-history">
                                <?php foreach ($dashboardOutreachEvents as $event): ?>
                                    <article class="dashboard-reply-item">
                                        <div class="dashboard-reply-item-head">
                                            <strong><?= $e((string) ($event['summary'] ?? 'Ereignis')) ?></strong>
                                            <span class="tag"><?= $e((string) ($event['event_type'] ?? '')) ?></span>
                                        </div>
                                        <p><?= ($event['user_display_name'] ?? '') !== '' ? 'Von: ' . $e((string) $event['user_display_name']) : 'System' ?></p>
                                        <?php if (!empty($event['details']['recipient_count'])): ?>
                                            <p>Empfänger: <?= $e((string) $event['details']['recipient_count']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($event['details']['sent_count']) || !empty($event['details']['failed_count'])): ?>
                                            <p>Versendet: <?= $e((string) ($event['details']['sent_count'] ?? 0)) ?> · Fehlgeschlagen: <?= $e((string) ($event['details']['failed_count'] ?? 0)) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($event['details']['failed_emails']) && is_array($event['details']['failed_emails'])): ?>
                                            <p class="form-help form-help-error">Fehlgeschlagen: <?= $e(implode(', ', $event['details']['failed_emails'])) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($event['details']['source_campaign_id'])): ?>
                                            <p>Quelle: Kampagne #<?= $e((string) $event['details']['source_campaign_id']) ?></p>
                                        <?php endif; ?>
                                        <small><?= $e((string) ($event['created_at'] ?? '')) ?></small>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                </div>
            </div>
        <?php elseif ($dashboardSection === 'dms'): ?>
            <div class="dashboard-postbox-layout dashboard-references-layout">
                <aside class="service-card dashboard-list-card">
                    <div class="dashboard-list-card-head">
                        <h2>DMS-Dokumente</h2>
                        <a class="btn btn-ghost btn-sm" href="/dashboard/dms">Neu</a>
                    </div>
                    <form method="get" action="/dashboard/dms" class="auth-form-stack dashboard-form-stack" style="margin-top:0; gap:10px;">
                        <label for="dms-search">Suche</label>
                        <input id="dms-search" type="text" name="q" value="<?= $e((string) ($dashboardDmsSearch ?? '')) ?>" placeholder="Titel, Kategorie, Dateiname, Notiz">
                        <label for="dms-status">Status</label>
                        <select id="dms-status" name="status">
                            <?php foreach (['all' => 'Alle', 'draft' => 'Draft', 'in_review' => 'In Review', 'approved' => 'Freigegeben'] as $filterValue => $filterLabel): ?>
                                <option value="<?= $e($filterValue) ?>"<?= ($dashboardDmsStatusFilter ?? 'all') === $filterValue ? ' selected' : '' ?>><?= $e($filterLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary" type="submit">Filtern</button>
                    </form>
                    <div class="dashboard-action-row" style="margin:12px 0">
                        <span class="tag">Dokumente: <?= $e((string) ($dashboardStats['dms_total'] ?? 0)) ?></span>
                        <span class="tag">Review: <?= $e((string) ($dashboardStats['dms_in_review'] ?? 0)) ?></span>
                        <span class="tag">Freigegeben: <?= $e((string) ($dashboardStats['dms_approved'] ?? 0)) ?></span>
                    </div>
                    <?php if (($dashboardDmsDocuments ?? []) === []): ?>
                        <p>Noch keine DMS-Dokumente vorhanden.</p>
                    <?php else: ?>
                        <div class="dashboard-contact-list">
                            <?php foreach ($dashboardDmsDocuments as $document): ?>
                                <a class="dashboard-contact-item<?= is_array($dashboardSelectedDmsDocument) && (int) $dashboardSelectedDmsDocument['id'] === (int) $document['id'] ? ' is-active' : '' ?>" href="/dashboard/dms?status=<?= $e((string) ($dashboardDmsStatusFilter ?? 'all')) ?>&q=<?= urlencode((string) ($dashboardDmsSearch ?? '')) ?>&document=<?= $e((string) $document['id']) ?>">
                                    <div class="dashboard-contact-item-head">
                                        <strong><?= $e((string) $document['title']) ?></strong>
                                        <span class="tag tag-status tag-status-<?= $e((string) $document['status']) ?>"><?= $e((string) $document['status']) ?></span>
                                    </div>
                                    <p><?= $e((string) $document['category']) ?></p>
                                    <small>Versionen: <?= $e((string) ($document['version_count'] ?? 0)) ?> · Ereignisse: <?= $e((string) ($document['event_count'] ?? 0)) ?></small>
                                    <small>
                                        <?= $e((string) (($document['current_original_filename'] ?? '') !== '' ? $document['current_original_filename'] : 'noch keine Datei')) ?>
                                        <?php if ((int) ($document['current_version_number'] ?? 0) > 0): ?> · v<?= $e((string) $document['current_version_number']) ?><?php endif; ?>
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>

                <div class="dashboard-detail-stack">
                    <article class="service-card dashboard-detail-card">
                        <div class="dashboard-detail-card-head">
                            <div>
                                <h2><?= is_array($dashboardSelectedDmsDocument) ? 'DMS-Dokument bearbeiten' : 'Neues DMS-Dokument anlegen' ?></h2>
                                <p>Version 1 wird beim Anlegen direkt hochgeladen. Neue Uploads erzeugen automatisch neue Versionen.</p>
                            </div>
                            <span class="tag">Max. 15 MB pro Datei</span>
                        </div>

                        <?php if (is_array($dashboardSelectedDmsDocument)): ?>
                            <form method="post" action="/dashboard/dms?document=<?= $e((string) $dashboardSelectedDmsDocument['id']) ?>" class="auth-form-stack dashboard-form-stack">
                                <input type="hidden" name="_action" value="dashboard.dms.update_meta">
                                <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                <input type="hidden" name="document_id" value="<?= $e((string) $dashboardSelectedDmsDocument['id']) ?>">

                                <label for="dms-title">Titel</label>
                                <input id="dms-title" type="text" name="dms_title" required value="<?= $e((string) ($dashboardDmsForm['dms_title'] ?? '')) ?>">

                                <label for="dms-category">Kategorie</label>
                                <input id="dms-category" type="text" name="dms_category" required value="<?= $e((string) ($dashboardDmsForm['dms_category'] ?? '')) ?>">

                                <label for="dms-summary">Kurzbeschreibung</label>
                                <textarea id="dms-summary" name="dms_summary" rows="5"><?= $e((string) ($dashboardDmsForm['dms_summary'] ?? '')) ?></textarea>

                                <div class="dashboard-action-row">
                                    <button class="btn btn-primary" type="submit">Metadaten speichern</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <form method="post" action="/dashboard/dms" enctype="multipart/form-data" class="auth-form-stack dashboard-form-stack">
                                <input type="hidden" name="_action" value="dashboard.dms.create">
                                <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">

                                <label for="dms-title">Titel</label>
                                <input id="dms-title" type="text" name="dms_title" required value="<?= $e((string) ($dashboardDmsForm['dms_title'] ?? '')) ?>">

                                <label for="dms-category">Kategorie</label>
                                <input id="dms-category" type="text" name="dms_category" required value="<?= $e((string) ($dashboardDmsForm['dms_category'] ?? 'Allgemein')) ?>">

                                <label for="dms-summary">Kurzbeschreibung</label>
                                <textarea id="dms-summary" name="dms_summary" rows="5"><?= $e((string) ($dashboardDmsForm['dms_summary'] ?? '')) ?></textarea>

                                <label for="dms-change-note">Versionshinweis</label>
                                <input id="dms-change-note" type="text" name="dms_change_note" value="<?= $e((string) ($dashboardDmsForm['dms_change_note'] ?? 'Initiale Fassung')) ?>">

                                <label for="dms-file">Datei</label>
                                <input id="dms-file" type="file" name="document_file" required>
                                <p class="form-help">PDF, Office-Dateien, Bilder oder Textdokumente werden als erste Version im DMS gespeichert.</p>

                                <div class="dashboard-action-row">
                                    <button class="btn btn-primary" type="submit">Dokument anlegen + Version 1 hochladen</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </article>

                    <article class="service-card dashboard-detail-card">
                        <div class="dashboard-detail-card-head">
                            <div>
                                <h2>Versionierung und Freigabe</h2>
                                <p>Editoren erstellen und überarbeiten Dokumente, Reviewer oder Admins geben sie mit optionalem Kommentar frei oder zurück.</p>
                            </div>
                            <span class="tag"><?= is_array($dashboardSelectedDmsDocument) ? $e((string) ($dashboardSelectedDmsDocument['status'] ?? 'draft')) : 'draft' ?></span>
                        </div>

                        <?php if (is_array($dashboardSelectedDmsDocument)): ?>
                            <div class="dashboard-action-row" style="margin-bottom:12px">
                                <span class="tag">Versionen: <?= $e((string) ($dashboardSelectedDmsDocument['version_count'] ?? 0)) ?></span>
                                <span class="tag">Ereignisse: <?= $e((string) ($dashboardSelectedDmsDocument['event_count'] ?? 0)) ?></span>
                                <span class="tag">Rolle: <?= $e((string) ($dashboardRoleLabels[strtolower((string) ($authUser['role'] ?? 'editor'))] ?? (string) ($authUser['role'] ?? 'editor'))) ?></span>
                                <?php if (($dashboardSelectedDmsDocument['approved_at'] ?? '') !== ''): ?>
                                    <span class="tag">Freigabe: <?= $e((string) $dashboardSelectedDmsDocument['approved_at']) ?><?= ($dashboardSelectedDmsDocument['approved_by_display_name'] ?? '') !== '' ? ' · ' . $e((string) $dashboardSelectedDmsDocument['approved_by_display_name']) : '' ?></span>
                                <?php endif; ?>
                            </div>

                            <form method="post" action="/dashboard/dms?document=<?= $e((string) $dashboardSelectedDmsDocument['id']) ?>" enctype="multipart/form-data" class="auth-form-stack dashboard-form-stack">
                                <input type="hidden" name="_action" value="dashboard.dms.upload_version">
                                <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                <input type="hidden" name="document_id" value="<?= $e((string) $dashboardSelectedDmsDocument['id']) ?>">

                                <label for="dms-version-note">Versionshinweis</label>
                                <input id="dms-version-note" type="text" name="dms_change_note" value="<?= $e((string) ($dashboardDmsForm['dms_change_note'] ?? '')) ?>" placeholder="z. B. Korrektur nach Kundenfeedback">

                                <label for="dms-version-file">Neue Datei hochladen</label>
                                <input id="dms-version-file" type="file" name="document_file" required>

                                <div class="dashboard-action-row">
                                    <button class="btn btn-primary" type="submit">Neue Version hochladen</button>
                                </div>
                            </form>

                            <div class="dashboard-action-row" style="margin-top:12px; align-items:flex-start">
                                <form method="post" action="/dashboard/dms?document=<?= $e((string) $dashboardSelectedDmsDocument['id']) ?>" class="auth-form-stack dashboard-form-stack" style="margin-top:0; min-width:220px">
                                    <input type="hidden" name="_action" value="dashboard.dms.submit">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="document_id" value="<?= $e((string) $dashboardSelectedDmsDocument['id']) ?>">
                                    <label for="dms-submit-note">Review-Notiz für Einreichung</label>
                                    <input id="dms-submit-note" type="text" name="dms_review_note" placeholder="optional, z. B. bitte Rechtschreibung prüfen">
                                    <button class="btn btn-ghost" type="submit"<?= (int) ($dashboardSelectedDmsDocument['current_version_id'] ?? 0) > 0 ? '' : ' disabled' ?>>Zur Freigabe einreichen</button>
                                </form>

                                <form method="post" action="/dashboard/dms?document=<?= $e((string) $dashboardSelectedDmsDocument['id']) ?>" class="auth-form-stack dashboard-form-stack" style="margin-top:0; min-width:220px">
                                    <input type="hidden" name="_action" value="dashboard.dms.approve">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="document_id" value="<?= $e((string) $dashboardSelectedDmsDocument['id']) ?>">
                                    <label for="dms-approve-note">Freigabe-Kommentar</label>
                                    <input id="dms-approve-note" type="text" name="dms_review_note" placeholder="optional, z. B. freigegeben für Versand">
                                    <button class="btn btn-primary" type="submit"<?= ((string) ($dashboardSelectedDmsDocument['status'] ?? '') === 'in_review' && !empty($dashboardCanApproveDms)) ? '' : ' disabled' ?>>Freigeben</button>
                                </form>

                                <form method="post" action="/dashboard/dms?document=<?= $e((string) $dashboardSelectedDmsDocument['id']) ?>" class="auth-form-stack dashboard-form-stack" style="margin-top:0; min-width:220px">
                                    <input type="hidden" name="_action" value="dashboard.dms.reset">
                                    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="document_id" value="<?= $e((string) $dashboardSelectedDmsDocument['id']) ?>">
                                    <label for="dms-reset-note">Rückgabe-Kommentar</label>
                                    <input id="dms-reset-note" type="text" name="dms_review_note" placeholder="optional, z. B. Abschnitt 3 nachschärfen">
                                    <button class="btn btn-danger" type="submit"<?= !empty($dashboardCanApproveDms) ? '' : ' disabled' ?>>Auf Draft zurücksetzen</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <p>Lege zuerst ein Dokument an, damit Versionierung, Download und Freigabe aktiviert werden.</p>
                        <?php endif; ?>
                    </article>

                    <article class="service-card dashboard-detail-card">
                        <div class="dashboard-list-card-head">
                            <h2>Versionen</h2>
                            <span class="tag"><?= $e((string) count($dashboardDmsVersions ?? [])) ?> Einträge</span>
                        </div>
                        <?php if (($dashboardDmsVersions ?? []) === []): ?>
                            <p>Noch keine Versionen vorhanden.</p>
                        <?php else: ?>
                            <div class="dashboard-reply-history">
                                <?php foreach ($dashboardDmsVersions as $version): ?>
                                    <article class="dashboard-reply-item">
                                        <div class="dashboard-reply-item-head">
                                            <strong>Version <?= $e((string) $version['version_number']) ?></strong>
                                            <span class="tag"><?= $e((string) $version['mime_type']) ?></span>
                                        </div>
                                        <p><?= $e((string) $version['original_filename']) ?></p>
                                        <?php if (($version['change_note'] ?? '') !== ''): ?>
                                            <p><?= $e((string) $version['change_note']) ?></p>
                                        <?php endif; ?>
                                        <p>Größe: <?= $e((string) $version['file_size']) ?> Bytes<?= ($version['uploaded_by_display_name'] ?? '') !== '' ? ' · Von: ' . $e((string) $version['uploaded_by_display_name']) : '' ?></p>
                                        <div class="dashboard-action-row">
                                            <a class="btn btn-ghost btn-sm" href="/dashboard/dms/download?version=<?= $e((string) $version['id']) ?>">Download</a>
                                        </div>
                                        <small><?= $e((string) ($version['created_at'] ?? '')) ?></small>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>

                    <article class="service-card dashboard-detail-card">
                        <div class="dashboard-list-card-head">
                            <h2>Audit / Verlauf</h2>
                            <span class="tag"><?= $e((string) count($dashboardDmsEvents ?? [])) ?> Ereignisse</span>
                        </div>
                        <?php if (($dashboardDmsEvents ?? []) === []): ?>
                            <p>Noch keine Historie vorhanden.</p>
                        <?php else: ?>
                            <div class="dashboard-reply-history">
                                <?php foreach ($dashboardDmsEvents as $event): ?>
                                    <article class="dashboard-reply-item">
                                        <div class="dashboard-reply-item-head">
                                            <strong><?= $e((string) ($event['summary'] ?? 'Ereignis')) ?></strong>
                                            <span class="tag"><?= $e((string) ($event['event_type'] ?? '')) ?></span>
                                        </div>
                                        <p><?= ($event['user_display_name'] ?? '') !== '' ? 'Von: ' . $e((string) $event['user_display_name']) : 'System' ?></p>
                                        <?php if (!empty($event['details']['version_number'])): ?>
                                            <p>Version: <?= $e((string) $event['details']['version_number']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($event['details']['original_filename'])): ?>
                                            <p>Datei: <?= $e((string) $event['details']['original_filename']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($event['details']['review_note'])): ?>
                                            <p>Kommentar: <?= $e((string) $event['details']['review_note']) ?></p>
                                        <?php endif; ?>
                                        <small><?= $e((string) ($event['created_at'] ?? '')) ?></small>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                </div>
            </div>
        <?php elseif ($dashboardSection === 'references'): ?>
            <div class="dashboard-postbox-layout dashboard-references-layout">
                <aside class="service-card dashboard-list-card">
                    <div class="dashboard-list-card-head">
                        <h2>Referenzen</h2>
                        <a class="btn btn-ghost btn-sm" href="/dashboard/references?reference=new">Neu</a>
                    </div>
                    <div class="dashboard-contact-list">
                        <?php foreach ($dashboardReferences as $reference): ?>
                            <a class="dashboard-contact-item<?= is_array($dashboardSelectedReference) && (int) $dashboardSelectedReference['id'] === (int) $reference['id'] ? ' is-active' : '' ?>" href="/dashboard/references?reference=<?= $e((string) $reference['id']) ?>">
                                <div class="dashboard-contact-item-head">
                                    <strong><?= $e((string) $reference['title']) ?></strong>
                                    <span class="tag<?= !empty($reference['is_visible']) ? '' : ' tag-warning' ?>"><?= !empty($reference['is_visible']) ? 'sichtbar' : 'versteckt' ?></span>
                                </div>
                                <p><?= $e((string) $reference['industry']) ?></p>
                                <small>Sortierung: <?= $e((string) $reference['sort_order']) ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </aside>

                <article class="service-card dashboard-detail-card">
                    <h2><?= is_array($dashboardSelectedReference) ? 'Referenz bearbeiten' : 'Neue Referenz anlegen' ?></h2>
                    <form method="post" action="/dashboard/references<?= is_array($dashboardSelectedReference) ? '?reference=' . $e((string) $dashboardSelectedReference['id']) : '?reference=new' ?>" class="auth-form-stack dashboard-form-stack">
                        <input type="hidden" name="_action" value="dashboard.reference.save">
                        <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                        <input type="hidden" name="reference_id" value="<?= $e((string) ($dashboardSelectedReference['id'] ?? 0)) ?>">

                        <label for="reference-title">Titel</label>
                        <input id="reference-title" type="text" name="title" required value="<?= $e((string) ($dashboardReferenceForm['title'] ?? '')) ?>">

                        <label for="reference-industry">Branche</label>
                        <input id="reference-industry" type="text" name="industry" required value="<?= $e((string) ($dashboardReferenceForm['industry'] ?? '')) ?>">

                        <label for="reference-description">Beschreibung</label>
                        <textarea id="reference-description" name="description" rows="5" required><?= $e((string) ($dashboardReferenceForm['description'] ?? '')) ?></textarea>

                        <label for="reference-outcome">Ergebnis</label>
                        <textarea id="reference-outcome" name="outcome" rows="4" required><?= $e((string) ($dashboardReferenceForm['outcome'] ?? '')) ?></textarea>

                        <label for="reference-focus">Fokus-Punkte, je Zeile ein Eintrag</label>
                        <textarea id="reference-focus" name="focus_lines" rows="5" required><?= $e((string) ($dashboardReferenceForm['focus_lines'] ?? '')) ?></textarea>

                        <label for="reference-url">URL</label>
                        <input id="reference-url" type="url" name="url" required value="<?= $e((string) ($dashboardReferenceForm['url'] ?? '')) ?>">

                        <label for="reference-link-label">Button-Beschriftung</label>
                        <input id="reference-link-label" type="text" name="link_label" required value="<?= $e((string) ($dashboardReferenceForm['link_label'] ?? 'Zur Website')) ?>">

                        <label for="reference-sort-order">Sortierung</label>
                        <input id="reference-sort-order" type="number" name="sort_order" value="<?= $e((string) ($dashboardReferenceForm['sort_order'] ?? 10)) ?>">

                        <label class="checkbox-row"><input type="checkbox" name="is_visible" value="1"<?= !empty($dashboardReferenceForm['is_visible']) ? ' checked' : '' ?>> <span>Öffentlich sichtbar</span></label>

                        <div class="dashboard-action-row">
                            <button class="btn btn-primary" type="submit">Speichern</button>
                        </div>
                    </form>

                    <?php if (is_array($dashboardSelectedReference)): ?>
                        <form method="post" action="/dashboard/references" class="dashboard-inline-danger-form">
                            <input type="hidden" name="_action" value="dashboard.reference.delete">
                            <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                            <input type="hidden" name="reference_id" value="<?= $e((string) $dashboardSelectedReference['id']) ?>">
                            <button class="btn btn-danger" type="submit">Löschen</button>
                        </form>
                    <?php endif; ?>
                </article>
            </div>
        <?php elseif ($dashboardSection === 'profile'): ?>
            <div class="dashboard-grid dashboard-profile-grid">
                <article class="service-card dashboard-detail-card">
                    <h2>Profildaten</h2>
                    <form method="post" action="/dashboard/profile" class="auth-form-stack dashboard-form-stack">
                        <input type="hidden" name="_action" value="dashboard.profile.update">
                        <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">

                        <label for="profile-display-name">Name</label>
                        <input id="profile-display-name" type="text" name="display_name" required value="<?= $e((string) ($old['profile_display_name'] ?? ($dashboardProfileUser['display_name'] ?? $authUser['display_name'] ?? ''))) ?>">

                        <label for="profile-email">E-Mail</label>
                        <input id="profile-email" type="email" name="email" required value="<?= $e((string) ($old['profile_email'] ?? ($dashboardProfileUser['email'] ?? $authUser['email'] ?? ''))) ?>">

                        <button class="btn btn-primary" type="submit">Profil speichern</button>
                    </form>
                </article>

                <article class="service-card dashboard-detail-card">
                    <h2>Passwort ändern</h2>
                    <form method="post" action="/dashboard/profile" class="auth-form-stack dashboard-form-stack">
                        <input type="hidden" name="_action" value="dashboard.password.update">
                        <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">

                        <label for="current-password">Aktuelles Passwort</label>
                        <input id="current-password" type="password" name="current_password" required>

                        <label for="new-password">Neues Passwort</label>
                        <input id="new-password" type="password" name="new_password" required>

                        <label for="new-password-confirmation">Neues Passwort bestätigen</label>
                        <input id="new-password-confirmation" type="password" name="new_password_confirmation" required>

                        <button class="btn btn-primary" type="submit">Passwort aktualisieren</button>
                    </form>
                </article>
            </div>
        <?php elseif ($dashboardSection === 'users' && !empty($dashboardCanManageUsers)): ?>
            <div class="dashboard-postbox-layout dashboard-references-layout">
                <aside class="service-card dashboard-list-card">
                    <div class="dashboard-list-card-head">
                        <h2>Benutzer</h2>
                        <span class="tag"><?= $e((string) count($dashboardUsers ?? [])) ?> Konten</span>
                    </div>
                    <?php if (($dashboardUsers ?? []) === []): ?>
                        <p>Noch keine Benutzer vorhanden.</p>
                    <?php else: ?>
                        <div class="dashboard-contact-list">
                            <?php foreach ($dashboardUsers as $dashboardUser): ?>
                                <?php $dashboardUserRole = strtolower((string) ($dashboardUser['role'] ?? 'editor')); ?>
                                <a class="dashboard-contact-item<?= is_array($dashboardSelectedUser) && (int) $dashboardSelectedUser['id'] === (int) $dashboardUser['id'] ? ' is-active' : '' ?>" href="/dashboard/users?user=<?= $e((string) $dashboardUser['id']) ?>">
                                    <div class="dashboard-contact-item-head">
                                        <strong><?= $e((string) ($dashboardUser['display_name'] ?? 'Benutzer')) ?></strong>
                                        <span class="tag<?= !empty($dashboardUser['is_active']) ? '' : ' tag-warning' ?>"><?= !empty($dashboardUser['is_active']) ? 'aktiv' : 'inaktiv' ?></span>
                                    </div>
                                    <p><?= $e((string) ($dashboardUser['email'] ?? '')) ?></p>
                                    <small>Rolle: <?= $e((string) ($dashboardRoleLabels[$dashboardUserRole] ?? $dashboardUserRole)) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>

                <article class="service-card dashboard-detail-card">
                    <?php if (is_array($dashboardSelectedUser)): ?>
                        <div class="dashboard-detail-card-head">
                            <div>
                                <h2>Benutzer bearbeiten</h2>
                                <p>Rollen steuern DMS-Freigaben: Editor erstellt, Reviewer/Admin gibt frei.</p>
                            </div>
                            <span class="tag">ID: <?= $e((string) $dashboardSelectedUser['id']) ?></span>
                        </div>

                        <form method="post" action="/dashboard/users?user=<?= $e((string) $dashboardSelectedUser['id']) ?>" class="auth-form-stack dashboard-form-stack">
                            <input type="hidden" name="_action" value="dashboard.user.update">
                            <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
                            <input type="hidden" name="user_id" value="<?= $e((string) $dashboardSelectedUser['id']) ?>">

                            <label for="user-display-name">Name</label>
                            <input id="user-display-name" type="text" name="display_name" required value="<?= $e((string) ($dashboardUserForm['user_display_name'] ?? ($dashboardSelectedUser['display_name'] ?? ''))) ?>">

                            <label for="user-email">E-Mail</label>
                            <input id="user-email" type="email" name="email" required value="<?= $e((string) ($dashboardUserForm['user_email'] ?? ($dashboardSelectedUser['email'] ?? ''))) ?>">

                            <label for="user-role">Rolle</label>
                            <select id="user-role" name="role">
                                <?php $selectedRole = strtolower((string) ($dashboardUserForm['user_role'] ?? ($dashboardSelectedUser['role'] ?? 'editor'))); ?>
                                <?php foreach ($dashboardRoleLabels as $roleValue => $roleLabel): ?>
                                    <option value="<?= $e($roleValue) ?>"<?= $selectedRole === $roleValue ? ' selected' : '' ?>><?= $e($roleLabel) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label class="checkbox-row"><input type="checkbox" name="is_active" value="1"<?= !empty($dashboardUserForm['user_is_active']) ? ' checked' : '' ?>> <span>Konto aktiv</span></label>

                            <div class="dashboard-action-row">
                                <button class="btn btn-primary" type="submit">Benutzer speichern</button>
                            </div>
                        </form>

                        <div class="dashboard-action-row" style="margin-top:12px">
                            <span class="tag">Letzter Login: <?= $e((string) (($dashboardSelectedUser['last_login_at'] ?? '') !== '' ? $dashboardSelectedUser['last_login_at'] : 'noch keiner')) ?></span>
                            <span class="tag">Aktuelle Rolle: <?= $e((string) ($dashboardRoleLabels[strtolower((string) ($dashboardSelectedUser['role'] ?? 'editor'))] ?? ($dashboardSelectedUser['role'] ?? 'editor'))) ?></span>
                        </div>
                    <?php else: ?>
                        <h2>Kein Benutzer ausgewählt</h2>
                        <p>Wählen Sie links einen Benutzer aus, um Rolle und Aktiv-Status zu pflegen.</p>
                    <?php endif; ?>
                </article>
            </div>
        <?php endif; ?>
    </div>
</section>
