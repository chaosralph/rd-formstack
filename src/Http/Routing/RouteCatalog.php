<?php

declare(strict_types=1);

namespace App\Http\Routing;

final class RouteCatalog
{
    /** @return array<string, array{title: string, headline: string, intro: string}> */
    public static function pages(): array
    {
        return [
            '/' => [
                'title' => 'Startseite',
                'headline' => 'Webentwicklung, Belegverwaltung und DMS für belastbare Geschäftsprozesse',
                'intro' => 'Wir unterstützen B2B-Teams dabei, manuelle Abläufe in robuste digitale Prozesse zu überführen: mit klarer Nutzerführung, wartbarer Architektur und messbarem operativem Nutzen.',
            ],
            '/leistungen' => [
                'title' => 'Leistungen',
                'headline' => 'Leistungen für digitale Prozessstabilität',
                'intro' => 'Vier Leistungsbereiche für klare Verantwortung in Umsetzung, Betrieb und Weiterentwicklung.',
            ],
            '/referenzen' => [
                'title' => 'Referenzen',
                'headline' => 'Praxisnahe Referenzszenarien',
                'intro' => 'Typische Projektkontexte, in denen wir Webentwicklung und Dokumentenprozesse zusammenführen.',
            ],
            '/kontakt' => [
                'title' => 'Kontakt',
                'headline' => 'Projekt unverbindlich besprechen',
                'intro' => 'Beschreiben Sie Ihr Vorhaben. Wir melden uns mit einer realistischen Einschätzung für die nächsten Schritte.',
            ],
            '/login' => [
                'title' => 'Login',
                'headline' => 'Kundenportal in Vorbereitung',
                'intro' => 'Der geschützte Login-Bereich wird in der nächsten Ausbaustufe bereitgestellt.',
            ],
            '/dms' => [
                'title' => 'DMS',
                'headline' => 'DMS-Bereich als technischer Platzhalter',
                'intro' => 'Die DMS-Fläche wird in Stufen mit Freigabe, Historie und Suchlogik ergänzt.',
            ],
        ];
    }
}
