<?php

declare(strict_types=1);

namespace App\Http\Routing;

final class RouteCatalog
{
    /** @return array<string, array{title: string, headline: string, intro: string, description: string}> */
    public static function pages(): array
    {
        return [
            '/' => [
                'title' => 'Startseite',
                'headline' => 'Webentwicklung, Belegverwaltung und DMS für belastbare Geschäftsprozesse',
                'intro' => 'Wir unterstützen B2B-Teams dabei, manuelle Abläufe in robuste digitale Prozesse zu überführen: mit klarer Nutzerführung, wartbarer Architektur und messbarem operativem Nutzen.',
                'description' => 'RD Formstack Solutions entwickelt Weblösungen, digitale Workflows, Belegverwaltung und DMS-nahe Prozesse für den Mittelstand.',
            ],
            '/leistungen' => [
                'title' => 'Leistungen',
                'headline' => 'Leistungen für digitale Prozessstabilität',
                'intro' => 'Vier Leistungsbereiche für klare Verantwortung in Umsetzung, Betrieb und Weiterentwicklung.',
                'description' => 'Leistungsbereiche von RD Formstack Solutions: Webentwicklung, digitale Workflows, Belegverwaltung und DMS-Architektur.',
            ],
            '/referenzen' => [
                'title' => 'Referenzen',
                'headline' => 'Praxisnahe Referenzszenarien',
                'intro' => 'Typische Projektkontexte, in denen wir Webentwicklung und Dokumentenprozesse zusammenführen.',
                'description' => 'Ausgewählte Referenzszenarien für Webentwicklung, Prozessdigitalisierung und dokumentenbasierte Abläufe im Mittelstand.',
            ],
            '/kontakt' => [
                'title' => 'Kontakt',
                'headline' => 'Projekt unverbindlich besprechen',
                'intro' => 'Beschreiben Sie Ihr Vorhaben. Wir melden uns mit einer realistischen Einschätzung für die nächsten Schritte.',
                'description' => 'Kontaktieren Sie RD Formstack Solutions für ein unverbindliches Erstgespräch zu Website, Workflow und DMS-Projekten.',
            ],
            '/impressum' => [
                'title' => 'Impressum',
                'headline' => 'Impressum und Pflichtangaben',
                'intro' => 'Technische Basis für die Anbieterkennzeichnung von rddigital.de. Nicht verifizierte Pflichtangaben sind ausdrücklich als offene Felder markiert.',
                'description' => 'Technische Impressumsseite für rddigital.de mit verifizierbaren Basisangaben und klar markierten offenen Pflichtfeldern.',
            ],
            '/datenschutz' => [
                'title' => 'Datenschutzerklärung',
                'headline' => 'Datenschutzhinweise für diese Website',
                'intro' => 'Technisch abgeleitete Datenschutzhinweise für rddigital.de auf Basis des aktuellen Projektstands und der verifizierbaren Datenverarbeitung.',
                'description' => 'Datenschutzerklärung für rddigital.de mit Angaben zu Hosting, Kontaktformular, Session-Cookie und offenen Verantwortlichkeitsfeldern.',
            ],
            '/login' => [
                'title' => 'Login',
                'headline' => 'Geschützter Zugang für das Kundenportal',
                'intro' => 'Melden Sie sich an, um Dashboard, Referenzen-Verwaltung und eingehende Kontaktanfragen zentral zu bearbeiten.',
                'description' => 'Geschützter Login für das RD-Portal mit Admin-Zugang, Dashboard und weiteren Modulen für Inhalte und Kommunikation.',
            ],
            '/dashboard' => [
                'title' => 'Dashboard',
                'headline' => 'Dashboard für Inhalte, Anfragen und nächste Ausbaustufen',
                'intro' => 'Der geschützte Bereich bündelt Login, Profil, Postbox, Referenzen und weitere Module für die operative Pflege von rddigital.de.',
                'description' => 'Internes Dashboard für RD Formstack Solutions zur Verwaltung von Inhalten, Kontaktanfragen und Portal-Modulen.',
            ],
            '/dashboard/postbox' => [
                'title' => 'Postbox',
                'headline' => 'Kontaktanfragen in der Postbox bearbeiten',
                'intro' => 'Alle Anfragen aus dem Kontaktformular werden hier mit Status, Notizen und Antwortverlauf zentral bearbeitet.',
                'description' => 'Geschützte Postbox für eingehende Kontaktanfragen mit Statuspflege und E-Mail-Antworten.',
            ],
            '/dashboard/references' => [
                'title' => 'Referenzen verwalten',
                'headline' => 'Referenzen pflegen und live auf der Landingpage ausgeben',
                'intro' => 'Referenzen können hier angelegt, sortiert, ein- oder ausgeblendet und direkt für die öffentliche Darstellung vorbereitet werden.',
                'description' => 'Dashboard-Modul zur Verwaltung öffentlicher Referenzen auf rddigital.de.',
            ],
            '/dashboard/profile' => [
                'title' => 'Profil',
                'headline' => 'Persönliche Daten und Passwort pflegen',
                'intro' => 'Profil- und Zugangsdaten lassen sich hier direkt im geschützten Bereich aktualisieren.',
                'description' => 'Profilverwaltung für den geschützten Bereich von rddigital.de.',
            ],
            '/dms' => [
                'title' => 'DMS',
                'headline' => 'DMS-Bereich als technischer Platzhalter',
                'intro' => 'Die DMS-Fläche ist vorbereitet und wird stufenweise mit Freigabe, Historie und Suchlogik ergänzt.',
                'description' => 'DMS-Platzhalter mit geplanter Dokumentensuche, Revisionshistorie und Freigabeprozessen für strukturierte Ablagen.',
            ],
        ];
    }
}
