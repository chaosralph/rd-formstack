<?php
$dashboardNavItems = [
    '/dashboard' => 'Übersicht',
    '/dashboard/postbox' => 'Postbox',
    '/dashboard/references' => 'Referenzen',
    '/dashboard/profile' => 'Profil',
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

                <article class="service-card dashboard-module-card placeholder-card">
                    <h2>DMS</h2>
                    <p>Der DMS-Bereich bleibt als nächster Ausbaupfad sichtbar und später direkt an dieses Dashboard angedockt.</p>
                    <a class="btn btn-ghost" href="/dms">DMS-Platzhalter öffnen</a>
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
        <?php endif; ?>
    </div>
</section>
